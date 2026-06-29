<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

function optimizeImage($source_path, $destination_path, $quality = 75) {
    $info = getimagesize($source_path);
    if ($info === false) return false;
    list($width, $height) = $info;
    $mime = $info['mime'];
    $target_width = 1200;
    if ($width <= $target_width) {
        return copy($source_path, $destination_path);
    }
    $target_height = ($height / $width) * $target_width;
    $thumb = imagecreatetruecolor($target_width, $target_height);
    $source = null;
    switch ($mime) {
        case 'image/jpeg': $source = imagecreatefromjpeg($source_path); break;
        case 'image/png':
            $source = imagecreatefrompng($source_path);
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            break;
        case 'image/gif': $source = imagecreatefromgif($source_path); break;
        case 'image/webp': $source = imagecreatefromwebp($source_path); break;
        default: return false;
    }
    if ($source === false) return false;
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $target_width, $target_height, $width, $height);
    $success = false;
    switch ($mime) {
        case 'image/jpeg': $success = imagejpeg($thumb, $destination_path, $quality); break;
        case 'image/png': $success = imagepng($thumb, $destination_path, 6); break;
        case 'image/gif': $success = imagegif($thumb, $destination_path); break;
        case 'image/webp': $success = imagewebp($thumb, $destination_path, $quality); break;
    }
    return $success;
}

function create_unique_slug($string, $mysqli) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    $slug = trim($slug, '-');
    if (empty($slug)) return 'berita-' . time();
    $original_slug = $slug;
    $counter = 1;
    while (true) {
        $stmt = $mysqli->prepare("SELECT id FROM berita WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $stmt->close();
            break;
        }
        $stmt->close();
        $slug = $original_slug . '-' . $counter++;
    }
    return $slug;
}

$page_title = "Tulis Berita Baru";
$penulis = $_SESSION['admin_nama_lengkap'] ?? 'Admin';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = trim($_POST['type']);
    $judul = trim($_POST['judul']);
    $teras_berita = trim($_POST['teras_berita']);
    $tubuh_berita = $_POST['tubuh_berita'];
    $sumber_gambar = trim($_POST['sumber_gambar']);
    $tags = trim($_POST['tags']);
    $nama_gambar = 'placeholder.png';

    if (empty($judul) || empty($teras_berita) || empty($tubuh_berita)) {
        $errors[] = "Judul, Teras, dan Tubuh Berita tidak boleh kosong.";
    }

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['gambar']['size'] > 5 * 1024 * 1024) {
            $errors[] = "Ukuran file mentah tidak boleh lebih dari 5MB.";
        }
        if (empty($errors)) {
            $target_dir = "../assets/uploads/berita/";
            $file_extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $nama_gambar_baru = time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $nama_gambar_baru;
            if (optimizeImage($_FILES['gambar']['tmp_name'], $target_file)) {
                $nama_gambar = $nama_gambar_baru;
            } else {
                $errors[] = "Gagal memproses gambar. Format tidak didukung.";
            }
        }
    }

    if (empty($errors)) {
        $slug = create_unique_slug($judul, $mysqli);
        $status = (isset($_POST['action']) && $_POST['action'] === 'pending') ? 'pending' : 'published';
        $penulis_asli = trim($_POST['penulis'] ?? $penulis);
        
        $stmt = $mysqli->prepare("INSERT INTO berita (type, judul, slug, teras_berita, tubuh_berita, gambar, sumber_gambar, penulis, editor, tags, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssss", $type, $judul, $slug, $teras_berita, $tubuh_berita, $nama_gambar, $sumber_gambar, $penulis_asli, $penulis, $tags, $status);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = ($status === 'pending') ? "Berita berhasil disimpan sebagai pending." : "Berita baru berhasil dipublikasikan.";
            header("Location: kelola_berita.php");
            exit();
        } else {
            $errors[] = "Gagal menyimpan berita: " . $stmt->error;
        }
        $stmt->close();
    }
}

require_once 'templates/header_admin.php';
?>
<!-- TinyMCE Script -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.6.0/tinymce.min.js" referrerpolicy="origin"></script>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="text-2xl font-bold text-dark-text"><?php echo $page_title; ?></h1>
            <p class="text-sm text-gray-500">Tulis, format, dan publikasikan artikel berita baru.</p>
        </div>
    </div>

    <form action="tambah_berita.php" method="POST" enctype="multipart/form-data">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kolom Utama (Konten) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="content-card">
                    <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <?php foreach ($errors as $error): ?><p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="space-y-4">
                        <div>
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
                        <div>
                            <label for="judul" class="form-label">Judul (Headline)</label>
                            <input type="text" class="form-input" id="judul" name="judul" required
                                value="<?php echo isset($_POST['judul']) ? htmlspecialchars($_POST['judul']) : ''; ?>">
                        </div>
                        <div>
                            <label for="teras_berita" class="form-label">Teras Berita (Lead)</label>
                            <textarea class="form-textarea" id="teras_berita" name="teras_berita" rows="3" required
                                placeholder="Paragraf ringkas pembuka berita..."><?php echo isset($_POST['teras_berita']) ? htmlspecialchars($_POST['teras_berita']) : ''; ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="content-card">
                    <label for="tubuh_berita" class="form-label">Tubuh Berita (Body)</label>
                    <textarea class="form-textarea" id="tubuh_berita" name="tubuh_berita"
                        rows="15"><?php echo isset($_POST['tubuh_berita']) ? htmlspecialchars($_POST['tubuh_berita']) : ''; ?></textarea>
                </div>
            </div>

            <!-- Kolom Samping (Metadata) -->
            <div class="lg:col-span-1 space-y-6">
                <div class="content-card">
                    <h3 class="card-title mb-4">Pengaturan Gambar</h3>
                    <div>
                        <label for="gambar" class="form-label">Gambar Utama</label>
                        <input class="form-input-file" type="file" id="gambar" name="gambar">
                        <p class="text-xs text-gray-500 mt-1">Maks. 5MB. Akan dioptimalkan.</p>
                    </div>
                    <div class="mt-4">
                        <label for="sumber_gambar" class="form-label">Sumber Gambar</label>
                        <input type="text" class="form-input" id="sumber_gambar" name="sumber_gambar"
                            placeholder="Contoh: Dokumentasi Lazismu"
                            value="<?php echo isset($_POST['sumber_gambar']) ? htmlspecialchars($_POST['sumber_gambar']) : ''; ?>">
                    </div>
                </div>
                <div class="content-card">
                    <h3 class="card-title mb-4">Informasi Tambahan</h3>
                    <div class="mb-4">
                        <label for="penulis" class="form-label">Penulis Asli (Author)</label>
                        <input type="text" class="form-input" id="penulis" name="penulis"
                            placeholder="Contoh: Budi Santoso"
                            value="<?php echo isset($_POST['penulis']) ? htmlspecialchars($_POST['penulis']) : ''; ?>">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika ingin menggunakan nama Anda sebagai penulis.</p>
                    </div>
                    <div>
                        <label for="tags" class="form-label">Tags</label>
                        <input type="text" class="form-input" id="tags" name="tags"
                            placeholder="pendidikan, beasiswa, sosial"
                            value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>">
                        <p class="text-xs text-gray-500 mt-1">Pisahkan dengan koma.</p>
                    </div>
                </div>
                <!-- [DIPINDAHKAN] Kartu Publikasi sekarang di bawah -->
                <div class="content-card">
                    <h3 class="card-title mb-4">Publikasi</h3>
                    <div class="flex flex-col gap-2">
                        <button type="submit" name="action" value="published" class="btn-primary w-full justify-center">Publikasikan Sekarang</button>
                        <button type="submit" name="action" value="pending" class="btn-secondary w-full justify-center text-yellow-700 bg-yellow-100 hover:bg-yellow-200 border-yellow-200">Simpan sebagai Pending</button>
                        <a href="kelola_berita.php" class="btn-secondary w-full justify-center mt-2">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>

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

<?php require_once 'templates/footer_admin.php'; ?>