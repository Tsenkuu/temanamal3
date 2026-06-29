<?php
require_once '../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
$page_title = "Kelola Donatur & Blast WA";

// Data untuk tabel
$result_users = $mysqli->query("SELECT id, nama_lengkap, no_telepon FROM user WHERE no_telepon IS NOT NULL AND no_telepon != '' ORDER BY id DESC");
$sql_donatur = "SELECT DISTINCT kontak_donatur, nama_donatur FROM donasi WHERE kontak_donatur IS NOT NULL AND kontak_donatur != '' AND kontak_donatur NOT LIKE '%@%' AND kontak_donatur NOT IN (SELECT no_telepon FROM user WHERE no_telepon IS NOT NULL AND no_telepon != '') ORDER BY nama_donatur ASC";
$result_donatur = $mysqli->query($sql_donatur);

require_once 'templates/header_admin.php';
?>
<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-bold text-dark-text"><?php echo $page_title; ?></h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <!-- Kolom Formulir & Progress -->
        <div class="lg:col-span-1 space-y-6">
            <div class="content-card">
                <h3 class="card-title mb-4">Kirim Notifikasi Massal</h3>
                <form id="blast-form" class="space-y-4">
                    <div>
                        <label for="pesan_blast" class="form-label">Isi Pesan</label>
                        <textarea class="form-input" id="pesan_blast" name="pesan" rows="5" required></textarea>
                        <p class="text-xs text-gray-500 mt-1">Gunakan <strong>[nama]</strong> dan
                            <strong>[sapaan]</strong> untuk personalisasi.</p>
                    </div>
                    <div>
                        <label for="gambar_blast" class="form-label">Gambar (Opsional)</label>
                        <input class="form-input-file" type="file" id="gambar_blast" name="gambar">
                    </div>
                    <div>
                        <label for="jeda_waktu" class="form-label">Jeda Antar Pesan</label>
                        <select id="jeda_waktu" class="form-select">
                            <option value="30000">30 Detik (Normal)</option>
                            <option value="40000" selected>40 Detik (Aman)</option>
                            <option value="50000">50 Detik (Sangat Aman)</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" id="start-blast-btn" class="btn-primary w-full"><i
                                class="bi bi-whatsapp mr-2"></i>Mulai</button>
                        <button type="button" id="stop-blast-btn" class="btn-secondary w-full" disabled><i
                                class="bi bi-stop-circle-fill mr-2"></i>Hentikan</button>
                    </div>
                </form>
            </div>
            <div class="content-card">
                <h3 class="card-title">Progress Blast</h3>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mt-4">
                    <div id="progress-bar" class="bg-primary-orange h-2.5 rounded-full" style="width: 0%"></div>
                </div>
                <p id="progress-text" class="text-sm text-center mt-2 text-gray-600">0% (0 / 0)</p>
                <div id="log-blast"
                    class="mt-4 p-2 bg-gray-800 text-white rounded-md text-xs font-mono h-32 overflow-y-auto">Menunggu
                    proses...</div>
            </div>
        </div>

        <!-- Kolom Daftar Target -->
        <div class="lg:col-span-2 content-card">
            <h3 class="card-title mb-4">Pilih Target Penerima</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="font-semibold text-dark-text mb-2">User Terdaftar</h4>
                    <div class="table-wrapper border rounded-lg max-h-96">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="p-2 w-10"><input type="checkbox" id="pilih-semua-user"></th>
                                    <th class="p-2 text-left">Nama</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_users && $result_users->num_rows > 0): while($user = $result_users->fetch_assoc()): ?>
                                <tr class="border-t">
                                    <td class="p-2"><input type="checkbox" class="pilih-target pilih-user"
                                            value="<?php echo htmlspecialchars($user['no_telepon']); ?>"></td>
                                    <td class="p-2"><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                </tr>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div>
                    <h4 class="font-semibold text-dark-text mb-2">Donatur Lainnya</h4>
                    <div class="table-wrapper border rounded-lg max-h-96">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="p-2 w-10"><input type="checkbox" id="pilih-semua-donatur"></th>
                                    <th class="p-2 text-left">Nama</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_donatur && $result_donatur->num_rows > 0): while($donatur = $result_donatur->fetch_assoc()): ?>
                                <tr class="border-t">
                                    <td class="p-2"><input type="checkbox" class="pilih-target pilih-donatur"
                                            value="<?php echo htmlspecialchars($donatur['kontak_donatur']); ?>"></td>
                                    <td class="p-2"><?php echo htmlspecialchars($donatur['nama_donatur']); ?></td>
                                </tr>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('blast-form'),
        startBtn = document.getElementById('start-blast-btn'),
        stopBtn = document.getElementById('stop-blast-btn');
    const progressBar = document.getElementById('progress-bar'),
        progressText = document.getElementById('progress-text'),
        logBlast = document.getElementById('log-blast');
    let isBlasting = false;

    document.getElementById('pilih-semua-user').addEventListener('change', function() {
        document.querySelectorAll('.pilih-user').forEach(c => c.checked = this.checked);
    });
    document.getElementById('pilih-semua-donatur').addEventListener('change', function() {
        document.querySelectorAll('.pilih-donatur').forEach(c => c.checked = this.checked);
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (isBlasting) return;
        const selectedTargets = Array.from(document.querySelectorAll('.pilih-target:checked')).map(
            c => c.value);
        if (selectedTargets.length === 0) {
            alert('Pilih minimal satu target.');
            return;
        }

        isBlasting = true;
        startBtn.disabled = true;
        stopBtn.disabled = false;
        logBlast.innerHTML = 'Memulai proses blast...\n';

        const formData = new FormData(form),
            totalTargets = selectedTargets.length;
        let sentCount = 0;

        const updateProgress = () => {
            const percentage = Math.round((sentCount / totalTargets) * 100);
            progressBar.style.width = percentage + '%';
            progressText.textContent = `${percentage}% (${sentCount} / ${totalTargets})`;
        };

        updateProgress();

        for (const nomor of selectedTargets) {
            if (!isBlasting) {
                logBlast.innerHTML += '<strong>Proses dihentikan oleh admin.</strong>\n';
                break;
            }
            const currentFormData = new FormData();
            currentFormData.append('pesan', formData.get('pesan'));
            if (formData.get('gambar').size > 0) currentFormData.append('gambar', formData.get(
                'gambar'));
            currentFormData.append('nomor', nomor);

            logBlast.innerHTML += `Mengirim ke ${nomor}... `;
            try {
                const response = await fetch('proses_blast_wa.php', {
                    method: 'POST',
                    body: currentFormData
                });
                const result = await response.json();
                logBlast.innerHTML += result.success ?
                    '<span class="text-green-400">Berhasil</span>\n' :
                    `<span class="text-red-400">Gagal: ${result.message}</span>\n`;
            } catch (error) {
                logBlast.innerHTML += '<span class="text-red-400">Error Jaringan</span>\n';
            }
            sentCount++;
            updateProgress();
            logBlast.scrollTop = logBlast.scrollHeight;
            if (isBlasting && sentCount < totalTargets) {
                const jeda = parseInt(document.getElementById('jeda_waktu').value, 10);
                await new Promise(resolve => setTimeout(resolve, jeda));
            }
        }
        logBlast.innerHTML += '<strong>Proses blast selesai.</strong>\n';
        isBlasting = false;
        startBtn.disabled = false;
        stopBtn.disabled = true;
    });

    stopBtn.addEventListener('click', function() {
        isBlasting = false;
        stopBtn.disabled = true;
    });
});
</script>
<?php require_once 'templates/footer_admin.php'; ?>