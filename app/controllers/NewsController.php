<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class NewsController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function index() {
        $page_title = "Kabar & Berita Terbaru";
        $meta_description = "Berita, artikel, dan opini terbaru dari Lazismu Tulungagung. Dapatkan informasi terkini tentang kegiatan penyaluran dan program kemanusiaan kami.";
        $meta_keywords = "berita lazismu, artikel zakat, opini kemanusiaan, lazismu tulungagung, info penyaluran donasi";
        
        // Fetch published news
        $stmt = $this->db->query("SELECT id, judul, slug, teras_berita, gambar, created_at, type, views FROM berita WHERE status = 'published' ORDER BY created_at DESC");
        $news_items = $stmt->fetchAll();
        
        // Featured News (e.g. most viewed or latest)
        $featured_news = !empty($news_items) ? $news_items[0] : null;
        
        // The rest of the news
        $other_news = !empty($news_items) ? array_slice($news_items, 1) : [];

        // Categories (if any logic exists, for now we just group by 'type')
        $stmt_cat = $this->db->query("SELECT type, COUNT(*) as count FROM berita WHERE status = 'published' GROUP BY type");
        $categories = $stmt_cat->fetchAll();

        $data = [
            'page_title' => $page_title,
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords,
            'featured_news' => $featured_news,
            'other_news' => $other_news,
            'categories' => $categories
        ];

        extract($data);
        require_once __DIR__ . '/../views/news/index.php';
    }

    public function detail($slug) {
        $stmt = $this->db->prepare("SELECT *, CASE WHEN type = 'berita' THEN 'Berita' WHEN type = 'opini' THEN 'Opini' ELSE 'Artikel' END as type_label FROM berita WHERE slug = ? AND status = 'published'");
        $stmt->execute([$slug]);
        $berita = $stmt->fetch();

        if (!$berita) {
            http_response_code(404);
            die("Berita tidak ditemukan.");
        }

        // Update Views (Basic Tracking)
        $update = $this->db->prepare("UPDATE berita SET views = views + 1 WHERE id = ?");
        $update->execute([$berita['id']]);

        // Berita Populer (Sidebar)
        $stmt_populer = $this->db->query("SELECT id, judul, slug, gambar, created_at, views FROM berita WHERE status = 'published' AND id != {$berita['id']} ORDER BY views DESC LIMIT 4");
        $populer = $stmt_populer->fetchAll();

        // Komentar
        $stmt_komentar = $this->db->prepare("SELECT nama_pengirim, isi_komentar, created_at FROM komentar WHERE id_berita = ? AND status = 'approved' ORDER BY created_at DESC");
        $stmt_komentar->execute([$berita['id']]);
        $komentar = $stmt_komentar->fetchAll();

        // SEO Meta Tags
        $page_title = $berita['judul'];
        $meta_description = substr(strip_tags($berita['teras_berita'] ?? $berita['tubuh_berita']), 0, 160) . '...';
        $meta_keywords = "berita " . strtolower($berita['type_label']) . ", lazismu tulungagung, artikel terbaru";
        $og_image = $berita['gambar'] ? BASE_URL . '/assets/uploads/berita/' . $berita['gambar'] : '';

        $data = [
            'page_title' => $page_title,
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords,
            'og_image' => $og_image,
            'berita' => $berita,
            'populer' => $populer,
            'komentar' => $komentar
        ];

        extract($data);
        require_once __DIR__ . '/../views/news/detail.php';
    }
}
