<?php
// Memuat file konfigurasi
require_once '../includes/config.php';

// Pengecekan login user
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = "Dashboard Saya";
$user_id = $_SESSION['user_id'];

// Ambil data user
$stmt_user = $mysqli->prepare("SELECT email, no_telepon FROM user WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

// [FIX] Ambil riwayat donasi HANYA berdasarkan kontak (email/telepon) karena kolom id_user tidak ada.
$sql_donasi = "SELECT d.created_at, d.nominal, d.status, p.nama_program 
               FROM donasi d 
               LEFT JOIN program p ON d.id_program = p.id 
               WHERE ((d.kontak_donatur = ? AND d.kontak_donatur != '') 
                  OR (d.kontak_donatur = ? AND d.kontak_donatur != ''))
               ORDER BY d.created_at DESC";
$stmt_donasi = $mysqli->prepare($sql_donasi);
// Sesuaikan binding parameter karena kondisi WHERE berubah
$stmt_donasi->bind_param("ss", $user_data['email'], $user_data['no_telepon']);
$stmt_donasi->execute();
$result_donasi = $stmt_donasi->get_result();

$total_donasi = 0;
$jumlah_transaksi = 0;
$donasi_list = [];

if ($result_donasi) {
    $jumlah_transaksi = $result_donasi->num_rows;
    while($donasi_row = $result_donasi->fetch_assoc()){
        $donasi_list[] = $donasi_row; // Simpan ke array dulu
        if($donasi_row['status'] == 'Selesai'){
            $total_donasi += $donasi_row['nominal'];
        }
    }
}


require_once 'templates/header_user.php';
?>

<?php
// Logika Pencapaian (Gamifikasi)
$level_nama = "Sahabat Amal";
$level_icon = "bi-star-fill text-amber-500";
$level_bg = "bg-gradient-to-r from-amber-100 to-yellow-200";
$level_border = "border-amber-300";
$progress_persen = 0;
$next_level = "Pejuang Kebaikan";
$donasi_butuh = 2 - $jumlah_transaksi;

if ($jumlah_transaksi >= 5 || $total_donasi >= 1000000) {
    $level_nama = "Pahlawan Kebaikan";
    $level_icon = "bi-award-fill text-indigo-600";
    $level_bg = "bg-gradient-to-r from-indigo-100 to-purple-200";
    $level_border = "border-indigo-300";
    $progress_persen = 100;
    $next_level = "Level Maksimal";
    $donasi_butuh = 0;
} elseif ($jumlah_transaksi >= 2) {
    $level_nama = "Pejuang Kebaikan";
    $level_icon = "bi-shield-fill-check text-emerald-600";
    $level_bg = "bg-gradient-to-r from-emerald-100 to-teal-200";
    $level_border = "border-emerald-300";
    $progress_persen = ($jumlah_transaksi / 5) * 100;
    $next_level = "Pahlawan Kebaikan";
    $donasi_butuh = 5 - $jumlah_transaksi;
} else {
    $progress_persen = ($jumlah_transaksi / 2) * 100;
}
?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-10 max-w-5xl">

    <!-- Header & Profile Card (Glassmorphism & Neumorphism blend) -->
    <div class="relative rounded-3xl overflow-hidden shadow-2xl mb-8 group bg-white border border-gray-100">
        <!-- Abstract Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary-orange via-orange-400 to-yellow-400 opacity-90"></div>
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-white/20 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-700"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-black/10 rounded-full blur-2xl group-hover:translate-x-4 transition-transform duration-700"></div>
        
        <div class="relative z-10 p-6 md:p-8 flex flex-col md:flex-row items-center md:items-start justify-between gap-6">
            <div class="flex items-center gap-5 text-left w-full md:w-auto">
                <a href="edit_profil.php" class="relative block shrink-0">
                    <img class="h-20 w-20 md:h-24 md:w-24 rounded-full object-cover border-4 border-white shadow-lg transform hover:rotate-3 transition-transform duration-300" src="<?php echo BASE_URL; ?>/assets/uploads/user/<?php echo htmlspecialchars($user_foto); ?>" alt="Foto Profil">
                    <div class="absolute bottom-0 right-0 bg-green-500 w-5 h-5 border-2 border-white rounded-full shadow-sm"></div>
                </a>
                <div class="text-white">
                    <p class="font-medium text-white/90 text-sm md:text-base tracking-wide uppercase mb-1">Selamat Datang,</p>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight drop-shadow-sm mb-2"><?php echo htmlspecialchars($user_nama); ?></h1>
                    
                    <!-- Lencana Pencapaian di Header -->
                    <div class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-md border border-white/30 px-3 py-1 rounded-full text-xs font-semibold text-white shadow-sm">
                        <i class="bi <?php echo $level_icon; ?> text-white"></i>
                        <span><?php echo $level_nama; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/20 backdrop-blur-md rounded-2xl p-4 border border-white/30 shadow-lg w-full md:w-auto text-white flex flex-col items-center md:items-end">
                <p class="text-sm font-medium text-white/90 mb-1">Total Donasi Anda</p>
                <p class="text-2xl md:text-3xl font-bold tracking-tight">Rp <?php echo number_format($total_donasi, 0, ',', '.'); ?></p>
            </div>
        </div>
    </div>

    <!-- Gamification Section: Pencapaian -->
    <div class="mb-8 bg-white p-6 rounded-3xl shadow-[0_8px_30px_rgba(0,0,0,0.04)] border border-gray-100 flex flex-col md:flex-row items-center gap-6">
        <div class="shrink-0 flex items-center justify-center w-20 h-20 rounded-2xl shadow-inner border <?php echo $level_bg; ?> <?php echo $level_border; ?>">
            <i class="bi <?php echo $level_icon; ?> text-4xl drop-shadow-sm"></i>
        </div>
        <div class="flex-grow w-full">
            <div class="flex justify-between items-end mb-2">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Level: <?php echo $level_nama; ?></h3>
                    <p class="text-xs font-medium text-gray-500 mt-1">
                        <?php if($donasi_butuh > 0): ?>
                            Kurang <span class="font-bold text-primary-orange"><?php echo $donasi_butuh; ?> donasi</span> lagi untuk menjadi <span class="font-bold"><?php echo $next_level; ?></span>!
                        <?php else: ?>
                            Luar biasa! Anda telah mencapai level tertinggi.
                        <?php endif; ?>
                    </p>
                </div>
                <span class="text-sm font-bold text-gray-700"><?php echo round($progress_persen); ?>%</span>
            </div>
            <!-- Progress Bar -->
            <div class="w-full bg-gray-100 rounded-full h-3.5 mb-1 overflow-hidden shadow-inner">
                <div class="h-3.5 rounded-full bg-gradient-to-r from-orange-400 to-primary-orange transition-all duration-1000 ease-out" style="width: <?php echo $progress_persen; ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Quick Actions (Neumorphic Grid) -->
    <h3 class="text-lg font-bold text-gray-800 mb-4 ml-2">Aksi Cepat</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-5 mb-10 text-center">
        <a href="../kalkulator_zakat" class="group bg-white p-5 md:p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
            <div class="w-14 h-14 mx-auto bg-orange-50 text-primary-orange rounded-full flex items-center justify-center group-hover:bg-primary-orange group-hover:text-white transition-colors duration-300 shadow-inner mb-3">
                <i class="bi bi-calculator-fill text-2xl"></i>
            </div>
            <p class="text-sm font-bold text-gray-700 group-hover:text-primary-orange transition-colors">Hitung Zakat</p>
        </a>
        <a href="edit_profil.php" class="group bg-white p-5 md:p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
            <div class="w-14 h-14 mx-auto bg-blue-50 text-blue-500 rounded-full flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition-colors duration-300 shadow-inner mb-3">
                <i class="bi bi-person-fill text-2xl"></i>
            </div>
            <p class="text-sm font-bold text-gray-700 group-hover:text-blue-500 transition-colors">Edit Profil</p>
        </a>
        <a href="ganti_sandi.php" class="group bg-white p-5 md:p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
            <div class="w-14 h-14 mx-auto bg-purple-50 text-purple-500 rounded-full flex items-center justify-center group-hover:bg-purple-500 group-hover:text-white transition-colors duration-300 shadow-inner mb-3">
                <i class="bi bi-shield-lock-fill text-2xl"></i>
            </div>
            <p class="text-sm font-bold text-gray-700 group-hover:text-purple-500 transition-colors">Keamanan</p>
        </a>
        <a href="../donasi" class="group bg-gradient-to-br from-primary-orange to-orange-500 p-5 md:p-6 rounded-3xl shadow-md border border-orange-400 hover:shadow-xl hover:shadow-orange-200 hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
            <div class="absolute inset-0 bg-white/10 blur-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="w-14 h-14 mx-auto bg-white/20 text-white rounded-full flex items-center justify-center border border-white/30 backdrop-blur-sm shadow-inner mb-3">
                <i class="bi bi-heart-fill text-2xl animate-pulse"></i>
            </div>
            <p class="text-sm font-bold text-white tracking-wide">Donasi Lagi</p>
        </a>
    </div>

    <!-- Donation History -->
    <div class="flex items-center justify-between mb-4 ml-2">
        <h3 class="text-lg font-bold text-gray-800">Riwayat Transaksi</h3>
        <span class="text-xs font-semibold bg-gray-100 text-gray-500 px-3 py-1 rounded-full border border-gray-200"><?php echo $jumlah_transaksi; ?> Kali</span>
    </div>
    
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgba(0,0,0,0.04)] border border-gray-100 overflow-hidden">
        <div class="p-2 sm:p-4">
            <?php if (!empty($donasi_list)): ?>
                <div class="space-y-2">
                <?php foreach($donasi_list as $donasi): 
                    $status = $donasi['status'];
                    $status_bg = 'bg-gray-100 text-gray-600';
                    $icon = 'bi-clock-history';
                    
                    if ($status == 'Selesai') {
                        $status_bg = 'bg-emerald-50 text-emerald-600 border border-emerald-200';
                        $icon = 'bi-check-circle-fill text-emerald-500';
                    } elseif (str_contains($status, 'Menunggu')) {
                        $status_bg = 'bg-amber-50 text-amber-600 border border-amber-200';
                        $icon = 'bi-hourglass-split text-amber-500';
                    } elseif ($status == 'Dibatalkan') {
                        $status_bg = 'bg-red-50 text-red-600 border border-red-200';
                        $icon = 'bi-x-circle-fill text-red-500';
                    }
                ?>
                <div class="flex items-center justify-between p-4 rounded-2xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100 group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center shrink-0 shadow-inner group-hover:scale-110 transition-transform">
                            <i class="bi <?php echo $icon; ?> text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800 text-sm sm:text-base line-clamp-1"><?php echo htmlspecialchars($donasi['nama_program'] ?: 'Donasi Umum'); ?></p>
                            <p class="text-xs font-medium text-gray-500 mt-0.5"><?php echo date('d M Y, H:i', strtotime($donasi['created_at'])); ?></p>
                        </div>
                    </div>
                    <div class="text-right flex flex-col items-end">
                        <p class="font-bold text-gray-900 text-sm sm:text-base mb-1">Rp <?php echo number_format($donasi['nominal'], 0, ',', '.'); ?></p>
                        <span class="px-2.5 py-0.5 text-[10px] sm:text-xs font-bold rounded-md <?php echo $status_bg; ?>">
                            <?php echo htmlspecialchars($status); ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12 px-4">
                    <div class="w-20 h-20 mx-auto bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                        <i class="bi bi-inbox text-3xl text-gray-300"></i>
                    </div>
                    <h4 class="text-gray-800 font-bold mb-1">Belum Ada Transaksi</h4>
                    <p class="text-sm text-gray-500 max-w-xs mx-auto">Mari mulai langkah kebaikan Anda hari ini bersama TemanAmal.</p>
                    <a href="../program" class="inline-block mt-6 px-6 py-2 bg-primary-orange text-white font-bold rounded-full shadow-lg hover:bg-orange-600 transition-colors">Lihat Program</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer_user.php';
?>

