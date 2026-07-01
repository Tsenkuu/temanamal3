<?php
// Memuat file konfigurasi untuk koneksi database dan BASE_URL
require_once 'includes/config.php';

// Fungsi untuk membuat slug yang aman untuk URL
function create_slug($string) {
    $string = strtolower(trim($string));
    // Hapus karakter non-alfanumerik kecuali spasi dan strip
    $string = preg_replace('/[^a-z0-9 -]/', '', $string);
    // Ganti spasi dengan strip
    $string = str_replace(' ', '-', $string);
    // Hapus strip ganda
    return preg_replace('/-+/', '-', $string);
}

// Ambil tipe sitemap dari parameter URL (jika ada)
$type = isset($_GET['type']) ? $_GET['type'] : 'index';

// Mengatur header sebagai XML
header("Content-Type: application/xml; charset=utf-8");

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

if ($type === 'index') {
    // --- SITEMAP INDEX ---
    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Daftar sub-sitemap
    $sitemaps = ['page', 'berita', 'program'];
    
    foreach ($sitemaps as $sitemap) {
        echo "  <sitemap>\n";
        echo "    <loc>" . BASE_URL . "/sitemap-{$sitemap}.xml</loc>\n";
        // Menggunakan tanggal hari ini sebagai lastmod default untuk index
        echo "    <lastmod>" . date('c') . "</lastmod>\n";
        echo "  </sitemap>\n";
    }
    
    echo '</sitemapindex>';

} else {
    // --- URLSET (Sub-sitemap) ---
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    if ($type === 'page') {
        // 1. URL Statis
        $static_pages = [
            '/' => 1.0,
            '/program' => 0.8,
            '/berita' => 0.8,
            '/laporan' => 0.7,
            '/tentang-kami' => 0.6,
            '/personalia' => 0.6,
            '/kalkulator-zakat' => 0.7,
            '/donasi' => 0.9,
            '/login' => 0.5
        ];

        foreach ($static_pages as $page => $priority) {
            echo "  <url>\n";
            echo "    <loc>" . BASE_URL . $page . "</loc>\n";
            echo "    <changefreq>monthly</changefreq>\n";
            echo "    <priority>{$priority}</priority>\n";
            echo "  </url>\n";
        }
    }
    elseif ($type === 'berita') {
        // 2. URL Dinamis (Berita)
        $result_berita = $mysqli->query("SELECT slug, updated_at, created_at FROM berita WHERE status = 'published' ORDER BY created_at DESC");
        if ($result_berita) {
            while ($berita = $result_berita->fetch_assoc()) {
                $last_modified = !empty($berita['updated_at']) ? $berita['updated_at'] : $berita['created_at'];
                echo "  <url>\n";
                echo "    <loc>" . BASE_URL . "/berita/" . htmlspecialchars($berita['slug']) . "</loc>\n";
                echo "    <lastmod>" . date('c', strtotime($last_modified)) . "</lastmod>\n";
                echo "    <changefreq>weekly</changefreq>\n";
                echo "    <priority>0.8</priority>\n";
                echo "  </url>\n";
            }
            $result_berita->close();
        }
    }
    elseif ($type === 'program') {
        // 3. URL Dinamis (Program)
        $result_program = $mysqli->query("SELECT id, slug, created_at FROM program ORDER BY created_at DESC");
        if ($result_program) {
            while ($program = $result_program->fetch_assoc()) {
                $identifier = !empty($program['slug']) ? $program['slug'] : $program['id'];
                $last_modified = $program['created_at'];
        echo "  <url>\n";
                echo "    <loc>" . BASE_URL . "/program/" . htmlspecialchars($identifier) . "</loc>\n";
        echo "    <lastmod>" . date('c', strtotime($last_modified)) . "</lastmod>\n";
                echo "    <changefreq>daily</changefreq>\n";
        echo "    <priority>0.9</priority>\n"; // Prioritas tinggi untuk konten dinamis
        echo "  </url>\n";
    }
            $result_program->close();
        }
    }

    echo '</urlset>';
}

$mysqli->close();
?>
