<?php
require_once '../includes/config.php';

// Cek login amil
if (!isset($_SESSION['amil_id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

$imageFolder = "../assets/uploads/berita/content/";

// Buat folder jika belum ada
if (!is_dir($imageFolder)) {
    mkdir($imageFolder, 0755, true);
}

if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (in_array($ext, $allowed)) {
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $destination = $imageFolder . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            echo json_encode(['location' => BASE_URL . '/assets/uploads/berita/content/' . $filename]);
        } else {
            header("HTTP/1.1 500 Server Error");
        }
    } else {
        header("HTTP/1.1 400 Invalid extension");
    }
}
?>