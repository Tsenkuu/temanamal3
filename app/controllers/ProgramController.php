<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class ProgramController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function index() {
        $page_title = "Program Donasi";
        $meta_description = "Daftar program donasi Lazismu Tulungagung. Mari berpartisipasi dalam program kebaikan untuk membantu mereka yang membutuhkan.";
        $meta_keywords = "program donasi, lazismu, pilar sosial, kemanusiaan, pendidikan, dakwah, kesehatan, ekonomi";
        
        $stmt = $this->db->query("SELECT id, nama_program, slug, deskripsi, gambar, target_donasi, donasi_terkumpul, created_at FROM program ORDER BY created_at DESC");
        $programs = $stmt->fetchAll();

        // Kategori program bisa dikembangkan nanti
        $categories = [
            ['id' => 'kemanusiaan', 'name' => 'Kemanusiaan'],
            ['id' => 'kesehatan', 'name' => 'Kesehatan'],
            ['id' => 'pendidikan', 'name' => 'Pendidikan'],
            ['id' => 'ekonomi', 'name' => 'Ekonomi'],
            ['id' => 'dakwah', 'name' => 'Dakwah']
        ];

        require_once __DIR__ . '/../views/program/index.php';
    }

    public function detail($slug) {
        $stmt = $this->db->prepare("SELECT * FROM program WHERE slug = ?");
        $stmt->execute([$slug]);
        $program = $stmt->fetch();

        if (!$program) {
            // Coba cari pakai ID (fallback untuk URL lama)
            if (is_numeric($slug)) {
                $stmt = $this->db->prepare("SELECT * FROM program WHERE id = ?");
                $stmt->execute([$slug]);
                $program = $stmt->fetch();
            }

            if (!$program) {
                http_response_code(404);
                $error_code = '404';
                $error_title = 'Program Tidak Ditemukan';
                $error_message = 'Maaf, program donasi yang Anda cari tidak ditemukan. Mungkin sudah dihapus atau tautan salah.';
                include __DIR__ . '/../../includes/templates/error.php';
                exit();
            }
        }

        // SEO Meta Tags
        $page_title = $program['nama_program'];
        $meta_description = substr(strip_tags($program['deskripsi']), 0, 160) . '...';
        $meta_keywords = "donasi " . strtolower($program['nama_program']) . ", lazismu, infak, sedekah";
        $og_image = $program['gambar'] ? BASE_URL . '/assets/uploads/program/' . $program['gambar'] : '';

        // Program terkait
        $stmt_terkait = $this->db->prepare("SELECT id, nama_program, gambar, slug, target_donasi, donasi_terkumpul FROM program WHERE id != ? ORDER BY RAND() LIMIT 3");
        $stmt_terkait->execute([$program['id']]);
        $program_terkait = $stmt_terkait->fetchAll();

        // Donatur terbaru
        $stmt_donatur = $this->db->prepare("SELECT nama_donatur as nama, nominal as jumlah, doa, anonim, created_at FROM donasi WHERE id_program = ? AND status = 'Selesai' ORDER BY created_at DESC LIMIT 5");
        $stmt_donatur->execute([$program['id']]);
        $donatur = $stmt_donatur->fetchAll();
        
        // Jumlah total donatur
        $stmt_count = $this->db->prepare("SELECT COUNT(*) as total FROM donasi WHERE id_program = ? AND status = 'Selesai'");
        $stmt_count->execute([$program['id']]);
        $total_donatur = $stmt_count->fetch()['total'];

        // Kabar Program (Update Program)
        $stmt_kabar = $this->db->prepare("SELECT * FROM kabar_program WHERE id_program = ? ORDER BY created_at DESC");
        $stmt_kabar->execute([$program['id']]);
        $kabar_program = $stmt_kabar->fetchAll();

        // Metode Pembayaran (yang aktif)
        $stmt_metode = $this->db->query("SELECT * FROM metode_pembayaran WHERE status = 'aktif' ORDER BY kategori, nama_metode");
        $metode_raw = $stmt_metode->fetchAll();
        $metode_pembayaran = [];
        foreach($metode_raw as $m) {
            $metode_pembayaran[$m['kategori']][] = $m;
        }

        require_once __DIR__ . '/../views/program/detail.php';
    }
}
