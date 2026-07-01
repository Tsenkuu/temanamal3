<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class HomeController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function index() {
        // Fetch Settings
        $total_disalurkan = (float) $this->getSetting('total_donasi_disalurkan', '0');
        $admin_wa = preg_replace('/[^0-9]/', '', $this->getSetting('admin_wa_number', '6285806917113'));
        $priority_program_id = (int) $this->getSetting('program_prioritas_beranda', '0');

        // Fetch Data
        $hero_slides = $this->db->query("SELECT nama_file FROM slider_images ORDER BY urutan ASC, id DESC LIMIT 5")->fetchAll();
        $programs = $this->db->query("SELECT id, nama_program, slug, gambar, target_donasi, donasi_terkumpul, kategori, deskripsi, created_at FROM program ORDER BY created_at DESC LIMIT 8")->fetchAll();
        $news_items = $this->db->query("SELECT id, judul, teras_berita, gambar, created_at, slug, type FROM berita WHERE status = 'published' ORDER BY created_at DESC LIMIT 4")->fetchAll();
        $recent_donations = $this->db->query("SELECT d.nama_donatur, d.anonim, d.nominal, d.created_at, p.nama_program FROM donasi d LEFT JOIN program p ON p.id = d.id_program WHERE d.status IN ('Menunggu Konfirmasi','Selesai') ORDER BY d.created_at DESC LIMIT 5")->fetchAll();

        // Counts
        $program_count = (int) $this->db->query("SELECT COUNT(*) FROM program")->fetchColumn();
        $news_count = (int) $this->db->query("SELECT COUNT(*) FROM berita WHERE status='published'")->fetchColumn();
        $amil_count = (int) $this->db->query("SELECT COUNT(*) FROM amil WHERE status='Aktif' AND tampilkan_di_beranda='Ya'")->fetchColumn();

        // Featured Program
        $featured = null;
        foreach ($programs as $p) {
            if ((int) $p['id'] === $priority_program_id) {
                $featured = $p;
                break;
            }
        }
        if (!$featured && $priority_program_id > 0) {
            $stmt = $this->db->prepare("SELECT id, nama_program, slug, gambar, target_donasi, donasi_terkumpul, kategori, deskripsi, created_at FROM program WHERE id = ? LIMIT 1");
            $stmt->execute([$priority_program_id]);
            $featured = $stmt->fetch();
        }
        if (!$featured) {
            $featured = $programs[0] ?? null;
        }

        $program_cards = array_values(array_filter($programs, fn($p) => (int) $p['id'] !== (int) ($featured['id'] ?? -1)));

        // SEO Meta Tags
        $page_title = 'Beranda';
        $meta_description = 'Selamat datang di TemanAmal Lazismu Tulungagung. Mari berdonasi, zakat, infak, dan sedekah untuk membantu sesama melalui program-program unggulan kami.';
        $meta_keywords = 'lazismu tulungagung, teman amal, donasi online, zakat online, infak, sedekah';

        // Pass data to view
        $data = [
            'page_title' => $page_title,
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords,
            'total_disalurkan' => $total_disalurkan,
            'admin_wa' => $admin_wa,
            'hero_slides' => $hero_slides,
            'featured' => $featured,
            'program_cards' => $program_cards,
            'news_items' => $news_items,
            'recent_donations' => $recent_donations,
            'program_count' => $program_count,
            'news_count' => $news_count,
            'amil_count' => $amil_count
        ];

        extract($data);
        global $mysqli; require_once __DIR__ . '/../views/home/index.php';
    }

    private function getSetting($name, $default = '') {
        $stmt = $this->db->prepare("SELECT nilai_pengaturan FROM pengaturan WHERE nama_pengaturan = ? LIMIT 1");
        $stmt->execute([$name]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (string)$val : $default;
    }
}
