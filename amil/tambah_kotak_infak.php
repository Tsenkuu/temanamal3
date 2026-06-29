<?php
require_once '../includes/config.php';

// Pengecekan login amil
if (!isset($_SESSION['amil_id'])) {
    header('Location: ../login.php');
    exit();
}

// Fungsi canggih untuk membaca tautan pendek Google Maps
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

// Proteksi CSRF & inisialisasi
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
$page_title = "Tambah Kotak Infak Baru";
$errors = [];
$input = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        die('Error: Invalid CSRF token.');
    }

    $input = [
        'kode_kotak' => trim($_POST['kode_kotak']),
        'nama_lokasi' => trim($_POST['nama_lokasi']),
        'alamat' => trim($_POST['alamat']),
        'pic_nama' => trim($_POST['pic_nama']),
        'pic_kontak' => trim($_POST['pic_kontak']),
        'link_gmaps' => trim($_POST['link_gmaps']),
        'tanggal_penempatan' => trim($_POST['tanggal_penempatan']),
        'status' => $_POST['status'],
        'latitude' => !empty($_POST['latitude']) ? trim($_POST['latitude']) : null,
        'longitude' => !empty($_POST['longitude']) ? trim($_POST['longitude']) : null
    ];
    
    if (empty($input['kode_kotak'])) { $errors[] = "Kode Kotak wajib diisi."; }
    if (empty($input['nama_lokasi'])) { $errors[] = "Nama Lokasi wajib diisi."; }
    if (empty($input['pic_nama'])) { $errors[] = "Nama PIC wajib diisi."; }

    if (empty($input['latitude']) && !empty($input['link_gmaps'])) {
        $finalUrl = getFinalRedirectUrl($input['link_gmaps']);
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $finalUrl, $matches)) {
            $input['latitude'] = $matches[1];
            $input['longitude'] = $matches[2];
        }
    }

    if (empty($errors)) {
        $stmt_check = $mysqli->prepare("SELECT id FROM kotak_infak WHERE kode_kotak = ?");
        $stmt_check->bind_param("s", $input['kode_kotak']);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $errors[] = "Kode Kotak sudah ada. Harap gunakan kode yang unik.";
        }
        $stmt_check->close();
    }

    if (empty($errors)) {
        $tanggal_db = !empty($input['tanggal_penempatan']) ? $input['tanggal_penempatan'] : null;
        $stmt = $mysqli->prepare("INSERT INTO kotak_infak (kode_kotak, nama_lokasi, alamat, pic_nama, pic_kontak, latitude, longitude, link_gmaps, tanggal_penempatan, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssdsss", $input['kode_kotak'], $input['nama_lokasi'], $input['alamat'], $input['pic_nama'], $input['pic_kontak'], $input['latitude'], $input['longitude'], $input['link_gmaps'], $tanggal_db, $input['status']);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Data Kotak Infak baru berhasil ditambahkan.";
            unset($_SESSION['csrf_token']);
            header("Location: kelola_kotak_infaq.php");
            exit;
        } else {
            $errors[] = "Gagal menyimpan data: " . $stmt->error;
        }
        $stmt->close();
    }
}

require_once 'templates/header_amil.php';
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map-picker { height: 350px; width: 100%; border-radius: 0.5rem; border: 1px solid #ddd; margin-top: 1rem; }
    /* [BARU] Style untuk loading spinner di input link */
    .input-group-gmaps { position: relative; }
    .gmaps-spinner {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        display: none; /* Sembunyikan secara default */
    }
</style>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_amil.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo htmlspecialchars($page_title); ?></h1>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><strong>Terjadi kesalahan:</strong><ul><?php foreach ($errors as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>

            <div class="card shadow-sm"><div class="card-body">
                <form action="tambah_kotak_infak.php" method="POST" id="kotak-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" id="latitude" name="latitude" value="<?php echo htmlspecialchars($input['latitude'] ?? ''); ?>">
                    <input type="hidden" id="longitude" name="longitude" value="<?php echo htmlspecialchars($input['longitude'] ?? ''); ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="kode_kotak" class="form-label">Kode Kotak</label><input type="text" class="form-control" id="kode_kotak" name="kode_kotak" value="<?php echo htmlspecialchars($input['kode_kotak'] ?? ''); ?>" required></div>
                        <div class="col-md-6 mb-3"><label for="nama_lokasi" class="form-label">Nama Lokasi</label><input type="text" class="form-control" id="nama_lokasi" name="nama_lokasi" value="<?php echo htmlspecialchars($input['nama_lokasi'] ?? ''); ?>" required></div>
                    </div>
                    <div class="mb-3"><label for="alamat" class="form-label">Alamat</label><textarea class="form-control" id="alamat" name="alamat" rows="2"><?php echo htmlspecialchars($input['alamat'] ?? ''); ?></textarea></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="pic_nama" class="form-label">Nama PIC</label><input type="text" class="form-control" id="pic_nama" name="pic_nama" value="<?php echo htmlspecialchars($input['pic_nama'] ?? ''); ?>" required></div>
                        <div class="col-md-6 mb-3"><label for="pic_kontak" class="form-label">Kontak PIC</label><input type="tel" class="form-control" id="pic_kontak" name="pic_kontak" value="<?php echo htmlspecialchars($input['pic_kontak'] ?? ''); ?>"></div>
                    </div>
                    <hr class="my-4">
                    <div class="mb-3">
                        <label for="link_gmaps" class="form-label">Lokasi di Peta</label>
                        <div class="input-group">
                            <button class="btn btn-outline-primary" type="button" id="get-location-btn"><i class="bi bi-geo-alt-fill me-2"></i> Pakai Lokasi Saat Ini</button>
                            <!-- [DIUBAH] Tambah wrapper dan spinner -->
                            <div class="input-group-gmaps flex-grow-1">
                                <input type="url" class="form-control" id="link_gmaps" name="link_gmaps" value="<?php echo htmlspecialchars($input['link_gmaps'] ?? ''); ?>" placeholder="Atau tempel link Google Maps di sini">
                                <div class="spinner-border spinner-border-sm text-secondary gmaps-spinner" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <small class="form-text text-muted">Akurasi terbaik didapat dengan menempel link dari Google Maps.</small>
                    </div>
                    <div id="map-picker"></div>
                    <div class="row mt-4">
                        <div class="col-md-6 mb-3"><label for="tanggal_penempatan" class="form-label">Tanggal Penempatan</label><input type="date" class="form-control" id="tanggal_penempatan" name="tanggal_penempatan" value="<?php echo htmlspecialchars($input['tanggal_penempatan'] ?? ''); ?>"></div>
                        <div class="col-md-6 mb-3"><label for="status" class="form-label">Status</label><select class="form-select" id="status" name="status" required><option value="Aktif" <?php echo (($input['status'] ?? 'Aktif') == 'Aktif') ? 'selected' : ''; ?>>Aktif</option><option value="Tidak Aktif" <?php echo (($input['status'] ?? '') == 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option></select></div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan</button>
                    <a href="kelola_kotak_infaq.php" class="btn btn-secondary">Batal</a>
                </form>
            </div></div>
        </main>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('map-picker').setView([-8.0633, 111.9008], 13);
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
        // [DIUBAH] Hanya update link jika belum diisi atau berbeda
        const currentLink = `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`;
        if (gmapsLinkInput.value !== currentLink) {
             gmapsLinkInput.value = currentLink;
        }
    }
    
    getLocationBtn.addEventListener('click', function() {
        if (!navigator.geolocation) { alert('Geolocation tidak didukung.'); return; }
        this.disabled = true; this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mencari...';
        navigator.geolocation.getCurrentPosition(
            (pos) => { 
                updateMarker(pos.coords.latitude, pos.coords.longitude); 
                this.disabled = false; 
                this.innerHTML = defaultBtnText; 
            },
            () => { alert('Gagal mengambil lokasi.'); this.disabled = false; this.innerHTML = defaultBtnText; },
            { enableHighAccuracy: true }
        );
    });

    // --- [LOGIKA BARU] AUTO-PARSE LINK GOOGLE MAPS ---
    gmapsLinkInput.addEventListener('paste', function(e) {
        // Ambil teks yang dipaste
        let pastedText = (e.clipboardData || window.clipboardData).getData('text');
        if (pastedText) {
            processGmapsLink(pastedText);
        }
    });
     gmapsLinkInput.addEventListener('input', function(e) {
         // Debounce untuk menghindari request berlebihan saat mengetik
         clearTimeout(this.timer);
         this.timer = setTimeout(() => {
             processGmapsLink(e.target.value);
         }, 500);
     });

    async function processGmapsLink(url) {
        if (!url || !url.includes('http')) return;

        gmapsSpinner.style.display = 'block';

        // Coba regex langsung
        const match = url.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
        if (match) {
            const lat = parseFloat(match[1]);
            const lng = parseFloat(match[2]);
            updateMarker(lat, lng);
            gmapsSpinner.style.display = 'none';
            return;
        }

        // Jika tidak ada match (kemungkinan link pendek), panggil helper via fetch
        if (url.includes('maps.app.goo.gl')) {
            try {
                const formData = new FormData();
                formData.append('url', url);

                const response = await fetch('parse_gmaps_link.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();

                if (data.success) {
                    updateMarker(data.latitude, data.longitude);
                    gmapsLinkInput.value = data.final_url; // Update link dengan URL panjang
                } else {
                    console.error('Gagal mem-parsing link:', data.message);
                }
            } catch (error) {
                console.error('Error saat menghubungi server:', error);
            } finally {
                gmapsSpinner.style.display = 'none';
            }
        } else {
             gmapsSpinner.style.display = 'none';
        }
    }

    // Inisialisasi peta jika ada data awal
    if (latInput.value && lngInput.value) { 
        updateMarker(parseFloat(latInput.value), parseFloat(lngInput.value)); 
    }
});
</script>

<?php require_once 'templates/footer_amil.php'; ?>
