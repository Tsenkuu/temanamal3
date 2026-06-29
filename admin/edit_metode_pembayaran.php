<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Edit Metode Pembayaran";
$id_metode = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_metode === 0) {
    header("Location: kelola_pembayaran.php");
    exit;
}

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_metode = trim($_POST['nama_metode']);
    $tipe = $_POST['tipe'];
    $kategori = $_POST['kategori'];
    $detail_1 = trim($_POST['detail_1']);
    $detail_2 = trim($_POST['detail_2']);
    $status = $_POST['status'];
    $gambar_lama = $_POST['gambar_lama'];
    $gambar_baru = $gambar_lama;

    if ($tipe == 'QRIS' && isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../assets/images/qris/";
        $gambar_baru = time() . '_' . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $gambar_baru;
        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            if ($gambar_lama && file_exists($target_dir . $gambar_lama)) {
                unlink($target_dir . $gambar_lama);
            }
        } else {
            $gambar_baru = $gambar_lama;
        }
    }

    $stmt = $mysqli->prepare("UPDATE metode_pembayaran SET nama_metode=?, tipe=?, kategori=?, detail_1=?, detail_2=?, gambar=?, status=? WHERE id=?");
    $stmt->bind_param("sssssssi", $nama_metode, $tipe, $kategori, $detail_1, $detail_2, $gambar_baru, $status, $id_metode);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Metode pembayaran berhasil diperbarui.";
        header("Location: kelola_pembayaran.php");
        exit();
    }
    $stmt->close();
}

// Ambil data metode saat ini
$stmt_select = $mysqli->prepare("SELECT * FROM metode_pembayaran WHERE id = ?");
$stmt_select->bind_param("i", $id_metode);
$stmt_select->execute();
$metode = $stmt_select->get_result()->fetch_assoc();
$stmt_select->close();

if (!$metode) {
    header("Location: kelola_pembayaran.php");
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
                    <!-- PERBAIKAN: Menambahkan enctype="multipart/form-data" -->
                    <form action="edit_metode_pembayaran.php?id=<?php echo $id_metode; ?>" method="POST"
                        enctype="multipart/form-data">
                        <input type="hidden" name="gambar_lama"
                            value="<?php echo htmlspecialchars($metode['gambar']); ?>">
                        <div class="mb-3">
                            <label for="nama_metode" class="form-label">Nama Metode</label>
                            <input type="text" class="form-control" id="nama_metode" name="nama_metode"
                                value="<?php echo htmlspecialchars($metode['nama_metode']); ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="tipe" class="form-label">Tipe</label>
                                <select class="form-select" id="tipe" name="tipe" required>
                                    <option value="Transfer Bank"
                                        <?php if($metode['tipe'] == 'Transfer Bank') echo 'selected'; ?>>Transfer Bank
                                    </option>
                                    <option value="QRIS" <?php if($metode['tipe'] == 'QRIS') echo 'selected'; ?>>QRIS
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="kategori" class="form-label">Kategori</label>
                                <select class="form-select" id="kategori" name="kategori" required>
                                    <option value="Umum" <?php if($metode['kategori'] == 'Umum') echo 'selected'; ?>>
                                        Umum</option>
                                    <option value="Zakat" <?php if($metode['kategori'] == 'Zakat') echo 'selected'; ?>>
                                        Zakat</option>
                                    <option value="Infak" <?php if($metode['kategori'] == 'Infak') echo 'selected'; ?>>
                                        Infak</option>
                                    <option value="Qurban"
                                        <?php if($metode['kategori'] == 'Qurban') echo 'selected'; ?>>Qurban</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Aktif" <?php if($metode['status'] == 'Aktif') echo 'selected'; ?>>
                                        Aktif</option>
                                    <option value="Tidak Aktif"
                                        <?php if($metode['status'] == 'Tidak Aktif') echo 'selected'; ?>>Tidak Aktif
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div id="kolom-bank"
                            style="<?php if($metode['tipe'] != 'Transfer Bank') echo 'display: none;'; ?>">
                            <div class="mb-3"><label for="detail_1" class="form-label">Nomor Rekening</label><input
                                    type="text" class="form-control" id="detail_1" name="detail_1"
                                    value="<?php echo htmlspecialchars($metode['detail_1']); ?>"></div>
                            <div class="mb-3"><label for="detail_2" class="form-label">Atas Nama</label><input
                                    type="text" class="form-control" id="detail_2" name="detail_2"
                                    value="<?php echo htmlspecialchars($metode['detail_2']); ?>"></div>
                        </div>
                        <div id="kolom-qris" style="<?php if($metode['tipe'] != 'QRIS') echo 'display: none;'; ?>">
                            <div class="mb-3"><label for="gambar" class="form-label">Ganti Gambar QRIS
                                    (Opsional)</label><input class="form-control" type="file" id="gambar" name="gambar"
                                    accept="image/*"></div>
                        </div>
                        <hr>
                        <a href="kelola_pembayaran.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<script>
document.getElementById('tipe').addEventListener('change', function() {
    if (this.value === 'QRIS') {
        document.getElementById('kolom-qris').style.display = 'block';
        document.getElementById('kolom-bank').style.display = 'none';
        document.getElementById('detail_1').value = '';
        document.getElementById('detail_2').value = '';
    } else {
        document.getElementById('kolom-qris').style.display = 'none';
        document.getElementById('kolom-bank').style.display = 'block';
    }
});
</script>
<?php require_once 'templates/footer_admin.php'; ?>