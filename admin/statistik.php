<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Statistik";

// --- Logika Filter Tanggal ---
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

$date_obj = DateTime::createFromFormat('!m', $selected_month);
$month_name = $date_obj->format('F');
$month_translations = [
    'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April',
    'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus',
    'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
];
$month_name_id = $month_translations[$month_name];

$month_start = date('Y-m-01', strtotime("$selected_year-$selected_month-01"));
$month_end = date('Y-m-t', strtotime("$selected_year-$selected_month-01"));
$today = date("Y-m-d");
$date_filter_sql = " AND created_at BETWEEN '$month_start 00:00:00' AND '$month_end 23:59:59'";

// --- Mengambil Data Statistik (Menggunakan Prepared Statements) ---

// 1. Statistik Pengunjung
$stmt_visitors_today = $mysqli->prepare("SELECT COUNT(DISTINCT ip_address) as total FROM visitors WHERE visit_date = ?");
$stmt_visitors_today->bind_param("s", $today);
$stmt_visitors_today->execute();
$visitors_today = $stmt_visitors_today->get_result()->fetch_assoc()['total'] ?? 0;

$stmt_visitors_month = $mysqli->prepare("SELECT COUNT(DISTINCT ip_address) as total FROM visitors WHERE visit_date BETWEEN ? AND ?");
$stmt_visitors_month->bind_param("ss", $month_start, $month_end);
$stmt_visitors_month->execute();
$visitors_month = $stmt_visitors_month->get_result()->fetch_assoc()['total'] ?? 0;

// 2. [DIPERBAIKI] Total Halaman Dilihat (Berita + Program) pada bulan terpilih
$query_total_views = "
    SELECT 
        (SELECT SUM(views) FROM berita WHERE DATE_FORMAT(created_at, '%Y-%m') = ? ) + 
        (SELECT SUM(views) FROM program WHERE DATE_FORMAT(created_at, '%Y-%m') = ?)
        AS total_views;
";
$stmt_total_views = $mysqli->prepare($query_total_views);
$month_year = "$selected_year-" . str_pad($selected_month, 2, '0', STR_PAD_LEFT);
$stmt_total_views->bind_param("ss", $month_year, $month_year);
$stmt_total_views->execute();
$total_views_month = $stmt_total_views->get_result()->fetch_assoc()['total_views'] ?? 0;


// 3. [BARU] Statistik Donasi
$stmt_donasi = $mysqli->prepare("SELECT COUNT(id) as jumlah_donasi, SUM(nominal) as total_donasi FROM donasi WHERE status = 'Lunas' AND created_at BETWEEN ? AND ?");
$stmt_donasi->bind_param("ss", $month_start, $month_end);
$stmt_donasi->execute();
$donasi_stats = $stmt_donasi->get_result()->fetch_assoc();
$jumlah_donasi_bulan = $donasi_stats['jumlah_donasi'] ?? 0;
$total_donasi_bulan = $donasi_stats['total_donasi'] ?? 0;
$rata_rata_donasi_bulan = $jumlah_donasi_bulan > 0 ? $total_donasi_bulan / $jumlah_donasi_bulan : 0;

// 4. Konten Populer
$date_format_sql = "DATE_FORMAT(created_at, '%Y-%m') = ?";
$top_berita = $mysqli->prepare("SELECT id, slug, judul, views FROM berita WHERE $date_format_sql ORDER BY views DESC LIMIT 5");
$top_berita->bind_param("s", $month_year);
$top_berita->execute();
$top_berita_result = $top_berita->get_result();

$top_program = $mysqli->prepare("SELECT id, slug, nama_program, views FROM program WHERE $date_format_sql ORDER BY views DESC LIMIT 5");
$top_program->bind_param("s", $month_year);
$top_program->execute();
$top_program_result = $top_program->get_result();

// 5. [BARU] Data untuk Grafik
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $selected_month, $selected_year);
$labels = range(1, $days_in_month);
$visitor_data = array_fill(0, $days_in_month, 0);
$donation_data = array_fill(0, $days_in_month, 0);

// Data Pengunjung Harian
$stmt_daily_visitors = $mysqli->prepare("SELECT DAY(visit_date) as day, COUNT(DISTINCT ip_address) as total FROM visitors WHERE visit_date BETWEEN ? AND ? GROUP BY visit_date");
$stmt_daily_visitors->bind_param("ss", $month_start, $month_end);
$stmt_daily_visitors->execute();
$daily_visitors_result = $stmt_daily_visitors->get_result();
while ($row = $daily_visitors_result->fetch_assoc()) {
    $visitor_data[$row['day'] - 1] = $row['total'];
}

// Data Donasi Harian
$stmt_daily_donations = $mysqli->prepare("SELECT DAY(created_at) as day, SUM(nominal) as total FROM donasi WHERE status = 'Lunas' AND created_at BETWEEN ? AND ? GROUP BY created_at");
$stmt_daily_donations->bind_param("ss", $month_start, $month_end);
$stmt_daily_donations->execute();
$daily_donations_result = $stmt_daily_donations->get_result();
while ($row = $daily_donations_result->fetch_assoc()) {
    $donation_data[$row['day'] - 1] = $row['total'];
}


require_once 'templates/header_admin.php';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-bold text-dark-text"><?php echo $page_title; ?></h1>
    </div>

    <!-- Form Filter Tanggal -->
    <div class="content-card">
        <form action="statistik.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="month" class="form-label">Pilih Bulan:</label>
                    <select name="month" id="month" class="form-select">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php if ($m == $selected_month) echo 'selected'; ?>>
                            <?php echo $month_translations[date('F', mktime(0, 0, 0, $m, 10))]; ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label for="year" class="form-label">Pilih Tahun:</label>
                    <select name="year" id="year" class="form-select">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php if ($y == $selected_year) echo 'selected'; ?>>
                            <?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary w-full">Tampilkan</button>
                <a href="statistik.php" class="btn-secondary w-full">Reset</a>
            </div>
        </form>
    </div>

    <h2 class="text-xl font-semibold text-dark-text mt-6 mb-4">Statistik untuk Bulan:
        <strong><?php echo "$month_name_id $selected_year"; ?></strong></h2>

    <!-- [BARU] Grafik Statistik -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="content-card">
            <h3 class="card-title mb-4">Grafik Pengunjung Harian</h3>
            <canvas id="visitorChart"></canvas>
        </div>
        <div class="content-card">
            <h3 class="card-title mb-4">Grafik Donasi Harian</h3>
            <canvas id="donationChart"></canvas>
        </div>
    </div>

    <!-- Ringkasan Umum -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="content-card text-center">
            <p class="text-gray-500 font-medium">Pengunjung Unik (Hari Ini)</p>
            <p class="text-4xl font-bold text-dark-text mt-2"><?php echo number_format($visitors_today); ?></p>
        </div>
        <div class="content-card text-center">
            <p class="text-gray-500 font-medium">Pengunjung Unik (Bulan Ini)</p>
            <p class="text-4xl font-bold text-dark-text mt-2"><?php echo number_format($visitors_month); ?></p>
        </div>
        <div class="content-card text-center">
            <p class="text-gray-500 font-medium">Total Halaman Dilihat (Bulan Ini)</p>
            <p class="text-4xl font-bold text-dark-text mt-2"><?php echo number_format($total_views_month); ?></p>
        </div>
    </div>

    <!-- Ringkasan Donasi -->
    <h3 class="text-xl font-semibold text-dark-text mt-8 mb-4">Ringkasan Donasi (Bulan Ini)</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="content-card text-center">
            <p class="text-gray-500 font-medium">Total Donasi Terkumpul</p>
            <p class="text-3xl font-bold text-dark-text mt-2">Rp <?php echo number_format($total_donasi_bulan, 0, ',', '.'); ?></p>
        </div>
        <div class="content-card text-center">
            <p class="text-gray-500 font-medium">Jumlah Transaksi Donasi</p>
            <p class="text-4xl font-bold text-dark-text mt-2"><?php echo number_format($jumlah_donasi_bulan); ?></p>
        </div>
        <div class="content-card text-center">
            <p class="text-gray-500 font-medium">Rata-rata per Donasi</p>
            <p class="text-3xl font-bold text-dark-text mt-2">Rp <?php echo number_format($rata_rata_donasi_bulan, 0, ',', '.'); ?></p>
        </div>
    </div>

    <!-- Konten Populer -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="content-card">
            <h3 class="card-title mb-4"><i class="bi bi-newspaper mr-2"></i>Berita Paling Populer</h3>
            <div class="space-y-3">
                <?php if ($top_berita_result && $top_berita_result->num_rows > 0): ?>
                <?php while($berita = $top_berita_result->fetch_assoc()): ?>
                <a href="../berita/<?php echo urlencode($berita['slug']); ?>" target="_blank"
                    class="flex justify-between items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-lg">
                    <span class="font-medium text-dark-text"><?php echo htmlspecialchars($berita['judul']); ?></span>
                    <span class="badge-info"><?php echo number_format($berita['views']); ?>x</span>
                </a>
                <?php endwhile; ?>
                <?php else: ?>
                <p class="text-center text-gray-500 py-4">Tidak ada data untuk periode ini.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="content-card">
            <h3 class="card-title mb-4"><i class="bi bi-heart-pulse-fill mr-2"></i>Program Paling Populer</h3>
            <div class="space-y-3">
                <?php if ($top_program_result && $top_program_result->num_rows > 0): ?>
                <?php while($program = $top_program_result->fetch_assoc()): ?>
                <a href="../program/<?php echo urlencode($program['slug']); ?>" target="_blank"
                    class="flex justify-between items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-lg">
                    <span
                        class="font-medium text-dark-text"><?php echo htmlspecialchars($program['nama_program']); ?></span>
                    <span class="badge-info"><?php echo number_format($program['views']); ?>x</span>
                </a>
                <?php endwhile; ?>
                <?php else: ?>
                <p class="text-center text-gray-500 py-4">Tidak ada data untuk periode ini.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const visitorCtx = document.getElementById('visitorChart').getContext('2d');
    const visitorChart = new Chart(visitorCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Pengunjung Unik Harian',
                data: <?php echo json_encode($visitor_data); ?>,
                borderColor: '#3b82f6',
                tension: 0.1
            }]
        }
    });

    const donationCtx = document.getElementById('donationChart').getContext('2d');
    const donationChart = new Chart(donationCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Donasi Harian (Rp)',
                data: <?php echo json_encode($donation_data); ?>,
                backgroundColor: '#10b981',
            }]
        }
    });
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>
