<?php
/**
 * TemanAmal - Front Controller
 */



// Memuat file konfigurasi lawas untuk kompatibilitas sementara
require_once __DIR__ . '/includes/config.php';

// Memuat autoloader untuk MVC baru
require_once __DIR__ . '/app/bootstrap.php';

// Routing Sederhana
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/' && $scriptName !== '\\') {
    $uri = str_replace($scriptName, '', $uri);
}
$uri = rtrim($uri, '/');
if (empty($uri)) {
    $uri = '/';
}

// Untuk sementara, rute utama (/) akan diarahkan ke HomeController
// Rute lain yang lolos dari .htaccess (file tidak ada) juga akan jatuh ke sini.
// Kedepannya bisa ditambahkan Router Class khusus.

use App\Config\Router;
use App\Controllers\HomeController;
use App\Controllers\NewsController;
use App\Controllers\ProgramController;
use App\Controllers\PageController;
use App\Controllers\AuthController;
use App\Controllers\DonationController;

$router = new Router();
$router->get('/', [HomeController::class, 'index']);
$router->get('/berita', [NewsController::class, 'index']);
$router->get('/berita/{slug}', [NewsController::class, 'detail']);
$router->get('/program', [ProgramController::class, 'index']);
$router->get('/program/{slug}', [ProgramController::class, 'detail']);

// Rute Halaman Statis
$router->get('/tentang_kami', [PageController::class, 'tentangKami']);
$router->get('/personalia', [PageController::class, 'personalia']);
$router->get('/kalkulator_zakat', [PageController::class, 'kalkulatorZakat']);
$router->get('/search', [PageController::class, 'search']);
$router->get('/sitemap\.xml', [PageController::class, 'sitemap']);
$router->get('/terima_kasih', [PageController::class, 'terimaKasih']);
$router->get('/error', [PageController::class, 'error']);

// Rute Autentikasi
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']); 
$router->get('/registrasi', [AuthController::class, 'register']);
$router->post('/registrasi', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/lupa_sandi', [AuthController::class, 'lupaSandi']);
$router->post('/lupa_sandi', [AuthController::class, 'lupaSandi']);
$router->get('/reset_sandi', [AuthController::class, 'resetSandi']);
$router->post('/reset_sandi', [AuthController::class, 'resetSandi']);

// Rute Transaksi Donasi
$router->get('/history/{token}', [DonationController::class, 'history']);
$router->get('/api/history', [DonationController::class, 'getHistory']);
$router->get('/konfirmasi_pembayaran', [DonationController::class, 'konfirmasiPembayaran']);
$router->get('/konfirmasi_donasi', [DonationController::class, 'konfirmasiDonasi']);
$router->post('/proses_donasi', [DonationController::class, 'prosesDonasi']);
$router->post('/upload_bukti', [DonationController::class, 'uploadBukti']);

// Rute Majalah & Laporan
use App\Controllers\MagazineController;
$router->get('/majalah', [MagazineController::class, 'index']);
$router->get('/baca_majalah', [MagazineController::class, 'read']);
$router->get('/laporan', [MagazineController::class, 'laporan']);

// Rute Chat
use App\Controllers\ChatController;
$router->post('/api/chat/send', [ChatController::class, 'send']);
$router->post('/api/chat/send_secure', [ChatController::class, 'sendSecure']);
$router->get('/api/chat/fetch', [ChatController::class, 'fetch']);

// Rute Webhook & Cron
use App\Controllers\WebhookController;
$router->post('/api/webhook/wa', [WebhookController::class, 'wa']);
$router->post('/api/webhook/midtrans', [WebhookController::class, 'midtrans']);
$router->get('/api/cron/reminder', [WebhookController::class, 'cronReminder']);

// Rute Upload Utility
use App\Controllers\UploadController;
$router->post('/api/upload_ckeditor', [UploadController::class, 'ckeditor']);
$router->post('/api/cek_upload', [UploadController::class, 'cek']);

// Rute Interaksi (Form Kontak & Komentar)
use App\Controllers\InteractionController;
$router->post('/submit_pesan', [InteractionController::class, 'submitPesan']);
$router->post('/proses_komentar', [InteractionController::class, 'prosesKomentar']);

// Redirect /donasi dan /donasi?id_program=X ke halaman detail program yang sesuai
$router->get('/donasi', function() use ($mysqli) {
    $id_program = (int)($_GET['id_program'] ?? 0);
    $nominal    = (int)($_GET['nominal'] ?? 0);

    if ($id_program > 0) {
        // Cari slug berdasarkan id_program
        $stmt = $mysqli->prepare("SELECT slug FROM program_base WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id_program);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row && $row['slug']) {
            header('Location: ' . BASE_URL . '/program/' . $row['slug']);
            exit();
        }
    }
    // Fallback: ke halaman daftar program
    header('Location: ' . BASE_URL . '/program');
    exit();
});

$router->run();