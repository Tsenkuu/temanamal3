<?php
require_once '../includes/config.php';

// Hanya 'owner' yang bisa mengakses halaman ini
if (!isset($_SESSION['admin_id']) || (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] != 'owner')) {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    header('Location: dashboard.php');
    exit();
}

$page_title = "Ekspor Data Pengguna";
require_once 'templates/header_admin.php';
?>
<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_admin.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Pilih Data untuk Diekspor ke Excel (XLSX)</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Pilih salah satu jenis data pengguna di bawah ini untuk diunduh dalam format Excel. Laporan akan berisi Nama Lengkap, Username, dan kata sandi default.</p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start mt-4">
                        <a href="proses_export_xlsx.php?type=admin" class="btn btn-primary btn-lg px-4 me-md-2">
                            <i class="bi bi-file-earmark-spreadsheet-fill me-2"></i> Ekspor Data Admin
                        </a>
                        <a href="proses_export_xlsx.php?type=amil" class="btn btn-success btn-lg px-4">
                            <i class="bi bi-file-earmark-spreadsheet-fill me-2"></i> Ekspor Data Amil
                        </a>
                    </div>
                    <div class="alert alert-warning mt-4" role="alert">
                        <h4 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i> Catatan Keamanan Penting</h4>
                        <p>Fitur ini akan menghasilkan dokumen yang berisi informasi sensitif. Pastikan Anda menyimpan dan mendistribusikan berkas Excel yang diunduh secara aman dan hanya kepada pihak yang berwenang.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php require_once 'templates/footer_admin.php'; ?>

