<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Tambah Program Donasi Baru";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_program = trim($_POST['nama_program']);
    $deskripsi = trim($_POST['deskripsi']);
    $kategori = $_POST['kategori'];
    $target_donasi = preg_replace('/[^\d]/', '', $_POST['target_donasi']);
    $metode_pembayaran_ids = isset($_POST['metode_pembayaran_ids']) ? implode(',', $_POST['metode_pembayaran_ids']) : '';
    $nama_gambar = 'placeholder.png';

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../assets/uploads/program/";
        // Sanitize filename
        $image_name = basename($_FILES["gambar"]["name"]);
        $safe_image_name = preg_replace("/[^a-zA-Z0-9._-]/", "", $image_name);
        $nama_gambar = time() . '_' . $safe_image_name;
        $target_file = $target_dir . $nama_gambar;
        if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            // Handle upload error, maybe set a session error message
            $nama_gambar = 'placeholder.png';
        }
    }

    $stmt = $mysqli->prepare("INSERT INTO program (nama_program, deskripsi, kategori, metode_pembayaran_ids, target_donasi, gambar) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssds", $nama_program, $deskripsi, $kategori, $metode_pembayaran_ids, $target_donasi, $nama_gambar);
    if ($stmt->execute()) {
        // Optional: Set a success message to display on the next page
        // $_SESSION['success_message'] = "Program baru berhasil ditambahkan.";
        header("Location: kelola_program.php");
        exit();
    }
    $stmt->close();
}

require_once 'templates/header_admin.php';
?>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
            <a href="kelola_program.php" class="hover:text-primary-orange transition-colors">Kelola Program</a>
            <i class="bi bi-chevron-right text-[10px]"></i>
            <span class="text-slate-800 font-medium">Tambah Baru</span>
        </div>
        <h1 class="text-2xl font-display font-bold text-slate-900"><?php echo $page_title; ?></h1>
        <p class="text-slate-500 mt-1">Buat program donasi baru untuk ditampilkan kepada donatur.</p>
    </div>

    <!-- Main Content Box -->
    <div class="bg-white rounded-[20px] shadow-sm border border-slate-100 overflow-hidden">
        <form action="tambah_program.php" method="POST" enctype="multipart/form-data" class="p-6 lg:p-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                
                <!-- Left Column: Main Inputs -->
                <div class="lg:col-span-8 space-y-6">
                    <div>
                        <label for="nama_program" class="block text-sm font-semibold text-slate-700 mb-2">Nama Program <span class="text-red-500">*</span></label>
                        <input type="text" id="nama_program" name="nama_program" required
                            class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary-orange/50 focus:border-primary-orange transition-all placeholder:text-slate-400"
                            placeholder="Contoh: Bantuan untuk Korban Banjir">
                    </div>
                    
                    <div>
                        <label for="deskripsi" class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Program <span class="text-red-500">*</span></label>
                        <textarea id="deskripsi" name="deskripsi" rows="8" required
                            class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary-orange/50 focus:border-primary-orange transition-all placeholder:text-slate-400"
                            placeholder="Jelaskan secara detail tujuan dan latar belakang program ini..."></textarea>
                        <p class="text-xs text-slate-500 mt-2 flex items-center gap-1">
                            <i class="bi bi-info-circle"></i> Gunakan beberapa paragraf agar lebih menarik bagi donatur.
                        </p>
                    </div>
                </div>

                <!-- Right Column: Settings & Image -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="p-5 rounded-xl bg-slate-50 border border-slate-100 space-y-5">
                        <div>
                            <label for="kategori" class="block text-sm font-semibold text-slate-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                            <select id="kategori" name="kategori" required
                                class="w-full bg-white border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary-orange/50 focus:border-primary-orange transition-all appearance-none cursor-pointer">
                                <option value="" disabled selected>Pilih Kategori...</option>
                                <option value="Pendidikan">Pendidikan</option>
                                <option value="Kesehatan">Kesehatan</option>
                                <option value="Bencana Alam">Bencana Alam</option>
                                <option value="Sosial">Sosial</option>
                                <option value="Infrastruktur">Infrastruktur</option>
                                <option value="Zakat">Zakat</option>
                                <option value="Infak">Infak</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="target_donasi" class="block text-sm font-semibold text-slate-700 mb-2">Target Donasi <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-slate-500 font-medium">Rp</span>
                                </div>
                                <input type="text" id="target_donasi" name="target_donasi" required 
                                    class="w-full bg-white border border-slate-200 text-slate-800 rounded-xl pl-11 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary-orange/50 focus:border-primary-orange transition-all"
                                    placeholder="10.000.000">
                            </div>
                        </div>

                        <div>
                            <label for="gambar" class="block text-sm font-semibold text-slate-700 mb-2">Gambar Utama</label>
                            <input type="file" id="gambar" name="gambar" accept="image/*"
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-primary-orange hover:file:bg-orange-100 transition-colors border border-slate-200 rounded-xl bg-white cursor-pointer">
                            <p class="text-xs text-slate-500 mt-2">Format: JPG, PNG. Rasio 16:9 direkomendasikan.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Metode Pembayaran Khusus</label>
                        <div class="rounded-xl border border-slate-200 bg-white overflow-hidden">
                            <div class="max-h-56 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                                <?php
                                $metode_stmt = $mysqli->prepare("SELECT id, nama_metode FROM metode_pembayaran WHERE status = 'Aktif' ORDER BY nama_metode");
                                $metode_stmt->execute();
                                $metode_result = $metode_stmt->get_result();
                                if ($metode_result->num_rows > 0) {
                                    while ($metode = $metode_result->fetch_assoc()) {
                                        echo '<label class="flex items-center gap-3 cursor-pointer group">';
                                        echo '<input type="checkbox" name="metode_pembayaran_ids[]" value="' . $metode['id'] . '" class="w-5 h-5 rounded border-slate-300 text-primary-orange focus:ring-primary-orange transition-colors cursor-pointer">';
                                        echo '<span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">' . htmlspecialchars($metode['nama_metode']) . '</span>';
                                        echo '</label>';
                                    }
                                } else {
                                    echo '<p class="text-sm text-slate-500 text-center py-2">Tidak ada metode pembayaran aktif.</p>';
                                }
                                $metode_stmt->close();
                                ?>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            Pilih metode tertentu jika program ini hanya menerima bank tertentu. Biarkan <span class="font-semibold text-slate-700">kosong</span> jika menerima semua metode pembayaran.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-slate-100">
                <a href="kelola_program.php" class="px-6 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl font-medium bg-primary-orange text-white hover:bg-orange-600 transition-all shadow-lg shadow-orange-500/20">
                    Simpan Program
                </button>
            </div>
        </form>
    </div>

<script>
// Script untuk format angka rupiah pada input target donasi
document.getElementById('target_donasi').addEventListener('keyup', function(e) {
    // Hapus karakter selain angka
    let value = e.target.value.replace(/[^\d]/g, '');
    
    // Format sebagai mata uang IDR jika ada isinya
    if (value) {
        e.target.value = new Intl.NumberFormat('id-ID').format(value);
    }
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>
