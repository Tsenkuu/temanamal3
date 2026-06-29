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

$router = new Router();
$router->get('/', [HomeController::class, 'index']);
$router->get('/berita', [NewsController::class, 'index']);
$router->get('/berita/{slug}', [NewsController::class, 'detail']);
$router->get('/program', [ProgramController::class, 'index']);
$router->get('/program/{slug}', [ProgramController::class, 'detail']);

$router->run();