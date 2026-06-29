<?php
require_once '../includes/config.php';
// (Tambahkan logika otentikasi dan otorisasi di sini)
require_once 'templates/header.php';

// Pengecekan login admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Pesan notifikasi
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Laporan Donatur</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Laporan Donatur</li>
    </ol>

    <?php if ($message): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-file-excel me-1"></i>
            Upload Laporan dari Excel
        </div>
        <div class="card-body">
            <p>Upload file Excel (.xlsx, .xls) dengan format kolom: <strong>A: Nama Donatur</strong>, <strong>B:
                    Jumlah</strong>, <strong>C: Tanggal (YYYY-MM-DD)</strong>, <strong>D: Keterangan</strong>.</p>
            <form action="proses_upload_excel.php" method="post" enctype="multipart/form-data">
                <div class="input-group">
                    <input type="file" class="form-control" name="file_excel" id="file_excel" accept=".xlsx, .xls"
                        required>
                    <button class="btn btn-primary" type="submit" name="upload">Upload File</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Data Donasi Terakhir
        </div>
        <div class="card-body">
            <table id="datatablesSimple">
                <!-- Gunakan library seperti DataTables untuk sorting & pagination -->
                <thead>
                    <tr>
                        <th>Nama Donatur</th>
                        <th>Jumlah</th>
                        <th>Tanggal Donasi</th>
                        <th>Keterangan</th>
                        <th>Di-upload Pada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT nama_donatur, jumlah, tanggal_donasi, keterangan, uploaded_at FROM donasi ORDER BY uploaded_at DESC LIMIT 100";
                    if ($result = $mysqli->query($sql)) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['nama_donatur']) . "</td>";
                            echo "<td>Rp " . number_format($row['jumlah'], 0, ',', '.') . "</td>";
                            echo "<td>" . date('d M Y', strtotime($row['tanggal_donasi'])) . "</td>";
                            echo "<td>" . htmlspecialchars($row['keterangan']) . "</td>";
                            echo "<td>" . date('d M Y H:i', strtotime($row['uploaded_at'])) . "</td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>