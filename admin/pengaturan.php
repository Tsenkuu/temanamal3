<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Pengaturan Website";

// Ambil nilai saat ini dari database
$result_total = $mysqli->query("SELECT nilai_pengaturan FROM pengaturan WHERE nama_pengaturan = 'total_donasi_disalurkan'");
$total_donasi_disalurkan = $result_total->fetch_assoc()['nilai_pengaturan'] ?? '0';

$result_wa = $mysqli->query("SELECT nilai_pengaturan FROM pengaturan WHERE nama_pengaturan = 'admin_wa_number'");
$admin_wa_number = $result_wa->fetch_assoc()['nilai_pengaturan'] ?? '';

require_once 'templates/header_admin.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-bold text-dark-text"><?php echo $page_title; ?></h1>
    </div>

    <?php
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert-danger">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <div class="content-card">
            <h3 class="card-title mb-4">Pengaturan Umum</h3>
            <form action="proses_pengaturan.php" method="POST" class="space-y-4">
                <div>
                    <label for="total_donasi_disalurkan" class="form-label">Total Donasi yang Telah Disalurkan
                        (Rp)</label>
                    <input type="text" class="form-input" id="total_donasi_disalurkan" name="total_donasi_disalurkan"
                        value="<?php echo number_format($total_donasi_disalurkan, 0, ',', '.'); ?>">
                </div>
                <div>
                    <label for="admin_wa_number" class="form-label">Nomor WA Admin untuk Notifikasi</label>
                    <input type="text" class="form-input" id="admin_wa_number" name="admin_wa_number"
                        value="<?php echo htmlspecialchars($admin_wa_number); ?>" placeholder="Contoh: 6281234567890">
                    <p class="text-xs text-gray-500 mt-1">Gunakan format internasional (diawali 62).</p>
                </div>
                <button type="submit" name="simpan_umum" class="btn-primary">Simpan Pengaturan</button>
            </form>
        </div>

        <div class="content-card">
            <h3 class="card-title mb-4">Pengaturan Bot WhatsApp</h3>
            <p class="text-gray-600 mb-4">Gunakan tombol di bawah untuk memeriksa koneksi atau menautkan ulang bot ke nomor baru.</p>
            <div class="mb-4 flex items-center">
                <button id="check-api-status" class="btn-secondary">Cek Koneksi API</button>
                <span id="api-status-label" class="ml-3 text-sm font-medium text-gray-600">—</span>
            </div>
            <div class="mt-4">
                 <button id="reset-bot-ajax" class="btn-danger">Reset Sesi & Tautkan Ulang</button>
                 <p id="wa-qr-status" class="text-xs text-gray-500 mt-2">Reset akan memutuskan koneksi bot dan meminta QR code baru.</p>
            </div>
        </div>
    </div>

    <!-- QR Card: ditampilkan di bawah reset sesi -->
    <div class="mt-6">
        <div class="content-card">
            <h3 class="card-title mb-4">QR Bot WhatsApp</h3>
            <div id="qr-container">
                <p class="text-sm text-gray-500 mb-4">Memuat QR code...</p>
            </div>
            <button id="refresh-qr" class="btn-secondary mt-3">Segarkan QR</button>

            <!-- [BARU] Bagian Pairing Code -->
            <div class="mt-6 pt-4 border-t border-gray-200">
                <h4 class="text-sm font-bold text-gray-700 mb-2">Alternatif: Tautkan dengan Nomor HP</h4>
                <div class="flex gap-2 max-w-md">
                    <input type="text" id="pairing-number" class="form-input text-sm" placeholder="Contoh: 628123456789">
                    <button id="get-pairing-code" class="btn-secondary text-sm whitespace-nowrap">Dapatkan Kode</button>
                </div>
                <div id="pairing-code-display" class="mt-3 hidden p-4 bg-gray-100 rounded text-center border border-gray-300">
                    <p class="text-xs text-gray-500">Masukkan kode ini di WhatsApp > Perangkat Tertaut > Tautkan dengan No. HP:</p>
                    <div class="text-3xl font-mono font-bold tracking-widest text-primary-orange mt-2" id="code-text"></div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
(function() {
    const apiBase = '<?php echo rtrim(API_WA_BASE_URL, "/"); ?>';
    const apiToken = '<?php echo API_WA_TOKEN; ?>';

    const statusBtn = document.getElementById('check-api-status');
    const statusLabel = document.getElementById('api-status-label');
    const resetBtn = document.getElementById('reset-bot-ajax');
    const qrContainer = document.getElementById('qr-container');
    const qrStatusEl = document.getElementById('wa-qr-status');
    const pairingBtn = document.getElementById('get-pairing-code');
    const pairingInput = document.getElementById('pairing-number');
    const pairingDisplay = document.getElementById('pairing-code-display');
    const codeText = document.getElementById('code-text');

    function setStatus(msg, isError = false) {
        qrStatusEl.textContent = msg;
        qrStatusEl.style.color = isError ? '#ef4444' : '#4b5563';
    }

    function updateQRContainer(content) {
        qrContainer.innerHTML = content;
    }

    async function checkStatus() {
        statusLabel.textContent = 'Memeriksa...';
        statusLabel.style.color = '#4b5563';
        try {
            const res = await fetch(`${apiBase}/status?token=${encodeURIComponent(apiToken)}`);
            const j = await res.json();
            if (j && j.success && j.data) {
                const d = j.data;
                statusLabel.textContent = d.message;
                statusLabel.style.color = d.connected ? '#10b981' : '#f59e0b';
            } else {
                statusLabel.textContent = (j && j.message) || 'Gagal mengambil status';
                statusLabel.style.color = '#ef4444';
            }
        } catch (err) {
            statusLabel.textContent = 'Terjadi kesalahan API';
            statusLabel.style.color = '#ef4444';
        }
    }

    async function fetchQR() {
        updateQRContainer('<p class="text-sm text-gray-500 mb-4">Memuat QR code...</p>');
        try {
            const res = await fetch(`${apiBase}/qr?token=${encodeURIComponent(apiToken)}`);
            const j = await res.json();
            if (j && j.success && j.data && j.data.qr) {
                const qrDataUrl = j.data.qr;
                const qrTs = j.data.ts ? new Date(j.data.ts).toLocaleString('id-ID') : '';
                let content = '<p class="text-sm text-gray-600 mb-2">Pindai QR ini dengan WhatsApp:</p>';
                content += `<img src="${qrDataUrl}" alt="QR WhatsApp" style="max-width:260px; display:block; margin-bottom:8px;" />`;
                if (qrTs) content += `<p class="text-xs text-gray-500">Terakhir diperbarui: ${qrTs}</p>`;
                updateQRContainer(content);
                return true;
            } else {
                updateQRContainer(`<p class="text-sm text-gray-500 mb-4">${j.message || 'QR belum tersedia. Pastikan bot belum terhubung.'}</p>`);
                return false;
            }
        } catch (err) {
            updateQRContainer(`<p class="text-sm text-red-500 mb-4">Gagal memuat QR: ${err.message}</p>`);
            return false;
        }
    }

    async function pollQR(timeoutSec = 20) {
        const until = Date.now() + timeoutSec * 1000;
        setStatus('Menunggu QR...');
        while (Date.now() < until) {
            if (await fetchQR()) {
                setStatus('QR diterima. Silakan pindai dengan WhatsApp.');
                return true;
            }
            await new Promise(r => setTimeout(r, 1000));
        }
        setStatus('Gagal mendapatkan QR setelah menunggu. Coba segarkan QR secara manual.', true);
        return false;
    }

    // Initial actions on page load
    checkStatus();
    fetchQR();

    // Event Listeners
    statusBtn && statusBtn.addEventListener('click', checkStatus);

    resetBtn && resetBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        if (!confirm('Anda yakin ingin mereset sesi bot? Koneksi saat ini akan terputus dan Anda perlu memindai QR code baru.')) {
            return;
        }
        resetBtn.disabled = true;
        setStatus('Mengirim permintaan reset...');
        try {
            const res = await fetch(`${apiBase}/reset`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    token: apiToken
                })
            });
            const j = await res.json();
            if (j && j.success) {
                setStatus('Reset berhasil. Menunggu QR baru...');
                await pollQR();
            } else {
                setStatus(`Reset gagal: ${j.message || 'tidak diketahui'}`, true);
            }
        } catch (err) {
            setStatus(`Gagal menghubungi API: ${err.message}`, true);
        }
        resetBtn.disabled = false;
    });

    document.getElementById('refresh-qr').addEventListener('click', async function(e) {
        e.preventDefault();
        const btn = e.target;
        btn.disabled = true;
        btn.textContent = 'Memuat...';
        await fetchQR();
        btn.disabled = false;
        btn.textContent = 'Segarkan QR';
    });

    // [BARU] Handler Tombol Pairing Code
    if (pairingBtn) {
        pairingBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            const phone = pairingInput.value.trim();
            if (!phone) {
                alert("Masukkan nomor HP terlebih dahulu (format: 628xxx)");
                return;
            }

            pairingBtn.disabled = true;
            pairingBtn.textContent = "Meminta Kode...";
            pairingDisplay.classList.add('hidden');

            try {
                const res = await fetch(`${apiBase}/pair`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'x-api-key': apiToken },
                    body: JSON.stringify({ token: apiToken, phone: phone })
                });
                const j = await res.json();
                if (j && j.success && j.data && j.data.code) {
                    codeText.textContent = j.data.code;
                    pairingDisplay.classList.remove('hidden');
                    setStatus('Kode pairing diterima. Silakan masukkan di WhatsApp.');
                } else {
                    alert("Gagal: " + (j.message || "Terjadi kesalahan"));
                }
            } catch (err) {
                alert("Error koneksi: " + err.message);
            }
            pairingBtn.disabled = false;
            pairingBtn.textContent = "Dapatkan Kode";
        });
    }

    document.getElementById('total_donasi_disalurkan').addEventListener('keyup', function(e) {
        let value = e.target.value.replace(/[^\d]/g, '');
        e.target.value = new Intl.NumberFormat('id-ID').format(value);
    });
})();
</script>

<?php require_once 'templates/footer_admin.php'; ?>