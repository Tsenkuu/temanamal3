<?php
require_once '../includes/config.php';
require_once 'templates/header_admin.php';

$page_title = "Kelola Slider Halaman Depan";

// Pengecekan login admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Proses upload gambar baru (tidak berubah)
require_once '../includes/image_converter.php';

// Proses upload gambar baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['gambar_slider'])) {
    if (isset($_FILES['gambar_slider']) && $_FILES['gambar_slider']['error'] == 0) {
        $target_dir = realpath("../assets/images") ?: "../assets/images";
        $file_type = strtolower(pathinfo($_FILES["gambar_slider"]["name"], PATHINFO_EXTENSION));

        if ($_FILES["gambar_slider"]["size"] > 2000000) {
            $_SESSION['error_message'] = "Ukuran file terlalu besar (maks 2MB).";
        } elseif (!in_array($file_type, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
            $_SESSION['error_message'] = "Hanya format JPG, JPEG, PNG, GIF, & WEBP yang diizinkan.";
        } else {
            // Upload & convert to WebP
            $result = upload_and_convert($_FILES['gambar_slider'], $target_dir, 82);
            if ($result && $result['success']) {
                $nama_file = $result['filename'];
                $mysqli->query("UPDATE slider_images SET urutan = urutan + 1");
                $stmt = $mysqli->prepare("INSERT INTO slider_images (nama_file, urutan) VALUES (?, 0)");
                $stmt->bind_param("s", $nama_file);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Gambar slider berhasil ditambahkan (dikonversi ke WebP).";
                }
                $stmt->close();
            } else {
                $_SESSION['error_message'] = "Gagal memproses gambar: " . ($result['message'] ?? 'Unknown error');
            }
        }
    }
    header("Location: kelola_slider.php");
    exit();
}

// Ambil semua gambar slider yang ada, diurutkan berdasarkan posisi
$result_slider = $mysqli->query("SELECT id, nama_file, urutan FROM slider_images ORDER BY urutan ASC");
?>
<!-- CSS Kustom untuk Tampilan Drag & Drop -->
<style>
.slider-gallery {
    display: flex;
    overflow-x: auto;
    padding-bottom: 1rem;
    min-height: 220px;
}

.slider-item {
    flex: 0 0 auto;
    width: 200px;
    margin-right: 1rem;
    cursor: grab;
}

.slider-item:active {
    cursor: grabbing;
}

.sortable-ghost {
    opacity: 0.4;
    background: #f0f0f0;
}
</style>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-bold text-dark-text"><?php echo $page_title; ?></h1>
    </div>

    <?php
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert-danger">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <!-- Form Upload -->
    <div class="content-card mt-6">
        <h3 class="card-title mb-4">Tambah Gambar Slider Baru</h3>
        <form action="kelola_slider.php" method="POST" enctype="multipart/form-data">
            <div>
                <label for="gambar_slider" class="form-label">Pilih Gambar</label>
                <input class="form-input-file" type="file" id="gambar_slider" name="gambar_slider" required>
                <p class="text-xs text-gray-500 mt-1">Rekomendasi rasio gambar 1:1 (persegi).</p>
            </div>
            <button type="submit" class="btn-primary mt-4">Upload Gambar</button>
        </form>
    </div>

    <!-- Daftar Gambar Slider dengan Drag & Drop -->
    <div class="content-card mt-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="card-title">Atur Posisi Slider (Geser untuk Mengurutkan)</h3>
            <button id="saveOrderBtn" class="btn-primary">
                <i class="bi bi-save-fill mr-2"></i> Simpan Urutan
            </button>
        </div>
        <div id="sliderGallery" class="slider-gallery">
            <?php if ($result_slider && $result_slider->num_rows > 0): ?>
            <?php while($slider = $result_slider->fetch_assoc()): ?>
            <div class="slider-item relative group" data-id="<?php echo $slider['id']; ?>">
                <img src="../assets/images/<?php echo htmlspecialchars($slider['nama_file']); ?>"
                    class="w-full h-full object-cover rounded-lg">
                <div
                    class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <form action="hapus_slider.php" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus gambar ini?');">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo $slider['id']; ?>">
                        <button type="submit" class="btn-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <p class="text-center text-gray-500 w-full self-center">Belum ada gambar slider.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Memuat library SortableJS dari CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const gallery = document.getElementById('sliderGallery');
    const saveBtn = document.getElementById('saveOrderBtn');

    // Inisialisasi SortableJS
    new Sortable(gallery, {
        animation: 150,
        ghostClass: 'sortable-ghost'
    });

    // Event listener untuk tombol simpan
    saveBtn.addEventListener('click', function() {
        const slideItems = gallery.querySelectorAll('.slider-item');
        const slideOrder = [];
        slideItems.forEach(item => {
            slideOrder.push(item.getAttribute('data-id'));
        });

        // Kirim urutan baru ke server menggunakan Fetch API
        fetch('simpan_urutan_slider.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order: slideOrder,
                    csrf_token: <?php echo json_encode(csrf_token()); ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Tampilkan notifikasi atau reload halaman
                    window.location.reload();
                } else {
                    alert('Gagal menyimpan urutan: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan jaringan.');
            });
    });
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>
