# 🛠️ Services — Lazismu Tulungagung Backend

Folder ini berisi dua **microservice Node.js** yang berjalan terpisah dari PHP.

---

## 📸 1. Image Converter (`image-converter/`)

Mengkonversi gambar upload menjadi **WebP** otomatis menggunakan [Sharp](https://sharp.pixelplumbing.com/).

### Cara Menjalankan
```bash
cd services/image-converter
cp .env.example .env   # Edit sesuai kebutuhan
npm install
npm start              # Berjalan di port 3001
```

### Endpoints
| Method | URL | Keterangan |
|--------|-----|------------|
| GET    | `/health` | Cek status service |
| POST   | `/convert` | Upload + konversi gambar (form-data) |
| POST   | `/convert-path` | Konversi file yang sudah ada di disk (JSON body) |

### Headers
```
x-api-token: RAHASIAPIXELYOGA
```

---

## 💬 2. WhatsApp Bot (`wa-bot/`)

Bot WhatsApp menggunakan [Baileys](https://github.com/WhiskeySockets/Baileys) (gratis, scan QR).

### Cara Menjalankan
```bash
cd services/wa-bot
cp .env.example .env   # Edit: ADMIN_WA, PHP_BASE_URL, BOT_SECRET
npm install
npm start              # Scan QR muncul di terminal → berjalan di port 3002
```

### Fitur Bot
| Perintah User | Fungsi |
|---|---|
| `halo` / `hai` | Pesan sambutan |
| `bantuan` / `menu` | Daftar perintah |
| `#program` | Daftar program donasi aktif |
| `cek INVOICE123` | Cek status donasi |

| Perintah Admin | Fungsi |
|---|---|
| `!jawab\|kode_user pesan` | Balas pesan user |
| `!laporan` | Laporan donasi hari ini |
| `!help` | Menu admin |

### API Endpoints (untuk PHP)
| Method | URL | Keterangan |
|--------|-----|------------|
| GET    | `/health` | Cek status service |
| POST   | `/send` | Kirim pesan bebas |
| POST   | `/notify/donasi-baru` | Notif donasi baru ke admin |
| POST   | `/notify/konfirmasi` | Notif konfirmasi ke donatur |
| POST   | `/notify/pesan-user` | Notif pesan user ke admin |

### Headers
```
x-bot-token: RAHASIAPIXELYOGA
```

### Scheduler Otomatis
- **10:00 WIB** — Kirim daftar donasi belum dikonfirmasi ke admin
- **20:00 WIB** — Kirim laporan harian ke admin

---

## 🔒 Catatan Keamanan
- Ganti semua `RAHASIAPIXELYOGA` dengan token acak yang kuat di production.
- Jangan expose port 3001 dan 3002 ke publik. Gunakan firewall / reverse proxy.
- Folder `sessions/` berisi sesi WhatsApp. Jangan dibagikan atau di-commit ke Git.

---

## 🚀 Deploy ke Hosting

Gunakan **PM2** untuk menjalankan service secara permanen:
```bash
npm install -g pm2
pm2 start services/image-converter/index.js --name image-converter
pm2 start services/wa-bot/index.js --name wa-bot
pm2 save
pm2 startup   # Agar berjalan otomatis saat server restart
```
