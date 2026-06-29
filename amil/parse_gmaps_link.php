<?php
// Set header untuk output JSON
header('Content-Type: application/json');

// Fungsi untuk mendapatkan URL final dari redirect (termasuk link pendek)
function getFinalRedirectUrl($url) {
    // Periksa apakah cURL tersedia
    if (!function_exists('curl_init')) {
        return null; 
    }
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Ikuti redirect
    curl_setopt($ch, CURLOPT_NOBODY, true); // Hanya ambil header, tidak perlu body
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Timeout koneksi 5 detik
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout total 10 detik
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'); // User agent standar
    curl_exec($ch);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Pastikan request berhasil
    if ($httpCode >= 200 && $httpCode < 400) {
        return $finalUrl;
    }
    
    return null;
}

// Inisialisasi respon
$response = ['success' => false, 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['url'])) {
    $url = trim($_POST['url']);

    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        $response['message'] = 'URL yang diberikan tidak valid.';
        echo json_encode($response);
        exit;
    }

    $finalUrl = getFinalRedirectUrl($url);

    if (!$finalUrl) {
        $response['message'] = 'Gagal mengakses URL. Mungkin link tidak valid atau server tidak merespon.';
        echo json_encode($response);
        exit;
    }

    // Pola regex untuk mengekstrak latitude dan longitude dari URL Google Maps
    // Contoh: .../@-8.1234567,111.9876543,17z/...
    if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $finalUrl, $matches)) {
        $response = [
            'success'   => true,
            'latitude'  => (float) $matches[1],
            'longitude' => (float) $matches[2],
            'final_url' => $finalUrl
        ];
    } else {
        $response['message'] = 'Tidak dapat menemukan koordinat pada URL final.';
        $response['final_url_debug'] = $finalUrl;
    }

}

echo json_encode($response);
exit;
?>
