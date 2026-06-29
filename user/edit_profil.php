<?php
// Memuat file konfigurasi, yang seharusnya sudah memanggil session_start()
require_once '../includes/config.php';

// Pengecekan login user, jika tidak ada, arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Edit Profil";
$user_id = $_SESSION['user_id'];
$errors = [];

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $no_telepon = trim($_POST['no_telepon']);
    $foto_lama = $_POST['foto_lama'];
    $nama_foto_baru = $foto_lama;

    // Validasi dasar
    if (empty($nama_lengkap) || empty($email)) {
        $errors[] = "Nama lengkap dan email tidak boleh kosong.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    // Cek apakah email baru sudah digunakan oleh user lain
    $stmt_check = $mysqli->prepare("SELECT id FROM user WHERE email = ? AND id != ?");
    $stmt_check->bind_param("si", $email, $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $errors[] = "Email ini sudah terdaftar untuk akun lain.";
    }
    $stmt_check->close();

    // --- Logika Upload Foto Baru ---
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "../assets/uploads/user/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $nama_foto_baru = time() . '_' . basename($_FILES["foto"]["name"]);
        $target_file = $target_dir . $nama_foto_baru;
        
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            // Hapus foto lama jika bukan default
            if ($foto_lama != 'default.png' && file_exists($target_dir . $foto_lama)) {
                unlink($target_dir . $foto_lama);
            }
        } else {
            $errors[] = "Gagal mengunggah foto.";
            $nama_foto_baru = $foto_lama; // Kembalikan ke foto lama jika upload gagal
        }
    }

    // Jika tidak ada error, update data
    if (empty($errors)) {
        $stmt_update = $mysqli->prepare("UPDATE user SET nama_lengkap = ?, email = ?, no_telepon = ?, foto = ? WHERE id = ?");
        $stmt_update->bind_param("ssssi", $nama_lengkap, $email, $no_telepon, $nama_foto_baru, $user_id);
        
        if ($stmt_update->execute()) {
            $_SESSION['user_nama_lengkap'] = $nama_lengkap;
            $_SESSION['user_foto'] = $nama_foto_baru;
            $_SESSION['success_message'] = "Profil Anda berhasil diperbarui.";
            header("Location: edit_profil.php");
            exit();
        } else {
            $errors[] = "Gagal memperbarui profil. Silakan coba lagi.";
        }
        $stmt_update->close();
    }
}

// Ambil data user saat ini untuk ditampilkan di form
$stmt_user = $mysqli->prepare("SELECT nama_lengkap, email, no_telepon, foto FROM user WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();
$stmt_user->close();

// Memuat header user
require_once 'templates/header_user.php';
?>

<!-- Konten Edit Profil User -->
<section class="py-16 px-4 md:px-12 bg-light-bg">
    <div class="container mx-auto max-w-4xl">

        <div class="bg-white p-8 rounded-2xl shadow-lg scroll-animate">
            <h1 class="text-3xl font-bold text-dark-text mb-6">Edit Profil</h1>

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

            <form action="edit_profil.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($user_data['foto']); ?>">

                <!-- Pratinjau Foto dan Tombol Upload -->
                <div class="text-center">
                    <img id="foto-preview"
                        src="../assets/uploads/user/<?php echo htmlspecialchars($user_data['foto']); ?>"
                        class="w-32 h-32 rounded-full mx-auto mb-4 object-cover border-4 border-gray-200">
                    <label for="foto-upload"
                        class="cursor-pointer bg-gray-200 text-sm font-semibold px-4 py-2 rounded-full hover:bg-gray-300 transition">
                        Ganti Foto
                    </label>
                    <input type="file" id="foto-upload" name="foto" class="hidden" accept="image/*">
                </div>

                <div>
                    <label for="nama_lengkap" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap"
                        value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" required
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email"
                        value="<?php echo htmlspecialchars($user_data['email']); ?>" required
                        class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-primary-orange focus:border-primary-orange">
                </div>
                <div>
                    <label for="no_telepon" class="block text-sm font-medium text-gray-700">Nomor HP (WhatsApp)</label>
                    <input type="tel" id="no_telepon" name="no_telepon"
                        value="<?php echo htmlspecialchars($user_data['no_telepon']); ?>"
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

<script>
// JavaScript untuk pratinjau gambar saat dipilih
document.getElementById('foto-upload').addEventListener('change', function(event) {
    const [file] = event.target.files;
    if (file) {
        document.getElementById('foto-preview').src = URL.createObjectURL(file);
    }
});
</script>

<?php
// Memuat footer user
require_once 'templates/footer_user.php';
?>