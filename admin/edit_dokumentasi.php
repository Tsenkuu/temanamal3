<?php
require_once '../includes/config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: ../login.php'); exit(); }
$page_title = "Edit Dokumentasi";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $mysqli->prepare("SELECT * FROM dokumentasi_kegiatan WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) { header("Location: kelola_dokumentasi.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $gambar = $data['gambar'];
    
    if (!empty($_FILES['gambar']['name'])) {
        $target_dir = "../assets/uploads/dokumentasi/";
        $file_extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $file_name = time() . '_' . uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                // Hapus gambar lama
                if (file_exists($target_dir . $data['gambar'])) unlink($target_dir . $data['gambar']);
                $gambar = $file_name;
            }
        }
    }
    
    $stmt_update = $mysqli->prepare("UPDATE dokumentasi_kegiatan SET judul=?, deskripsi=?, gambar=? WHERE id=?");
    $stmt_update->bind_param("sssi", $judul, $deskripsi, $gambar, $id);
    
    if ($stmt_update->execute()) {
        $_SESSION['success_message'] = "Dokumentasi berhasil diperbarui.";
        header("Location: kelola_dokumentasi.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui data.";
    }
}
require_once 'templates/header_admin.php';
?>
<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_admin.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h1 class="h2 pt-3 pb-2 mb-3 border-bottom"><?php echo $page_title; ?></h1>
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Judul Kegiatan</label>
                            <input type="text" name="judul" class="form-control" required value="<?php echo htmlspecialchars($data['judul']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Singkat</label>
                            <textarea name="deskripsi" class="form-control" rows="3" required><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Saat Ini</label><br>
                            <img src="../assets/uploads/dokumentasi/<?php echo htmlspecialchars($data['gambar']); ?>" class="img-thumbnail mb-2" style="max-height: 200px;">
                            <input type="file" name="gambar" class="form-control" accept="image/*">
                            <div class="form-text">Biarkan kosong jika tidak ingin mengganti foto.</div>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="kelola_dokumentasi.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<?php require_once 'templates/footer_admin.php'; ?>
