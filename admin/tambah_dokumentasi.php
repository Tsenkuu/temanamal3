<?php
require_once '../includes/config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: ../login.php'); exit(); }
$page_title = "Tambah Dokumentasi";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    
    // Upload Gambar
    $target_dir = "../assets/uploads/dokumentasi/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    
    $file_extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
    $file_name = time() . '_' . uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $file_name;
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($file_extension, $allowed_types)) {
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
            $stmt = $mysqli->prepare("INSERT INTO dokumentasi_kegiatan (judul, deskripsi, gambar) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $judul, $deskripsi, $file_name);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Dokumentasi berhasil ditambahkan.";
                header("Location: kelola_dokumentasi.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Gagal mengupload gambar.";
        }
    } else {
        $_SESSION['error_message'] = "Format gambar tidak valid (hanya JPG, JPEG, PNG, WEBP).";
    }
}
require_once 'templates/header_admin.php';
?>
<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_admin.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h1 class="h2 pt-3 pb-2 mb-3 border-bottom"><?php echo $page_title; ?></h1>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Judul Kegiatan</label>
                            <input type="text" name="judul" class="form-control" required placeholder="Contoh: Penyaluran Sembako">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Singkat</label>
                            <textarea name="deskripsi" class="form-control" rows="3" required placeholder="Jelaskan kegiatan secara singkat..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Kegiatan</label>
                            <input type="file" name="gambar" class="form-control" required accept="image/*">
                            <div class="form-text">Format: JPG, PNG, WEBP. Maksimal 2MB.</div>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="kelola_dokumentasi.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<?php require_once 'templates/footer_admin.php'; ?>
