<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Tambah Metode Pembayaran";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_metode = trim($_POST['nama_metode']);
    $tipe = $_POST['tipe'];
    $kategori = $_POST['kategori'];
    $detail_1 = trim($_POST['detail_1']);
    $detail_2 = trim($_POST['detail_2']);
    $status = $_POST['status'];
    $gambar = null;

    // Proses upload gambar jika tipenya QRIS
    if ($tipe == 'QRIS' && isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../assets/images/qris/";
        
        // Cek dan buat direktori jika belum ada
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        // Cek apakah direktori bisa ditulisi
        if (is_writable($target_dir)) {
            $gambar = time() . '_' . basename($_FILES["gambar"]["name"]);
            $target_file = $target_dir . $gambar;
            
            if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                $_SESSION['error_message'] = "Gagal mengunggah file gambar.";
                $gambar = null; // Gagal upload
            }
        } else {
             $_SESSION['error_message'] = "Direktori upload tidak bisa ditulisi. Cek izin folder.";
        }
    }

    if (!isset($_SESSION['error_message'])) {
        $stmt = $mysqli->prepare("INSERT INTO metode_pembayaran (nama_metode, tipe, kategori, detail_1, detail_2, gambar, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $nama_metode, $tipe, $kategori, $detail_1, $detail_2, $gambar, $status);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Metode pembayaran baru berhasil ditambahkan.";
            header("Location: kelola_pembayaran.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Gagal menambahkan metode pembayaran.";
        }
        $stmt->close();
    }
}

require_once 'templates/header_admin.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_admin.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h1 class="h2 pt-3 pb-2 mb-3 border-bottom"><?php echo $page_title; ?></h1>

            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- PERBAIKAN: Menambahkan enctype="multipart/form-data" -->
                    <form action="tambah_metode_pembayaran.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="nama_metode" class="form-label">Nama Metode</label>
                            <input type="text" class="form-control" id="nama_metode" name="nama_metode"
                                placeholder="Contoh: BSI - Rekening Qurban" required>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="tipe" class="form-label">Tipe</label>
                                <select class="form-select" id="tipe" name="tipe" required>
                                    <option value="Transfer Bank" selected>Transfer Bank</option>
                                    <option value="QRIS">QRIS</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="kategori" class="form-label">Kategori</label>
                                <input class="form-control" list="listKategori" id="kategori" name="kategori" placeholder="Pilih atau ketik kategori..." required>
                                <datalist id="listKategori">
                                    <option value="Zakat">
                                    <option value="Infak">
                                    <option value="Kemanusiaan">
                                    <option value="Qurban">
                                    <option value="Umum">
                                </datalist>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Aktif" selected>Aktif</option>
                                    <option value="Tidak Aktif">Tidak Aktif</option>
                                </select>
                            </div>
                        </div>

                        <!-- Kolom untuk Transfer Bank -->
                        <div id="kolom-bank">
                            <div class="mb-3">
                                <label for="detail_1" class="form-label">Nomor Rekening</label>
                                <input type="text" class="form-control" id="detail_1" name="detail_1">
                            </div>
                            <div class="mb-3">
                                <label for="detail_2" class="form-label">Atas Nama</label>
                                <input type="text" class="form-control" id="detail_2" name="detail_2">
                            </div>
                        </div>

                        <!-- Kolom untuk QRIS -->
                        <div id="kolom-qris" style="display: none;">
                            <div class="mb-3">
                                <label for="gambar" class="form-label">Upload Gambar QRIS</label>
                                <input class="form-control" type="file" id="gambar" name="gambar" accept="image/*">
                            </div>
                        </div>

                        <hr>
                        <a href="kelola_pembayaran.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Metode</button>
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
