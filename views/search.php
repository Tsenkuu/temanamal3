<?php
// Memuat file konfigurasi
require_once 'includes/config.php';

// Ambil kata kunci pencarian dari URL, pastikan aman
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$page_title = "Hasil Pencarian untuk: '" . htmlspecialchars($search_query) . "'";

// Inisialisasi array untuk menampung hasil
$program_results = [];
$berita_results = [];

// Hanya jalankan pencarian jika ada kata kunci
if (!empty($search_query)) {
    $search_term = "%{$search_query}%";

    // 1. Cari di tabel program
    $stmt_program = $mysqli->prepare("SELECT slug, nama_program, deskripsi, gambar FROM program WHERE (nama_program LIKE ? OR deskripsi LIKE ?) ORDER BY created_at DESC");
    $stmt_program->bind_param("ss", $search_term, $search_term);
    $stmt_program->execute();
    $result_program = $stmt_program->get_result();
    if ($result_program) {
        $program_results = $result_program->fetch_all(MYSQLI_ASSOC);
    }
    $stmt_program->close();

    // 2. Cari di tabel berita (hanya yang statusnya 'published')
    $stmt_berita = $mysqli->prepare("SELECT slug, judul, teras_berita, gambar, created_at FROM berita WHERE status = 'published' AND (judul LIKE ? OR teras_berita LIKE ? OR tubuh_berita LIKE ?) ORDER BY created_at DESC");
    $stmt_berita->bind_param("sss", $search_term, $search_term, $search_term);
    $stmt_berita->execute();
    $result_berita = $stmt_berita->get_result();
    if ($result_berita) {
        $berita_results = $result_berita->fetch_all(MYSQLI_ASSOC);
    }
    $stmt_berita->close();
}

// Memuat header
require_once 'includes/templates/header.php';
?>

<main class="container mx-auto my-12 px-6">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-dark-text mb-2">Hasil Pencarian</h1>
        <p class="text-gray-600 text-lg mb-8">Menampilkan hasil untuk: <span class="font-semibold text-primary-orange">"<?php echo htmlspecialchars($search_query); ?>"</span></p>

        <?php if (empty($program_results) && empty($berita_results)) : ?>
            <div class="text-center bg-white p-8 rounded-lg shadow-md">
                <h3 class="text-2xl font-semibold text-gray-700">Tidak ada hasil yang ditemukan.</h3>
                <p class="text-gray-500 mt-2">Coba gunakan kata kunci lain yang lebih umum atau periksa kembali ejaan Anda.</p>
            </div>
        <?php else : ?>
            <!-- Hasil Program -->
            <?php if (!empty($program_results)) : ?>
                <section class="mb-12">
                    <h2 class="text-2xl font-bold border-b-2 border-primary-orange pb-2 mb-6">Program Ditemukan (<?php echo count($program_results); ?>)</h2>
                    <div class="space-y-6">
                        <?php foreach ($program_results as $program) : ?>
                            <a href="<?php echo BASE_URL; ?>/program/<?php echo $program['slug']; ?>" class="flex flex-col md:flex-row items-center bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                                <img src="<?php echo BASE_URL . '/assets/uploads/program/' . htmlspecialchars($program['gambar']); ?>" alt="<?php echo htmlspecialchars($program['nama_program']); ?>" class="w-full md:w-48 h-32 object-cover rounded-md mb-4 md:mb-0 md:mr-6">
                                <div class="flex-grow">
                                    <h3 class="text-xl font-semibold text-dark-text hover:text-primary-orange"><?php echo htmlspecialchars($program['nama_program']); ?></h3>
                                    <p class="text-gray-600 mt-2 text-sm"><?php echo htmlspecialchars(substr(strip_tags($program['deskripsi']), 0, 150)); ?>...</p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Hasil Berita -->
            <?php if (!empty($berita_results)) : ?>
                <section>
                    <h2 class="text-2xl font-bold border-b-2 border-primary-orange pb-2 mb-6">Berita Ditemukan (<?php echo count($berita_results); ?>)</h2>
                    <div class="space-y-6">
                        <?php foreach ($berita_results as $berita) : ?>
                            <a href="<?php echo BASE_URL; ?>/berita/<?php echo $berita['slug']; ?>" class="flex flex-col md:flex-row items-center bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                                <img src="<?php echo BASE_URL . '/assets/uploads/berita/' . htmlspecialchars($berita['gambar']); ?>" alt="<?php echo htmlspecialchars($berita['judul']); ?>" class="w-full md:w-48 h-32 object-cover rounded-md mb-4 md:mb-0 md:mr-6">
                                <div class="flex-grow">
                                    <p class="text-sm text-gray-500 mb-1"><?php echo date('d F Y', strtotime($berita['created_at'])); ?></p>
                                    <h3 class="text-xl font-semibold text-dark-text hover:text-primary-orange"><?php echo htmlspecialchars($berita['judul']); ?></h3>
                                    <p class="text-gray-600 mt-2 text-sm"><?php echo htmlspecialchars($berita['teras_berita']); ?>...</p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/templates/footer.php'; ?>
