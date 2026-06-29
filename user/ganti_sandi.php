<?php
// Memuat file konfigurasi, yang seharusnya sudah memanggil session_start()
require_once '../includes/config.php';

// Pengecekan login user, jika tidak ada, arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Ganti Kata Sandi";
$user_id = $_SESSION['user_id'];
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
        $stmt = $mysqli->prepare("SELECT password FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        // Verifikasi password lama
        if ($user && password_verify($password_lama, $user['password'])) {
            // Jika benar, hash password baru dan update ke database
            $password_baru_hashed = password_hash($password_baru, PASSWORD_DEFAULT);
            
            $stmt_update = $mysqli->prepare("UPDATE user SET password = ? WHERE id = ?");
            $stmt_update->bind_param("si", $password_baru_hashed, $user_id);
            
            if ($stmt_update->execute()) {
                $_SESSION['success_message'] = "Password Anda berhasil diperbarui.";
                header("Location: ganti_sandi.php"); // Refresh halaman untuk menampilkan pesan
                exit();
            } else {
                $errors[] = "Gagal memperbarui password di database.";
            }
            $stmt_update->close();
        } else {
            $errors[] = "Password lama yang Anda masukkan salah.";
        }
    }
}

// PERBAIKAN: Memuat header user
require_once 'templates/header_user.php';
?>

<!-- Konten Ganti Kata Sandi User -->
<section class="py-16 px-4 md:px-12 bg-light-bg">
    <div class="container mx-auto max-w-4xl">

        <div class="bg-white p-8 rounded-2xl shadow-lg scroll-animate">
            <h1 class="text-3xl font-bold text-dark-text mb-6">Ganti Kata Sandi</h1>

            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
                <p><?php echo $_SESSION['success_message']; ?></p>
            </div>
            <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form action="ganti_sandi.php" method="POST" class="space-y-6">
                <div>
                    <label for="password_lama" class="block text-sm font-medium text-gray-700">Password Lama</label>
                    <input type="password" id="password_lama" name="password_lama" required
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                </div>
                <div>
                    <label for="password_baru" class="block text-sm font-medium text-gray-700">Password Baru</label>
                    <input type="password" id="password_baru" name="password_baru" required
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                </div>
                <div>
                    <label for="konfirmasi_password" class="block text-sm font-medium text-gray-700">Konfirmasi Password
                        Baru</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" required
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                </div>
                <div class="border-t pt-6 flex flex-col sm:flex-row gap-4">
                    <button type="submit"
                        class="w-full sm:w-auto text-center bg-primary-orange text-white px-6 py-3 rounded-full font-bold hover:bg-orange-600 transition duration-300">
                        Simpan Perubahan
                    </button>
                    <a href="dashboard.php"
                        class="w-full sm:w-auto text-center bg-gray-200 text-dark-text px-6 py-3 rounded-full font-bold hover:bg-gray-300 transition duration-300">
                        Kembali ke Dasbor
                    </a>
                </div>
            </form>
        </div>

    </div>
</section>

<?php
// PERBAIKAN: Memuat footer user
require_once 'templates/footer_user.php';
?>