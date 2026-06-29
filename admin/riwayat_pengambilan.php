<?php
// Memuat file konfigurasi, yang seharusnya sudah memanggil session_start()
require_once '../includes/config.php';

// Pengecekan login admin (sangat direkomendasikan)
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Riwayat Pengambilan Kotak Infak";

// --- Logika Filter ---
$bulan_terpilih = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_terpilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// --- Query untuk Statistik ---
// 1. Total Pengambilan Semuanya
$total_semuanya = $mysqli->query("SELECT SUM(jumlah_terkumpul) as total FROM riwayat_pengambilan")->fetch_assoc()['total'] ?? 0;

// 2. Total Pengambilan Bulan Ini (berdasarkan filter)
$stmt_total_bulan = $mysqli->prepare("SELECT SUM(jumlah_terkumpul) as total FROM riwayat_pengambilan WHERE MONTH(tanggal_pengambilan) = ? AND YEAR(tanggal_pengambilan) = ?");
$stmt_total_bulan->bind_param("ss", $bulan_terpilih, $tahun_terpilih);
$stmt_total_bulan->execute();
$total_bulan_ini = $stmt_total_bulan->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_total_bulan->close();

// --- Query untuk mengambil data riwayat berdasarkan filter ---
$sql = "SELECT r.id, r.tanggal_pengambilan, r.jumlah_terkumpul, a.nama_lengkap AS nama_amil, k.nama_lokasi
        FROM riwayat_pengambilan r
        JOIN amil a ON r.id_amil = a.id
        JOIN kotak_infak k ON r.id_kotak_infak = k.id
        WHERE MONTH(r.tanggal_pengambilan) = ? AND YEAR(r.tanggal_pengambilan) = ?
        ORDER BY r.tanggal_pengambilan DESC";

$stmt_riwayat = $mysqli->prepare($sql);
$stmt_riwayat->bind_param("ss", $bulan_terpilih, $tahun_terpilih);
$stmt_riwayat->execute();
$result_riwayat = $stmt_riwayat->get_result();

// Memuat header admin
require_once 'templates/header_admin.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-bold text-dark-text"><?php echo $page_title; ?></h1>
        <form action="hapus_pengambilan.php" method="POST"
            onsubmit="return confirm('PERINGATAN: Anda akan menghapus SEMUA riwayat pengambilan. Aksi ini tidak dapat dibatalkan. Lanjutkan?');">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="hapus" value="semua">
            <button type="submit" class="btn-danger">
                <i class="bi bi-trash-fill mr-2"></i> Hapus Semua Riwayat
            </button>
        </form>
    </div>

    <?php
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    ?>

    <!-- Kartu Total -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div class="content-card bg-blue-50">
            <p class="font-medium text-blue-800">Total Bulan
                <?php echo date('F Y', mktime(0,0,0,$bulan_terpilih,1,$tahun_terpilih)); ?></p>
            <p class="text-3xl font-bold text-blue-900 mt-2">Rp
                <?php echo number_format($total_bulan_ini, 0, ',', '.'); ?></p>
        </div>
        <div class="content-card bg-indigo-50">
            <p class="font-medium text-indigo-800">Total Pengambilan Keseluruhan</p>
            <p class="text-3xl font-bold text-indigo-900 mt-2">Rp
                <?php echo number_format($total_semuanya, 0, ',', '.'); ?></p>
        </div>
    </div>

    <!-- Daftar Riwayat -->
    <div class="content-card mt-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end mb-6">
            <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="bulan" class="form-label">Bulan:</label>
                    <select name="bulan" id="bulan" class="form-select">
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
                        <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php if($tahun_terpilih == $i) echo 'selected'; ?>>
                            <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-primary w-full">Filter</button>
        </form>

        <div class="table-wrapper">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Detail Pengambilan</th>
                        <th scope="col" class="px-6 py-3 text-right">Jumlah (Rp)</th>
                        <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_riwayat && $result_riwayat->num_rows > 0): ?>
                    <?php while ($riwayat = $result_riwayat->fetch_assoc()): ?>
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-dark-text">
                                <?php echo htmlspecialchars($riwayat['nama_lokasi']); ?></p>
                            <p class="text-xs">Oleh <?php echo htmlspecialchars($riwayat['nama_amil']); ?> |
                                <?php echo date('d M Y, H:i', strtotime($riwayat['tanggal_pengambilan'])); ?></p>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-green-600">
                            <?php echo number_format($riwayat['jumlah_terkumpul'], 0, ',', '.'); ?></td>
                        <td class="px-6 py-4 text-center flex justify-center gap-2">
                            <a href="edit_pengambilan.php?id=<?php echo $riwayat['id']; ?>"
                                class="btn-icon bg-yellow-100 text-yellow-600 hover:bg-yellow-200" title="Edit"><i
                                    class="bi bi-pencil-square"></i></a>
                            <form action="hapus_pengambilan.php" method="POST"
                                onsubmit="return confirm('Anda yakin ingin menghapus riwayat ini?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo $riwayat['id']; ?>">
                                <button type="submit" class="btn-icon bg-red-100 text-red-600 hover:bg-red-200" title="Hapus"><i
                                        class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">Tidak ada riwayat pengambilan
                            untuk periode ini.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once 'templates/footer_admin.php'; ?>
