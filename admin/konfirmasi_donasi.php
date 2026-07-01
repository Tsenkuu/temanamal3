<?php
require_once '../includes/config.php';

// Pengecekan login admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Konfirmasi Donasi";

// --- LOGIKA AKSI (ACC ATAU TOLAK) ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id_donasi'])) {
    require_valid_csrf();
    $id_donasi = (int)$_POST['id_donasi'];
    $action = clean_text($_POST['action'] ?? '', 20);
    
    if ($action === 'acc') {
    $mysqli->begin_transaction();
    try {
        // Ambil detail donasi untuk notifikasi dan update program
        $stmt_get = $mysqli->prepare("SELECT nominal, id_program, nama_donatur, invoice_id, kontak_donatur FROM donasi WHERE id = ?");
        $stmt_get->bind_param("i", $id_donasi);
        $stmt_get->execute();
        $donasi = $stmt_get->get_result()->fetch_assoc();
        
        // Update donasi terkumpul di program jika ada
        if($donasi && $donasi['id_program']){
            $stmt_prog = $mysqli->prepare("UPDATE program SET donasi_terkumpul = donasi_terkumpul + ? WHERE id = ?");
            $stmt_prog->bind_param("di", $donasi['nominal'], $donasi['id_program']);
            $stmt_prog->execute();
        }

        // Update status donasi menjadi 'Selesai'
        $stmt_update = $mysqli->prepare("UPDATE donasi SET status = 'Selesai' WHERE id = ?");
        $stmt_update->bind_param("i", $id_donasi);
        $stmt_update->execute();
        
        $mysqli->commit();
        $_SESSION['success_message'] = "Donasi berhasil dikonfirmasi.";

        // Kirim Notifikasi WhatsApp
        if ($donasi) {
            // Notifikasi ke Admin
            $pesan_admin = "✅ *Donasi Dikonfirmasi*\n\n" .
                                "Anda telah berhasil mengonfirmasi donasi:\n\n" .
                                "*Invoice ID:* " . $donasi['invoice_id'] . "\n" .
                                "*Nama Donatur:* " . $donasi['nama_donatur'] . "\n" .
                                "*Nominal:* Rp " . number_format($donasi['nominal'], 0, ',', '.');
            kirimNotifikasiWA(ADMIN_WA_NUMBER, $pesan_admin);
            
            // Notifikasi ke Donatur
            if (!empty($donasi['kontak_donatur'])) {
                $pesan_donatur = "Alhamdulillah ✅\n\n" .
                                 "Terima kasih, donasi Anda telah kami terima dan berhasil dikonfirmasi:\n\n" .
                                 "*Invoice:* " . $donasi['invoice_id'] . "\n" .
                                 "*Nominal:* Rp " . number_format($donasi['nominal'], 0, ',', '.') . "\n\n" .
                                 "Semoga Allah memberikan pahala atas apa yang Anda berikan, menjadikannya penyuci bagi Anda, dan memberikan keberkahan pada harta yang tersisa.\n\n" .
                                 "_Lazismu Tulungagung_";
                kirimNotifikasiWA($donasi['kontak_donatur'], $pesan_donatur);
            }
        }

    } catch (Exception $e) {
        $mysqli->rollback();
        $_SESSION['error_message'] = "Gagal mengonfirmasi donasi.";
    }
    header("Location: konfirmasi_donasi.php");
    exit();
    }
}

// 2. Proses Tolak Donasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action ?? '') === 'tolak' && $id_donasi > 0) {
    $stmt = $mysqli->prepare("UPDATE donasi SET status = 'Dibatalkan' WHERE id = ?");
    $stmt->bind_param("i", $id_donasi);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Donasi telah ditolak/dibatalkan.";
    } else {
        $_SESSION['error_message'] = "Gagal menolak donasi.";
    }
    header("Location: konfirmasi_donasi.php");
    exit();
}


// Ambil data donasi yang menunggu konfirmasi
$result_pending = $mysqli->query("SELECT * FROM donasi WHERE status = 'Menunggu Konfirmasi' ORDER BY created_at ASC");

require_once 'templates/header_admin.php';
?>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-display font-bold text-slate-900"><?php echo $page_title; ?></h1>
            <p class="text-slate-500 mt-1">Verifikasi donasi yang masuk melalui transfer bank atau manual.</p>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 flex items-center gap-3">
            <i class="bi bi-check-circle-fill text-xl"></i>
            <span class="font-medium"><?php echo htmlspecialchars($_SESSION['success_message']); ?></span>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 flex items-center gap-3">
            <i class="bi bi-exclamation-triangle-fill text-xl"></i>
            <span class="font-medium"><?php echo htmlspecialchars($_SESSION['error_message']); ?></span>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Main Content Box -->
    <div class="bg-white rounded-[20px] shadow-sm border border-slate-100 overflow-hidden">
        
        <?php if ($result_pending && $result_pending->num_rows > 0): ?>
            <!-- Desktop Table View (hidden on small screens) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Donatur & Info</th>
                            <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-right">Nominal</th>
                            <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-center">Bukti</th>
                            <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php 
                        // Reset pointer if we need to iterate again, but since we split views by CSS we don't need to reset in PHP yet. Wait, we DO need to render both or use responsive grid. 
                        // Better to use a responsive grid/flex layout for each item that looks like a table row on desktop and a card on mobile.
                        ?>
                        <?php while($donasi = $result_pending->fetch_assoc()): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800 text-base mb-1">
                                    <?php echo htmlspecialchars($donasi['nama_donatur']); ?>
                                </p>
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-1 bg-slate-100 px-2 py-0.5 rounded font-mono text-slate-600">
                                        <i class="bi bi-receipt"></i> <?php echo htmlspecialchars($donasi['invoice_id']); ?>
                                    </span>
                                    <span>&bull;</span>
                                    <span><?php echo date('d M Y, H:i', strtotime($donasi['created_at'])); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="font-bold text-emerald-600 text-base">
                                    Rp <?php echo number_format($donasi['nominal'], 0, ',', '.'); ?>
                                </p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" data-modal-toggle="buktiModal"
                                    data-bukti-img="../assets/uploads/bukti/<?php echo htmlspecialchars($donasi['bukti_pembayaran']); ?>" title="Lihat Bukti Transfer">
                                    <i class="bi bi-image text-lg"></i>
                                </button>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <form action="konfirmasi_donasi.php" method="POST" class="inline" onsubmit="return confirm('Anda yakin ingin menyetujui donasi ini?');">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="id_donasi" value="<?php echo $donasi['id']; ?>">
                                        <input type="hidden" name="action" value="acc">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:text-emerald-800 font-medium transition-colors" title="Setujui">
                                            <i class="bi bi-check2-circle text-lg"></i> Setujui
                                        </button>
                                    </form>
                                    
                                    <form action="konfirmasi_donasi.php" method="POST" class="inline" onsubmit="return confirm('Anda yakin ingin menolak donasi ini?');">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="id_donasi" value="<?php echo $donasi['id']; ?>">
                                        <input type="hidden" name="action" value="tolak">
                                        <button type="submit" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Tolak">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden divide-y divide-slate-100">
                <?php 
                $result_pending->data_seek(0); // Reset pointer
                while($donasi = $result_pending->fetch_assoc()): 
                ?>
                <div class="p-5 space-y-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-slate-800 mb-1"><?php echo htmlspecialchars($donasi['nama_donatur']); ?></p>
                            <span class="inline-flex items-center gap-1 bg-slate-100 px-2 py-0.5 rounded font-mono text-xs text-slate-600">
                                <i class="bi bi-receipt"></i> <?php echo htmlspecialchars($donasi['invoice_id']); ?>
                            </span>
                        </div>
                        <p class="font-bold text-emerald-600">Rp <?php echo number_format($donasi['nominal'], 0, ',', '.'); ?></p>
                    </div>
                    
                    <div class="text-xs text-slate-500 flex items-center gap-1">
                        <i class="bi bi-clock"></i> <?php echo date('d M Y, H:i', strtotime($donasi['created_at'])); ?>
                    </div>

                    <div class="flex gap-2 pt-2 border-t border-slate-100">
                        <button type="button" class="flex-1 inline-flex justify-center items-center gap-2 py-2.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors text-sm font-medium" data-modal-toggle="buktiModal"
                            data-bukti-img="../assets/uploads/bukti/<?php echo htmlspecialchars($donasi['bukti_pembayaran']); ?>">
                            <i class="bi bi-image"></i> Bukti
                        </button>
                        
                        <form action="konfirmasi_donasi.php" method="POST" class="flex-1 inline-flex" onsubmit="return confirm('Setujui donasi?');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id_donasi" value="<?php echo $donasi['id']; ?>">
                            <input type="hidden" name="action" value="acc">
                            <button type="submit" class="w-full inline-flex justify-center items-center gap-2 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-medium transition-colors text-sm">
                                <i class="bi bi-check2-circle"></i> Setujui
                            </button>
                        </form>
                        
                        <form action="konfirmasi_donasi.php" method="POST" class="inline-flex" onsubmit="return confirm('Tolak donasi?');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id_donasi" value="<?php echo $donasi['id']; ?>">
                            <input type="hidden" name="action" value="tolak">
                            <button type="submit" class="w-10 h-10 inline-flex justify-center items-center rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <div class="text-center py-16 px-4">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                    <i class="bi bi-check-circle text-3xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Semua Beres!</h3>
                <p class="text-slate-500 max-w-sm mx-auto">Tidak ada donasi yang menunggu konfirmasi saat ini.</p>
            </div>
        <?php endif; ?>
    </div>

<!-- Modal Bukti Pembayaran (Alpine / Tailwind) -->
<div id="buktiModal" class="fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" id="buktiModalBackdrop"></div>

        <!-- Modal Panel -->
        <div class="relative inline-block bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full scale-100">
            
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-800" id="modal-title">Bukti Transfer</h3>
                <button type="button" id="closeBuktiModal" class="text-slate-400 hover:text-slate-600 transition-colors p-2 -mr-2">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div class="p-6">
                <div class="bg-slate-100 rounded-xl p-2 flex justify-center items-center min-h-[300px]">
                    <img id="gambarBukti" src="" class="max-h-[60vh] w-auto rounded-lg shadow-sm object-contain" alt="Bukti Pembayaran">
                </div>
            </div>
            
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <a id="downloadBukti" href="#" download class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-medium hover:bg-slate-100 transition-colors shadow-sm text-sm">
                    <i class="bi bi-download"></i> Unduh Gambar
                </a>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const buktiModal = document.getElementById('buktiModal');
    const gambarBukti = document.getElementById('gambarBukti');
    const downloadBukti = document.getElementById('downloadBukti');
    const closeBuktiModal = document.getElementById('closeBuktiModal');
    const buktiModalBackdrop = document.getElementById('buktiModalBackdrop');

    const modalToggles = document.querySelectorAll('[data-modal-toggle]');
    modalToggles.forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-modal-toggle');
            if (modalId === 'buktiModal') {
                const imageUrl = button.getAttribute('data-bukti-img');
                if (imageUrl) {
                    gambarBukti.src = imageUrl;
                    downloadBukti.href = imageUrl;
                    buktiModal.classList.remove('hidden');
                }
            }
        });
    });

    function closeModal() {
        buktiModal.classList.add('hidden');
        gambarBukti.src = '';
    }

    if (closeBuktiModal) closeBuktiModal.addEventListener('click', closeModal);
    if (buktiModalBackdrop) buktiModalBackdrop.addEventListener('click', closeModal);
    
    // Close on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape" && !buktiModal.classList.contains('hidden')) {
            closeModal();
        }
    });
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>
