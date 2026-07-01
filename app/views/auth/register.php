<?php
require_once 'includes/config.php';

$errors = [];
$page_title = "Registrasi Akun";
$sapaan = 'Bapak';
$nama_lengkap = '';
$email = '';
$no_telepon = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_valid_csrf();

    if (!rate_limit_request('register_attempt', 5, 600)) {
        $errors[] = "Terlalu banyak percobaan registrasi. Coba lagi nanti.";
    }

    $sapaan = clean_text($_POST['sapaan'] ?? '', 20);
    $nama_lengkap = clean_text($_POST['nama_lengkap'] ?? '', 120);
    $email = strtolower(clean_text($_POST['email'] ?? '', 190));
    $password = (string) ($_POST['password'] ?? '');
    $konfirmasi_password = (string) ($_POST['konfirmasi_password'] ?? '');
    $no_telepon = normalize_phone_number($_POST['no_telepon'] ?? '');
    $allowedSapaan = ['Bapak', 'Ibu', 'Kak'];
    if (!in_array($sapaan, $allowedSapaan, true)) {
        $sapaan = 'Bapak';
    }

    // Validasi
    if (empty($nama_lengkap) || empty($email) || empty($password)) {
        $errors[] = "Nama, Email, dan Password tidak boleh kosong.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password minimal harus 8 karakter.";
    }
    if ($password !== $konfirmasi_password) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }
    if (!validate_phone_number($no_telepon)) {
        $errors[] = "Nomor WhatsApp tidak valid.";
    }

    // Cek apakah email sudah terdaftar
    $stmt_email = $mysqli->prepare("SELECT id FROM user WHERE email = ?");
    $stmt_email->bind_param("s", $email);
    $stmt_email->execute();
    $stmt_email->store_result();
    if ($stmt_email->num_rows > 0) {
        $errors[] = "Email sudah terdaftar. Silakan gunakan email lain.";
    }
    $stmt_email->close();

    // Cek apakah nomor telepon sudah terdaftar (jika diisi)
    if (!empty($no_telepon)) {
        $nomor_bersih = preg_replace('/[^\d]/', '', $no_telepon);
        $nomor_format_0 = '';
        $nomor_format_62 = '';

        if (substr($nomor_bersih, 0, 2) == '62') {
            $nomor_format_62 = $nomor_bersih;
            $nomor_format_0 = '0' . substr($nomor_bersih, 2);
        } else {
            if (substr($nomor_bersih, 0, 1) == '0') {
                $nomor_format_0 = $nomor_bersih;
                $nomor_format_62 = '62' . substr($nomor_bersih, 1);
            } else {
                $nomor_format_0 = '0' . $nomor_bersih;
                $nomor_format_62 = '62' . $nomor_bersih;
            }
        }

        $stmt_phone = $mysqli->prepare("SELECT id FROM user WHERE no_telepon = ? OR no_telepon = ?");
        $stmt_phone->bind_param("ss", $nomor_format_0, $nomor_format_62);
        $stmt_phone->execute();
        $stmt_phone->store_result();
        if ($stmt_phone->num_rows > 0) {
            $errors[] = "Nomor telepon sudah digunakan untuk akun lain.";
        }
        $stmt_phone->close();
    }


    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        
        // Menambahkan sapaan ke query INSERT
        $stmt_insert = $mysqli->prepare("INSERT INTO user (nama_lengkap, sapaan, email, password, no_telepon) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sssss", $nama_lengkap, $sapaan, $email, $password_hashed, $no_telepon);

        if ($stmt_insert->execute()) {
            $_SESSION['success_message'] = "Registrasi berhasil! Silakan login dengan akun Anda.";
            header("Location: " . BASE_URL . "/login");
            exit();
        } else {
            $errors[] = "Registrasi gagal. Silakan coba lagi.";
        }
        $stmt_insert->close();
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
                Bergabunglah Dalam <br>Barisan Kebaikan.
            </h1>
            <p class="text-orange-50 text-lg max-w-md">
                Jadilah bagian dari ribuan orang baik yang telah bergabung bersama TemanAmal untuk menebar manfaat.
            </p>
        </div>
    </div>

    <!-- Right Section: Register Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 md:p-12 relative overflow-y-auto max-h-screen">
        
        <!-- Mobile Back Button -->
        <a href="<?php echo BASE_URL; ?>/" class="lg:hidden absolute top-6 left-6 w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm text-gray-600 z-50">
            <i class="bi bi-arrow-left text-xl"></i>
        </a>

        <div class="w-full max-w-lg pt-16 lg:pt-0 pb-10">
            
            <div class="text-center lg:text-left mb-10 mt-8 lg:mt-0">
                <img src="assets/images/logo.png" alt="Logo" class="h-14 lg:h-12 w-auto mx-auto lg:mx-0 mb-6 lg:mb-8">
                <h2 class="text-3xl font-bold text-dark-text tracking-tight mb-2">Buat Akun Baru</h2>
                <p class="text-gray-500 text-sm">Daftar sekarang dan mulai sebarkan kebaikan</p>
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

            <form action="<?= BASE_URL ?>/registrasi" method="POST" class="space-y-5" @submit="isLoading = true">
                <?php echo csrf_field(); ?>
                
                <div class="flex gap-4">
                    <!-- Floating Label Sapaan -->
                    <div class="relative w-1/3 group">
                        <select id="sapaan" name="sapaan" required class="block w-full px-4 pt-6 pb-2 text-gray-900 bg-white border-2 border-gray-200 rounded-xl appearance-none focus:outline-none focus:ring-0 focus:border-primary-orange peer transition-colors font-medium">
                            <option <?php echo $sapaan === 'Bapak' ? 'selected' : ''; ?>>Bapak</option>
                            <option <?php echo $sapaan === 'Ibu' ? 'selected' : ''; ?>>Ibu</option>
                            <option <?php echo $sapaan === 'Kak' ? 'selected' : ''; ?>>Kak</option>
                        </select>
                        <i class="bi bi-chevron-down absolute right-4 top-5 text-gray-400 pointer-events-none"></i>
                        <label for="sapaan" class="absolute text-gray-500 duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-focus:text-primary-orange peer-focus:font-bold">Sapaan</label>
                    </div>

                    <!-- Floating Label Nama Lengkap -->
                    <div class="relative w-2/3 group">
                        <input type="text" id="nama_lengkap" name="nama_lengkap" required placeholder=" " 
                            value="<?php echo htmlspecialchars($nama_lengkap ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            class="block w-full px-4 pt-6 pb-2 text-gray-900 bg-white border-2 border-gray-200 rounded-xl appearance-none focus:outline-none focus:ring-0 focus:border-primary-orange peer transition-colors font-medium">
                        <label for="nama_lengkap" class="absolute text-gray-500 duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3 peer-focus:text-primary-orange peer-focus:font-bold">Nama Lengkap</label>
                    </div>
                </div>

                <!-- Floating Label Email -->
                <div class="relative group">
                    <input type="email" id="email" name="email" required placeholder=" " 
                        value="<?php echo htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        class="block w-full px-4 pt-6 pb-2 text-gray-900 bg-white border-2 border-gray-200 rounded-xl appearance-none focus:outline-none focus:ring-0 focus:border-primary-orange peer transition-colors font-medium">
                    <label for="email" class="absolute text-gray-500 duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3 peer-focus:text-primary-orange peer-focus:font-bold">Alamat Email</label>
                </div>

                <!-- Floating Label No WhatsApp -->
                <div class="relative group">
                    <input type="tel" id="no_telepon" name="no_telepon" required placeholder=" " 
                        value="<?php echo htmlspecialchars($no_telepon ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        class="block w-full px-4 pt-6 pb-2 pl-12 text-gray-900 bg-white border-2 border-gray-200 rounded-xl appearance-none focus:outline-none focus:ring-0 focus:border-primary-orange peer transition-colors font-medium">
                    <i class="bi bi-whatsapp absolute left-4 top-4 text-gray-400 peer-focus:text-primary-orange transition-colors"></i>
                    <label for="no_telepon" class="absolute text-gray-500 duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-12 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3 peer-focus:text-primary-orange peer-focus:font-bold">No. WhatsApp Aktif</label>
                </div>

                <div class="flex flex-col md:flex-row gap-5">
                    <!-- Floating Label Password -->
                    <div class="relative w-full group" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" id="password" name="password" required placeholder=" " minlength="8"
                            class="block w-full px-4 pt-6 pb-2 text-gray-900 bg-white border-2 border-gray-200 rounded-xl appearance-none focus:outline-none focus:ring-0 focus:border-primary-orange peer transition-colors font-medium">
                        <label for="password" class="absolute text-gray-500 duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3 peer-focus:text-primary-orange peer-focus:font-bold">Buat Sandi</label>
                        <button type="button" @click="show = !show" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i class="bi" :class="show ? 'bi-eye-slash-fill' : 'bi-eye-fill'"></i>
                        </button>
                    </div>

                    <!-- Floating Label Konfirmasi Password -->
                    <div class="relative w-full group" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" id="konfirmasi_password" name="konfirmasi_password" required placeholder=" " minlength="8"
                            class="block w-full px-4 pt-6 pb-2 text-gray-900 bg-white border-2 border-gray-200 rounded-xl appearance-none focus:outline-none focus:ring-0 focus:border-primary-orange peer transition-colors font-medium">
                        <label for="konfirmasi_password" class="absolute text-gray-500 duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-4 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3 peer-focus:text-primary-orange peer-focus:font-bold">Ulangi Sandi</label>
                    </div>
                </div>
                
                <p class="text-xs text-gray-400 mt-2">Dengan mendaftar, Anda menyetujui Syarat & Ketentuan kami.</p>

                <button type="submit" :disabled="isLoading"
                    class="w-full flex items-center justify-center gap-2 bg-primary-orange text-white text-base font-bold py-3.5 rounded-xl hover:bg-orange-600 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] transition-all shadow-lg shadow-orange-200 mt-8 disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-show="!isLoading">Daftar Akun</span>
                    <i x-show="isLoading" class="bi bi-arrow-repeat animate-spin text-xl"></i>
                </button>
            </form>

            <p class="text-center text-gray-500 text-sm mt-10">
                Sudah punya akun? <a href="<?= BASE_URL ?>/login" class="font-bold text-primary-orange hover:underline">Masuk di sini</a>
            </p>

        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const footer = document.querySelector('.site-footer');
    const header = document.querySelector('.site-header');
    if (footer) footer.style.display = 'none';
    if (header) header.style.display = 'none';
});
</script>
</body>
</html>
