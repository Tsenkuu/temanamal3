<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Edit Data Amil";
$id_amil = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_amil === 0) {
    header("Location: kelola_amil.php");
    exit;
}

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $jabatan = trim($_POST['jabatan']);
    $username = trim($_POST['username']);
    $no_telepon = trim($_POST['no_telepon']);
    $status = $_POST['status'];
    $tampilkan_di_beranda = $_POST['tampilkan_di_beranda'];
    $password = $_POST['password'];
    $foto_lama = $_POST['foto_lama'];
    $nama_foto_baru = $foto_lama;
    $cropped_image_data = $_POST['cropped_image_data'] ?? null;

    $target_dir = "../assets/uploads/amil/";

    // Logika untuk menyimpan foto yang sudah di-crop
    if (!empty($cropped_image_data)) {
        // Data gambar dalam format base64
        list($type, $data) = explode(';', $cropped_image_data);
        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);

        // Buat nama file baru yang unik
        $nama_foto_baru = time() . '_' . uniqid() . '.png';
        $target_file = $target_dir . $nama_foto_baru;

        // Simpan file gambar baru
        if (file_put_contents($target_file, $data)) {
            // Hapus foto lama jika bukan default dan file-nya ada
            if ($foto_lama != 'default.png' && file_exists($target_dir . $foto_lama)) {
                unlink($target_dir . $foto_lama);
            }
        } else {
            // Jika gagal menyimpan, kembalikan ke foto lama
            $nama_foto_baru = $foto_lama;
            $_SESSION['error_message'] = "Gagal menyimpan foto yang dipotong.";
        }
    }

    // Logika update database
    if (!empty($password)) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("UPDATE amil SET nama_lengkap=?, jabatan=?, foto=?, username=?, no_telepon=?, status=?, tampilkan_di_beranda=?, password=? WHERE id=?");
        $stmt->bind_param("ssssssssi", $nama_lengkap, $jabatan, $nama_foto_baru, $username, $no_telepon, $status, $tampilkan_di_beranda, $password_hashed, $id_amil);
    } else {
        $stmt = $mysqli->prepare("UPDATE amil SET nama_lengkap=?, jabatan=?, foto=?, username=?, no_telepon=?, status=?, tampilkan_di_beranda=? WHERE id=?");
        $stmt->bind_param("sssssssi", $nama_lengkap, $jabatan, $nama_foto_baru, $username, $no_telepon, $status, $tampilkan_di_beranda, $id_amil);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Data Amil berhasil diperbarui.";
        header("Location: kelola_amil.php");
        exit;
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui data Amil.";
    }
    $stmt->close();
}

// Ambil data amil saat ini
$stmt_select = $mysqli->prepare("SELECT nama_lengkap, jabatan, foto, username, no_telepon, status, tampilkan_di_beranda FROM amil WHERE id = ?");
$stmt_select->bind_param("i", $id_amil);
$stmt_select->execute();
$result = $stmt_select->get_result();
$amil = $result->fetch_assoc();
$stmt_select->close();

if (!$amil) {
    header("Location: kelola_amil.php");
    exit;
}

require_once 'templates/header_admin.php';
?>

<!-- DIUBAH: Menambahkan CSS untuk Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
<style>
    /* Pastikan gambar di dalam modal tidak melebihi kontainer */
    .img-container {
        max-height: 500px;
        overflow: hidden;
    }
    #imageToCrop {
        max-width: 100%;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_admin.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h1 class="h2 pt-3 pb-2 mb-3 border-bottom"><?php echo $page_title; ?></h1>
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="edit_amil.php?id=<?php echo $id_amil; ?>" method="POST" enctype="multipart/form-data" id="editAmilForm">
                        <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($amil['foto']); ?>">
                        <input type="hidden" name="cropped_image_data" id="cropped_image_data">
                        
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <img id="profileImagePreview" src="../assets/uploads/amil/<?php echo htmlspecialchars($amil['foto']); ?>"
                                     class="img-thumbnail mb-3" alt="Foto Profil" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                                <div class="mb-3">
                                    <label for="foto" class="form-label">Pilih Foto Baru</label>
                                    <input class="form-control form-control-sm" type="file" id="foto" name="foto" accept="image/*">
                                </div>
                                <!-- DIUBAH: Tombol yang tidak perlu dihapus -->
                            </div>
                            <div class="col-md-8">
                                <!-- Form fields lainnya (nama, jabatan, dll) -->
                                <div class="mb-3">
                                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                                           value="<?php echo htmlspecialchars($amil['nama_lengkap']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="jabatan" class="form-label">Jabatan</label>
                                    <input type="text" class="form-control" id="jabatan" name="jabatan"
                                           value="<?php echo htmlspecialchars($amil['jabatan']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                           value="<?php echo htmlspecialchars($amil['username']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="no_telepon" class="form-label">No. Telepon</label>
                                    <input type="text" class="form-control" id="no_telepon" name="no_telepon"
                                           value="<?php echo htmlspecialchars($amil['no_telepon']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password Baru (Opsional)</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Status Akun</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="Aktif" <?php if ($amil['status'] == 'Aktif') echo 'selected'; ?>>Aktif</option>
                                            <option value="Tidak Aktif" <?php if ($amil['status'] == 'Tidak Aktif') echo 'selected'; ?>>Tidak Aktif</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tampilkan_di_beranda" class="form-label">Tampilkan di Beranda</label>
                                        <select class="form-select" id="tampilkan_di_beranda" name="tampilkan_di_beranda" required>
                                            <option value="Ya" <?php if ($amil['tampilkan_di_beranda'] == 'Ya') echo 'selected'; ?>>Ya</option>
                                            <option value="Tidak" <?php if ($amil['tampilkan_di_beranda'] == 'Tidak') echo 'selected'; ?>>Tidak</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <a href="kelola_amil.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal untuk Crop Gambar -->
<div class="modal fade" id="cropImageModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Potong Gambar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="img-container">
          <img id="imageToCrop" src="">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="cropAndSave">Potong dan Simpan</button>
      </div>
    </div>
  </div>
</div>

<!-- DIUBAH: Menambahkan library Cropper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('foto');
    const profileImagePreview = document.getElementById('profileImagePreview');
    const cropImageModalEl = document.getElementById('cropImageModal');
    const cropImageModal = new bootstrap.Modal(cropImageModalEl);
    const imageToCrop = document.getElementById('imageToCrop');
    const cropAndSaveButton = document.getElementById('cropAndSave');
    const croppedImageDataInput = document.getElementById('cropped_image_data');
    let cropper;

    // Tampilkan modal saat gambar dipilih
    imageInput.addEventListener('change', function (e) {
        if (e.target.files && e.target.files.length > 0) {
            const reader = new FileReader();
            reader.onload = function (event) {
                imageToCrop.src = event.target.result;
                cropImageModal.show();
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    // Inisialisasi Cropper.js saat modal ditampilkan
    cropImageModalEl.addEventListener('shown.bs.modal', function () {
        if (cropper) {
            cropper.destroy();
        }
        cropper = new Cropper(imageToCrop, {
            aspectRatio: 1, // Rasio 1:1 (persegi)
            viewMode: 1,
            background: false,
            autoCropArea: 0.8,
        });
    });

    // Hancurkan instance cropper saat modal ditutup
    cropImageModalEl.addEventListener('hidden.bs.modal', function () {
        if (cropper) {
            cropper.destroy();
            // Tidak perlu reset input file di sini agar jika user batal, file tetap terpilih
            // Jika ingin direset, baris di bawah bisa diaktifkan kembali
            // imageInput.value = ''; 
        }
    });

    // Proses crop dan simpan
    cropAndSaveButton.addEventListener('click', function () {
        if (!cropper) {
            return;
        }

        const canvas = cropper.getCroppedCanvas({
            width: 400, // Ukuran output gambar
            height: 400,
        });

        // DIUBAH: Logika disederhanakan menggunakan toDataURL
        const base64data = canvas.toDataURL('image/png');
        
        // Tampilkan preview gambar yang sudah di-crop
        profileImagePreview.src = base64data;
        
        // Masukkan data base64 ke input tersembunyi
        croppedImageDataInput.value = base64data;
        
        // Tutup modal
        cropImageModal.hide();
    });
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>
