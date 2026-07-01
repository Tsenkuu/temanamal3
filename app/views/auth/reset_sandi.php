<?php
require_once 'includes/config.php';

if (!isset($_SESSION['reset_user_id'])) {
    header('Location: ' . BASE_URL . '/lupa_sandi');
    exit();
}

$page_title = "Reset Kata Sandi";
$user_id = $_SESSION['reset_user_id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = trim($_POST['token']);
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    if (empty($token) || empty($password_baru) || empty($konfirmasi_password)) {
        $errors[] = "Semua kolom wajib diisi.";
    } elseif ($password_baru !== $konfirmasi_password) {
        $errors[] = "Password baru dan konfirmasi tidak cocok.";
    } elseif (strlen($password_baru) < 6) {
        $errors[] = "Password baru minimal harus 6 karakter.";
    } else {
        // Cek token di database
        $stmt = $mysqli->prepare("SELECT reset_token, reset_token_expiry FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($db_token, $db_expiry);
        $stmt->fetch();

        if ($stmt->num_rows > 0 && $db_token == $token && strtotime($db_expiry) > time()) {
            // Token valid, update password
            $password_hashed = password_hash($password_baru, PASSWORD_DEFAULT);
            $stmt_update = $mysqli->prepare("UPDATE user SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            $stmt_update->bind_param("si", $password_hashed, $user_id);
            
            if ($stmt_update->execute()) {
                unset($_SESSION['reset_user_id']);
                $_SESSION['success_message'] = "Password Anda telah berhasil direset. Silakan login kembali.";
                header('Location: ' . BASE_URL . '/login');
                exit();
            } else {
                $errors[] = "Gagal memperbarui password.";
            }
        } else {
            $errors[] = "Kode verifikasi tidak valid atau sudah kedaluwarsa.";
        }
        $stmt->close();
    }
}

require_once 'includes/templates/header.php';
?>
<main class="min-h-screen bg-gray-50 flex items-center justify-center p-6" x-data="{ isLoading: false }">
    <div class="w-full max-w-md">
        
        <div class="bg-white p-8 md:p-10 rounded-[24px] shadow-card border border-gray-100">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-shield-lock text-2xl text-primary-orange"></i>
                </div>
                <h1 class="text-2xl font-bold text-dark-text tracking-tight mb-2">Atur Ulang Sandi</h1>
                <p class="text-gray-500 text-sm leading-relaxed">Masukkan kode verifikasi 6 digit yang dikirim ke WhatsApp Anda beserta sandi baru.</p>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-100 text-red-600 p-4 mb-6 rounded-xl flex items-start gap-3 text-sm font-medium">
                <i class="bi bi-exclamation-circle-fill text-red-500 text-lg"></i>
                <div class="pt-0.5 space-y-1">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/reset_sandi" method="POST" class="space-y-6" @submit="isLoading = true">
                
                <!-- Floating Label Token -->
                <div class="relative group">
                    <input type="text" id="token" name="token" required placeholder=" " maxlength="6"
                        class="block w-full px-4 pt-6 pb-2 text-center tracking-widest text-xl text-gray-900 bg-gray-50 border-2 border-gray-100 rounded-xl appearance-none focus:outline-none focus:bg-white focus:ring-0 focus:border-primary-orange peer transition-colors font-bold">
                    <label for="token" class="absolute text-gray-500 duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3 peer-focus:text-primary-orange peer-focus:font-bold">Kode Verifikasi (6 Digit)</label>
                </div>

                <!-- Floating Label Password -->
                <div class="relative group" x-data="{ show: false }">
                    <input :type="show ? 'text' : 'password'" id="password_baru" name="password_baru" required placeholder=" " minlength="6"
                        class="block w-full px-4 pt-6 pb-2 text-gray-900 bg-white border-2 border-gray-200 rounded-xl appearance-none focus:outline-none focus:ring-0 focus:border-primary-orange peer transition-colors font-medium">
                    <label for="password_baru" class="absolute text-gray-500 duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3 peer-focus:text-primary-orange peer-focus:font-bold">Sandi Baru</label>
                    <button type="button" @click="show = !show" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="bi" :class="show ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                    </button>
                </div>

                <!-- Floating Label Konfirmasi Password -->
                <div class="relative group" x-data="{ show: false }">
                    <input :type="show ? 'text' : 'password'" id="konfirmasi_password" name="konfirmasi_password" required placeholder=" " minlength="6"
                        class="block w-full px-4 pt-6 pb-2 text-gray-900 bg-white border-2 border-gray-200 rounded-xl appearance-none focus:outline-none focus:ring-0 focus:border-primary-orange peer transition-colors font-medium">
                    <label for="konfirmasi_password" class="absolute text-gray-500 duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3 peer-focus:text-primary-orange peer-focus:font-bold">Konfirmasi Sandi Baru</label>
                </div>

                <button type="submit" :disabled="isLoading"
                    class="w-full flex items-center justify-center gap-2 bg-primary-orange text-white text-base font-bold py-3.5 rounded-xl hover:bg-orange-600 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] transition-all shadow-lg shadow-orange-200 mt-8 disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-show="!isLoading">Simpan Sandi Baru</span>
                    <i x-show="isLoading" class="bi bi-arrow-repeat animate-spin text-xl"></i>
                </button>
            </form>
            
        </div>
    </div>
</main>
<?php require_once 'includes/templates/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const footer = document.getElementById('kontak');
    if (footer) {
        footer.style.display = 'none';
    }
});
</script>
</body>

</html>