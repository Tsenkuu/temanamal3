<?php
// Memuat file konfigurasi, yang seharusnya sudah memanggil session_start()
require_once '../includes/config.php';

// Pengecekan login admin (sangat direkomendasikan)
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php'); // Arahkan ke halaman login utama jika belum login
    exit();
}

$page_title = "Edit Program Donasi";
$id_program = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_program === 0) {
    header("Location: kelola_program.php");
    exit;
}

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_program = trim($_POST['nama_program']);
    $deskripsi = trim($_POST['deskripsi']);
    $target_donasi = preg_replace('/[^\d]/', '', $_POST['target_donasi']);
    // PENAMBAHAN: Mengambil dan membersihkan data donasi terkumpul
    $donasi_terkumpul = preg_replace('/[^\d]/', '', $_POST['donasi_terkumpul']);
    $gambar_lama = $_POST['gambar_lama'];
    $nama_gambar_baru = $gambar_lama;

    // Logika upload gambar baru jika ada
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../assets/uploads/program/";
        $nama_gambar_baru = time() . '_' . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $nama_gambar_baru;
        
        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            if ($gambar_lama != 'placeholder.png' && file_exists($target_dir . $gambar_lama)) {
                unlink($target_dir . $gambar_lama);
            }
        } else {
            $nama_gambar_baru = $gambar_lama;
        }
    }

    // PERBAIKAN: Memperbarui query UPDATE untuk menyertakan donasi_terkumpul dan metode_pembayaran_ids
    $metode_pembayaran_ids = isset($_POST['metode_pembayaran_ids']) ? implode(',', $_POST['metode_pembayaran_ids']) : '';
    $stmt = $mysqli->prepare("UPDATE program SET nama_program = ?, deskripsi = ?, target_donasi = ?, donasi_terkumpul = ?, metode_pembayaran_ids = ?, gambar = ? WHERE id = ?");
    $stmt->bind_param("ssddssi", $nama_program, $deskripsi, $target_donasi, $donasi_terkumpul, $metode_pembayaran_ids, $nama_gambar_baru, $id_program);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Program berhasil diperbarui.";
        header("Location: kelola_program.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui program.";
    }
    $stmt->close();
}

// PERBAIKAN: Mengambil data donasi_terkumpul dan metode_pembayaran_ids dari database
$stmt_select = $mysqli->prepare("SELECT nama_program, deskripsi, gambar, target_donasi, donasi_terkumpul, metode_pembayaran_ids FROM program WHERE id = ?");
$stmt_select->bind_param("i", $id_program);
$stmt_select->execute();
$result = $stmt_select->get_result();
$program = $result->fetch_assoc();
$stmt_select->close();

if (!$program) {
    header("Location: kelola_program.php");
    exit;
}

require_once 'templates/header_admin.php';
?>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
            <a href="kelola_program.php" class="hover:text-primary-orange transition-colors">Kelola Program</a>
            <i class="bi bi-chevron-right text-[10px]"></i>
            <span class="text-slate-800 font-medium">Edit Program</span>
        </div>
        <h1 class="text-2xl font-display font-bold text-slate-900"><?php echo $page_title; ?></h1>
        <p class="text-slate-500 mt-1">Sesuaikan detail program dan target donasi.</p>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 flex items-center gap-3">
            <i class="bi bi-exclamation-triangle-fill text-xl"></i>
            <span class="font-medium"><?php echo htmlspecialchars($_SESSION['error_message']); ?></span>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Main Content Box -->
    <div class="bg-white rounded-[20px] shadow-sm border border-slate-100 overflow-hidden">
        <form action="edit_program.php?id=<?php echo $id_program; ?>" method="POST" enctype="multipart/form-data" class="p-6 lg:p-8">
            <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($program['gambar']); ?>">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                
                <!-- Left Column: Main Inputs -->
                <div class="lg:col-span-8 space-y-6">
                    <div>
                        <label for="nama_program" class="block text-sm font-semibold text-slate-700 mb-2">Nama Program <span class="text-red-500">*</span></label>
                        <input type="text" id="nama_program" name="nama_program" required
                            class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary-orange/50 focus:border-primary-orange transition-all placeholder:text-slate-400"
                            value="<?php echo htmlspecialchars($program['nama_program']); ?>">
                    </div>
                    
                    <div>
                        <label for="deskripsi" class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Program <span class="text-red-500">*</span></label>
                        <textarea id="deskripsi" name="deskripsi" rows="8" required
                            class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary-orange/50 focus:border-primary-orange transition-all placeholder:text-slate-400"><?php echo htmlspecialchars($program['deskripsi']); ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="target_donasi" class="block text-sm font-semibold text-slate-700 mb-2">Target Donasi <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-slate-500 font-medium">Rp</span>
                                </div>
                                <input type="text" id="target_donasi" name="target_donasi" required 
                                    class="w-full bg-white border border-slate-200 text-slate-800 rounded-xl pl-11 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary-orange/50 focus:border-primary-orange transition-all"
                                    value="<?php echo number_format($program['target_donasi'], 0, ',', '.'); ?>">
                            </div>
                        </div>

                        <div>
                            <label for="donasi_terkumpul" class="block text-sm font-semibold text-slate-700 mb-2">Donasi Terkumpul Saat Ini <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-slate-500 font-medium">Rp</span>
                                </div>
                                <input type="text" id="donasi_terkumpul" name="donasi_terkumpul" required 
                                    class="w-full bg-orange-50 border border-orange-200 text-slate-800 rounded-xl pl-11 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary-orange/50 focus:border-primary-orange transition-all font-semibold"
                                    value="<?php echo number_format($program['donasi_terkumpul'], 0, ',', '.'); ?>">
                            </div>
                            <p class="text-[11px] text-slate-500 mt-2 leading-tight">Nilai ini bertambah otomatis dari transaksi, tetapi dapat Anda sesuaikan manual jika ada donasi offline.</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Settings & Image -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="p-5 rounded-xl bg-slate-50 border border-slate-100 space-y-5">
                        
                        <div>
                            <label for="gambar" class="block text-sm font-semibold text-slate-700 mb-2">Gambar Utama</label>
                            <div class="mb-4">
                                <p class="text-xs font-medium text-slate-500 mb-2">Gambar Saat Ini:</p>
                                <img src="../assets/uploads/program/<?php echo htmlspecialchars($program['gambar']); ?>" class="w-full h-auto object-cover rounded-xl border border-slate-200 shadow-sm" alt="Gambar Program">
                            </div>
                            <input type="file" id="gambar" name="gambar" accept="image/*"
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-primary-orange hover:file:bg-orange-100 transition-colors border border-slate-200 rounded-xl bg-white cursor-pointer">
                            <p class="text-xs text-slate-500 mt-2">Biarkan kosong jika tidak ingin mengubah gambar.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Metode Pembayaran Khusus</label>
                        <div class="rounded-xl border border-slate-200 bg-white overflow-hidden">
                            <div class="max-h-56 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                                <?php
                                $selected_metode = explode(',', $program['metode_pembayaran_ids']);
                                $metode_stmt = $mysqli->prepare("SELECT id, nama_metode FROM metode_pembayaran WHERE status = 'Aktif' ORDER BY nama_metode");
                                $metode_stmt->execute();
                                $metode_result = $metode_stmt->get_result();
                                if ($metode_result->num_rows > 0) {
                                    while ($metode = $metode_result->fetch_assoc()) {
                                        $checked = in_array($metode['id'], $selected_metode) ? 'checked' : '';
                                        echo '<label class="flex items-center gap-3 cursor-pointer group">';
                                        echo '<input type="checkbox" name="metode_pembayaran_ids[]" value="' . $metode['id'] . '" ' . $checked . ' class="w-5 h-5 rounded border-slate-300 text-primary-orange focus:ring-primary-orange transition-colors cursor-pointer">';
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
                            Biarkan <span class="font-semibold text-slate-700">kosong</span> jika menerima semua metode pembayaran.
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
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

<script>
// Script untuk memformat input angka dengan titik ribuan
document.getElementById('target_donasi').addEventListener('keyup', function(e) {
    let value = e.target.value.replace(/[^\d]/g, '');
    e.target.value = new Intl.NumberFormat('id-ID').format(value);
});

// PENAMBAHAN: Script untuk memformat input donasi terkumpul
document.getElementById('donasi_terkumpul').addEventListener('keyup', function(e) {
    let value = e.target.value.replace(/[^\d]/g, '');
    e.target.value = new Intl.NumberFormat('id-ID').format(value);
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>
