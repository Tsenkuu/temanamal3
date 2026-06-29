<?php
// Pastikan file ini hanya bisa diakses melalui skrip, bukan langsung
if (empty($_GET['id'])) {
    die("Akses tidak sah.");
}

require_once '../includes/config.php';

// Ambil ID dengan aman
$id = (int)$_GET['id'];
if ($id <= 0) {
    echo '<div class="alert alert-danger">ID tidak valid.</div>';
    exit();
}

// Gunakan PREPARED STATEMENT untuk mencegah SQL Injection
$stmt = $mysqli->prepare("SELECT * FROM kotak_infak WHERE id = ?");
if (!$stmt) {
    // Tampilkan pesan error jika query gagal disiapkan
    error_log("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
    echo '<div class="alert alert-danger">Terjadi kesalahan pada server.</div>';
    exit();
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();

    // Fungsi helper untuk status badge (bisa ditaruh di config jika sering dipakai)
    function get_detail_status_badge($status) {
        if ($status == 'Aktif') {
            return '<span class="badge bg-success fs-6"><i class="bi bi-check-circle-fill me-1"></i> Aktif</span>';
        }
        return '<span class="badge bg-secondary fs-6"><i class="bi bi-x-circle-fill me-1"></i> Tidak Aktif</span>';
    }

    // Format output HTML untuk modal
    ?>
    <div class="list-group">
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <strong>Kode Kotak</strong>
            <span class="text-primary fw-bold"><?php echo htmlspecialchars($data['kode_kotak'] ?? '-'); ?></span>
        </div>
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <strong>Nama Lokasi</strong>
            <span><?php echo htmlspecialchars($data['nama_lokasi'] ?? '-'); ?></span>
        </div>
        <div class="list-group-item">
            <strong>Alamat Lokasi</strong>
            <p class="mb-0 text-muted"><?php echo !empty($data['alamat_lokasi']) ? nl2br(htmlspecialchars($data['alamat_lokasi'])) : '<em>Alamat tidak tersedia.</em>'; ?></p>
        </div>

        <?php if (!empty($data['alamat_lokasi'])):
            $encoded_address = urlencode($data['alamat_lokasi']);
        ?>
        <div class="list-group-item">
            <div class="ratio ratio-16x9 rounded overflow-hidden mt-1 shadow-sm">
                <iframe
                    src="https://maps.google.com/maps?q=<?php echo $encoded_address; ?>&output=embed"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
            <div class="mt-2 text-center">
                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $encoded_address; ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                    <i class="bi bi-geo-alt-fill me-1"></i> Buka di Google Maps
                </a>
            </div>
        </div>
        <?php endif; ?>

        <div class="list-group-item d-flex justify-content-between align-items-center">
            <strong>Nama PIC</strong>
            <span><?php echo htmlspecialchars($data['pic_nama'] ?? '-'); ?></span>
        </div>
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <strong>No. Telepon PIC</strong>
            <span><?php echo !empty($data['pic_telepon']) ? htmlspecialchars($data['pic_telepon']) : '<em>Tidak tersedia</em>'; ?></span>
        </div>
         <div class="list-group-item d-flex justify-content-between align-items-center">
            <strong>Status</strong>
            <?php echo get_detail_status_badge($data['status'] ?? 'Tidak Aktif'); ?>
        </div>
        <div class="list-group-item">
            <strong>Catatan</strong>
            <p class="mb-0 text-muted"><?php echo !empty($data['catatan']) ? nl2br(htmlspecialchars($data['catatan'])) : '<em>Tidak ada catatan.</em>'; ?></p>
        </div>
    </div>
    <?php
} else {
    echo '<div class="alert alert-warning">Data kotak infak tidak ditemukan.</div>';
}

$stmt->close();
$mysqli->close();
?>

