<?php
/*
|--------------------------------------------------------------------------
| File: amil/kelola_kotak_infaq.php (DIROMBAK)
|--------------------------------------------------------------------------
|
| Halaman pengelolaan kotak infak dengan UI/UX baru yang mobile-first.
| Menghilangkan layout 2 kolom dan menggunakan kartu (cards) yang responsif.
|
*/
require_once '../includes/config.php';

// Pengecekan login amil
if (!isset($_SESSION['amil_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Kelola Kotak Infak";
require_once 'templates/header_amil.php';

// --- LOGIKA PAGINASI & PENCARIAN (Backend tetap sama) ---
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = isset($_GET['per_page']) && in_array((int)$_GET['per_page'], [10, 25, 50]) ? (int)$_GET['per_page'] : 10;
$offset = ($page - 1) * $per_page;

$sql_base = "FROM kotak_infak";
$sql_where = "";
$params = [];
$types = "";

if (!empty($search_query)) {
    $sql_where = " WHERE (kode_kotak LIKE ? OR nama_lokasi LIKE ? OR pic_nama LIKE ? OR alamat LIKE ? OR pic_kontak LIKE ?)";
    $search_term = "%{$search_query}%";
    $params = [$search_term, $search_term, $search_term, $search_term, $search_term];
    $types = "sssss";
}

$sql_total = "SELECT COUNT(id) as total " . $sql_base . $sql_where;
$stmt_total = $mysqli->prepare($sql_total);
if (!empty($params)) { $stmt_total->bind_param($types, ...$params); }
$stmt_total->execute();
$total_rows = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $per_page);
$stmt_total->close();

$sql_data = "SELECT * " . $sql_base . $sql_where . " ORDER BY nama_lokasi ASC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$stmt_data = $mysqli->prepare($sql_data);
$stmt_data->bind_param($types, ...$params);
$stmt_data->execute();
$result = $stmt_data->get_result();
$kotak_infak_list = $result->fetch_all(MYSQLI_ASSOC);
$stmt_data->close();

// Query untuk data peta (tetap sama)
$map_result = $mysqli->query("SELECT nama_lokasi, alamat, pic_nama, latitude, longitude FROM kotak_infak WHERE status = 'Aktif' AND latitude IS NOT NULL AND longitude IS NOT NULL");
$locations_for_map = $map_result->fetch_all(MYSQLI_ASSOC);

function get_status_badge($status) {
    $badge_class = ($status == 'Aktif') ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis';
    $icon = ($status == 'Aktif') ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
    return "<span class=\"badge {$badge_class} px-2 py-1 rounded-pill\"><i class=\"bi {$icon} me-1\"></i>{$status}</span>";
}
?>
<!-- CSS & JS untuk Peta -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map-view { height: 350px; border-radius: 0.75rem; }
</style>

<!-- Tombol Aksi Utama -->
<div class="d-flex justify-content-end mb-4">
    <a href="tambah_kotak_infak.php" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Tambah Kotak Baru
    </a>
</div>

<!-- Form Filter dan Pencarian -->
<div class="card mb-4">
    <div class="card-body">
        <form id="filterForm" action="kelola_kotak_infaq.php" method="GET" class="d-flex flex-column flex-md-row gap-2">
            <div class="input-group flex-grow-1">
                <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control border-0 bg-light" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Cari kode, lokasi, PIC...">
            </div>
            <select name="per_page" class="form-select" onchange="this.form.submit()" style="max-width: 150px;">
                <option value="10" <?php if($per_page == 10) echo 'selected'; ?>>10 / Halaman</option>
                <option value="25" <?php if($per_page == 25) echo 'selected'; ?>>25 / Halaman</option>
                <option value="50" <?php if($per_page == 50) echo 'selected'; ?>>50 / Halaman</option>
            </select>
        </form>
    </div>
</div>

<!-- Peta Sebaran (Collapsible) -->
<div class="accordion mb-4" id="mapAccordion">
  <div class="accordion-item card">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMap" aria-expanded="false" aria-controls="collapseMap">
        <i class="bi bi-map-fill me-2"></i> Lihat Peta Sebaran Kotak Infak
      </button>
    </h2>
    <div id="collapseMap" class="accordion-collapse collapse" data-bs-parent="#mapAccordion">
      <div class="accordion-body">
         <div id="map-view" class="shadow-sm"></div>
      </div>
    </div>
  </div>
</div>


<!-- Daftar Kotak Infak -->
<div class="list-kotak-infak">
    <?php if (!empty($kotak_infak_list)) : foreach ($kotak_infak_list as $row) : ?>
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="badge bg-primary-subtle text-primary-emphasis mb-1 rounded-pill"><?php echo htmlspecialchars($row['kode_kotak']); ?></span>
                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($row['nama_lokasi']); ?></h6>
                    <small class="text-muted"><i class="bi bi-geo-alt-fill me-1"></i><?php echo htmlspecialchars($row['alamat'] ?: 'Alamat tidak tersedia'); ?></small>
                </div>
                <div><?php echo get_status_badge($row['status']); ?></div>
            </div>
            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                <div class="pic-info small">
                    <i class="bi bi-person-fill me-1"></i>
                    <strong>PIC:</strong> <?php echo htmlspecialchars($row['pic_nama']); ?> (<?php echo htmlspecialchars($row['pic_kontak'] ?: '-'); ?>)
                </div>
                <div class="actions">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill detail-btn" data-id="<?php echo $row['id']; ?>"><i class="bi bi-info-circle me-1"></i> Detail</button>
                    <a href="edit_kotak_infak.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary rounded-pill"><i class="bi bi-pencil me-1"></i> Edit</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; else : ?>
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-search display-4 d-block mb-2"></i>
            <?php echo empty($search_query) ? 'Belum ada data kotak infak.' : 'Data tidak ditemukan untuk pencarian Anda.'; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Navigasi Halaman -->
<?php if($total_pages > 1): ?>
<nav class="mt-4 d-flex justify-content-between align-items-center">
    <div class="small text-muted">Menampilkan <?php echo count($kotak_infak_list); ?> dari <?php echo $total_rows; ?> data</div>
    <ul class="pagination mb-0">
        <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>"><a class="page-link" href="?page=<?php echo $page-1; ?>&per_page=<?php echo $per_page; ?>&search=<?php echo urlencode($search_query); ?>">Sebelumnya</a></li>
        <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>"><a class="page-link" href="?page=<?php echo $page+1; ?>&per_page=<?php echo $per_page; ?>&search=<?php echo urlencode($search_query); ?>">Berikutnya</a></li>
    </ul>
</nav>
<?php endif; ?>


<!-- Modal Detail (tetap sama, akan mengadopsi style baru secara otomatis) -->
<div class="modal fade" id="detailModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4">
    <div class="modal-header"><h5 class="modal-title">Detail Kotak Infak</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="modalBodyContent"></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Tutup</button><a href="#" id="editLink" class="btn btn-primary rounded-pill">Edit Data</a></div>
</div></div></div>


<?php require_once 'templates/footer_amil.php'; ?>
<!-- JS untuk Peta & Modal -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Logika Peta
    var map;
    var mapElement = document.getElementById('map-view');
    var collapseElement = document.getElementById('collapseMap');

    // Inisialisasi peta hanya saat accordion dibuka untuk pertama kali
    collapseElement.addEventListener('shown.bs.collapse', function () {
        if (!map) {
            var mapLocations = <?php echo json_encode($locations_for_map); ?>;
            map = L.map(mapElement).setView([-8.0633, 111.9008], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
            var markers = L.layerGroup().addTo(map);
            mapLocations.forEach(loc => {
                L.marker([loc.latitude, loc.longitude]).addTo(markers)
                 .bindPopup(`<b>${loc.nama_lokasi}</b><br><small>PIC: ${loc.pic_nama}</small>`);
            });
            if (mapLocations.length > 0) {
                map.fitBounds(markers.getBounds(), { padding: [20, 20] });
            }
        }
    });

    // Logika Modal Detail (tetap sama)
    const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
    document.querySelector('.list-kotak-infak').addEventListener('click', async function(event) {
        const target = event.target.closest('.detail-btn');
        if (!target) return;

        const id = target.dataset.id;
        const modalBody = document.getElementById('modalBodyContent');
        const editLink = document.getElementById('editLink');
        
        modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Memuat data...</p></div>`;
        editLink.href = '#';
        detailModal.show();

        try {
            const response = await fetch(`get_kotak_detail.php?id=${id}`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const html = await response.text();
            modalBody.innerHTML = html;
            editLink.href = `edit_kotak_infak.php?id=${id}`;
        } catch (error) {
            console.error('Fetch error:', error);
            modalBody.innerHTML = `<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>Gagal memuat data.</div>`;
        }
    });
});
</script>
