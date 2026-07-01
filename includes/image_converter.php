<?php
/**
 * Helper: Konversi gambar ke WebP via Image Converter Service
 * Dipanggil dari upload_ckeditor.php, proses_berita.php, dll.
 */

define('IMAGE_CONVERTER_URL',   getenv('IMAGE_CONVERTER_URL')   ?: 'http://localhost:3001');
define('IMAGE_CONVERTER_TOKEN', getenv('IMAGE_CONVERTER_TOKEN') ?: 'RAHASIAPIXELYOGA');

/**
 * Konversi file gambar yang sudah ada di disk ke WebP.
 *
 * @param string $file_path       Path lengkap ke file asli (misal: /xampp/htdocs/.../uploads/foo.jpg)
 * @param bool   $delete_original Apakah file asli dihapus setelah konversi? (default: true)
 * @param int    $quality         Kualitas WebP 0-100 (default: 80)
 * @return array ['success'=>bool, 'filename'=>string, 'path'=>string, ...]
 */
function convert_to_webp(string $file_path, bool $delete_original = true, int $quality = 80): array {
    // Jika service tidak berjalan, kembalikan file asli agar upload tetap jalan
    $url = IMAGE_CONVERTER_URL . '/convert-path';

    $payload = json_encode([
        'file_path'        => $file_path,
        'output_dir'       => dirname($file_path),
        'quality'          => $quality,
        'delete_original'  => $delete_original,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-token: ' . IMAGE_CONVERTER_TOKEN,
        ],
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        error_log("[ImageConverter] Gagal konversi: HTTP $httpCode | file: $file_path");
        // Fallback: kembalikan info file asli agar tidak break upload
        return [
            'success'  => false,
            'filename' => basename($file_path),
            'path'     => $file_path,
            'message'  => 'Service tidak tersedia, file asli digunakan.',
        ];
    }

    $result = json_decode($response, true);
    if (!$result || !$result['success']) {
        error_log("[ImageConverter] Error: " . ($result['message'] ?? 'Unknown'));
        return [
            'success'  => false,
            'filename' => basename($file_path),
            'path'     => $file_path,
            'message'  => $result['message'] ?? 'Konversi gagal.',
        ];
    }

    return $result;
}

/**
 * Upload gambar langsung ke converter service (tanpa simpan ke disk dulu).
 *
 * @param array  $file_data   Array dari $_FILES['field']
 * @param string $output_dir  Folder tujuan penyimpanan WebP
 * @param int    $quality     Kualitas WebP 0-100
 * @return array
 */
function upload_and_convert(array $file_data, string $output_dir, int $quality = 80): array {
    if ($file_data['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error: ' . $file_data['error']];
    }

    $url = IMAGE_CONVERTER_URL . '/convert';

    $cfile = new CURLFile($file_data['tmp_name'], $file_data['type'], $file_data['name']);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => [
            'file'       => $cfile,
            'output_dir' => $output_dir,
            'quality'    => $quality,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'x-api-token: ' . IMAGE_CONVERTER_TOKEN,
        ],
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        // Fallback: simpan file asli saja
        error_log("[ImageConverter] Upload+convert gagal: HTTP $httpCode");
        $fallback_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._\-]/', '_', basename($file_data['name']));
        $fallback_path = rtrim($output_dir, '/') . '/' . $fallback_name;
        move_uploaded_file($file_data['tmp_name'], $fallback_path);
        return [
            'success'  => false,
            'filename' => $fallback_name,
            'path'     => $fallback_path,
            'message'  => 'Service tidak tersedia, file asli disimpan.',
        ];
    }

    return json_decode($response, true) ?? ['success' => false, 'message' => 'Response invalid'];
}
