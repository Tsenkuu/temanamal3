<?php
/*
|--------------------------------------------------------------------------
| File: amil/dashboard.php (DIPERBARUI)
|--------------------------------------------------------------------------
|
| Halaman utama Amil dengan tambahan kartu statistik untuk Kotak Infak.
|
*/
require_once '../includes/config.php';

if (!isset($_SESSION['amil_id'])) {
    header('Location: ../login.php');
    exit();
}
$id_amil_login = $_SESSION['amil_id'];
$page_title = "Dashboard";

// Query untuk statistik tugas & donasi
$stmt_pending = $mysqli->prepare("SELECT COUNT(id) AS total FROM tugas_pengambilan WHERE id_amil = ? AND status = 'Ditugaskan'");
$stmt_pending->bind_param("i", $id_amil_login);
$stmt_pending->execute();
$tugas_pending = $stmt_pending->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_pending->close();

$stmt_selesai = $mysqli->prepare("SELECT COUNT(id) AS total FROM tugas_pengambilan WHERE id_amil = ? AND status = 'Selesai'");
$stmt_selesai->bind_param("i", $id_amil_login);
$stmt_selesai->execute();
$tugas_selesai = $stmt_selesai->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_selesai->close();

$stmt_donasi = $mysqli->prepare("SELECT SUM(jumlah_terkumpul) AS total FROM riwayat_pengambilan WHERE id_amil = ?");
$stmt_donasi->bind_param("i", $id_amil_login);
$stmt_donasi->execute();
$total_donasi = $stmt_donasi->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_donasi->close();

// --- [BARU] Query untuk statistik kotak infak ---
$stats_result = $mysqli->query("SELECT COUNT(id) as total, SUM(CASE WHEN status = 'Aktif' THEN 1 ELSE 0 END) as aktif FROM kotak_infak");
$stats_kotak = $stats_result->fetch_assoc();
$total_kotak = $stats_kotak['total'] ?? 0;
$kotak_aktif = $stats_kotak['aktif'] ?? 0;


// Query untuk tugas terbaru (maksimal 3)
$stmt_tugas_terbaru = $mysqli->prepare("SELECT k.nama_lokasi, t.tanggal_tugas FROM tugas_pengambilan t JOIN kotak_infak k ON t.id_kotak_infak = k.id WHERE t.id_amil = ? AND t.status = 'Ditugaskan' ORDER BY t.tanggal_tugas ASC LIMIT 3");
$stmt_tugas_terbaru->bind_param("i", $id_amil_login);
$stmt_tugas_terbaru->execute();
$result_tugas_terbaru = $stmt_tugas_terbaru->get_result();
$stmt_tugas_terbaru->close();


require_once 'templates/header_amil.php';
?>

<!-- Kartu Selamat Datang -->
<div class="card bg-primary-subtle border-0 mb-4">
    <div class="card-body">
        <h4 class="card-title">Selamat Datang, <?php echo htmlspecialchars($nama_amil_login); ?>!</h4>
        <p class="card-text text-muted">Berikut adalah ringkasan aktivitas dan data Anda.</p>
    </div>
</div>

<!-- Kartu Statistik dengan Desain Baru -->
<div class="row g-3 g-lg-4">
    <!-- [BARU] Kartu Kotak Infak -->
    <div class="col-lg-3 col-md-6 col-6">
        <a href="kelola_kotak_infaq.php" class="text-decoration-none">
            <div class="card h-100 text-bg-info border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam-fill fs-2 mb-2"></i>
                    <h3 class="fw-bold mb-0"><?php echo $total_kotak; ?></h3>
                    <p class="small mb-0">Total Kotak</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-3 col-md-6 col-6">
        <a href="tugas_saya.php" class="text-decoration-none">
            <div class="card h-100 text-bg-warning border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split fs-2 mb-2"></i>
                    <h3 class="fw-bold mb-0"><?php echo $tugas_pending; ?></h3>
                    <p class="small mb-0">Tugas Pending</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-3 col-md-6 col-6">
         <a href="riwayat_pengambilan.php" class="text-decoration-none">
            <div class="card h-100 text-bg-success border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle-fill fs-2 mb-2"></i>
                    <h3 class="fw-bold mb-0"><?php echo $tugas_selesai; ?></h3>
                    <p class="small mb-0">Tugas Selesai</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg-3 col-md-6 col-6">
        <a href="riwayat_pengambilan.php" class="text-decoration-none">
            <div class="card h-100 text-bg-primary border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-wallet2 fs-2 mb-2"></i>
                    <h4 class="fw-bold mb-0">Rp <?php echo number_format($total_donasi, 0, ',', '.'); ?></h4>
                    <p class="small mb-0">Terkumpul</p>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Aksi Cepat -->
<h5 class="mt-4 pt-2">Aksi Cepat</h5>
<div class="d-flex gap-3">
    <a href="tambah_kotak_infak.php" class="btn btn-lg btn-outline-primary flex-fill">
        <i class="bi bi-plus-circle-dotted me-2"></i>Kotak Baru
    </a>
    <a href="tambah_berita.php" class="btn btn-lg btn-outline-secondary flex-fill">
        <i class="bi bi-pencil-square me-2"></i>Tulis Berita
    </a>
</div>


<!-- Tugas Terbaru -->
<div class="card mt-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Tugas Anda Berikutnya</h5>
    </div>
    <div class="list-group list-group-flush">
        <?php if ($result_tugas_terbaru->num_rows > 0): ?>
            <?php while($tugas = $result_tugas_terbaru->fetch_assoc()): ?>
            <a href="tugas_saya.php" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1"><?php echo htmlspecialchars($tugas['nama_lokasi']); ?></h6>
                    <small class="text-muted"><?php echo date('d M Y', strtotime($tugas['tanggal_tugas'])); ?></small>
                </div>
                <small class="text-muted">Segera lakukan pengambilan donasi.</small>
            </a>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="list-group-item">
                <p class="text-center text-muted my-3">Tidak ada tugas yang menunggu. Kerja bagus!</p>
            </div>
        <?php endif; ?>
    </div>
     <div class="card-footer bg-white text-center">
        <a href="tugas_saya.php">Lihat Semua Tugas <i class="bi bi-arrow-right-short"></i></a>
    </div>
</div>


<?php require_once 'templates/footer_amil.php'; ?>

