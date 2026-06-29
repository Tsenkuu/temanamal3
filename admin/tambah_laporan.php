<?php
// Memuat file konfigurasi, yang seharusnya sudah memanggil session_start()
require_once '../includes/config.php';

// Pengecekan login admin (sangat direkomendasikan)
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Tambah Laporan Dokumen";
$errors = [];

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul_laporan = trim($_POST['judul_laporan']);
    $deskripsi = trim($_POST['deskripsi']);
    $uploader_id = $_SESSION['admin_id'] ?? null; // Ambil ID admin jika ada sesi

    // Validasi input
    if (empty($judul_laporan)) {
        $errors[] = "Judul laporan tidak boleh kosong.";
    }

    // Validasi dan proses upload file
    if (isset($_FILES['nama_file']) && $_FILES['nama_file']['error'] == 0) {
        $target_dir = "../assets/uploads/laporan/";
        // Buat nama file unik untuk menghindari tumpang tindih
        $nama_file = time() . '_' . basename($_FILES["nama_file"]["name"]);
        $target_file = $target_dir . $nama_file;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Cek ukuran file (misal, maks 5MB)
        if ($_FILES["nama_file"]["size"] > 5000000) {
            $errors[] = "Maaf, ukuran file terlalu besar (maks 5MB).";
        }

        // Izinkan format file tertentu
        $allowed_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Maaf, hanya format PDF, DOC, DOCX, XLS, XLSX yang diizinkan.";
        }

        // Jika tidak ada error, coba upload file
        if (empty($errors)) {
            if (!move_uploaded_file($_FILES["nama_file"]["tmp_name"], $target_file)) {
                $errors[] = "Maaf, terjadi error saat mengunggah file.";
            }
        }
    } else {
        $errors[] = "File laporan wajib diunggah.";
    }

    // Jika semua validasi berhasil, masukkan data ke database
    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO laporan (judul_laporan, deskripsi, nama_file, uploader_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $judul_laporan, $deskripsi, $nama_file, $uploader_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Laporan dokumen baru berhasil ditambahkan.";
            header("Location: kelola_laporan.php"); // Arahkan ke halaman kelola laporan
            exit;
        } else {
            $errors[] = "Gagal menyimpan laporan ke database: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Memuat header admin
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

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="tambah_laporan.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="judul_laporan" class="form-label">Judul Laporan</label>
                            <input type="text" class="form-control" id="judul_laporan" name="judul_laporan" required>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi Singkat (Opsional)</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="nama_file" class="form-label">Upload File Laporan</label>
                            <input class="form-control" type="file" id="nama_file" name="nama_file" required>
                            <small class="form-text text-muted">Format yang diizinkan: PDF, DOC, DOCX, XLS, XLSX. Ukuran
                                maks: 5MB.</small>
                        </div>
                        <hr>
                        <a href="kelola_laporan.php" class="btn btn-secondary me-2">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Laporan</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php 
// Memuat footer admin
require_once 'templates/footer_admin.php'; 
?>