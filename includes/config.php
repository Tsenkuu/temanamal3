<?php
require_once __DIR__ . '/security.php';

app_bootstrap_security();

// --- DETEKSI LINGKUNGAN (LOCAL vs SERVER) ---
// Lingkungan lokal HANYA 'localhost' atau '127.0.0.1'.
// Semua nama domain atau IP lain dianggap sebagai server/produksi.
$isLocal = app_is_local_environment();

define('DB_SERVER', getenv('DB_HOST') ?: 'localhost');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'temanamal');

// --- KONEKSI DATABASE ---
// Menggunakan error handling dengan try-catch untuk mysqli
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    $mysqli->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // Tampilkan pesan error yang lebih ramah di produksi
    // dan log error yang detail untuk developer.
    error_log("Database Connection Error: " . $e->getMessage());
    $error_code = '500';
    $error_title = 'Database Tidak Terhubung';
    $error_message = 'Terjadi masalah koneksi ke database. Tim kami sedang menanganinya. Silakan coba lagi nanti.';
    include __DIR__ . '/templates/error.php';
    exit();
}


// --- BASE URL OTOMATIS ---
$protocol = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
) ? "https" : "http";

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$path_to_includes = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', __DIR__));
$base_path = str_replace('/includes', '', $path_to_includes);
$base_url = rtrim("$protocol://$host" . ($base_path === '/' ? '' : $base_path), '/');
define('BASE_URL', $base_url);

// --- TIMEZONE ---
date_default_timezone_set('Asia/Jakarta');

// --- KONFIGURASI WHATSAPP ---
$admin_wa_number_from_db = '6285806917113';
$stmt_wa = $mysqli->prepare("SELECT nilai_pengaturan FROM pengaturan WHERE nama_pengaturan = ? LIMIT 1");
$setting_name = 'admin_wa_number';
$stmt_wa->bind_param("s", $setting_name);
$stmt_wa->execute();
$result_wa = $stmt_wa->get_result();
if ($result_wa && $result_wa->num_rows > 0) {
    $admin_wa_number_from_db = $result_wa->fetch_assoc()['nilai_pengaturan'];
}
$stmt_wa->close();

define('ADMIN_WA_NUMBER', $admin_wa_number_from_db);
// Local wa-api service (default). Sesuaikan jika Anda menjalankan di host/port lain.
define('API_WA_BASE_URL', getenv('API_WA_BASE_URL') ?: ($isLocal ? 'http://localhost:3002' : 'https://apiwa.invtulungagung.my.id')); // Base URL server Node.js (wa-api)
define('API_WA_URL',      API_WA_BASE_URL . '/send');
define('API_KALKULATOR_URL', API_WA_BASE_URL . '/kalkulator-details');
define('API_WA_RESET_URL',  API_WA_BASE_URL . '/reset-sesi');
// Pastikan nilai token ini sama dengan `API_WA_TOKEN` di file .env pada folder wa-api
define('API_WA_TOKEN', getenv('API_WA_TOKEN') ?: 'RAHASIAPIXELYOGA');

/**
 * Fungsi helper untuk melakukan panggilan ke API WhatsApp Node.js.
 * @param string $endpoint Endpoint yang dituju (misal: '/kirim-pesan').
 * @param string $method Metode HTTP ('GET' atau 'POST').
 * @param array $data Data yang akan dikirim (untuk metode POST).
 * @return array Hasil response dari API dalam bentuk array.
 */
function callWhatsappAPI($endpoint, $method = 'POST', $data = []) {
    // Selalu tambahkan token ke data yang dikirim
    $data['token'] = API_WA_TOKEN;

    // CHANGE START: Logging Request yang lebih jelas
    // [LOGGING] Catat request yang akan dikirim ke error log server
    error_log("[WA API Request] Method: $method | Endpoint: $endpoint | Data: " . json_encode($data));
    // CHANGE END

    $url = API_WA_BASE_URL . $endpoint;
    // Jika metode GET, tambahkan parameter query dari $data (termasuk token)
    if (strtoupper($method) === 'GET' && !empty($data)) {
        $query = http_build_query($data);
        $url .= (strpos($url, '?') === false ? '?' : '&') . $query;
    }

    $ch = curl_init($url);

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10, // Waktu tunggu total
        CURLOPT_CONNECTTIMEOUT => 5,  // Waktu tunggu koneksi
    ];

    if (strtoupper($method) === 'POST') {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
        $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
    }

    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    $error = curl_error($ch);

    // CHANGE START: Error Handling & Response Logging
    // [LOGGING] Catat response atau error
    if ($error) {
        // Jika cURL gagal, kembalikan response error dan catat di log
        error_log("[WA API Error] Curl Failed: " . $error);
        return ['success' => false, 'message' => 'Curl Error: ' . $error];
    }

    error_log("[WA API Response] " . $response);
    $decoded = json_decode($response, true);
    return $decoded ?: ['success' => false, 'message' => 'Invalid JSON response from API: ' . $response];
    // CHANGE END
}

/**
 * Mengambil nomor WA admin dari server Node.js.
 * @return string|null Nomor WA admin atau null jika gagal.
 */
function getAdminWhatsappNumber() {
    // Gunakan static variable untuk caching agar tidak memanggil API berulang kali dalam satu request
    static $admin_number = null;
    if ($admin_number !== null) {
        return $admin_number;
    }

    $response = callWhatsappAPI('/ambil-pengaturan', 'GET');
    if (isset($response['success']) && $response['success']) {
        $admin_number = $response['data']['admin_wa_number'] ?? null;
        return $admin_number;
    }
    return null; // Kembalikan null jika gagal mengambil
}

/**
 * Kirim notifikasi WhatsApp via API.
 * @param string $nomor Nomor tujuan (format 08xx atau 628xx).
 * @param string $pesan Isi pesan.
 * @return array Response dari API.
 */
function kirimNotifikasiWA($nomor, $pesan) {
    return callWhatsappAPI('/send', 'POST', [
        'to' => $nomor,
        'message' => $pesan,
    ]);
}

// CHANGE START: Fungsi Cek Status Koneksi WA
/**
 * Mengecek status koneksi WhatsApp API.
 * Berguna untuk memastikan WA terhubung sebelum mencoba mengirim pesan.
 * @return boolean True jika terhubung, False jika tidak.
 */
function checkWhatsappStatus() {
    // Panggil endpoint /status dari API Node.js
    $response = callWhatsappAPI('/status', 'GET');
    
    if (isset($response['success']) && $response['success'] && isset($response['data']['connected'])) {
        return (bool) $response['data']['connected'];
    }
    return false;
}
// CHANGE END

/**
 * Fungsi untuk melacak pengunjung unik harian dan menambah jumlah view.
 */
function track_visitor($mysqli, $page_type, $page_id = null) {
    // Jangan lacak jika yang mengakses adalah admin atau amil yang sedang login
    if (isset($_SESSION['admin_id']) || isset($_SESSION['amil_id'])) {
        return;
    }

    $ip_address = $_SERVER['REMOTE_ADDR'];
    $today = date("Y-m-d");

    // Query untuk mencatat kunjungan unik. ON DUPLICATE KEY UPDATE memastikan
    // satu IP hanya dihitung sekali per hari untuk halaman yang sama.
    $stmt_insert = $mysqli->prepare(
        "INSERT INTO visitors (page_type, page_id, ip_address, visit_date) VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE visit_count = visit_count + 0" // Tidak melakukan apa-apa jika duplikat
    );
    // Memastikan page_id adalah integer atau null
    $page_id_val = $page_id ? (int)$page_id : null;
    $stmt_insert->bind_param("siss", $page_type, $page_id_val, $ip_address, $today);
    $stmt_insert->execute();

    // affected_rows akan > 0 hanya jika baris BARU berhasil dimasukkan (kunjungan unik pertama hari ini)
    if ($stmt_insert->affected_rows > 0) {
        // Hanya update jumlah view jika ini adalah kunjungan unik pertama hari ini
        if ($page_type === 'berita' && $page_id) {
            $mysqli->query("UPDATE berita SET views = views + 1 WHERE id = " . (int)$page_id);
        } elseif ($page_type === 'program' && $page_id) {
            $mysqli->query("UPDATE program SET views = views + 1 WHERE id = " . (int)$page_id);
        }
    }
    $stmt_insert->close();
}

// --- KONFIGURASI HARGA EMAS (MetalPriceAPI) ---
define('GOLD_API_KEY', getenv('GOLD_API_KEY') ?: 'b293a40257dd5c3109f120502052a434');
define('USD_IDR_RATE', 16000); // Kurs Manual USD ke IDR
define('GOLD_CACHE_FILE', __DIR__ . '/../assets/cache/gold_price.json');

/**
 * Mengambil harga emas per gram dalam Rupiah.
 * Menggunakan API MetalPriceAPI dengan caching.
 */
function getHargaEmasIDR() {
    $cacheFile = GOLD_CACHE_FILE;
    $apiKey = GOLD_API_KEY;
    $kursDollar = USD_IDR_RATE;

    // Buat folder cache jika belum ada
    $cacheDir = dirname($cacheFile);
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    // 1. Cek Cache (Berlaku 30 Menit = 1800 detik)
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 1800)) {
        $cachedData = json_decode(file_get_contents($cacheFile), true);
        if (isset($cachedData['price_idr_gram'])) {
            $cachedData['source'] = 'cache';
            return $cachedData;
        }
    }

    // 2. Panggil API Eksternal
    $url = "https://api.metalpriceapi.com/v1/latest?api_key={$apiKey}&base=USD&currencies=XAU";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Uncomment jika di localhost bermasalah SSL
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    $price_usd_oz = 0;
    $success = false;

    if (!$error) {
        $json = json_decode($response, true);
        if (isset($json['rates']['XAU'])) {
            $rate = $json['rates']['XAU'];
            // Konversi: Jika rate < 1, berarti 1 USD = rate XAU. Harga 1 XAU = 1/rate USD.
            // Jika rate > 100, asumsi API mengembalikan harga langsung (edge case).
            $price_usd_oz = ($rate > 0) ? (1 / $rate) : 0;
            $success = true;
        }
    }

    // 3. Fallback & Kalkulasi
    if (!$success) {
        // Gunakan cache lama jika ada, atau default hardcoded
        $price_usd_oz = file_exists($cacheFile) ? (json_decode(file_get_contents($cacheFile), true)['rates']['XAU_USD_OZ'] ?? 2500) : 2500;
    }

    // 1 Troy Ounce = 31.1035 Gram
    $price_usd_gram = $price_usd_oz / 31.1035;
    $price_idr_gram = $price_usd_gram * $kursDollar;

    $result = [
        'success' => $success,
        'source' => $success ? 'api' : 'fallback',
        'rates' => [
            'USD_IDR' => $kursDollar,
            'XAU_USD_OZ' => $price_usd_oz,
            'XAU_USD_GRAM' => $price_usd_gram
        ],
        'price_idr_gram' => round($price_idr_gram),
        'formatted_price' => "Rp " . number_format(round($price_idr_gram), 0, ',', '.'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Simpan ke cache
    file_put_contents($cacheFile, json_encode($result));

    return $result;
}

// Jalankan Auto-Migration
require_once __DIR__ . '/migration_runner.php';

?>
