<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

function optimizeImage($source_path, $destination_path, $quality = 75) {
    $info = getimagesize($source_path);
    if ($info === false) return false;
    list($width, $height, $type) = $info;
    $target_width = 1200;
    $target_height = ($height / $width) * $target_width;
    $thumb = imagecreatetruecolor($target_width, $target_height);
    $source = null;
    switch ($type) {
        case IMAGETYPE_JPEG: $source = imagecreatefromjpeg($source_path); break;
        case IMAGETYPE_PNG: $source = imagecreatefrompng($source_path); imagealphablending($thumb, false); imagesavealpha($thumb, true); break;
        case IMAGETYPE_GIF: $source = imagecreatefromgif($source_path); break;
        case IMAGETYPE_WEBP: $source = imagecreatefromwebp($source_path); break;
        default: return false;
    }
    if ($source === false) return false;
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $target_width, $target_height, $width, $height);
    $success = false;
    switch ($type) {
        case IMAGETYPE_JPEG: $success = imagejpeg($thumb, $destination_path, $quality); break;
        case IMAGETYPE_PNG: $success = imagepng($thumb, $destination_path, 6); break;
        case IMAGETYPE_GIF: $success = imagegif($thumb, $destination_path); break;
        case IMAGETYPE_WEBP: $success = imagewebp($thumb, $destination_path, $quality); break;
    }
    return $success;
}

$id_berita = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_berita === 0) {
    header("Location: kelola_berita.php");
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = trim($_POST['type']);
    $judul = trim($_POST['judul']);
    $teras_berita = trim($_POST['teras_berita']);
    $tubuh_berita = $_POST['tubuh_berita']; // CKEditor sends HTML
    $sumber_gambar = trim($_POST['sumber_gambar']);
    $tags = trim($_POST['tags']);
    $penulis = trim($_POST['penulis']);
    $status = trim($_POST['status']);
    $gambar_lama = $_POST['gambar_lama'];
    $gambar_baru = $gambar_lama;

    if (empty($judul) || empty($teras_berita) || empty($tubuh_berita)) {
        $errors[] = "Judul, Teras, dan Tubuh Berita tidak boleh kosong.";
    }

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['gambar']['tmp_name'];
        if ($_FILES['gambar']['size'] > 5 * 1024 * 1024) {
            $errors[] = "Ukuran file mentah tidak boleh lebih dari 5MB.";
        }
        if (empty($errors)) {
            $target_dir = "../assets/uploads/berita/";
            $file_extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $gambar_baru = time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $gambar_baru;
            if (optimizeImage($file_tmp_name, $target_file)) {
                if ($gambar_lama && $gambar_lama != 'placeholder.png' && file_exists($target_dir . $gambar_lama)) {
                    @unlink($target_dir . $gambar_lama);
                }
            } else {
                $errors[] = "Gagal memproses gambar. Format tidak didukung.";
                $gambar_baru = $gambar_lama;
            }
        }
    }

    if (empty($errors)) {
        $editor = $_SESSION['admin_nama_lengkap'] ?? 'Admin';
        $stmt = $mysqli->prepare("UPDATE berita SET type=?, judul=?, teras_berita=?, tubuh_berita=?, gambar=?, sumber_gambar=?, penulis=?, editor=?, tags=?, status=? WHERE id=?");
        $stmt->bind_param("ssssssssssi", $type, $judul, $teras_berita, $tubuh_berita, $gambar_baru, $sumber_gambar, $penulis, $editor, $tags, $status, $id_berita);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Berita berhasil diperbarui.";
            header("Location: kelola_berita.php");
            exit;
        } else {
            $errors[] = "Gagal memperbarui data di database: " . $stmt->error;
        }
        $stmt->close();
    }
}

$stmt_current = $mysqli->prepare("SELECT * FROM berita WHERE id = ?");
$stmt_current->bind_param("i", $id_berita);
$stmt_current->execute();
$result_current = $stmt_current->get_result();
$berita = $result_current->fetch_assoc();
$stmt_current->close();

if (!$berita) {
    header("Location: kelola_berita.php");
    exit;
}

$page_title = "Edit Berita";
require_once 'templates/header_admin.php';
?>

<!-- TinyMCE Script -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.6.0/tinymce.min.js" referrerpolicy="origin"></script>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-bold text-dark-text"><?php echo $page_title; ?></h1>
        <a href="kelola_berita.php" class="btn-secondary">
            <i class="bi bi-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <?php foreach ($errors as $error) echo "<p class='mb-0'>".htmlspecialchars($error)."</p>"; ?>
    </div>
    <?php endif; ?>

    <div class="content-card">
        <form action="edit_berita.php?id=<?php echo $id_berita; ?>" method="POST" enctype="multipart/form-data"
            class="space-y-6">
            <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($berita['gambar']); ?>">

            <div>
                <label for="type" class="form-label">Tipe Konten</label>
                <select class="form-select" id="type" name="type" required>
                    <option value="berita" <?php if($berita['type'] == 'berita') echo 'selected'; ?>>Berita</option>
                    <option value="opini" <?php if($berita['type'] == 'opini') echo 'selected'; ?>>Opini</option>
                </select>
            </div>

            <div>
                <label for="judul" class="form-label">Judul (Headline)</label>
                <input type="text" class="form-input" id="judul" name="judul"
                    value="<?php echo htmlspecialchars($berita['judul']); ?>" required>
            </div>

            <div>
                <label for="teras_berita" class="form-label">Teras Berita (Lead)</label>
                <textarea class="form-textarea" id="teras_berita" name="teras_berita" rows="3"
                    required><?php echo htmlspecialchars($berita['teras_berita']); ?></textarea>
            </div>

            <div>
                <label for="tubuh_berita" class="form-label">Tubuh Berita (Body)</label>
                <textarea class="form-textarea" id="tubuh_berita" name="tubuh_berita"
                    rows="12"><?php echo htmlspecialchars($berita['tubuh_berita']); ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="gambar" class="form-label">Ganti Gambar Utama</label>
                    <input class="form-input-file" type="file" id="gambar" name="gambar">
                    <p class="text-xs text-gray-500 mt-1">Maks. 5MB. Akan dioptimalkan otomatis.</p>
                    <?php if (!empty($berita['gambar']) && $berita['gambar'] != 'placeholder.png'): ?>
                    <img src="../assets/uploads/berita/<?php echo htmlspecialchars($berita['gambar']); ?>"
                        alt="Gambar saat ini" class="mt-2 rounded-lg w-40 object-cover">
                    <?php endif; ?>
                </div>
                <div>
                    <label for="sumber_gambar" class="form-label">Sumber Gambar</label>
                    <input type="text" class="form-input" id="sumber_gambar" name="sumber_gambar"
                        value="<?php echo htmlspecialchars($berita['sumber_gambar']); ?>"
                        placeholder="Contoh: Dokumentasi Lazismu">
                </div>
            </div>

            <div>
                <label for="tags" class="form-label">Tags</label>
                <input type="text" class="form-input" id="tags" name="tags"
                    value="<?php echo htmlspecialchars($berita['tags']); ?>" placeholder="pendidikan, beasiswa, sosial">
                <p class="text-xs text-gray-500 mt-1">Pisahkan setiap tag dengan koma (,)</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="penulis" class="form-label">Penulis</label>
                    <input type="text" class="form-input" id="penulis" name="penulis"
                        value="<?php echo htmlspecialchars($berita['penulis']); ?>">
                </div>
                <div>
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="published" <?php if($berita['status'] == 'published') echo 'selected'; ?>>
                            Published</option>
                        <option value="pending" <?php if($berita['status'] == 'pending') echo 'selected'; ?>>Pending
                        </option>
                        <option value="rejected" <?php if($berita['status'] == 'rejected') echo 'selected'; ?>>Rejected
                        </option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="kelola_berita.php" class="btn-secondary">Batal</a>
                <button type="button" id="previewBtn" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition duration-200 flex items-center">
                    <i class="bi bi-eye mr-2"></i> Preview
                </button>
                <button type="submit" class="btn-primary">Update Berita</button>
            </div>
        </form>
    </div>
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

// Script untuk Preview Berita
document.getElementById('previewBtn').addEventListener('click', function() {
    if (typeof tinymce !== 'undefined' && tinymce.get('tubuh_berita')) {
        tinymce.get('tubuh_berita').save();
    }
    
    const form = document.querySelector('form');
    const originalAction = form.action;
    const originalTarget = form.target;
    
    form.action = 'preview_content.php';
    form.target = '_blank';
    form.submit();
    
    setTimeout(() => {
        form.action = originalAction;
        form.target = originalTarget;
    }, 500);
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>