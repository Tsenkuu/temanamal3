<?php
require_once '../includes/config.php';

// Pengecekan login amil
if (!isset($_SESSION['amil_id'])) {
    header('Location: ../login.php');
    exit();
}

/**
 * Membuat slug yang unik dan ramah SEO dari sebuah string.
 */
function create_unique_slug($string, $mysqli) {
    // 1. Bersihkan dan format string dasar
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', "-", $slug);
    $slug = trim($slug, '-');

    if (empty($slug)) {
        return 'berita-' . time();
    }

    // 2. Periksa keunikan dan tambahkan angka jika perlu
    $original_slug = $slug;
    $counter = 1;
    while (true) {
        $stmt = $mysqli->prepare("SELECT id FROM berita WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $stmt->store_result();
        $is_unique = ($stmt->num_rows === 0);
        $stmt->close();

        if ($is_unique) {
            break; // Slug sudah unik
        }
        $slug = $original_slug . '-' . $counter++;
    }
    return $slug;
}

$page_title = "Tulis Berita Baru";
$penulis = $_SESSION['amil_nama_lengkap'] ?? 'Amil';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil semua data dari form
    $type = trim($_POST['type']);
    $judul = trim($_POST['judul']);
    $teras_berita = trim($_POST['teras_berita']);
    $tubuh_berita = $_POST['tubuh_berita']; // Ambil HTML dari CKEditor
    $sumber_gambar = trim($_POST['sumber_gambar']);
    $tags = trim($_POST['tags']);
    $nama_gambar = 'placeholder.png'; // Default

    if (empty($judul) || empty($teras_berita) || empty($tubuh_berita)) {
        $errors[] = "Judul, Teras, dan Tubuh Berita tidak boleh kosong.";
    }

    // Logika Upload Gambar
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_info = $_FILES['gambar'];
        $file_tmp_name = $file_info['tmp_name'];
        $file_size = $file_info['size'];
        
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_mime_type = mime_content_type($file_tmp_name);
        
        if (!in_array($file_mime_type, $allowed_mime_types)) {
            $errors[] = "Format file tidak diizinkan. Hanya JPG, PNG, GIF, atau WEBP.";
        }
        if ($file_size > 2 * 1024 * 1024) {
            $errors[] = "Ukuran file gambar tidak boleh lebih dari 2MB.";
        }

        if (empty($errors)) {
            $target_dir = "../assets/uploads/berita/";
            $file_extension = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
            $safe_basename = preg_replace("/[^A-Za-z0-9_\-]/", '', pathinfo($file_info['name'], PATHINFO_FILENAME));
            $nama_gambar_baru = time() . '_' . $safe_basename . '.' . $file_extension;
            $target_file = $target_dir . $nama_gambar_baru;

            if (move_uploaded_file($file_tmp_name, $target_file)) {
                $nama_gambar = $nama_gambar_baru;
            } else {
                $errors[] = "Gagal memindahkan file yang diunggah.";
            }
        }
    } elseif (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = "Terjadi kesalahan saat mengunggah file. Kode Error: " . $_FILES['gambar']['error'];
    }

    // Jika tidak ada error, buat slug dan simpan
    if (empty($errors)) {
        $slug = create_unique_slug($judul, $mysqli);
        $status = 'pending';
        $penulis_asli = trim($_POST['penulis'] ?? $penulis);
        
        $stmt = $mysqli->prepare("INSERT INTO berita (type, judul, slug, teras_berita, tubuh_berita, gambar, sumber_gambar, penulis, editor, tags, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssss", $type, $judul, $slug, $teras_berita, $tubuh_berita, $nama_gambar, $sumber_gambar, $penulis_asli, $penulis, $tags, $status);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Berita berhasil dikirim dan menunggu persetujuan Admin.";
            header("Location: kelola_berita.php");
            exit();
        } else {
            $errors[] = "Gagal menyimpan berita ke database: " . $stmt->error;
        }
        $stmt->close();
    }
}

require_once 'templates/header_amil.php';
?>
<!-- TinyMCE Script -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.6.0/tinymce.min.js" referrerpolicy="origin"></script>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_amil.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h1 class="h2 pt-3 pb-2 mb-3 border-bottom"><?php echo $page_title; ?></h1>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?><p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="tambah_berita.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="type" class="form-label">Tipe Konten</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="berita"
                                    <?php echo (isset($_POST['type']) && $_POST['type'] == 'berita') ? 'selected' : ''; ?>>
                                    Berita</option>
                                <option value="opini"
                                    <?php echo (isset($_POST['type']) && $_POST['type'] == 'opini') ? 'selected' : ''; ?>>
                                    Opini</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul (Headline)</label>
                            <input type="text" class="form-control" id="judul" name="judul" required
                                value="<?php echo isset($_POST['judul']) ? htmlspecialchars($_POST['judul']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="teras_berita" class="form-label">Teras Berita (Lead)</label>
                            <textarea class="form-control" id="teras_berita" name="teras_berita" rows="3" required
                                placeholder="Paragraf pertama yang memuat informasi paling penting (5W+1H)."><?php echo isset($_POST['teras_berita']) ? htmlspecialchars($_POST['teras_berita']) : ''; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="tubuh_berita" class="form-label">Tubuh Berita (Body)</label>
                            <textarea class="form-control" id="tubuh_berita" name="tubuh_berita"
                                rows="12"><?php echo isset($_POST['tubuh_berita']) ? htmlspecialchars($_POST['tubuh_berita']) : ''; ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="gambar" class="form-label">Gambar Utama</label>
                                <input class="form-control" type="file" id="gambar" name="gambar">
                                <small class="text-muted">Maks. 2MB. Format: JPG, PNG, GIF, WEBP.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sumber_gambar" class="form-label">Sumber Gambar</label>
                                <input type="text" class="form-control" id="sumber_gambar" name="sumber_gambar"
                                    placeholder="Contoh: Dokumentasi Pribadi"
                                    value="<?php echo isset($_POST['sumber_gambar']) ? htmlspecialchars($_POST['sumber_gambar']) : ''; ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="penulis" class="form-label">Penulis Asli (Author)</label>
                            <input type="text" class="form-control" id="penulis" name="penulis"
                                placeholder="Contoh: Budi Santoso"
                                value="<?php echo isset($_POST['penulis']) ? htmlspecialchars($_POST['penulis']) : ''; ?>">
                            <small class="text-muted">Kosongkan jika ingin menggunakan nama Anda sebagai penulis.</small>
                        </div>
                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" class="form-control" id="tags" name="tags"
                                placeholder="Pisahkan dengan koma, contoh: sosial, kemanusiaan, tulungagung"
                                value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>">
                            <small class="text-muted">Pisahkan setiap tag dengan koma (,)</small>
                        </div>
                        <hr>
                        <a href="kelola_berita.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Kirim untuk Ditinjau</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
tinymce.init({
    selector: '#tubuh_berita',
    height: 400,
    plugins: 'image link lists media table code help wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | removeformat | help',
    
    /* Konfigurasi Upload Gambar */
    images_upload_url: 'upload_tinymce.php',
    automatic_uploads: true,
    file_picker_types: 'image',
    
    /* Agar link bisa diklik/diedit dengan mudah */
    link_context_toolbar: true,
    
    /* Memungkinkan resize gambar di editor */
    image_dimensions: true
});
</script>

<?php require_once 'templates/footer_amil.php'; ?>