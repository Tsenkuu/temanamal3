<?php
require_once '../includes/config.php';

// Pengecekan login admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Edit Pengaturan";
$id_pengaturan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_pengaturan === 0) {
    header("Location: pengaturan.php");
    exit;
}

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nilai_pengaturan = $_POST['nilai_pengaturan'];

    // Jika pengaturan berupa angka, bersihkan dari karakter non-numerik
    if (is_numeric(preg_replace('/[^\d]/', '', $nilai_pengaturan))) {
        $nilai_pengaturan = preg_replace('/[^\d]/', '', $nilai_pengaturan);
    }
    
    $stmt = $mysqli->prepare("UPDATE pengaturan SET nilai_pengaturan = ? WHERE id = ?");
    $stmt->bind_param("si", $nilai_pengaturan, $id_pengaturan);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Pengaturan berhasil diperbarui.";
        header("Location: pengaturan.php");
        exit();
    } else {
        $error_message = "Gagal memperbarui pengaturan.";
    }
    $stmt->close();
}

// Ambil nilai pengaturan saat ini dari database
$stmt_select = $mysqli->prepare("SELECT nama_pengaturan, nilai_pengaturan FROM pengaturan WHERE id = ?");
$stmt_select->bind_param("i", $id_pengaturan);
$stmt_select->execute();
$result = $stmt_select->get_result();
$pengaturan = $result->fetch_assoc();
$stmt_select->close();

if (!$pengaturan) {
    header("Location: pengaturan.php");
    exit;
}

require_once 'templates/header_admin.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_admin.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
            </div>

            <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="edit_pengaturan.php?id=<?php echo $id_pengaturan; ?>" method="POST">
                        <div class="mb-3">
                            <label for="nilai_pengaturan" class="form-label">
                                <?php echo ucwords(str_replace('_', ' ', $pengaturan['nama_pengaturan'])); ?>
                            </label>
                            <input type="text" class="form-control" id="nilai_pengaturan" name="nilai_pengaturan" value="<?php 
                                if (is_numeric($pengaturan['nilai_pengaturan'])) {
                                    echo number_format($pengaturan['nilai_pengaturan'], 0, ',', '.');
                                } else {
                                    echo htmlspecialchars($pengaturan['nilai_pengaturan']);
                                }
                            ?>">
                            <?php if (is_numeric($pengaturan['nilai_pengaturan'])): ?>
                            <small class="form-text text-muted">Masukkan angka saja. Tanda titik akan ditambahkan
                                otomatis.</small>
                            <?php endif; ?>
                        </div>
                        <a href="pengaturan.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php if (is_numeric($pengaturan['nilai_pengaturan'])): ?>
<script>
// Script untuk memformat input angka dengan titik ribuan
document.getElementById('nilai_pengaturan').addEventListener('keyup', function(e) {
    let value = e.target.value.replace(/[^\d]/g, '');
    e.target.value = new Intl.NumberFormat('id-ID').format(value);
});
</script>
<?php endif; ?>

<?php require_once 'templates/footer_admin.php'; ?>