<?php
// Memuat file konfigurasi, yang seharusnya sudah memanggil session_start()
require_once '../includes/config.php';

// Pengecekan login amil
if (!isset($_SESSION['amil_id'])) {
    header('Location: ../login.php');
    exit();
}
$id_amil_login = $_SESSION['amil_id'];

$page_title = "Riwayat Pengambilan Saya";

// --- Query untuk mengambil data riwayat KHUSUS AMIL INI ---
$sql = "SELECT 
            r.id,
            r.tanggal_pengambilan,
            r.jumlah_terkumpul,
            k.nama_lokasi
        FROM 
            riwayat_pengambilan r
        JOIN 
            kotak_infak k ON r.id_kotak_infak = k.id
        WHERE 
            r.id_amil = ?
        ORDER BY 
            r.tanggal_pengambilan DESC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id_amil_login);
$stmt->execute();
$result_riwayat = $stmt->get_result();

// Mengelompokkan hasil per bulan dan menghitung total
$riwayat_per_bulan = [];
$total_semuanya = 0;
if ($result_riwayat) {
    while ($row = $result_riwayat->fetch_assoc()) {
        $bulan_tahun = date('F Y', strtotime($row['tanggal_pengambilan']));
        $riwayat_per_bulan[$bulan_tahun][] = $row;
        $total_semuanya += $row['jumlah_terkumpul'];
    }
}
$stmt->close();

// Memuat header amil
require_once 'templates/header_amil.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_amil.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
            </div>

            <?php
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['success_message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                unset($_SESSION['success_message']);
            }
            ?>

            <!-- Kartu Total Keseluruhan -->
            <div class="card bg-primary text-white mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Pengambilan Saya (Semua Waktu)</h5>
                    <p class="card-text fs-4 fw-bold">Rp <?php echo number_format($total_semuanya, 0, ',', '.'); ?></p>
                </div>
            </div>

            <!-- Daftar Riwayat per Bulan -->
            <?php if (!empty($riwayat_per_bulan)): ?>
            <?php foreach ($riwayat_per_bulan as $bulan => $riwayat_list): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Riwayat Bulan: <?php echo $bulan; ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Lokasi</th>
                                    <th class="text-end">Jumlah (Rp)</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $total_bulan_ini = 0; ?>
                                <?php foreach ($riwayat_list as $riwayat): ?>
                                <tr>
                                    <td><?php echo date('d M Y, H:i', strtotime($riwayat['tanggal_pengambilan'])); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($riwayat['nama_lokasi']); ?></td>
                                    <td class="text-end fw-bold">
                                        <?php echo number_format($riwayat['jumlah_terkumpul'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <a href="edit_pengambilan.php?id=<?php echo $riwayat['id']; ?>"
                                            class="btn btn-warning btn-sm me-2" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="hapus_pengambilan.php" method="POST" class="d-inline"
                                            onsubmit="return confirm('Anda yakin ingin menghapus riwayat ini?');">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?php echo $riwayat['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php $total_bulan_ini += $riwayat['jumlah_terkumpul']; ?>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total Bulan <?php echo $bulan; ?></td>
                                    <td class="text-end fw-bold fs-5">Rp
                                        <?php echo number_format($total_bulan_ini, 0, ',', '.'); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="alert alert-info">Anda belum memiliki riwayat pengambilan.</div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once 'templates/footer_amil.php'; ?>
