<?php
require_once '../includes/config.php';

if (!isset($_SESSION['amil_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Edit Profil Saya";
$id_amil_login = $_SESSION['amil_id'];

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $no_telepon = trim($_POST['no_telepon']);
    $foto_lama = $_POST['foto_lama'];
    $nama_foto_baru = $foto_lama;

    // --- Logika Upload Foto yang Diperbaiki ---
    // Cek apakah ada file yang diunggah dan tidak ada error
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../assets/uploads/amil/";
        
        // Pastikan direktori upload ada dan bisa ditulisi
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        if (is_writable($target_dir)) {
            $nama_foto_baru = time() . '_' . basename($_FILES["foto"]["name"]);
            $target_file = $target_dir . $nama_foto_baru;
            
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                // Hapus foto lama jika bukan default
                if ($foto_lama != 'default.png' && file_exists($target_dir . $foto_lama)) {
                    unlink($target_dir . $foto_lama);
                }
            } else {
                $_SESSION['error_message'] = "Gagal memindahkan file yang diunggah.";
                $nama_foto_baru = $foto_lama; // Kembalikan ke foto lama jika upload gagal
            }
        } else {
             $_SESSION['error_message'] = "Direktori upload tidak bisa ditulisi. Silakan cek izin folder di cPanel.";
             $nama_foto_baru = $foto_lama;
        }
    }

    // Update data ke database
    if (!isset($_SESSION['error_message'])) {
        $stmt = $mysqli->prepare("UPDATE amil SET nama_lengkap = ?, no_telepon = ?, foto = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nama_lengkap, $no_telepon, $nama_foto_baru, $id_amil_login);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Profil berhasil diperbarui.";
            // Perbarui juga nama di sesi
            $_SESSION['amil_nama_lengkap'] = $nama_lengkap;
        } else {
            $_SESSION['error_message'] = "Gagal memperbarui profil di database.";
        }
        $stmt->close();
    }
    header("Location: edit_profil.php");
    exit();
}

// Ambil data amil saat ini
$stmt_select = $mysqli->prepare("SELECT nama_lengkap, no_telepon, foto FROM amil WHERE id = ?");
$stmt_select->bind_param("i", $id_amil_login);
$stmt_select->execute();
$result = $stmt_select->get_result();
$amil = $result->fetch_assoc();
$stmt_select->close();

require_once 'templates/header_amil.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_amil.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h1 class="h2 pt-3 pb-2 mb-3 border-bottom"><?php echo $page_title; ?></h1>
            <?php
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']);
            }
            ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- PERBAIKAN: Menambahkan enctype="multipart/form-data" yang wajib untuk upload file -->
                    <form action="edit_profil.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($amil['foto']); ?>">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <img src="../assets/uploads/amil/<?php echo htmlspecialchars($amil['foto']); ?>"
                                    class="img-thumbnail mb-3" alt="Foto Profil"
                                    style="width: 150px; height: 150px; object-fit: cover;">
                                <div class="mb-3">
                                    <label for="foto" class="form-label">Ganti Foto Profil</label>
                                    <input class="form-control form-control-sm" type="file" id="foto" name="foto">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                                        value="<?php echo htmlspecialchars($amil['nama_lengkap']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="no_telepon" class="form-label">No. Telepon</label>
                                    <input type="text" class="form-control" id="no_telepon" name="no_telepon"
                                        value="<?php echo htmlspecialchars($amil['no_telepon']); ?>">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<?php require_once 'templates/footer_amil.php'; ?>