<?php
require_once '../includes/config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: ../login.php'); exit(); }
$page_title = "Kelola Dokumentasi Kegiatan";
require_once 'templates/header_admin.php';

$result = $mysqli->query("SELECT * FROM dokumentasi_kegiatan ORDER BY created_at DESC");
?>
<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_admin.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
                <a href="tambah_dokumentasi.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Dokumentasi</a>
            </div>
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Foto</th>
                                <th>Judul</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): $no = 1; while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><img src="../assets/uploads/dokumentasi/<?php echo htmlspecialchars($row['gambar']); ?>" width="100" class="img-thumbnail" style="height: 60px; object-fit: cover;"></td>
                                <td><?php echo htmlspecialchars($row['judul']); ?></td>
                                <td><?php echo htmlspecialchars(substr($row['deskripsi'], 0, 80)) . '...'; ?></td>
                                <td>
                                    <a href="edit_dokumentasi.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                    <a href="hapus_dokumentasi.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus dokumentasi ini?');"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="5" class="text-center py-3">Belum ada data dokumentasi.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
<?php require_once 'templates/footer_admin.php'; ?>
