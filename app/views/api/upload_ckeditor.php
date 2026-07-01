<?php
require_once '../includes/config.php';
require_once '../includes/image_converter.php';

// Cek login amil
if (!isset($_SESSION['amil_id'])) {
    http_response_code(403);
    exit(json_encode(['error' => ['message' => 'Unauthorized']]));
}

// Cek apakah ada file yang diupload
if (!isset($_FILES['upload']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
    exit(json_encode(['error' => ['message' => 'Upload failed or no file selected']]));
}

$file    = $_FILES['upload'];
$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Validasi ekstensi
if (!in_array($ext, $allowed)) {
    exit(json_encode(['error' => ['message' => 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed.']]));
}

$upload_dir = realpath('../assets/uploads/berita/content/');
if (!$upload_dir) {
    mkdir('../assets/uploads/berita/content/', 0755, true);
    $upload_dir = realpath('../assets/uploads/berita/content/');
}

// Konversi ke WebP via service (ada fallback otomatis jika service mati)
$result = upload_and_convert($file, $upload_dir, 82);

if ($result['success'] || isset($result['filename'])) {
    $filename = $result['filename'];
    echo json_encode([
        'uploaded' => 1,
        'fileName' => $filename,
        'url'      => BASE_URL . '/assets/uploads/berita/content/' . $filename,
    ]);
} else {
    exit(json_encode(['error' => ['message' => $result['message'] ?? 'Failed to save file on server.']]));
}