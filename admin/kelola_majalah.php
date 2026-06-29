<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
include '../includes/config.php';
include 'functions.php';

$page_title = "Kelola Majalah";
include 'templates/header_admin.php';
require_once 'templates/sidebar_admin.php';

$result = $mysqli->query("SELECT * FROM majalah ORDER BY tanggal_upload DESC");
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-semibold text-gray-800">Kelola Majalah</h1>
        <div class="flex items-center space-x-2">
            <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">Dashboard</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-800">Majalah</span>
        </div>
    </div>

    <div class="content-card">
        <div class="flex justify-between items-center mb-6">
            <h2 class="card-title">Data Majalah</h2>
            <a href="tambah_majalah.php" class="btn-primary">
                <i class="bi bi-plus-lg mr-2"></i>
                Tambah Majalah
            </a>
        </div>

        <div class="table-wrapper">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">No</th>
                        <th scope="col" class="px-6 py-3">Judul</th>
                        <th scope="col" class="px-6 py-3">Deskripsi</th>
                        <th scope="col" class="px-6 py-3">Link</th>
                        <th scope="col" class="px-6 py-3">Tanggal Upload</th>
                        <th scope="col" class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = $result->fetch_assoc()) { ?>
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4"><?php echo $no++; ?></td>
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                            <?php echo htmlspecialchars($row['judul']); ?>
                        </td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                        <td class="px-6 py-4">
                            <a href="<?php echo htmlspecialchars($row['link']); ?>" target="_blank" class="text-blue-600 hover:underline">Buka Link</a>
                        </td>
                        <td class="px-6 py-4"><?php echo date('d F Y', strtotime($row['tanggal_upload'])); ?></td>
                        <td class="px-6 py-4 flex space-x-2">
                            <a href="edit_majalah.php?id=<?php echo $row['id']; ?>" class="btn-secondary btn-icon-sm">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <a href="hapus_majalah.php?id=<?php echo $row['id']; ?>" class="btn-danger-sm btn-icon-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus majalah ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include 'templates/footer_admin.php'; ?>