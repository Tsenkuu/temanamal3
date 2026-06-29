<?php
require_once '../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
$page_title = "Kelola Kotak Infak";

// --- PENCARIAN & PAGINASI ---
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

$sql_base = "FROM kotak_infak";
$sql_where = !empty($search) ? " WHERE (kode_kotak LIKE ? OR nama_lokasi LIKE ? OR pic_nama LIKE ?)" : '';
$search_param = "%{$search}%";

// Total data
$stmt_total = $mysqli->prepare("SELECT COUNT(id) as total " . $sql_base . $sql_where);
if(!empty($search)) $stmt_total->bind_param('sss', $search_param, $search_param, $search_param);
$stmt_total->execute();
$total_results = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);

// Data per halaman
$stmt_data = $mysqli->prepare("SELECT id, kode_kotak, nama_lokasi, pic_nama, status " . $sql_base . $sql_where . " ORDER BY nama_lokasi ASC LIMIT ? OFFSET ?");
if(!empty($search)) {
    $stmt_data->bind_param('sssii', $search_param, $search_param, $search_param, $limit, $offset);
} else {
    $stmt_data->bind_param('ii', $limit, $offset);
}
$stmt_data->execute();
$result_kotak = $stmt_data->get_result();

// Data untuk peta (semua lokasi aktif)
$result_map = $mysqli->query("SELECT nama_lokasi, latitude, longitude FROM kotak_infak WHERE status = 'Aktif' AND latitude IS NOT NULL AND longitude IS NOT NULL");
$locations_for_map = $result_map->fetch_all(MYSQLI_ASSOC);

require_once 'templates/header_admin.php';
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-bold text-dark-text">Kelola Kotak Infak</h1>
        <a href="tambah_kotak_infak.php" class="btn-primary"><i class="bi bi-plus-circle mr-2"></i> Tambah Kotak
            Baru</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <!-- Kolom Daftar & Peta -->
        <div class="lg:col-span-2">
            <div class="content-card">
                <form action="kelola_kotak_infak.php" method="GET" class="mb-4">
                    <div class="flex">
                        <input type="text" name="search" class="form-input rounded-r-none"
                            placeholder="Cari kode, lokasi, atau PIC..."
                            value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn-primary rounded-l-none">Cari</button>
                    </div>
                </form>

                <div class="space-y-3">
                    <?php if ($result_kotak->num_rows > 0): ?>
                    <?php while($kotak = $result_kotak->fetch_assoc()): ?>
                    <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                        <div class="p-3 bg-primary-orange text-white rounded-lg"><i
                                class="bi bi-box2-heart-fill text-xl"></i></div>
                        <div class="flex-1">
                            <p class="font-bold text-dark-text"><?php echo htmlspecialchars($kotak['nama_lokasi']); ?>
                            </p>
                            <p class="text-sm text-gray-500">Kode: <?php echo htmlspecialchars($kotak['kode_kotak']); ?>
                                | PIC: <?php echo htmlspecialchars($kotak['pic_nama']); ?></p>
                        </div>
                        <span
                            class="badge-<?php echo $kotak['status'] == 'Aktif' ? 'success' : 'secondary'; ?>"><?php echo $kotak['status']; ?></span>
                        <div>
                            <a href="edit_kotak_infak.php?id=<?php echo $kotak['id']; ?>"
                                class="btn-icon bg-yellow-100 text-yellow-600 hover:bg-yellow-200" title="Edit"><i
                                    class="bi bi-pencil-square"></i></a>
                            <a href="hapus_kotak_infak.php?id=<?php echo $kotak['id']; ?>" class="btn-icon bg-red-100 text-red-600 hover:bg-red-200"
                                onclick="return confirm('Yakin?');" title="Hapus"><i class="bi bi-trash"></i></a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <p class="text-center text-gray-500 py-8">Tidak ada kotak infak ditemukan.</p>
                    <?php endif; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="pagination mt-4">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                        class="<?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Kolom Peta -->
        <div class="lg:col-span-1">
            <div class="content-card">
                <h3 class="card-title mb-4">Peta Sebaran Kotak Aktif</h3>
                <div id="map" class="h-96 rounded-lg z-10"></div>
            </div>
        </div>
    </div>
</main>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('map').setView([-8.0633, 111.9008], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);
    var locations = <?php echo json_encode($locations_for_map); ?>;
    locations.forEach(loc => {
        L.marker([loc.latitude, loc.longitude]).addTo(map).bindPopup(`<b>${loc.nama_lokasi}</b>`);
    });
});
</script>
<?php require_once 'templates/footer_admin.php'; ?>