<?php
require_once 'includes/config.php';
$page_title = "Lupa Kata Sandi";
require_once 'includes/templates/header.php';

$errors = [];
$success_message = '';
$show_form = true; // Variabel untuk mengontrol tampilan form

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil dan bersihkan nomor yang diinput pengguna
    $input_nomor = trim($_POST['no_telepon']);
    $nomor_bersih = preg_replace('/[^\d]/', '', $input_nomor);

    // 2. Siapkan dua format nomor: dengan awalan '0' dan '62'
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
    
    // 3. Cari di database menggunakan kedua format
    $stmt = $mysqli->prepare("SELECT id FROM user WHERE no_telepon = ? OR no_telepon = ?");
    $stmt->bind_param("ss", $nomor_format_0, $nomor_format_62);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id);
        $stmt->fetch();

        $token = rand(100000, 999999); // 6 digit kode verifikasi
        $expiry = date("Y-m-d H:i:s", strtotime('+15 minutes')); // Token berlaku 15 menit

        $stmt_update = $mysqli->prepare("UPDATE user SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        $stmt_update->bind_param("ssi", $token, $expiry, $user_id);
        $stmt_update->execute();

        $pesan = "Kode verifikasi Lazismu Anda adalah: *$token*. Jangan berikan kode ini kepada siapapun. Kode berlaku selama 15 menit.";
        kirimNotifikasiWA($nomor_format_62, $pesan);

        $_SESSION['reset_user_id'] = $user_id;
        
        // Tampilkan pesan sukses dan sembunyikan form
        $success_message = "Kode verifikasi telah berhasil dikirim ke nomor WhatsApp Anda.";
        $show_form = false;

    } else {
        $errors[] = "Nomor WhatsApp tidak terdaftar di sistem kami.";
    }
    $stmt->close();
}
?>
<main class="min-h-screen bg-gray-50 flex items-center justify-center p-6" x-data="{ isLoading: false }">
    <div class="w-full max-w-md">
        
        <!-- Back Button -->
        <a href="<?php echo BASE_URL; ?>/login" class="inline-flex items-center gap-2 text-gray-500 hover:text-primary-orange font-medium text-sm mb-6 transition-colors">
            <i class="bi bi-arrow-left"></i> Kembali ke Login
        </a>

        <div class="bg-white p-8 md:p-10 rounded-[24px] shadow-card border border-gray-100">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-key text-2xl text-primary-orange"></i>
                </div>
                <h1 class="text-2xl font-bold text-dark-text tracking-tight mb-2">Lupa Kata Sandi?</h1>
                <?php if ($show_form): ?>
                    <p class="text-gray-500 text-sm leading-relaxed">Jangan khawatir! Masukkan nomor WhatsApp yang terdaftar. Kami akan mengirimkan instruksi untuk mengatur ulang sandi Anda.</p>
                <?php endif; ?>
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

            <?php if ($success_message): ?>
            <div class="bg-green-50 border border-green-200 p-6 mb-6 rounded-2xl text-center shadow-sm">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="bi bi-check-lg text-green-600 text-xl font-bold"></i>
                </div>
                <h3 class="font-bold text-green-800 text-lg mb-2">Kode Terkirim!</h3>
                <p class="text-green-700 text-sm mb-6 leading-relaxed"><?php echo $success_message; ?></p>
                <a href="<?= BASE_URL ?>/reset_sandi" class="block w-full bg-primary-orange text-white font-bold py-3.5 rounded-xl hover:bg-orange-600 active:scale-[0.98] transition-all shadow-lg shadow-orange-200">
                    Masukkan Kode Verifikasi
                </a>
            </div>
            <?php endif; ?>

            <?php if ($show_form): ?>
            <form action="<?= BASE_URL ?>/lupa_sandi" method="POST" class="space-y-6" @submit="isLoading = true">
                <!-- Floating Label No WhatsApp -->
                <div class="relative group">
                    <input type="tel" id="no_telepon" name="no_telepon" required placeholder=" " 
                        class="block w-full px-4 pt-6 pb-2 pl-12 text-gray-900 bg-gray-50 border-2 border-gray-100 rounded-xl appearance-none focus:outline-none focus:bg-white focus:ring-0 focus:border-primary-orange peer transition-colors font-medium">
                    <i class="bi bi-whatsapp absolute left-4 top-4 text-gray-400 peer-focus:text-primary-orange transition-colors"></i>
                    <label for="no_telepon" class="absolute text-gray-500 duration-300 transform -translate-y-3 scale-75 top-4 z-10 origin-[0] left-12 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-3 peer-focus:text-primary-orange peer-focus:font-bold">No. WhatsApp Aktif</label>
                </div>

                <button type="submit" :disabled="isLoading"
                    class="w-full flex items-center justify-center gap-2 bg-primary-orange text-white text-base font-bold py-3.5 rounded-xl hover:bg-orange-600 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] transition-all shadow-lg shadow-orange-200 disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-show="!isLoading">Kirim Kode Verifikasi</span>
                    <i x-show="isLoading" class="bi bi-arrow-repeat animate-spin text-xl"></i>
                </button>
            </form>
            <?php endif; ?>
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