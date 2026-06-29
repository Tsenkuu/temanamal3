<?php
require_once '../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
$page_title = "Kelola Metode Pembayaran";
require_once 'templates/header_admin.php';

$result_metode = $mysqli->query("SELECT * FROM metode_pembayaran ORDER BY tipe, nama_metode");
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-bold text-dark-text"><?php echo $page_title; ?></h1>
        <a href="tambah_metode_pembayaran.php" class="btn-primary">
            <i class="bi bi-plus-circle mr-2"></i> Tambah Metode Baru
        </a>
    </div>

    <?php
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    ?>

    <div class="content-card mt-6">
        <div class="table-wrapper overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Nama Metode</th>
                        <th scope="col" class="px-6 py-3">Detail</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_metode && $result_metode->num_rows > 0): ?>
                    <?php while($metode = $result_metode->fetch_assoc()): ?>
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-semibold text-dark-text">
                            <?php echo htmlspecialchars($metode['nama_metode']); ?>
                            <div class="flex gap-2 mt-1">
                                <span class="badge-info"><?php echo $metode['tipe']; ?></span>
                                <span class="badge-secondary"><?php echo $metode['kategori']; ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if($metode['tipe'] == 'Transfer Bank'): ?>
                            <span class="font-medium"><?php echo htmlspecialchars($metode['detail_1']); ?></span>
                            (a.n. <?php echo htmlspecialchars($metode['detail_2']); ?>)
                            <?php else: ?>
                            <img src="../assets/images/qris/<?php echo htmlspecialchars($metode['gambar']); ?>"
                                width="80" class="rounded-md">
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4"><span
                                class="badge-<?php echo $metode['status'] == 'Aktif' ? 'success' : 'secondary'; ?>"><?php echo $metode['status']; ?></span>
                        </td>
                        <td class="px-6 py-4 text-center flex justify-center gap-2">
                            <a href="edit_metode_pembayaran.php?id=<?php echo $metode['id']; ?>"
                                class="btn-icon bg-yellow-100 text-yellow-600 hover:bg-yellow-200" title="Edit"><i
                                    class="bi bi-pencil-square"></i></a>
                            <form action="hapus_metode_pembayaran.php" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus metode pembayaran ini?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo $metode['id']; ?>">
                                <button type="submit" class="btn-icon bg-red-100 text-red-600 hover:bg-red-200" title="Hapus"><i
                                        class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center">Belum ada metode pembayaran.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once 'templates/footer_admin.php'; ?>
