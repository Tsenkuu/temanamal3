<?php
require_once '../includes/config.php';

// Cek login admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Preview hanya tersedia melalui form edit.";
    exit;
}

// Ambil data dari form
$judul = $_POST['judul'] ?? 'Judul Berita';
$teras_berita = $_POST['teras_berita'] ?? '';
$tubuh_berita = $_POST['tubuh_berita'] ?? '';
$penulis = $_POST['penulis'] ?? 'Admin';
$type = $_POST['type'] ?? 'berita';
$tags = $_POST['tags'] ?? '';
$sumber_gambar = $_POST['sumber_gambar'] ?? '';
$created_at = date('Y-m-d H:i:s');
$views = 0;

$type_label = ($type == 'berita') ? 'Berita' : 'Opini';

// Handle Gambar
$gambar_url = BASE_URL . '/assets/uploads/berita/placeholder.png';

if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    // Jika ada upload gambar baru, baca kontennya dan jadikan base64 untuk preview
    $path = $_FILES['gambar']['tmp_name'];
    $type_img = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = base64_encode($data);
    $gambar_url = 'data:image/' . $type_img . ';base64,' . $base64;
} elseif (!empty($_POST['gambar_lama'])) {
    // Jika tidak ada upload, gunakan gambar lama
    $gambar_url = BASE_URL . '/assets/uploads/berita/' . $_POST['gambar_lama'];
}

// Setup variabel untuk header.php (SEO & OpenGraph)
$page_title = "Preview: " . htmlspecialchars($judul);
$og_title = htmlspecialchars($judul);
$og_description = htmlspecialchars(strip_tags($teras_berita));
$og_image = $gambar_url;
$og_url = "#";
$og_type = "article";

// Load Header (Public Header)
require_once '../includes/templates/header.php';
?>

<!-- Style tambahan untuk preview banner -->
<style>
    .preview-banner {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background-color: #ffc107;
        color: #000;
        text-align: center;
        padding: 10px;
        font-weight: bold;
        z-index: 9999;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    body {
        padding-top: 40px; /* Space for banner */
    }
    /* Style dari detail_berita.php */
    .prose {
        text-align: justify;
        hyphens: auto;
    }
    .prose img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
        margin-top: 1rem;
        margin-bottom: 1rem;
    }
    .prose p,
    .prose ul,
    .prose ol {
        margin-bottom: 1.25rem;
    }
</style>

<div class="preview-banner">
    <i class="bi bi-eye-fill"></i> MODE PREVIEW - Tampilan ini belum disimpan
</div>

<main class="container mx-auto my-8 px-4 md:my-12 md:px-6">
    <div class="max-w-4xl mx-auto">
        <article class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <figure class="relative">
                <img src="<?php echo $gambar_url; ?>"
                    alt="<?php echo htmlspecialchars($judul); ?>" class="w-full h-auto md:h-96 object-cover">
                <?php if (!empty($sumber_gambar)): ?>
                <figcaption class="absolute bottom-0 right-0 bg-black bg-opacity-50 text-white text-xs px-2 py-1">
                    Sumber: <?php echo htmlspecialchars($sumber_gambar); ?>
                </figcaption>
                <?php endif; ?>
            </figure>

            <div class="p-6 md:p-10">

                <h1 class="text-3xl md:text-4xl font-bold text-dark-text mb-4 leading-tight">
                    <?php echo htmlspecialchars($judul); ?></h1>
                <div class="flex items-center justify-between text-gray-500 text-sm mb-6">
                    <div class="flex items-center">
                        <span>Oleh: <span
                                class="font-semibold"><?php echo htmlspecialchars($penulis); ?></span></span>
                        <span class="mx-2">&bull;</span>
                        <span><?php echo date('d F Y', strtotime($created_at)); ?></span>
                        <span class="mx-2">&bull;</span>
                        <span><i class="bi bi-eye"></i> <?php echo number_format($views, 0, ',', '.'); ?> Dilihat</span>
                    </div>
                    <span
                        class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $type == 'berita' ? 'text-blue-800 bg-blue-100' : 'text-purple-800 bg-purple-100'; ?>">
                        <?php echo htmlspecialchars($type_label); ?>
                    </span>
                </div>
                <p
                    class="text-lg text-gray-600 font-semibold leading-relaxed mb-6 border-l-4 border-primary-orange pl-4">
                    <?php echo nl2br(htmlspecialchars($teras_berita)); ?>
                </p>

                <div class="prose max-w-none text-gray-700 leading-relaxed break-words">
                    <?php echo $tubuh_berita; // HTML dari editor ?>
                </div>

                <?php if (!empty($tags)): ?>
                <div class="mt-8 pt-6 border-t">
                    <h4 class="font-semibold mb-2">Tags:</h4>
                    <div class="flex flex-wrap gap-2">
                        <?php
                        $tags_arr = explode(',', $tags);
                        foreach ($tags_arr as $tag):
                        ?>
                        <span class="bg-gray-200 text-gray-700 text-sm font-medium px-3 py-1 rounded-full">
                            <?php echo htmlspecialchars(trim($tag)); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </article>
    </div>
</main>

<?php require_once '../includes/templates/footer.php'; ?>