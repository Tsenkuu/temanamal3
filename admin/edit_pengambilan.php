<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
$id_riwayat = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_riwayat === 0) {
    header("Location: riwayat_pengambilan.php");
    exit;
}

$page_title = "Edit Riwayat Pengambilan";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jumlah_terkumpul = preg_replace('/[^\d]/', '', $_POST['jumlah_terkumpul']);
    $catatan = trim($_POST['catatan']);

    $stmt = $mysqli->prepare("UPDATE riwayat_pengambilan SET jumlah_terkumpul = ?, catatan = ? WHERE id = ?");
    $stmt->bind_param("dsi", $jumlah_terkumpul, $catatan, $id_riwayat);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Riwayat pengambilan berhasil diperbarui.";
        header("Location: riwayat_pengambilan.php");
        exit();
    }
    $stmt->close();
}

$stmt_select = $mysqli->prepare("SELECT r.jumlah_terkumpul, r.catatan, k.nama_lokasi FROM riwayat_pengambilan r JOIN kotak_infak k ON r.id_kotak_infak = k.id WHERE r.id = ?");
$stmt_select->bind_param("i", $id_riwayat);
$stmt_select->execute();
$riwayat = $stmt_select->get_result()->fetch_assoc();
$stmt_select->close();

if (!$riwayat) {
    header("Location: riwayat_pengambilan.php");
    exit;
}

require_once 'templates/header_admin.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_admin.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h1 class="h2 pt-3 pb-2 mb-3 border-bottom"><?php echo $page_title; ?></h1>
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="edit_pengambilan.php?id=<?php echo $id_riwayat; ?>" method="POST">
                        <div class="mb-3"><label class="form-label">Lokasi</label><input type="text"
                                class="form-control" value="<?php echo htmlspecialchars($riwayat['nama_lokasi']); ?>"
                                readonly></div>
                        <div class="mb-3"><label for="jumlah_terkumpul" class="form-label">Jumlah Terkumpul
                                (Rp)</label><input type="text" class="form-control" id="jumlah_terkumpul"
                                name="jumlah_terkumpul"
                                value="<?php echo number_format($riwayat['jumlah_terkumpul'], 0, ',', '.'); ?>"
                                required></div>
                        <div class="mb-3"><label for="catatan" class="form-label">Catatan</label><textarea
                                class="form-control" id="catatan" name="catatan"
                                rows="3"><?php echo htmlspecialchars($riwayat['catatan']); ?></textarea></div>
                        <hr>
                        <a href="riwayat_pengambilan.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.getElementById('jumlah_terkumpul').addEventListener('keyup', function(e) {
    let value = e.target.value.replace(/[^\d]/g, '');
    e.target.value = new Intl.NumberFormat('id-ID').format(value);
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>