<?php
// Memuat file konfigurasi dan keamanan
require_once '../includes/config.php';

// Pengecekan login amil
if (!isset($_SESSION['amil_id'])) {
    http_response_code(403); // Forbidden
    die("Akses ditolak. Silakan login kembali.");
}

// Validasi input ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id === 0) {
    http_response_code(400); // Bad Request
    die("ID kotak infak tidak valid.");
}

// Ambil data dari database menggunakan prepared statement untuk keamanan
$stmt = $mysqli->prepare("SELECT * FROM kotak_infak WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$kotak = $result->fetch_assoc();
$stmt->close();

if (!$kotak) {
    http_response_code(404); // Not Found
    die("Data kotak infak tidak ditemukan.");
}

// Fungsi helper untuk menampilkan baris data dengan aman dan rapi
function display_row($label, $value, $is_link = false, $is_coordinate = false) {
    // Jika value kosong, tampilkan strip (-)
    if (empty($value)) {
        $display_value = '<span class="text-muted">-</span>';
    } else {
        // Jika tidak kosong, amankan output
        $display_value = htmlspecialchars($value);
        
        // Format khusus untuk koordinat
        if ($is_coordinate && !empty($kotak['latitude']) && !empty($kotak['longitude'])) {
            $display_value = "{$kotak['latitude']}, {$kotak['longitude']}";
            $display_value = "<span class='font-monospace small'>{$display_value}</span>";
        }
        
        // Jika ini adalah link, format sebagai tautan
        if ($is_link) {
            $display_value = "<a href='{$display_value}' target='_blank' rel='noopener noreferrer' class='text-decoration-none'>
                                <span class='badge bg-light text-dark border'><i class='bi bi-geo-alt-fill me-1 text-primary'></i>Lihat di Peta</span>
                             </a>";
        }
        
        // Format khusus untuk tanggal
        if ($label === 'Tgl. Penempatan') {
            $display_value = date('d F Y', strtotime($value));
        }
    }
    
    return "
        <div class='row mb-2 py-1'>
            <div class='col-sm-4 text-muted small'>{$label}</div>
            <div class='col-sm-8'>{$display_value}</div>
        </div>
    ";
}
?>

<style>
    .detail-header {
        background: linear-gradient(135deg, #6c5ce7 0%, #8c7ae6 100%);
        color: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .status-badge {
        font-size: 0.85rem;
        padding: 5px 12px;
        border-radius: 50px;
    }
    .detail-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 15px;
    }
    .detail-card .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
        padding: 12px 15px;
    }
    .info-divider {
        border-top: 2px dashed #e9ecef;
        margin: 15px 0;
    }
</style>

<div class="container-fluid p-0">
    <!-- Header dengan informasi utama -->
    <div class="detail-header shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 fw-bold"><?php echo htmlspecialchars($kotak['nama_lokasi']); ?></h5>
                <div class="d-flex align-items-center gap-2 mt-2">
                    <span class="badge bg-light text-dark"><?php echo htmlspecialchars($kotak['kode_kotak']); ?></span>
                    <span class="status-badge <?php echo ($kotak['status'] == 'Aktif') ? 'bg-success' : 'bg-secondary'; ?>">
                        <i class="bi <?php echo ($kotak['status'] == 'Aktif') ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?> me-1"></i>
                        <?php echo htmlspecialchars($kotak['status']); ?>
                    </span>
                </div>
            </div>
            <div class="text-end">
                <i class="bi bi-geo-fill display-4 opacity-75"></i>
            </div>
        </div>
    </div>

    <!-- Kartu Informasi Lokasi -->
    <div class="card detail-card">
        <div class="card-header d-flex align-items-center">
            <i class="bi bi-geo-alt me-2"></i> Informasi Lokasi
        </div>
        <div class="card-body">
            <?php echo display_row('Kode Kotak', $kotak['kode_kotak']); ?>
            <?php echo display_row('Nama Lokasi', $kotak['nama_lokasi']); ?>
            <?php 
            if (!empty($kotak['alamat'])) {
                echo "
                <div class='row mb-2 py-1'>
                    <div class='col-sm-4 text-muted small'>Alamat</div>
                    <div class='col-sm-8'><strong>".nl2br(htmlspecialchars($kotak['alamat']))."</strong></div>
                </div>
                ";
            } else {
                echo display_row('Alamat', '');
            }
            ?>
        </div>
    </div>

    <!-- Kartu Informasi PIC -->
    <div class="card detail-card">
        <div class="card-header d-flex align-items-center">
            <i class="bi bi-person-badge me-2"></i> Informasi PIC
        </div>
        <div class="card-body">
            <?php echo display_row('Nama PIC', $kotak['pic_nama']); ?>
            <?php 
            if (!empty($kotak['pic_kontak'])) {
                echo "
                <div class='row mb-2 py-1'>
                    <div class='col-sm-4 text-muted small'>Kontak PIC</div>
                    <div class='col-sm-8'>
                        <strong>".htmlspecialchars($kotak['pic_kontak'])."</strong>
                        <a href='tel:".htmlspecialchars($kotak['pic_kontak'])."' class='ms-2 text-decoration-none'>
                            <span class='badge bg-primary'><i class='bi bi-telephone-fill me-1'></i>Hubungi</span>
                        </a>
                    </div>
                </div>
                ";
            } else {
                echo display_row('Kontak PIC', '');
            }
            ?>
        </div>
    </div>

    <!-- Kartu Informasi Teknis -->
    <div class="card detail-card">
        <div class="card-header d-flex align-items-center">
            <i class="bi bi-info-circle me-2"></i> Informasi Teknis
        </div>
        <div class="card-body">
            <?php echo display_row('Tgl. Penempatan', $kotak['tanggal_penempatan']); ?>
            <?php echo display_row('Koordinat (Lat, Lng)', '', false, true); ?>
            <?php echo display_row('Link Google Maps', $kotak['link_gmaps'], true); ?>
            <?php 
            if (!empty($kotak['keterangan'])) {
                echo "
                <div class='row mb-2 py-1'>
                    <div class='col-sm-4 text-muted small'>Keterangan</div>
                    <div class='col-sm-8'><strong>".nl2br(htmlspecialchars($kotak['keterangan']))."</strong></div>
                </div>
                ";
            }
            ?>
        </div>
    </div>

    <!-- Footer dengan timestamp -->
    <div class="text-center mt-4">
        <small class="text-muted">Data diperbarui: <?php echo date('d/m/Y H:i', strtotime($kotak['updated_at'] ?? 'now')); ?></small>
    </div>
</div>