<?php
// Memuat file konfigurasi, yang seharusnya sudah memanggil session_start()
require_once '../includes/config.php';

// Pengecekan login admin (sangat direkomendasikan)
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php'); // Arahkan ke halaman login utama jika belum login
    exit();
}

$page_title = "Riwayat Donasi";

// --- Logika Filter ---
$bulan_terpilih = isset($_GET['bulan']) ? $_GET['bulan'] : 'semua';
$tahun_terpilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// --- Query untuk mengambil data riwayat berdasarkan filter ---
$sql = "SELECT 
            d.id, d.invoice_id, d.nama_donatur, d.nominal,d.kontak_donatur, d.status, d.created_at, d.bukti_pembayaran,
            p.nama_program
        FROM donasi d
        LEFT JOIN program p ON d.id_program = p.id";

$where_clauses = [];
$params = [];
$types = "";

if ($tahun_terpilih != 'semua') {
    $where_clauses[] = "YEAR(d.created_at) = ?";
    $params[] = $tahun_terpilih;
    $types .= "s";
}

if ($bulan_terpilih != 'semua') {
    $where_clauses[] = "MONTH(d.created_at) = ?";
    $params[] = $bulan_terpilih;
    $types .= "s";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY d.created_at DESC";

$stmt_donasi = $mysqli->prepare($sql);
if (!empty($params)) {
    $stmt_donasi->bind_param($types, ...$params);
}
$stmt_donasi->execute();
$result_donasi = $stmt_donasi->get_result();

// Memuat header admin
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
    ?>

    <div class="content-card mt-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end mb-6">
            <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="bulan" class="form-label">Bulan:</label>
                    <select name="bulan" id="bulan" class="form-select">
                        <option value="semua" <?php if($bulan_terpilih == 'semua') echo 'selected'; ?>>Semua Bulan
                        </option>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"
                            <?php if($bulan_terpilih == $i) echo 'selected'; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $i, 10)); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label for="tahun" class="form-label">Tahun:</label>
                    <select name="tahun" id="tahun" class="form-select">
                        <option value="semua" <?php if($tahun_terpilih == 'semua') echo 'selected'; ?>>Semua Tahun
                        </option>
                        <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php if($tahun_terpilih == $i) echo 'selected'; ?>>
                            <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary w-full">Filter</button>
                <a href="export_donasi.php?bulan=<?php echo $bulan_terpilih; ?>&tahun=<?php echo $tahun_terpilih; ?>"
                    class="btn-secondary w-full" title="Export ke Excel"><i class="bi bi-file-earmark-excel-fill"></i></a>
            </div>
        </form>

        <form action="hapus_donasi.php" method="POST" id="form-donasi">
            <div class="table-wrapper">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="p-4"><input type="checkbox" id="pilih-semua"></th>
                            <th scope="col" class="px-6 py-3">Donatur & Program</th>
                            <th scope="col" class="px-6 py-3 text-right">Nominal</th>
                            <th scope="col" class="px-6 py-3 text-center">Status</th>
                            <th scope="col" class="px-6 py-3 text-center">Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_donasi && $result_donasi->num_rows > 0): ?>
                        <?php while($donasi = $result_donasi->fetch_assoc()): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="w-4 p-4"><input type="checkbox" name="donasi_ids[]"
                                    value="<?php echo $donasi['id']; ?>" class="pilih-item"></td>
                            <td class="px-6 py-4">
    <p class="font-semibold text-dark-text">
        <?php echo htmlspecialchars($donasi['nama_donatur']); ?></p>
    <p class="text-sm text-gray-600">
        <?php echo htmlspecialchars($donasi['kontak_donatur']); ?></p>
    <p class="text-xs mt-1">
        <?php echo htmlspecialchars($donasi['nama_program'] ?: 'Donasi Umum'); ?> |
        <?php echo date('d M Y, H:i', strtotime($donasi['created_at'])); ?></p>
</td>
                            <td class="px-6 py-4 text-right font-semibold text-green-600">Rp
                                <?php echo number_format($donasi['nominal'], 0, ',', '.'); ?></td>
                            <td class="px-6 py-4 text-center">
                                <?php
                                                        $status = $donasi['status'];
                                                        $badge_class = 'bg-secondary';
                                                        if ($status == 'Selesai') $badge_class = 'bg-success';
                                                        elseif ($status == 'Menunggu Pembayaran') $badge_class = 'bg-warning text-dark';
                                                        elseif ($status == 'Menunggu Konfirmasi') $badge_class = 'bg-info text-dark';
                                                        elseif ($status == 'Dibatalkan') $badge_class = 'bg-danger';
                                                    ?>
                                <span
                                    class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if(!empty($donasi['bukti_pembayaran'])): ?>
                                <button type="button" class="btn-secondary" data-modal-toggle="buktiModal"
                                    data-bukti-img="../assets/uploads/bukti/<?php echo htmlspecialchars($donasi['bukti_pembayaran']); ?>">
                                    Lihat
                                </button>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada data donasi untuk
                                periode ini.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <button type="submit" name="hapus_dipilih" class="btn-danger"
                    onclick="return confirm('Anda yakin ingin menghapus semua donasi yang dipilih?');">
                    <i class="bi bi-trash-fill mr-1"></i> Hapus yang Dipilih
                </button>
            </div>
        </form>
    </div>
</main>

<!-- Modal Bukti Pembayaran (Tailwind Style) -->
<div id="buktiModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" id="buktiModalBackdrop"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Bukti Pembayaran</h3>
                        <div class="mt-2 flex justify-center bg-gray-100 rounded-lg p-2">
                            <img id="gambarBukti" src="" class="max-h-[70vh] w-auto rounded-md shadow-sm object-contain" alt="Bukti Pembayaran">
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <a id="downloadBukti" href="#" download class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    <i class="bi bi-download mr-2"></i> Download
                </a>
                <button type="button" id="closeBuktiModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Tutup
                </button>
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

    // Script untuk checkbox "Pilih Semua"
    const pilihSemua = document.getElementById('pilih-semua');
    const pilihItem = document.querySelectorAll('.pilih-item');
    if (pilihSemua) {
        pilihSemua.addEventListener('change', function() {
            pilihItem.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>