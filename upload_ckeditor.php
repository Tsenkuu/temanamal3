<?php
require_once '../includes/config.php';

// Cek login amil
if (!isset($_SESSION['amil_id'])) {
    http_response_code(403);
    exit(json_encode(['error' => ['message' => 'Unauthorized']]));
}

// Cek apakah ada file yang diupload
if (!isset($_FILES['upload']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
    exit(json_encode(['error' => ['message' => 'Upload failed or no file selected']]));
}

$file = $_FILES['upload'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Validasi ekstensi
if (!in_array($ext, $allowed)) {
    exit(json_encode(['error' => ['message' => 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed.']]));
}

// Buat nama file unik
$filename = time() . '_' . uniqid() . '.' . $ext;
$path = '../assets/uploads/berita/content/';

// Buat folder jika belum ada
if (!is_dir($path)) mkdir($path, 0755, true);

if (move_uploaded_file($file['tmp_name'], $path . $filename)) {
    echo json_encode([
        'uploaded' => 1,
        'fileName' => $filename,
        'url' => BASE_URL . '/assets/uploads/berita/content/' . $filename
    ]);
} else {
    exit(json_encode(['error' => ['message' => 'Failed to save file on server.']]));
}
?>