<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Pengaturan Website";

// Ambil nilai dari database
$result_total = $mysqli->query("SELECT nilai_pengaturan FROM pengaturan WHERE nama_pengaturan = 'total_donasi_disalurkan'");
$total_donasi_disalurkan = $result_total->fetch_assoc()['nilai_pengaturan'] ?? '0';

$result_wa = $mysqli->query("SELECT nilai_pengaturan FROM pengaturan WHERE nama_pengaturan = 'admin_wa_number'");
$admin_wa_number = $result_wa->fetch_assoc()['nilai_pengaturan'] ?? '';

// Ambil URL wa-bot dari config
$wa_bot_url = getenv('WA_BOT_URL') ?: 'http://localhost:3002';
$wa_bot_token = getenv('BOT_SECRET') ?: 'RAHASIAPIXELYOGA';

require_once 'templates/header_admin.php';
?>

<style>
/* ─── WA Bot Card ─────────────────────────────────────────────── */
.wa-status-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
    flex-shrink: 0;
}
.wa-status-dot.connected    { background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.2); animation: pulse-green 2s infinite; }
.wa-status-dot.connecting   { background: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,.2); animation: pulse-amber 1.5s infinite; }
.wa-status-dot.disconnected { background: #ef4444; }

@keyframes pulse-green { 0%,100%{box-shadow:0 0 0 3px rgba(16,185,129,.2)} 50%{box-shadow:0 0 0 6px rgba(16,185,129,.05)} }
@keyframes pulse-amber { 0%,100%{box-shadow:0 0 0 3px rgba(245,158,11,.2)} 50%{box-shadow:0 0 0 6px rgba(245,158,11,.05)} }

.tab-btn { border-bottom: 2px solid transparent; padding: .5rem 1.25rem; font-weight: 600; font-size: .875rem; color: #6b7280; transition: all .2s; cursor: pointer; }
.tab-btn.active { border-color: #f97316; color: #f97316; }
.tab-panel { display: none; }
.tab-panel.active { display: block; }

.pairing-code-box {
    font-family: 'Courier New', monospace;
    font-size: 2.5rem;
    font-weight: 800;
    letter-spacing: .4rem;
    color: #f97316;
    background: linear-gradient(135deg, #fff7ed, #ffedd5);
    border: 2px dashed #f97316;
    border-radius: 1rem;
    padding: 1.25rem 2rem;
    text-align: center;
    user-select: all;
}
.qr-wrapper {
    background: #fff;
    border: 2px solid #e5e7eb;
    border-radius: 1rem;
    padding: 1.5rem;
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: .75rem;
}
.qr-wrapper img { border-radius: .5rem; max-width: 240px; }

.spin { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

#qr-timer-bar {
    height: 4px;
    background: linear-gradient(90deg, #f97316, #fb923c);
    border-radius: 2px;
    width: 100%;
    transition: width 1s linear;
}
</style>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-bold text-dark-text"><?= $page_title ?></h1>
        <p class="text-gray-500 text-sm mt-1">Kelola konfigurasi website dan tautkan akun WhatsApp bot</p>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert-success mt-4"><?= $_SESSION['success_message'] ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert-danger mt-4"><?= $_SESSION['error_message'] ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">

        <!-- ── Pengaturan Umum ─────────────────────────────────────── -->
        <div class="content-card">
            <h3 class="card-title mb-4">⚙️ Pengaturan Umum</h3>
            <form action="proses_pengaturan.php" method="POST" class="space-y-4">
                <div>
                    <label for="total_donasi_disalurkan" class="form-label">Total Donasi Disalurkan (Rp)</label>
                    <input type="text" class="form-input" id="total_donasi_disalurkan" name="total_donasi_disalurkan"
                        value="<?= number_format($total_donasi_disalurkan, 0, ',', '.') ?>">
                </div>
                <div>
                    <label for="admin_wa_number" class="form-label">Nomor WA Admin (penerima notifikasi)</label>
                    <input type="text" class="form-input" id="admin_wa_number" name="admin_wa_number"
                        value="<?= htmlspecialchars($admin_wa_number) ?>" placeholder="6281234567890">
                    <p class="text-xs text-gray-400 mt-1">Format internasional, diawali 62 tanpa tanda + atau spasi.</p>
                </div>
                <button type="submit" name="simpan_umum" class="btn-primary w-full">💾 Simpan Pengaturan</button>
            </form>
        </div>

        <!-- ── Status & Ringkasan Bot ─────────────────────────────── -->
        <div class="content-card">
            <h3 class="card-title mb-4">📊 Status Sistem</h3>
            <!-- Status WA Bot -->
            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 mb-3">
                <div class="flex items-center">
                    <span class="wa-status-dot disconnected" id="status-dot-wa"></span>
                    <span class="text-sm font-medium text-gray-700">WhatsApp Bot</span>
                </div>
                <span id="status-text-wa" class="text-xs font-semibold text-gray-400">Memeriksa…</span>
            </div>
            <!-- Status Image Converter -->
            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 mb-3">
                <div class="flex items-center">
                    <span class="wa-status-dot disconnected" id="status-dot-img"></span>
                    <span class="text-sm font-medium text-gray-700">Image Converter</span>
                </div>
                <span id="status-text-img" class="text-xs font-semibold text-gray-400">Memeriksa…</span>
            </div>
            <div class="mt-4 p-3 rounded-xl bg-orange-50 border border-orange-100 text-xs text-orange-700">
                <strong>📌 URL Bot:</strong> <code><?= $wa_bot_url ?></code><br>
                Pastikan service Node.js sudah berjalan (<code>npm start</code> di folder <code>services/wa-bot/</code>)
            </div>
        </div>
    </div>

    <!-- ── Panel Tautkan WhatsApp Bot ─────────────────────────────── -->
    <div class="content-card mt-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <div>
                <h3 class="card-title">📱 Tautkan Akun WhatsApp Bot</h3>
                <p class="text-sm text-gray-500 mt-1">Pilih metode yang paling mudah untuk Anda</p>
            </div>
            <!-- Badge status besar -->
            <div id="wa-badge" class="flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 text-sm font-semibold text-gray-500 self-start sm:self-auto">
                <span class="wa-status-dot disconnected" id="badge-dot"></span>
                <span id="badge-text">Tidak Terhubung</span>
            </div>
        </div>

        <!-- Tab pilih metode -->
        <div class="flex border-b border-gray-200 mb-5">
            <button class="tab-btn active" data-tab="qr">📷 Scan QR Code</button>
            <button class="tab-btn" data-tab="phone">📞 Kode Nomor HP</button>
        </div>

        <!-- ─── Tab: QR Code ──────────────────────────────────────── -->
        <div class="tab-panel active" id="tab-qr">
            <div class="flex flex-col md:flex-row gap-6 items-start">
                <!-- QR Display -->
                <div class="flex-shrink-0">
                    <div class="qr-wrapper" id="qr-wrapper">
                        <!-- Placeholder state -->
                        <div id="qr-placeholder" class="flex flex-col items-center gap-3 p-4 text-gray-400 text-sm" style="min-width:240px; min-height:240px; justify-content:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                            <span id="qr-status-text">Memuat QR Code…</span>
                        </div>
                        <img id="qr-image" src="" alt="QR WhatsApp" class="hidden" style="max-width:240px; border-radius:.5rem;">
                        <!-- Timer bar -->
                        <div id="qr-timer-wrapper" class="hidden w-full mt-1">
                            <div style="height:4px; background:#f3f4f6; border-radius:2px; overflow:hidden;">
                                <div id="qr-timer-bar" style="width:100%;"></div>
                            </div>
                            <p class="text-xs text-gray-400 text-center mt-1">QR akan expire dalam <span id="qr-countdown">60</span>s — <button class="text-orange-500 hover:underline font-semibold" id="btn-refresh-qr">Segarkan</button></p>
                        </div>
                    </div>
                </div>

                <!-- Instruksi -->
                <div class="flex-1 space-y-4">
                    <h4 class="font-bold text-gray-700">Cara Scan QR Code:</h4>
                    <ol class="space-y-3 text-sm text-gray-600">
                        <li class="flex gap-3 items-start"><span class="flex-shrink-0 w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center font-bold text-xs">1</span> Buka WhatsApp pada nomor bot yang akan digunakan</li>
                        <li class="flex gap-3 items-start"><span class="flex-shrink-0 w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center font-bold text-xs">2</span> Ketuk ikon <strong>⋮ (tiga titik)</strong> → <strong>Perangkat Tertaut</strong></li>
                        <li class="flex gap-3 items-start"><span class="flex-shrink-0 w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center font-bold text-xs">3</span> Ketuk <strong>Tautkan Perangkat</strong> dan arahkan kamera ke QR di sebelah kiri</li>
                        <li class="flex gap-3 items-start"><span class="flex-shrink-0 w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center font-bold text-xs">4</span> Tunggu hingga status berubah menjadi <strong class="text-green-600">✅ Terhubung</strong></li>
                    </ol>

                    <div class="flex gap-3 mt-4 flex-wrap">
                        <button id="btn-reset-session" class="px-4 py-2 rounded-xl border-2 border-red-200 text-red-600 hover:bg-red-50 text-sm font-semibold transition-colors">
                            🔄 Reset Sesi & Minta QR Baru
                        </button>
                    </div>
                    <div id="reset-status" class="hidden text-sm mt-2"></div>
                </div>
            </div>
        </div>

        <!-- ─── Tab: Pairing via Nomor HP ──────────────────────────── -->
        <div class="tab-panel" id="tab-phone">
            <div class="flex flex-col md:flex-row gap-6 items-start">
                <!-- Input & Kode -->
                <div class="flex-1 space-y-4">
                    <div>
                        <label class="form-label">Nomor HP WhatsApp Bot</label>
                        <div class="flex gap-2 mt-1">
                            <input type="tel" id="pairing-phone" class="form-input" placeholder="Contoh: 628123456789" style="flex:1;">
                            <button id="btn-get-pairing" class="px-5 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm transition-colors whitespace-nowrap">
                                Dapatkan Kode
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Masukkan nomor WA bot (diawali 62, tanpa + atau spasi)</p>
                    </div>

                    <div id="pairing-result" class="hidden space-y-3">
                        <div class="pairing-code-box" id="pairing-code-text">— — — — — —</div>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p class="font-semibold">Cara memasukkan kode:</p>
                            <ol class="list-decimal ml-4 space-y-1 text-gray-500">
                                <li>Buka WhatsApp di nomor bot</li>
                                <li>Ketuk <strong>⋮</strong> → <strong>Perangkat Tertaut</strong> → <strong>Tautkan dengan Nomor Telepon</strong></li>
                                <li>Masukkan kode di atas sebelum habis masa berlakunya</li>
                            </ol>
                        </div>
                    </div>
                    <div id="pairing-status" class="text-sm hidden"></div>
                </div>

                <!-- Ilustrasi -->
                <div class="flex-shrink-0 hidden md:flex flex-col items-center gap-3 opacity-60 select-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                    <p class="text-xs text-gray-400 text-center">Tautkan via<br>nomor HP</p>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
(function () {
    const WA_BOT  = '<?= rtrim($wa_bot_url, '/') ?>';
    const TOKEN   = '<?= $wa_bot_token ?>';
    const IMG_URL = 'http://localhost:3001';

    const headers = { 'Content-Type': 'application/json', 'x-bot-token': TOKEN, 'x-api-key': TOKEN };

    // ── Helpers ─────────────────────────────────────────────────────────────
    function setDot(dotEl, textEl, status, msg) {
        dotEl.className = `wa-status-dot ${status}`;
        textEl.textContent = msg;
        textEl.className = `text-xs font-semibold ${status === 'connected' ? 'text-green-600' : status === 'connecting' ? 'text-amber-500' : 'text-red-500'}`;
    }

    function setBadge(status, msg) {
        const dot  = document.getElementById('badge-dot');
        const text = document.getElementById('badge-text');
        dot.className  = `wa-status-dot ${status}`;
        text.textContent = msg;
        const badge = document.getElementById('wa-badge');
        badge.className = `flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold self-start sm:self-auto ${
            status === 'connected'    ? 'bg-green-50 text-green-700' :
            status === 'connecting'   ? 'bg-amber-50 text-amber-700' :
                                        'bg-red-50 text-red-600'
        }`;
    }

    // ── Cek Status Layanan ────────────────────────────────────────────────
    async function checkServices() {
        // WA Bot
        try {
            const r = await fetch(`${WA_BOT}/status?token=${encodeURIComponent(TOKEN)}`);
            const j = await r.json();
            const d = j?.data ?? {};
            const st = d.status ?? 'disconnected';
            const msg = d.connected ? `✅ ${d.number || 'Terhubung'}` : st === 'connecting' ? '⏳ Menghubungkan…' : '❌ Terputus';
            setDot(document.getElementById('status-dot-wa'), document.getElementById('status-text-wa'), st, msg);
            setBadge(st, d.connected ? `Terhubung (${d.number})` : st === 'connecting' ? 'Sedang Menghubungkan…' : 'Tidak Terhubung');
        } catch {
            setDot(document.getElementById('status-dot-wa'), document.getElementById('status-text-wa'), 'disconnected', '⚠️ Service Mati');
            setBadge('disconnected', 'Service Mati');
        }
        // Image Converter
        try {
            const r = await fetch(`${IMG_URL}/health`);
            const j = await r.json();
            setDot(document.getElementById('status-dot-img'), document.getElementById('status-text-img'), 'connected', '✅ Berjalan');
        } catch {
            setDot(document.getElementById('status-dot-img'), document.getElementById('status-text-img'), 'disconnected', '⚠️ Service Mati');
        }
    }

    // ── QR Code Logic ─────────────────────────────────────────────────────
    const qrImage      = document.getElementById('qr-image');
    const qrPlaceholder = document.getElementById('qr-placeholder');
    const qrStatusText = document.getElementById('qr-status-text');
    const qrTimerWrap  = document.getElementById('qr-timer-wrapper');
    const qrTimerBar   = document.getElementById('qr-timer-bar');
    const qrCountdown  = document.getElementById('qr-countdown');

    let qrCountdownInterval = null;

    function showQR(dataUrl) {
        qrImage.src = dataUrl;
        qrImage.classList.remove('hidden');
        qrPlaceholder.classList.add('hidden');
        qrTimerWrap.classList.remove('hidden');
        startQRTimer(55);
    }

    function hideQR(msg) {
        qrImage.classList.add('hidden');
        qrTimerWrap.classList.add('hidden');
        qrPlaceholder.classList.remove('hidden');
        qrStatusText.textContent = msg;
        clearInterval(qrCountdownInterval);
    }

    function startQRTimer(seconds) {
        clearInterval(qrCountdownInterval);
        let remaining = seconds;
        qrTimerBar.style.width = '100%';
        qrCountdown.textContent = remaining;
        qrCountdownInterval = setInterval(() => {
            remaining--;
            qrCountdown.textContent = remaining;
            qrTimerBar.style.width = `${(remaining / seconds) * 100}%`;
            if (remaining <= 0) {
                clearInterval(qrCountdownInterval);
                hideQR('QR expired. Klik "Segarkan" untuk mendapatkan QR baru.');
                fetchQR();
            }
        }, 1000);
    }

    async function fetchQR() {
        hideQR('Memuat QR Code…');
        try {
            const r = await fetch(`${WA_BOT}/qr?token=${encodeURIComponent(TOKEN)}`);
            const j = await r.json();
            if (j?.success && j?.data?.qr) {
                showQR(j.data.qr);
            } else {
                hideQR(j?.message || 'QR belum tersedia. Pastikan bot berjalan.');
            }
        } catch {
            hideQR('⚠️ Tidak bisa terhubung ke wa-bot service.');
        }
    }

    document.getElementById('btn-refresh-qr').addEventListener('click', fetchQR);

    // ── Reset Sesi ────────────────────────────────────────────────────────
    document.getElementById('btn-reset-session').addEventListener('click', async function () {
        if (!confirm('Reset sesi akan memutuskan WhatsApp bot saat ini. Lanjutkan?')) return;
        const btn = this;
        const statusEl = document.getElementById('reset-status');
        btn.disabled = true;
        btn.textContent = '⏳ Mereset…';
        statusEl.classList.remove('hidden');
        statusEl.textContent = 'Mengirim permintaan reset…';
        statusEl.className = 'text-sm text-amber-600';
        try {
            const r = await fetch(`${WA_BOT}/reset`, { method: 'POST', headers, body: JSON.stringify({ secret: TOKEN }) });
            const j = await r.json();
            if (j?.success) {
                statusEl.textContent = '✅ Reset berhasil. Menunggu QR baru…';
                statusEl.className = 'text-sm text-green-600';
                setTimeout(fetchQR, 3000);
                setTimeout(checkServices, 3000);
            } else {
                statusEl.textContent = '❌ ' + (j?.message || 'Gagal reset');
                statusEl.className = 'text-sm text-red-500';
            }
        } catch {
            statusEl.textContent = '⚠️ Tidak bisa menghubungi wa-bot service.';
            statusEl.className = 'text-sm text-red-500';
        }
        btn.disabled = false;
        btn.textContent = '🔄 Reset Sesi & Minta QR Baru';
    });

    // ── Pairing Code ──────────────────────────────────────────────────────
    document.getElementById('btn-get-pairing').addEventListener('click', async function () {
        const phone    = document.getElementById('pairing-phone').value.trim();
        const result   = document.getElementById('pairing-result');
        const codeText = document.getElementById('pairing-code-text');
        const statusEl = document.getElementById('pairing-status');

        if (!phone || !/^62\d{8,13}$/.test(phone)) {
            statusEl.textContent = '❌ Nomor tidak valid. Gunakan format 62xxx (diawali 62).';
            statusEl.className = 'text-sm text-red-500';
            statusEl.classList.remove('hidden');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.textContent = '⏳ Meminta kode…';
        result.classList.add('hidden');
        statusEl.classList.remove('hidden');
        statusEl.textContent = 'Menghubungi server WhatsApp…';
        statusEl.className = 'text-sm text-amber-600';

        try {
            const r = await fetch(`${WA_BOT}/pair`, {
                method: 'POST',
                headers,
                body: JSON.stringify({ phone, token: TOKEN, secret: TOKEN }),
            });
            const j = await r.json();
            if (j?.success && j?.data?.code) {
                codeText.textContent = j.data.code;
                result.classList.remove('hidden');
                statusEl.textContent = '✅ Kode berhasil didapat. Masukkan di WhatsApp sebelum expired!';
                statusEl.className = 'text-sm text-green-600';
            } else {
                statusEl.textContent = '❌ ' + (j?.message || 'Gagal mendapatkan kode. Pastikan bot belum terhubung.');
                statusEl.className = 'text-sm text-red-500';
            }
        } catch {
            statusEl.textContent = '⚠️ Tidak bisa menghubungi wa-bot service. Pastikan sudah dijalankan.';
            statusEl.className = 'text-sm text-red-500';
        }
        btn.disabled = false;
        btn.textContent = 'Dapatkan Kode';
    });

    // ── Tab Switcher ──────────────────────────────────────────────────────
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('tab-' + this.dataset.tab).classList.add('active');
        });
    });

    // ── Format Rupiah Input ───────────────────────────────────────────────
    document.getElementById('total_donasi_disalurkan').addEventListener('keyup', function (e) {
        const v = e.target.value.replace(/[^\d]/g, '');
        e.target.value = new Intl.NumberFormat('id-ID').format(v);
    });

    // ── Init ──────────────────────────────────────────────────────────────
    checkServices();
    fetchQR();
    // Auto-refresh status setiap 10 detik
    setInterval(checkServices, 10000);
})();
</script>

<?php require_once 'templates/footer_admin.php'; ?>