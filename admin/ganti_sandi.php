<?php
// Memuat file konfigurasi, yang seharusnya sudah memanggil session_start()
require_once '../includes/config.php';

// Pengecekan login admin (sangat direkomendasikan)
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php'); // Arahkan ke halaman login utama jika belum login
    exit();
}

$page_title = "Ganti Password";
$admin_id = $_SESSION['admin_id'];
$errors = [];
$success_message = '';

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi dasar
    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
        $errors[] = "Semua kolom wajib diisi.";
    } elseif ($password_baru !== $konfirmasi_password) {
        $errors[] = "Password baru dan konfirmasi tidak cocok.";
    } elseif (strlen($password_baru) < 6) {
        $errors[] = "Password baru minimal harus 6 karakter.";
    } else {
        // Ambil password saat ini dari database
        $stmt = $mysqli->prepare("SELECT password FROM admin WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        // Verifikasi password lama
        if (password_verify($password_lama, $hashed_password)) {
            // Jika benar, hash password baru dan update ke database
            $password_baru_hashed = password_hash($password_baru, PASSWORD_DEFAULT);
            
            $stmt_update = $mysqli->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $stmt_update->bind_param("si", $password_baru_hashed, $admin_id);
            
            if ($stmt_update->execute()) {
                $success_message = "Password berhasil diperbarui.";
            } else {
                $errors[] = "Gagal memperbarui password di database.";
            }
            $stmt_update->close();
        } else {
            $errors[] = "Password lama yang Anda masukkan salah.";
        }
        $stmt->close();
    }
}

// Memuat header admin
require_once 'templates/header_admin.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_admin.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                <p class="mb-0"><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="ganti_sandi.php" method="POST">
                        <div class="mb-3">
                            <label for="password_lama" class="form-label">Password Lama</label>
                            <input type="password" class="form-control" id="password_lama" name="password_lama"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="password_baru" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="password_baru" name="password_baru"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="konfirmasi_password"
                                name="konfirmasi_password" required>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once 'templates/footer_admin.php'; ?>