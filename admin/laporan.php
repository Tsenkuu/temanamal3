<?php
require_once '../includes/config.php';
require_once 'functions.php';


$page_title = "Laporan Donasi";
// Pengecekan login admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Default tanggal adalah bulan ini
$tanggal_mulai = date('Y-m-01');
$tanggal_akhir = date('Y-m-t');

// Jika ada filter tanggal dari form
if (isset($_GET['filter'])) {
    if (!empty($_GET['tanggal_mulai'])) {
        $tanggal_mulai = $_GET['tanggal_mulai'];
    }
    if (!empty($_GET['tanggal_akhir'])) {
        $tanggal_akhir = $_GET['tanggal_akhir'];
    }
}

// Query untuk mengambil data program (ini adalah contoh, idealnya data donasi dicatat per transaksi)
// Untuk saat ini, kita akan menampilkan total donasi terkumpul per program.
$sql = "SELECT nama_program, target_donasi, donasi_terkumpul, created_at 
        FROM program 
        WHERE DATE(created_at) BETWEEN ? AND ? 
        ORDER BY created_at DESC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss", $tanggal_mulai, $tanggal_akhir);
$stmt->execute();
$result = $stmt->get_result();

$total_seluruh_donasi = 0;
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'sidebar_admin.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Laporan Donasi</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer-fill"></i>
                        Cetak Laporan
                    </button>
                </div>
            </div>

            <!-- Filter Tanggal -->
            <div class="card mb-4">
                <div class="card-header">
                    Filter Laporan
                </div>
                <div class="card-body">
                    <form method="GET" action="laporan.php" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai"
                                value="<?php echo $tanggal_mulai; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir"
                                value="<?php echo $tanggal_akhir; ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="filter" value="true"
                                class="btn btn-primary w-100">Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="laporan.php" class="btn btn-secondary w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Laporan -->
            <div id="laporan-area">
                <div class="text-center mb-4 d-none d-print-block">
                    <img src="https://lazismu.org/images/logo.png" alt="Logo Lazismu" width="150">
                    <h3 class="mt-2">Laporan Donasi Lazismu Tulungagung</h3>
                    <p>Periode: <?php echo date('d M Y', strtotime($tanggal_mulai)); ?> -
                        <?php echo date('d M Y', strtotime($tanggal_akhir)); ?></p>
                    <hr>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" class="text-center">#</th>
                                <th scope="col">Nama Program</th>
                                <th scope="col" class="text-end">Donasi Terkumpul</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                $i = 1;
                                while ($row = $result->fetch_assoc()) {
                                    $total_seluruh_donasi += $row['donasi_terkumpul'];
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_program']); ?></td>
                                <td class="text-end">Rp
                                    <?php echo number_format($row['donasi_terkumpul'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo '<tr><td colspan="3" class="text-center">Tidak ada data donasi pada periode ini.</td></tr>';
                            }
                            $stmt->close();
                            ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-dark fw-bold">
                                <td colspan="2" class="text-end">TOTAL KESELURUHAN</td>
                                <td class="text-end">Rp <?php echo number_format($total_seluruh_donasi, 0, ',', '.'); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<?php require_once 'footer_admin.php'; ?>