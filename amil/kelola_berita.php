<?php
/*
|--------------------------------------------------------------------------
| File: amil/kelola_berita.php (DIROMBAK)
|--------------------------------------------------------------------------
|
| Halaman pengelolaan berita dengan UI baru yang mobile-first.
| Mengganti tabel dengan layout kartu (cards) yang lebih modern.
|
*/
require_once '../includes/config.php';

if (!isset($_SESSION['amil_id'])) {
    header('Location: ../login.php');
    exit();
}

$nama_amil_login = $_SESSION['amil_nama_lengkap'] ?? 'Amil';
$page_title = "Berita Saya";

// Query (tetap sama)
$stmt = $mysqli->prepare("SELECT id, judul, gambar, status, type, created_at FROM berita WHERE penulis = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $nama_amil_login);
$stmt->execute();
$result_berita = $stmt->get_result();
$stmt->close();

require_once 'templates/header_amil.php';
?>

<!-- Tombol Aksi Utama -->
<div class="d-flex justify-content-end mb-4">
    <a href="tambah_berita.php" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm">
        <i class="bi bi-pencil-square me-1"></i> Tulis Berita Baru
    </a>
</div>

<?php
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['success_message']);
}
?>

<!-- Daftar Berita dalam Bentuk Kartu -->
<div class="berita-list">
    <?php if ($result_berita->num_rows > 0): ?>
    <?php while($berita = $result_berita->fetch_assoc()): 
            $status_badge_class = 'bg-secondary-subtle text-secondary-emphasis';
            $status_icon = 'bi-hourglass-split';
            $status_text = 'Pending';
            if ($berita['status'] == 'published') {
                $status_badge_class = 'bg-success-subtle text-success-emphasis';
                $status_icon = 'bi-check-circle-fill';
                $status_text = 'Diterbitkan';
            } elseif ($berita['status'] == 'rejected') {
                $status_badge_class = 'bg-danger-subtle text-danger-emphasis';
                $status_icon = 'bi-x-circle-fill';
                $status_text = 'Ditolak';
            }
        ?>
    <div class="card mb-3">
        <div class="row g-0">
            <div class="col-md-3 col-4">
                <img src="../assets/uploads/berita/<?php echo htmlspecialchars($berita['gambar']); ?>"
                    alt="<?php echo htmlspecialchars($berita['judul']); ?>" class="img-fluid rounded-start h-100"
                    style="object-fit: cover;">
            </div>
            <div class="col-md-9 col-8">
                <div class="card-body d-flex flex-column h-100">
                    <h5 class="card-title fw-bold mb-1"><?php echo htmlspecialchars($berita['judul']); ?></h5>
                    <p class="card-text small text-muted mb-2">
                        <i class="bi bi-calendar3 me-1"></i>
                        <?php echo date('d M Y', strtotime($berita['created_at'])); ?>
                    </p>
                    <p class="card-text mt-auto d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-2">
                        <span class="badge <?php echo $status_badge_class; ?> rounded-pill px-2 py-1">
                            <i class="bi <?php echo $status_icon; ?> me-1"></i> <?php echo $status_text; ?>
                        </span>
                        <span
                            class="badge <?php echo $berita['type'] == 'berita' ? 'bg-primary-subtle text-primary-emphasis' : 'bg-info-subtle text-info-emphasis'; ?> rounded-pill px-2 py-1">
                            <i class="bi bi-tag me-1"></i> <?php echo ucfirst($berita['type']); ?>
                        </span>
                    </div>
                    <span class="actions">
                        <a href="edit_berita.php?id=<?php echo $berita['id']; ?>" class="btn btn-sm btn-secondary"
                            title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="hapus_berita.php" method="POST" class="d-inline"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus berita ini?');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo $berita['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
    <?php else: ?>
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-newspaper display-4 d-block mb-2"></i>
            Anda belum menulis berita apapun.
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer_amil.php'; ?>
