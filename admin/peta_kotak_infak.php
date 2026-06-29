<?php
require_once '../includes/config.php';

// Pengecekan login amil
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Peta Navigasi Kotak Infak";

// Mengambil ID untuk linking antara daftar dan peta
$sql = "SELECT id, nama_lokasi, alamat, latitude, longitude, pic_nama, pic_kontak 
        FROM kotak_infak 
        WHERE status = 'Aktif' AND latitude IS NOT NULL AND longitude IS NOT NULL";
$result_peta = $mysqli->query($sql);

$locations = [];
if ($result_peta) {
    while ($row = $result_peta->fetch_assoc()) {
        $locations[] = $row;
    }
}

require_once 'templates/header_admin.php';
?>

<!-- Memuat CSS Leaflet & Plugin-pluginnya -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />

<style>
    /* CSS untuk UI Peta Fullscreen Mobile-First */
    body, html {
        overflow: hidden;
    }
    main.main-content {
        padding: 0 !important;
        height: calc(100vh - 56px);
        position: relative;
    }
    #map {
        width: 100%;
        height: 100%;
        z-index: 1;
    }
    .leaflet-top, .leaflet-bottom {
        z-index: 999;
    }
    .floating-button {
        position: absolute;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    #location-list-btn {
        top: 20px;
        left: 20px;
    }
    #locate-btn {
        bottom: 30px;
        right: 20px;
    }
    /* [DIUBAH] Tombol di dalam popup */
    .popup-actions a {
        display: block;
        text-align: center;
        text-decoration: none;
        padding: 8px 12px;
        margin-top: 8px;
        border-radius: 5px;
        font-weight: 500;
        border: 1px solid transparent;
    }
    .btn-gmaps-route {
        background-color: #1a73e8;
        color: white;
    }
    .btn-internal-route {
        background-color: #6c757d;
        color: white;
    }
     .btn-internal-route.disabled {
        background-color: #adb5bd;
        cursor: not-allowed;
    }
    .offcanvas-body .list-group-item {
        cursor: pointer;
    }
    /* [BARU] Peringatan HTTPS */
    #https-warning {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        background-color: #fff3cd;
        color: #664d03;
        text-align: center;
        padding: 10px;
        z-index: 1001;
        font-weight: 500;
        display: none; /* Disembunyikan secara default */
    }
</style>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_admin.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
            <!-- [BARU] Tempat untuk Peringatan HTTPS -->
            <div id="https-warning">
                <i class="bi bi-exclamation-triangle-fill"></i> Fitur GPS memerlukan koneksi aman (HTTPS) untuk berfungsi.
            </div>

            <div id="map"></div>

            <button id="location-list-btn" class="btn btn-light btn-lg floating-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasLocations" aria-controls="offcanvasLocations">
                <i class="bi bi-list-ul"></i>
            </button>

            <button id="locate-btn" class="btn btn-light btn-lg rounded-circle floating-button" title="Mulai Lacak Lokasi Saya">
                <i id="locate-icon" class="bi bi-geo-fill text-primary"></i>
            </button>
        </main>
    </div>
</div>

<!-- Offcanvas untuk Daftar Lokasi -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasLocations" aria-labelledby="offcanvasLocationsLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasLocationsLabel">Daftar Kotak Infak</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <div class="mb-3">
        <input type="text" id="search-box" class="form-control" placeholder="Cari nama lokasi atau alamat...">
    </div>
    <div class="list-group" id="list-items-container"></div>
  </div>
</div>

<!-- Memuat JS Leaflet & Plugin -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // [BARU] Cek koneksi HTTPS
    if (window.location.protocol !== 'https:') {
        document.getElementById('https-warning').style.display = 'block';
    }

    var map = L.map('map').setView([-8.0633, 111.9008], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    var locations = <?php echo json_encode($locations); ?>;
    var allMarkers = L.layerGroup().addTo(map);
    var userMarker = null;
    var routingControl = null;
    var isWatching = false;

    var boxIcon = L.icon({ iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png', shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png', iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41] });
    var userIcon = L.icon({ iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png', shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png', iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41] });

    function displayLocations(locationsToShow) {
        allMarkers.clearLayers();
        var listContainer = document.getElementById('list-items-container');
        listContainer.innerHTML = '';

        locationsToShow.forEach(function(loc) {
            var listItem = document.createElement('a');
            listItem.href = '#';
            listItem.className = 'list-group-item list-group-item-action';
            listItem.dataset.id = loc.id;
            listItem.innerHTML = `<div class="d-flex w-100 justify-content-between"><h6 class="mb-1">${loc.nama_lokasi}</h6></div><p class="mb-1 small text-muted">${loc.alamat || ''}</p>`;
            listContainer.appendChild(listItem);

            var marker = L.marker([loc.latitude, loc.longitude], { icon: boxIcon });
            marker.options.locationId = loc.id;
            
            // [DIUBAH] Konten popup dengan tombol navigasi
            var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            var gmapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${loc.latitude},${loc.longitude}`;
            var appleMapsUrl = `http://maps.apple.com/?daddr=${loc.latitude},${loc.longitude}`;

            var popupContent = `<b>${loc.nama_lokasi}</b><br><small>${loc.alamat || ''}</small>
                <div class="popup-actions">
                    <a href="${isIOS ? appleMapsUrl : gmapsUrl}" target="_blank" class="btn-gmaps-route">
                        <i class="bi bi-sign-turn-right-fill"></i> Buka Navigasi
                    </a>
                    <a href="#" class="btn-internal-route ${userMarker ? '' : 'disabled'}" data-lat="${loc.latitude}" data-lng="${loc.longitude}" title="${userMarker ? 'Tampilkan rute di peta ini' : 'Aktifkan GPS Anda terlebih dahulu'}">
                        <i class="bi bi-pin-map-fill"></i> Rute di Peta Ini
                    </a>
                </div>`;
            marker.bindPopup(popupContent);
            allMarkers.addLayer(marker);
        });
    }

    displayLocations(locations);

    document.getElementById('search-box').addEventListener('keyup', function(e) {
        var keyword = e.target.value.toLowerCase();
        var filtered = locations.filter(loc => loc.nama_lokasi.toLowerCase().includes(keyword) || (loc.alamat && loc.alamat.toLowerCase().includes(keyword)));
        displayLocations(filtered);
    });

    document.getElementById('list-items-container').addEventListener('click', function(e) {
        e.preventDefault();
        var item = e.target.closest('.list-group-item');
        if (!item) return;

        var locationId = item.dataset.id;
        allMarkers.eachLayer(function(marker) {
            if (marker.options.locationId == locationId) {
                map.setView(marker.getLatLng(), 17);
                marker.openPopup();
                var offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasLocations'));
                offcanvas.hide();
            }
        });
    });
    
    function createRoute(destLat, destLng) {
        if (!userMarker) {
            alert("Lokasi Anda belum ditemukan. Mohon aktifkan GPS dan klik tombol lokasi.");
            return;
        }
        if (routingControl) map.removeControl(routingControl);
        
        routingControl = L.Routing.control({
            waypoints: [ userMarker.getLatLng(), L.latLng(destLat, destLng) ],
            routeWhileDragging: true, language: 'id', createMarker: () => null,
            lineOptions: { styles: [{color: '#0d6efd', opacity: 0.8, weight: 6}] }
        }).addTo(map);
    }

    map.on('popupopen', function(e) {
        var popupNode = e.popup.getElement();
        var routeBtn = popupNode.querySelector('.btn-internal-route');
        if (routeBtn && !routeBtn.classList.contains('disabled')) {
            routeBtn.onclick = function(ev) {
                ev.preventDefault();
                createRoute(routeBtn.dataset.lat, routeBtn.dataset.lng);
                map.closePopup();
            };
        }
    });

    var locateBtn = document.getElementById('locate-btn');
    var locateIcon = document.getElementById('locate-icon');

    locateBtn.addEventListener('click', function() {
        if (!isWatching) {
            locateIcon.className = 'spinner-border spinner-border-sm';
            this.disabled = true;
            map.locate({ setView: true, maxZoom: 17, watch: true, enableHighAccuracy: true });
        } else {
            map.stopLocate();
        }
    });

    map.on('locationfound', function(e) {
        locateBtn.disabled = false;
        
        if (!isWatching) {
            isWatching = true;
            locateBtn.setAttribute('title', 'Hentikan Pelacakan');
            locateIcon.className = 'bi bi-geo-alt-fill text-danger';
        }
        
        var popupContent = `<b>Posisi Anda</b><br>(Akurasi: ${e.accuracy.toFixed(0)} meter)`;
        if (!userMarker) {
            userMarker = L.marker(e.latlng, { icon: userIcon }).addTo(map).bindPopup(popupContent).openPopup();
        } else {
            userMarker.setLatLng(e.latlng).getPopup().setContent(popupContent);
        }
        
        if (routingControl) routingControl.spliceWaypoints(0, 1, e.latlng);

        // [BARU] Perbarui status tombol rute di semua popup yang mungkin terbuka
        document.querySelectorAll('.btn-internal-route').forEach(btn => {
            btn.classList.remove('disabled');
            btn.setAttribute('title', 'Tampilkan rute di peta ini');
        });
    });

    map.on('locationerror', function(e) {
        map.stopLocate(); // Hentikan pemantauan jika error
        isWatching = false;
        locateBtn.disabled = false;
        locateBtn.setAttribute('title', 'Gagal! Coba lagi');
        locateIcon.className = 'bi bi-geo-fill text-danger';
        alert("Gagal mendapatkan lokasi. Pastikan GPS aktif dan Anda memberikan izin lokasi untuk browser ini. Pesan: " + e.message);
    });
    
    // [BARU] Event saat pelacakan dihentikan
    map.on('locationstop', function() {
        isWatching = false;
        locateBtn.setAttribute('title', 'Mulai Lacak Lokasi Saya');
        locateIcon.className = 'bi bi-geo-fill text-primary';
        if(userMarker) {
            userMarker.getPopup().setContent('Pelacakan dihentikan. Ini lokasi terakhir Anda.').openPopup();
        }
    });

    if (locations.length > 0) {
        map.fitBounds(allMarkers.getBounds(), { padding: [50, 50] });
    }
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>

