const {
  default: makeWASocket,
  DisconnectReason,
  useMultiFileAuthState,
  fetchLatestBaileysVersion,
  makeCacheableSignalKeyStore,
  isJidBroadcast,
} = require('@whiskeysockets/baileys');
const pino   = require('pino');
const qr     = require('qrcode-terminal');
const QRCode = require('qrcode');
const path   = require('path');
const fs     = require('fs');
const { handleMessage } = require('./handlers/messageHandler');

const SESSION_DIR = path.join(__dirname, 'sessions');

// ─── State Global yang bisa diakses endpoint HTTP ─────────────────────────────
const botState = {
  sock:        null,
  status:      'disconnected',  // 'disconnected' | 'connecting' | 'connected'
  qrDataUrl:   null,
  qrTimestamp: null,
  connectedNum: null,
};

/**
 * Mulai koneksi ke WhatsApp.
 */
async function connectToWhatsApp() {
  const { state, saveCreds } = await useMultiFileAuthState(SESSION_DIR);
  const { version } = await fetchLatestBaileysVersion();

  const logger = pino({ level: 'silent' });

  botState.status = 'connecting';
  botState.qrDataUrl = null;

  const sock = makeWASocket({
    version,
    logger,
    printQRInTerminal: false,
    auth: {
      creds: state.creds,
      keys: makeCacheableSignalKeyStore(state.keys, logger),
    },
    generateHighQualityLinkPreview: true,
    getMessage: async () => undefined,
  });

  botState.sock = sock;

  // ─── Event: Update Koneksi ─────────────────────────────────────────────────
  sock.ev.on('connection.update', async (update) => {
    const { connection, lastDisconnect, qr: qrCode } = update;

    if (qrCode) {
      // Konversi QR string ke Data URL (gambar PNG base64) agar bisa ditampilkan di browser
      try {
        botState.qrDataUrl   = await QRCode.toDataURL(qrCode, { width: 300, margin: 2 });
        botState.qrTimestamp = new Date().toISOString();
      } catch (e) {
        console.error('[BOT] QR generate error:', e.message);
      }
      console.log('\n📱 QR Code tersedia di endpoint /qr\n');
      // Tampilkan juga di terminal sebagai fallback
      qr.generate(qrCode, { small: true });
    }

    if (connection === 'close') {
      botState.status     = 'disconnected';
      botState.qrDataUrl  = null;
      botState.connectedNum = null;

      const statusCode    = lastDisconnect?.error?.output?.statusCode;
      const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

      if (shouldReconnect) {
        console.log(`🔄 Koneksi terputus (kode: ${statusCode}). Reconnect dalam 5 detik...`);
        setTimeout(connectToWhatsApp, 5000);
      } else {
        console.log('🚫 Sesi logout. Hapus folder sessions/ dan restart bot.');
        process.exit(1);
      }
    }

    if (connection === 'open') {
      botState.status      = 'connected';
      botState.qrDataUrl   = null; // QR tidak diperlukan lagi
      botState.connectedNum = sock.user?.id?.split(':')[0] ?? 'unknown';
      console.log(`✅ WhatsApp Bot terhubung! Nomor: ${botState.connectedNum}`);
    }
  });

  // ─── Event: Simpan Credentials ────────────────────────────────────────────
  sock.ev.on('creds.update', saveCreds);

  // ─── Event: Pesan Masuk ───────────────────────────────────────────────────
  sock.ev.on('messages.upsert', async ({ messages, type }) => {
    if (type !== 'notify') return;
    for (const msg of messages) {
      if (msg.key.fromMe)                    continue;
      if (isJidBroadcast(msg.key.remoteJid)) continue;
      await handleMessage(sock, msg);
    }
  });

  return sock;
}

/**
 * Kirim pesan teks ke nomor tertentu.
 */
async function sendMessage(number, text) {
  if (!botState.sock || botState.status !== 'connected') {
    throw new Error('Bot belum terhubung ke WhatsApp.');
  }
  const jid = number.includes('@') ? number : `${number}@s.whatsapp.net`;
  await botState.sock.sendMessage(jid, { text });
  console.log(`[BOT] Pesan terkirim ke ${number}`);
}

/**
 * Minta pairing code lewat nomor HP.
 * @param {string} phoneNumber - Format internasional, misal 628123456789
 */
async function requestPairingCode(phoneNumber) {
  if (!botState.sock) throw new Error('Bot belum diinisialisasi.');
  if (botState.status === 'connected') throw new Error('Bot sudah terhubung. Disconnect dulu jika ingin ganti nomor.');

  const clean = phoneNumber.replace(/[^0-9]/g, '');
  const code  = await botState.sock.requestPairingCode(clean);
  console.log(`[BOT] Pairing code untuk ${clean}: ${code}`);
  return code;
}

/**
 * Reset sesi (logout & hapus folder sessions).
 */
async function resetSession() {
  if (botState.sock) {
    try { await botState.sock.logout(); } catch (_) {}
    botState.sock = null;
  }
  // Hapus folder sessions
  if (fs.existsSync(SESSION_DIR)) {
    fs.rmSync(SESSION_DIR, { recursive: true, force: true });
    fs.mkdirSync(SESSION_DIR, { recursive: true });
  }
  botState.status      = 'disconnected';
  botState.qrDataUrl   = null;
  botState.connectedNum = null;
  console.log('[BOT] Sesi direset. Memulai koneksi ulang...');
  setTimeout(connectToWhatsApp, 1000);
}

module.exports = { connectToWhatsApp, sendMessage, requestPairingCode, resetSession, botState };
