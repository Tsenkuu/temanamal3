<?php
require_once '../includes/config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: ../login.php'); exit(); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $mysqli->prepare("SELECT gambar FROM dokumentasi_kegiatan WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    
    if ($data) {
        // Hapus file gambar
        $file_path = "../assets/uploads/dokumentasi/" . $data['gambar'];
        if (file_exists($file_path)) // unlink($file_path);
        
        // Hapus data di database
        $stmt_del = $mysqli->prepare("UPDATE dokumentasi_kegiatan SET deleted_at = NOW() WHERE id = ?");
        $stmt_del->bind_param("i", $id);
        if ($stmt_del->execute()) {
            $_SESSION['success_message'] = "Dokumentasi berhasil dihapus.";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus data.";
        }
    }
}
header("Location: kelola_dokumentasi.php");
exit();
?>
