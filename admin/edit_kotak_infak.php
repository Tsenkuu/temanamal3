<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$page_title = "Edit Data Kotak Infak";
$id_kotak = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_kotak === 0) {
    header("Location: kelola_kotak_infak.php");
    exit;
}

// [BARU] Fungsi untuk memproses link pendek Gmaps di sisi server
function getFinalRedirectUrl($url) {
    if (!function_exists('curl_init')) { return $url; }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_exec($ch);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    return $finalUrl ?: $url;
}

$kotak = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        die('Error: Invalid CSRF token.');
    }

    $kotak = [
        'kode_kotak' => trim($_POST['kode_kotak']),
        'nama_lokasi' => trim($_POST['nama_lokasi']),
        'alamat' => trim($_POST['alamat']),
        'pic_nama' => trim($_POST['pic_nama']),
        'pic_kontak' => trim($_POST['pic_kontak']),
        'link_gmaps' => trim($_POST['link_gmaps']),
        'tanggal_penempatan' => !empty($_POST['tanggal_penempatan']) ? trim($_POST['tanggal_penempatan']) : null,
        'status' => $_POST['status'],
        'latitude' => !empty($_POST['latitude']) ? trim($_POST['latitude']) : null,
        'longitude' => !empty($_POST['longitude']) ? trim($_POST['longitude']) : null,
    ];
    
    if (empty($kotak['kode_kotak'])) $errors[] = "Kode Kotak tidak boleh kosong.";
    if (empty($kotak['nama_lokasi'])) $errors[] = "Nama Lokasi tidak boleh kosong.";

    // [BARU] Fallback jika JS gagal, proses link di backend
    if (empty($kotak['latitude']) && !empty($kotak['link_gmaps'])) {
        $finalUrl = getFinalRedirectUrl($kotak['link_gmaps']);
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $finalUrl, $matches)) {
            $kotak['latitude'] = $matches[1];
            $kotak['longitude'] = $matches[2];
        }
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare("UPDATE kotak_infak SET kode_kotak=?, nama_lokasi=?, alamat=?, pic_nama=?, pic_kontak=?, latitude=?, longitude=?, link_gmaps=?, tanggal_penempatan=?, status=? WHERE id=?");
        $stmt->bind_param("ssssssdsssi", $kotak['kode_kotak'], $kotak['nama_lokasi'], $kotak['alamat'], $kotak['pic_nama'], $kotak['pic_kontak'], $kotak['latitude'], $kotak['longitude'], $kotak['link_gmaps'], $kotak['tanggal_penempatan'], $kotak['status'], $id_kotak);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Data Kotak Infak berhasil diperbarui.";
            unset($_SESSION['csrf_token']);
            header("Location: kelola_kotak_infak.php");
            exit;
        } else {
            $errors[] = "Gagal memperbarui data. Error: " . $stmt->error;
        }
        $stmt->close();
    }
} else {
    $stmt_select = $mysqli->prepare("SELECT * FROM kotak_infak WHERE id = ?");
    $stmt_select->bind_param("i", $id_kotak);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $kotak = $result->fetch_assoc();
    $stmt_select->close();

    if (!$kotak) {
        header("Location: kelola_kotak_infak.php");
        exit;
    }
}

require_once 'templates/header_admin.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map-picker { height: 350px; width: 100%; border-radius: 0.5rem; border: 1px solid #ddd; margin-top: 1rem; }
    .input-group-gmaps { position: relative; }
    .gmaps-spinner {
        position: absolute; right: 10px; top: 50%;
        transform: translateY(-50%); display: none;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_admin.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo htmlspecialchars($page_title); ?></h1>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><strong>Terjadi kesalahan:</strong><ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>

            <div class="card shadow-sm"><div class="card-body">
                <form action="edit_kotak_infak.php?id=<?php echo $id_kotak; ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" id="latitude" name="latitude" value="<?php echo htmlspecialchars($kotak['latitude'] ?? ''); ?>">
                    <input type="hidden" id="longitude" name="longitude" value="<?php echo htmlspecialchars($kotak['longitude'] ?? ''); ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="kode_kotak" class="form-label">Kode Kotak</label><input type="text" class="form-control" id="kode_kotak" name="kode_kotak" value="<?php echo htmlspecialchars($kotak['kode_kotak'] ?? ''); ?>" required></div>
                        <div class="col-md-6 mb-3"><label for="nama_lokasi" class="form-label">Nama Lokasi</label><input type="text" class="form-control" id="nama_lokasi" name="nama_lokasi" value="<?php echo htmlspecialchars($kotak['nama_lokasi'] ?? ''); ?>" required></div>
                    </div>
                    <div class="mb-3"><label for="alamat" class="form-label">Alamat</label><textarea class="form-control" id="alamat" name="alamat" rows="2"><?php echo htmlspecialchars($kotak['alamat'] ?? ''); ?></textarea></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="pic_nama" class="form-label">Nama PIC</label><input type="text" class="form-control" id="pic_nama" name="pic_nama" value="<?php echo htmlspecialchars($kotak['pic_nama'] ?? ''); ?>" required></div>
                        <div class="col-md-6 mb-3"><label for="pic_kontak" class="form-label">Kontak PIC</label><input type="tel" class="form-control" id="pic_kontak" name="pic_kontak" value="<?php echo htmlspecialchars($kotak['pic_kontak'] ?? ''); ?>"></div>
                    </div>

                    <hr class="my-4">
                    <div class="mb-3">
                        <label for="link_gmaps" class="form-label">Lokasi di Peta</label>
                        <div class="input-group">
                            <button class="btn btn-outline-primary" type="button" id="get-location-btn"><i class="bi bi-geo-alt-fill me-2"></i> Pakai Lokasi Saat Ini</button>
                            <div class="input-group-gmaps flex-grow-1">
                                <input type="url" class="form-control" id="link_gmaps" name="link_gmaps" value="<?php echo htmlspecialchars($kotak['link_gmaps'] ?? ''); ?>" placeholder="Atau tempel link Google Maps di sini">
                                <div class="spinner-border spinner-border-sm text-secondary gmaps-spinner" role="status"></div>
                            </div>
                        </div>
                        <small class="form-text text-muted">Akurasi terbaik didapat dengan menempel link dari Google Maps.</small>
                    </div>
                    <div id="map-picker"></div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6 mb-3"><label for="tanggal_penempatan" class="form-label">Tanggal Penempatan</label><input type="date" class="form-control" id="tanggal_penempatan" name="tanggal_penempatan" value="<?php echo htmlspecialchars($kotak['tanggal_penempatan'] ?? ''); ?>"></div>
                        <div class="col-md-6 mb-3"><label for="status" class="form-label">Status</label><select class="form-select" id="status" name="status" required><option value="Aktif" <?php if (($kotak['status'] ?? '') == 'Aktif') echo 'selected'; ?>>Aktif</option><option value="Tidak Aktif" <?php if (($kotak['status'] ?? '') == 'Tidak Aktif') echo 'selected'; ?>>Tidak Aktif</option></select></div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan Perubahan</button>
                    <a href="kelola_kotak_infak.php" class="btn btn-secondary">Batal</a>
                </form>
            </div></div>
        </main>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var initialLat = <?php echo !empty($kotak['latitude']) ? $kotak['latitude'] : '-8.0633'; ?>;
    var initialLng = <?php echo !empty($kotak['longitude']) ? $kotak['longitude'] : '111.9008'; ?>;
    var initialZoom = <?php echo !empty($kotak['latitude']) ? '17' : '13'; ?>;
    
    var map = L.map('map-picker').setView([initialLat, initialLng], initialZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
    var marker = null;
    var latInput = document.getElementById('latitude');
    var lngInput = document.getElementById('longitude');
    var gmapsLinkInput = document.getElementById('link_gmaps');
    var getLocationBtn = document.getElementById('get-location-btn');
    var gmapsSpinner = document.querySelector('.gmaps-spinner');
    var defaultBtnText = getLocationBtn.innerHTML;

    function updateMarker(lat, lng) {
        if (!lat || !lng) return;
        var newLatLng = L.latLng(lat, lng);
        if (marker) {
            marker.setLatLng(newLatLng);
        } else {
            marker = L.marker(newLatLng, { draggable: true }).addTo(map);
            marker.on('dragend', function(e) { updateInputs(e.target.getLatLng().lat, e.target.getLatLng().lng); });
        }
        map.setView(newLatLng, 17);
        updateInputs(lat, lng);
    }

    function updateInputs(lat, lng) {
        latInput.value = lat.toFixed(8);
        lngInput.value = lng.toFixed(8);
        const currentLink = `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`;
        if (gmapsLinkInput.value !== currentLink) {
             gmapsLinkInput.value = currentLink;
        }
    }

    getLocationBtn.addEventListener('click', function() {
        if (!navigator.geolocation) { alert('Geolocation tidak didukung.'); return; }
        this.disabled = true; this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mencari...';
        navigator.geolocation.getCurrentPosition(
            (pos) => { updateMarker(pos.coords.latitude, pos.coords.longitude); this.disabled = false; this.innerHTML = defaultBtnText; },
            () => { alert('Gagal mengambil lokasi.'); this.disabled = false; this.innerHTML = defaultBtnText; },
            { enableHighAccuracy: true }
        );
    });
    
    gmapsLinkInput.addEventListener('paste', function(e) {
        let pastedText = (e.clipboardData || window.clipboardData).getData('text');
        if (pastedText) processGmapsLink(pastedText);
    });
    gmapsLinkInput.addEventListener('input', function(e) {
         clearTimeout(this.timer);
         this.timer = setTimeout(() => { processGmapsLink(e.target.value); }, 500);
     });

    async function processGmapsLink(url) {
        if (!url || !url.includes('http')) return;
        gmapsSpinner.style.display = 'block';
        const match = url.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
        if (match) {
            updateMarker(parseFloat(match[1]), parseFloat(match[2]));
            gmapsSpinner.style.display = 'none';
            return;
        }
        if (url.includes('maps.app.goo.gl')) {
            try {
                const formData = new FormData();
                formData.append('url', url);
                const response = await fetch('parse_gmaps_link.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    updateMarker(data.latitude, data.longitude);
                    gmapsLinkInput.value = data.final_url;
                }
            } catch (error) { console.error('Error:', error); } 
            finally { gmapsSpinner.style.display = 'none'; }
        } else { gmapsSpinner.style.display = 'none'; }
    }

    if (initialLat !== -8.0633) { 
        updateMarker(initialLat, initialLng); 
    }
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>
