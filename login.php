<?php
require_once 'includes/config.php';

$errors = [];
$page_title = "Login";

// Jika sudah login, redirect ke dashboard masing-masing
if (isset($_SESSION['admin_id'])) {
    header('Location: admin/index.php');
    exit();
}
if (isset($_SESSION['amil_id'])) {
    header('Location: amil/dashboard.php');
    exit();
}
if (isset($_SESSION['user_id'])) {
    header('Location: user/dashboard.php'); // Arahkan ke dashboard user
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_valid_csrf();

    if (!rate_limit_request('login_attempt', 8, 300)) {
        $errors[] = "Terlalu banyak percobaan login. Coba lagi dalam beberapa menit.";
    }

    $username = clean_text($_POST['username'] ?? '', 120); // Bisa username atau email
    $password = (string) ($_POST['password'] ?? '');

    if (empty($errors) && (empty($username) || empty($password))) {
        $errors[] = "Username/Email dan password wajib diisi.";
    } elseif (empty($errors)) {
        $login_berhasil = false;

        // 1. Cek di tabel admin
        $stmt = $mysqli->prepare("SELECT id, nama_lengkap, password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_nama_lengkap'] = $admin['nama_lengkap'];
                $login_berhasil = true;
                header('Location: admin/dashboard.php');
                exit();
            }
        }

        // 2. Jika bukan admin, cek di tabel amil
        if (!$login_berhasil) {
            $stmt = $mysqli->prepare("SELECT id, nama_lengkap, password FROM amil WHERE username = ? AND status = 'Aktif'");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result_amil = $stmt->get_result();

            if ($result_amil && $result_amil->num_rows == 1) {
                $amil = $result_amil->fetch_assoc();
                if (password_verify($password, $amil['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['amil_id'] = $amil['id'];
                    $_SESSION['amil_username'] = $username;
                    $_SESSION['amil_nama_lengkap'] = $amil['nama_lengkap'];
                    $login_berhasil = true;
                    header('Location: amil/dashboard.php');
                    exit();
                }
            }
        }

        // 3. Jika bukan amil, cek di tabel user (login dengan email)
        if (!$login_berhasil) {
            $stmt = $mysqli->prepare("SELECT id, nama_lengkap, password FROM user WHERE email = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result_user = $stmt->get_result();

            if ($result_user && $result_user->num_rows == 1) {
                $user = $result_user->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nama_lengkap'] = $user['nama_lengkap'];
                    $login_berhasil = true;
                    header('Location: user/dashboard.php');
                    exit();
                }
            }
        }
        
        // Jika setelah semua pengecekan login tetap gagal
        if (!$login_berhasil) {
            $errors[] = "Akun tidak ditemukan atau password salah.";
        }
        $stmt->close();
    }
}

require_once 'includes/templates/header.php';
?>

<main class="min-h-screen bg-gray-50 flex" x-data="{ isLoading: false }">
    
    <!-- Left Section: Image/Branding (Hidden on Mobile) -->
    <div class="hidden lg:flex lg:w-1/2 relative bg-primary-orange overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-orange to-orange-600 opacity-90 z-10"></div>
        <img src="assets/images/hero-bg.jpg" class="absolute inset-0 w-full h-full object-cover mix-blend-overlay opacity-50" alt="Background">
        <div class="relative z-20 flex flex-col justify-center px-16 text-white w-full">
            <a href="<?php echo BASE_URL; ?>/" class="mb-12 inline-flex items-center gap-2 bg-white/20 backdrop-blur-md px-4 py-2 rounded-full w-max hover:bg-white/30 transition-colors z-50">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <h1 class="text-5xl font-display font-bold leading-tight mb-6">
                Lanjutkan Langkah <br>Kebaikanmu Hari Ini.
            </h1>
            <p class="text-orange-50 text-lg max-w-md">
                Setiap donasi yang Anda salurkan melalui TemanAmal membawa perubahan nyata bagi mereka yang membutuhkan.
            </p>
        </div>
    </div>

    <!-- Right Section: Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 md:p-12 relative">
        
        <!-- Mobile Back Button -->
        <a href="<?php echo BASE_URL; ?>/" class="lg:hidden absolute top-6 left-6 w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm text-gray-600 z-50">
            <i class="bi bi-arrow-left text-xl"></i>
        </a>

        <div class="w-full max-w-md">
            
            <div class="text-center lg:text-left mb-10 mt-8 lg:mt-0">
                <img src="assets/images/logo.png" alt="Logo" class="h-14 lg:h-12 w-auto mx-auto lg:mx-0 mb-6 lg:mb-8">
                <h2 class="text-3xl font-bold text-dark-text tracking-tight mb-2">Selamat Datang Kembali</h2>
                <p class="text-gray-500 text-sm">Masuk ke akun Anda untuk mulai berdonasi</p>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 p-4 mb-6 rounded-xl flex items-start gap-3 text-sm font-medium">
                <i class="bi bi-check-circle-fill text-green-500 text-lg"></i>
                <p class="pt-0.5"><?php echo $_SESSION['success_message']; ?></p>
            </div>
            <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

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

            <form action="login.php" method="POST" class="space-y-5" @submit="isLoading = true">
                <?php echo csrf_field(); ?>
                
                <!-- Floating Label Username/Email -->
                <div class="relative group">
                    <input type="text" id="username" name="username" required placeholder=" " 
                        class="block w-full px-4 pt-6 pb-2 text-gray-900 bg-white border-2 border-gray-200 rounded-xl appearance-none focus:outline-none focus:ring-0 focus:border-primary-orange peer transition-colors font-medium">
                    <label for="username" class="absolute text-gray-500 duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3 peer-focus:text-primary-orange peer-focus:font-bold">Username atau Email</label>
                </div>
                
                <!-- Floating Label Password -->
                <div class="relative group" x-data="{ show: false }">
                    <input :type="show ? 'text' : 'password'" id="password" name="password" required placeholder=" " 
                        class="block w-full px-4 pt-6 pb-2 text-gray-900 bg-white border-2 border-gray-200 rounded-xl appearance-none focus:outline-none focus:ring-0 focus:border-primary-orange peer transition-colors font-medium">
                    <label for="password" class="absolute text-gray-500 duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3 peer-focus:text-primary-orange peer-focus:font-bold">Password</label>
                    <button type="button" @click="show = !show" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="bi" :class="show ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                    </button>
                </div>

                <div class="flex items-center justify-between mt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-primary-orange border-gray-300 rounded focus:ring-primary-orange">
                        <span class="text-sm text-gray-600 font-medium">Ingat Saya</span>
                    </label>
                    <a href="lupa_sandi.php" class="text-sm font-bold text-primary-orange hover:text-orange-600">Lupa Sandi?</a>
                </div>

                <button type="submit" :disabled="isLoading"
                    class="w-full flex items-center justify-center gap-2 bg-primary-orange text-white text-base font-bold py-3.5 rounded-xl hover:bg-orange-600 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] transition-all shadow-lg shadow-orange-200 mt-8 disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-show="!isLoading">Masuk ke Akun</span>
                    <i x-show="isLoading" class="bi bi-arrow-repeat animate-spin text-xl"></i>
                </button>
            </form>

            <p class="text-center text-gray-500 text-sm mt-10">
                Belum punya akun? <a href="register.php" class="font-bold text-primary-orange hover:underline">Daftar sekarang</a>
            </p>

        </div>
    </div>
</main>

<script>
// Sembunyikan footer & header default di halaman auth (karena kita panggil header.php, kita hide tag header & footer defaultnya)
document.addEventListener('DOMContentLoaded', function() {
    const footer = document.querySelector('.site-footer');
    const header = document.querySelector('.site-header');
    if (footer) footer.style.display = 'none';
    if (header) header.style.display = 'none';
});
</script>
</body>
</html>
