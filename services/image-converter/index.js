require('dotenv').config();
const express = require('express');
const multer  = require('multer');
const sharp   = require('sharp');
const path    = require('path');
const fs      = require('fs');

const app  = express();
const PORT = process.env.PORT || 3001;
const TOKEN = process.env.API_TOKEN || 'RAHASIAPIXELYOGA';

// ─── Upload ke memori (bukan disk) ───────────────────────────────────────────
const upload = multer({
  storage: multer.memoryStorage(),
  limits:  { fileSize: 20 * 1024 * 1024 }, // 20 MB
  fileFilter: (_req, file, cb) => {
    const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/tiff'];
    cb(null, allowed.includes(file.mimetype));
  },
});

// ─── Middleware Auth ──────────────────────────────────────────────────────────
function authMiddleware(req, res, next) {
  const token = req.headers['x-api-token'] || req.query.token;
  if (token !== TOKEN) {
    return res.status(401).json({ success: false, message: 'Unauthorized' });
  }
  next();
}

// ─── Health Check ─────────────────────────────────────────────────────────────
app.get('/health', (_req, res) => {
  res.json({ success: true, service: 'image-converter', status: 'running' });
});

// ─── Endpoint: Konversi Gambar ke WebP ───────────────────────────────────────
// POST /convert
// Headers: x-api-token: <TOKEN>
// Form-data: file=<gambar>, output_dir=<path folder tujuan>, quality=<0-100>
app.post('/convert', authMiddleware, upload.single('file'), async (req, res) => {
  if (!req.file) {
    return res.status(400).json({ success: false, message: 'Tidak ada file yang di-upload.' });
  }

  const outputDir = req.body.output_dir;
  if (!outputDir) {
    return res.status(400).json({ success: false, message: 'Parameter output_dir wajib diisi.' });
  }

  // Pastikan folder tujuan ada
  if (!fs.existsSync(outputDir)) {
    try { fs.mkdirSync(outputDir, { recursive: true }); }
    catch (e) {
      return res.status(500).json({ success: false, message: `Gagal membuat folder: ${e.message}` });
    }
  }

  const quality    = parseInt(req.body.quality || '80', 10);
  const origName   = path.parse(req.file.originalname).name
                        .replace(/[^a-zA-Z0-9_\-]/g, '_')
                        .substring(0, 100);
  const timestamp  = Date.now();
  const filename   = `${origName}_${timestamp}.webp`;
  const outputPath = path.join(outputDir, filename);

  try {
    const info = await sharp(req.file.buffer)
      .webp({ quality })
      .toFile(outputPath);

    const originalSize = req.file.size;
    const savedSize    = info.size;
    const savedPercent = (((originalSize - savedSize) / originalSize) * 100).toFixed(1);

    console.log(`[CONVERT] ${req.file.originalname} → ${filename} (${savedPercent}% lebih kecil)`);

    res.json({
      success:        true,
      filename,
      path:           outputPath,
      original_size:  originalSize,
      webp_size:      savedSize,
      saved_percent:  savedPercent,
      width:          info.width,
      height:         info.height,
    });
  } catch (err) {
    console.error('[CONVERT ERROR]', err.message);
    res.status(500).json({ success: false, message: `Konversi gagal: ${err.message}` });
  }
});

// ─── Endpoint: Konversi dari path file lokal (sudah tersimpan) ───────────────
// POST /convert-path
// Body JSON: { file_path, output_dir, quality, delete_original }
app.use(express.json());
app.post('/convert-path', authMiddleware, async (req, res) => {
  const { file_path, output_dir, quality = 80, delete_original = false } = req.body;

  if (!file_path || !fs.existsSync(file_path)) {
    return res.status(400).json({ success: false, message: 'file_path tidak valid atau file tidak ditemukan.' });
  }

  const destDir  = output_dir || path.dirname(file_path);
  const origName = path.parse(file_path).name.replace(/[^a-zA-Z0-9_\-]/g, '_').substring(0, 100);
  const filename   = `${origName}_${Date.now()}.webp`;
  const outputPath = path.join(destDir, filename);

  try {
    const info = await sharp(file_path).webp({ quality }).toFile(outputPath);
    const originalSize = fs.statSync(file_path).size;
    const savedPercent = (((originalSize - info.size) / originalSize) * 100).toFixed(1);

    if (delete_original) {
      fs.unlinkSync(file_path);
    }

    console.log(`[CONVERT-PATH] ${path.basename(file_path)} → ${filename} (${savedPercent}% lebih kecil)`);

    res.json({
      success:        true,
      filename,
      path:           outputPath,
      original_size:  originalSize,
      webp_size:      info.size,
      saved_percent:  savedPercent,
    });
  } catch (err) {
    console.error('[CONVERT-PATH ERROR]', err.message);
    res.status(500).json({ success: false, message: `Konversi gagal: ${err.message}` });
  }
});

// ─── Start Server ─────────────────────────────────────────────────────────────
app.listen(PORT, () => {
  console.log(`✅ Image Converter Service berjalan di port ${PORT}`);
  console.log(`   Health check: http://localhost:${PORT}/health`);
  console.log(`   Endpoint    : POST http://localhost:${PORT}/convert`);
});
