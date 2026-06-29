<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Keranjang Sampah";
require_once 'templates/header_admin.php';

$tables_config = [
    'program_base' => ['label' => 'Program Donasi', 'title_col' => 'nama_program'],
    'berita_base' => ['label' => 'Berita / Artikel', 'title_col' => 'judul'],
    'donasi_base' => ['label' => 'Donasi', 'title_col' => 'nama_donatur'],
    'kabar_program_base' => ['label' => 'Kabar Program', 'title_col' => 'judul'],
    'amil_base' => ['label' => 'Amil', 'title_col' => 'nama'],
    'dokumentasi_kegiatan_base' => ['label' => 'Dokumentasi', 'title_col' => 'judul'],
    'komentar_base' => ['label' => 'Komentar', 'title_col' => 'nama'],
    'kotak_infak_base' => ['label' => 'Kotak Infak', 'title_col' => 'nama_masjid'],
    'majalah_base' => ['label' => 'Majalah', 'title_col' => 'judul'],
    'metode_pembayaran_base' => ['label' => 'Metode Pembayaran', 'title_col' => 'nama_bank'],
    'riwayat_pengambilan_base' => ['label' => 'Riwayat Pengambilan', 'title_col' => 'nama_pengambil'],
    'slider_images_base' => ['label' => 'Slider', 'title_col' => 'id'],
    'laporan_transaksi_base' => ['label' => 'Laporan Transaksi', 'title_col' => 'keterangan'],
    'tugas_pengambilan_base' => ['label' => 'Tugas Pengambilan', 'title_col' => 'status']
];

$selected_table = isset($_GET['table']) && array_key_exists($_GET['table'], $tables_config) ? $_GET['table'] : 'program_base';
$config = $tables_config[$selected_table];

// Fetch deleted data
$query = "SELECT id, {$config['title_col']} as display_title, deleted_at FROM `$selected_table` WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC";
$result = $mysqli->query($query);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Keranjang Sampah</h1>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
            <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-trash-alt mr-2"></i>Data yang Dihapus</h6>
            <form method="GET" class="form-inline">
                <label class="mr-2">Pilih Kategori:</label>
                <select name="table" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                    <?php foreach ($tables_config as $table => $cfg): ?>
                        <option value="<?= $table ?>" <?= $selected_table === $table ? 'selected' : '' ?>><?= $cfg['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul / Nama</th>
                            <th>Waktu Dihapus</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars((string)$row['display_title']) ?></td>
                                <td><?= date('d M Y H:i', strtotime($row['deleted_at'])) ?></td>
                                <td>
                                    <form action="proses_restore.php" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="table" value="<?= $selected_table ?>">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Apakah Anda yakin ingin memulihkan data ini?');">
                                            <i class="fas fa-undo"></i> Restore
                                        </button>
                                    </form>
                                    <!-- Delete Permanently Form -->
                                    <form action="proses_hapus_permanen.php" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="table" value="<?= $selected_table ?>">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('PERINGATAN: Aksi ini akan menghapus data beserta file gambarnya secara PERMANEN. Anda yakin?');">
                                            <i class="fas fa-trash"></i> Hapus Permanen
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data yang dihapus pada kategori ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>
