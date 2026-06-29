<?php
// Matikan display error agar tidak merusak respon JSON
ini_set('display_errors', 0);
error_reporting(0);

require_once '../includes/config.php';

// Set header JSON
header('Content-Type: application/json');

// Cek login admin
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$imageFolder = "../assets/uploads/berita/content/";

// Buat folder jika belum ada
if (!is_dir($imageFolder)) {
    if (!mkdir($imageFolder, 0755, true)) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal membuat folder upload']);
        exit;
    }
}

if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(500);
        echo json_encode(['error' => 'Upload error code: ' . $file['error']]);
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (in_array($ext, $allowed)) {
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $destination = $imageFolder . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            echo json_encode(['location' => BASE_URL . '/assets/uploads/berita/content/' . $filename]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal menyimpan file ke server']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Format file tidak diizinkan (hanya JPG, PNG, GIF, WEBP)']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Tidak ada file yang dikirim']);
}
?>