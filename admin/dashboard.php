<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Dashboard Admin";
$admin_nama = $_SESSION['admin_nama_lengkap'] ?? 'Admin';

// --- Query Komprehensif untuk Dasbor ---

// 1. Statistik Utama
$total_donasi_online = $mysqli->query("SELECT SUM(nominal) as total FROM donasi WHERE status = 'Selesai'")->fetch_assoc()['total'] ?? 0;
$total_donasi_laporan = $mysqli->query("SELECT SUM(nominal) as total FROM laporan_transaksi")->fetch_assoc()['total'] ?? 0;
$total_donasi_kotak = $mysqli->query("SELECT SUM(jumlah_terkumpul) as total FROM riwayat_pengambilan")->fetch_assoc()['total'] ?? 0;
$total_donasi_keseluruhan = $total_donasi_online + $total_donasi_laporan + $total_donasi_kotak;
$total_program_aktif = $mysqli->query("SELECT COUNT(id) as total FROM program")->fetch_assoc()['total'] ?? 0;
$donasi_perlu_konfirmasi = $mysqli->query("SELECT COUNT(id) as total FROM donasi WHERE status = 'Menunggu Konfirmasi'")->fetch_assoc()['total'] ?? 0;
$berita_perlu_persetujuan = $mysqli->query("SELECT COUNT(id) as total FROM berita WHERE status = 'pending'")->fetch_assoc()['total'] ?? 0;

// 2. Data untuk Grafik Donasi 7 Hari Terakhir (Online)
$chart_labels = [];
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('d M', strtotime($date));
    $stmt_chart = $mysqli->prepare("SELECT SUM(nominal) as total FROM donasi WHERE status = 'Selesai' AND DATE(created_at) = ?");
    $stmt_chart->bind_param("s", $date);
    $stmt_chart->execute();
    $daily_total = $stmt_chart->get_result()->fetch_assoc()['total'] ?? 0;
    $chart_data[] = $daily_total;
    $stmt_chart->close();
}

// 3. Tugas Pengambilan Kotak Infak Hari Ini
$today = date('Y-m-d');
$result_tugas_hari_ini = $mysqli->query("SELECT t.id, a.nama_lengkap, k.nama_lokasi, k.alamat FROM tugas_pengambilan t JOIN amil a ON t.id_amil = a.id JOIN kotak_infak k ON t.id_kotak_infak = k.id WHERE t.tanggal_tugas = '$today' AND t.status = 'Ditugaskan' ORDER BY a.nama_lengkap");

// 4. Donasi Terbaru yang Perlu Dikonfirmasi
$result_konfirmasi_terbaru = $mysqli->query("SELECT id, nama_donatur, nominal, created_at FROM donasi WHERE status = 'Menunggu Konfirmasi' ORDER BY created_at DESC LIMIT 5");


require_once 'templates/header_admin.php';
?>

    <!-- Header Halaman -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-display font-bold text-slate-900 tracking-tight">Selamat Datang, <?php echo htmlspecialchars($admin_nama); ?>!</h1>
            <p class="text-slate-500 mt-1">Ringkasan aktivitas dan performa platform hari ini.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="export_users.php" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 font-medium rounded-xl hover:bg-slate-50 hover:text-primary-orange transition-all shadow-sm">
                <i class="bi bi-download"></i> Export Data
            </a>
            <button class="inline-flex items-center gap-2 px-4 py-2 bg-primary-orange text-white font-medium rounded-xl hover:bg-orange-600 transition-all shadow-lg shadow-orange-500/20">
                <i class="bi bi-calendar-event"></i> <?php echo date('d M Y'); ?>
            </button>
        </div>
    </div>

    <!-- Kartu Statistik Utama -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Stat Card 1 -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group hover:border-orange-200 transition-colors">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0 shadow-inner">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 mb-1">Total Dana Terkumpul</p>
                    <h3 class="text-2xl font-bold text-slate-800 tracking-tight">Rp <?php echo number_format($total_donasi_keseluruhan, 0, ',', '.'); ?></h3>
                </div>
            </div>
        </div>

        <!-- Stat Card 2 -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group hover:border-orange-200 transition-colors">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-green-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-14 h-14 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0 shadow-inner">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 mb-1">Perlu Dikonfirmasi</p>
                    <h3 class="text-2xl font-bold text-slate-800 tracking-tight"><?php echo number_format($donasi_perlu_konfirmasi); ?> <span class="text-sm font-normal text-slate-500">Donasi</span></h3>
                </div>
            </div>
        </div>

        <!-- Stat Card 3 -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group hover:border-orange-200 transition-colors">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0 shadow-inner">
                    <i class="bi bi-heart-pulse"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 mb-1">Program Aktif</p>
                    <h3 class="text-2xl font-bold text-slate-800 tracking-tight"><?php echo number_format($total_program_aktif); ?> <span class="text-sm font-normal text-slate-500">Program</span></h3>
                </div>
            </div>
        </div>

        <!-- Stat Card 4 -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden group hover:border-orange-200 transition-colors">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-purple-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0 shadow-inner">
                    <i class="bi bi-newspaper"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 mb-1">Persetujuan Berita</p>
                    <h3 class="text-2xl font-bold text-slate-800 tracking-tight"><?php echo number_format($berita_perlu_persetujuan); ?> <span class="text-sm font-normal text-slate-500">Berita</span></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid Content -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-8">
        
        <!-- Left Column: Chart & Tasks -->
        <div class="xl:col-span-2 space-y-8">
            <!-- Chart Card -->
            <div class="bg-white rounded-[20px] p-6 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Tren Donasi Online</h3>
                        <p class="text-sm text-slate-500">7 Hari Terakhir</p>
                    </div>
                    <button class="p-2 text-slate-400 hover:text-primary-orange transition-colors rounded-lg hover:bg-orange-50">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                </div>
                <div class="h-72 w-full relative">
                    <canvas id="donationChart"></canvas>
                </div>
            </div>

            <!-- Tugas Hari Ini -->
            <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Tugas Pengambilan Kotak Infak</h3>
                        <p class="text-sm text-slate-500">Jadwal hari ini (<?php echo date('d M Y'); ?>)</p>
                    </div>
                    <a href="kelola_tugas.php" class="text-sm font-semibold text-primary-orange hover:text-orange-600 transition-colors">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Amil Bertugas</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Lokasi Kotak</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Alamat</th>
                                <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if ($result_tugas_hari_ini && $result_tugas_hari_ini->num_rows > 0): ?>
                            <?php while($tugas = $result_tugas_hari_ini->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-orange-100 text-primary-orange flex items-center justify-center font-bold text-xs">
                                            <?php echo strtoupper(substr($tugas['nama_lengkap'], 0, 1)); ?>
                                        </div>
                                        <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($tugas['nama_lengkap']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-700">
                                    <i class="bi bi-box-seam text-slate-400 mr-2"></i>
                                    <?php echo htmlspecialchars($tugas['nama_lokasi']); ?>
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    <div class="max-w-[200px] truncate" title="<?php echo htmlspecialchars($tugas['alamat']); ?>">
                                        <?php echo htmlspecialchars($tugas['alamat']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="text-slate-400 hover:text-primary-orange transition-colors p-2 rounded-lg hover:bg-orange-50 opacity-0 group-hover:opacity-100">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="bi bi-inbox text-4xl text-slate-300"></i>
                                        <p>Tidak ada tugas yang dijadwalkan untuk hari ini.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Quick Actions & Pending Confirmations -->
        <div class="space-y-8">
            <!-- Quick Actions -->
            <div class="bg-white rounded-[20px] p-6 border border-slate-100 shadow-sm">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Aksi Cepat</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="tambah_berita.php" class="flex flex-col items-center justify-center gap-2 p-4 bg-slate-50 rounded-xl hover:bg-blue-50 text-slate-600 hover:text-blue-600 hover:-translate-y-1 transition-all border border-transparent hover:border-blue-100 group">
                        <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <i class="bi bi-pencil-square text-lg"></i>
                        </div>
                        <span class="text-xs font-semibold">Tulis Berita</span>
                    </a>
                    <a href="tambah_program.php" class="flex flex-col items-center justify-center gap-2 p-4 bg-slate-50 rounded-xl hover:bg-green-50 text-slate-600 hover:text-green-600 hover:-translate-y-1 transition-all border border-transparent hover:border-green-100 group">
                        <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center group-hover:bg-green-600 group-hover:text-white transition-colors">
                            <i class="bi bi-plus-circle text-lg"></i>
                        </div>
                        <span class="text-xs font-semibold">Buat Program</span>
                    </a>
                    <a href="konfirmasi_donasi.php" class="flex flex-col items-center justify-center gap-2 p-4 bg-slate-50 rounded-xl hover:bg-amber-50 text-slate-600 hover:text-amber-600 hover:-translate-y-1 transition-all border border-transparent hover:border-amber-100 group">
                        <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors">
                            <i class="bi bi-check2-square text-lg"></i>
                        </div>
                        <span class="text-xs font-semibold">Konfirmasi</span>
                    </a>
                    <a href="kelola_tugas.php" class="flex flex-col items-center justify-center gap-2 p-4 bg-slate-50 rounded-xl hover:bg-purple-50 text-slate-600 hover:text-purple-600 hover:-translate-y-1 transition-all border border-transparent hover:border-purple-100 group">
                        <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-colors">
                            <i class="bi bi-card-checklist text-lg"></i>
                        </div>
                        <span class="text-xs font-semibold">Tugas Amil</span>
                    </a>
                </div>
            </div>

            <!-- Pending Confirmations -->
            <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm flex flex-col h-[calc(100%-250px)] min-h-[400px]">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between shrink-0">
                    <h3 class="text-lg font-bold text-slate-800">Menunggu Konfirmasi</h3>
                    <span class="px-2.5 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-full"><?php echo $donasi_perlu_konfirmasi; ?></span>
                </div>
                
                <div class="flex-1 overflow-y-auto p-2 custom-scrollbar">
                    <?php if ($result_konfirmasi_terbaru && $result_konfirmasi_terbaru->num_rows > 0): ?>
                        <div class="space-y-1">
                            <?php while($donasi = $result_konfirmasi_terbaru->fetch_assoc()): ?>
                            <a href="konfirmasi_donasi.php" class="block p-4 rounded-xl hover:bg-slate-50 transition-colors group">
                                <div class="flex justify-between items-start mb-1">
                                    <p class="font-bold text-slate-800 group-hover:text-primary-orange transition-colors truncate pr-4">
                                        <?php echo htmlspecialchars($donasi['nama_donatur']); ?>
                                    </p>
                                    <p class="font-bold text-emerald-600 shrink-0">
                                        Rp <?php echo number_format($donasi['nominal'], 0, ',', '.'); ?>
                                    </p>
                                </div>
                                <div class="flex items-center text-xs text-slate-500 gap-2">
                                    <i class="bi bi-clock"></i>
                                    <span><?php echo date('d M Y, H:i', strtotime($donasi['created_at'])); ?></span>
                                </div>
                            </a>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="h-full flex flex-col items-center justify-center text-slate-400 p-6">
                            <i class="bi bi-check-circle text-4xl mb-3 text-emerald-300"></i>
                            <p class="text-center text-sm">Semua donasi telah dikonfirmasi.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="p-4 border-t border-slate-100 shrink-0">
                    <a href="konfirmasi_donasi.php" class="block w-full py-2.5 text-center text-sm font-semibold text-slate-600 bg-slate-50 hover:bg-slate-100 rounded-xl transition-colors">
                        Lihat Semua Data
                    </a>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('donationChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Donasi Masuk (Rp)',
                data: <?php echo json_encode($chart_data); ?>,
                backgroundColor: 'rgba(251, 130, 1, 0.1)',
                borderColor: '#fb8201',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php 
require_once 'templates/footer_admin.php'; 
?>