<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Metode permintaan tidak valid.";
    header("Location: keranjang_sampah.php");
    exit();
}

require_valid_csrf();

if (isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['table'])) {
    $id = (int)$_POST['id'];
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table']);
    
    if (strpos($table, '_base') === false) {
        $_SESSION['error_message'] = "Tabel tidak valid.";
        header("Location: keranjang_sampah.php");
        exit();
    }

    // Attempt to find any associated file (gambar, file_majalah, dll)
    $file_path_to_delete = null;
    
    // Some tables have specific file column names and upload folders
    $file_info = [
        'berita_base' => ['col' => 'gambar', 'folder' => 'berita'],
        'program_base' => ['col' => 'gambar', 'folder' => 'program'],
        'donasi_base' => ['col' => 'bukti_pembayaran', 'folder' => 'bukti_donasi'],
        'majalah_base' => ['col' => 'file_majalah', 'folder' => 'majalah'],
        'slider_images_base' => ['col' => 'gambar', 'folder' => 'slider'],
        'dokumentasi_kegiatan_base' => ['col' => 'gambar', 'folder' => 'dokumentasi']
    ];

    if (isset($file_info[$table])) {
        $col = $file_info[$table]['col'];
        $folder = $file_info[$table]['folder'];
        
        $stmt_sel = $mysqli->prepare("SELECT `$col` FROM `$table` WHERE id = ?");
        if ($stmt_sel) {
            $stmt_sel->bind_param("i", $id);
            $stmt_sel->execute();
            $stmt_sel->bind_result($filename);
            if ($stmt_sel->fetch() && !empty($filename) && $filename !== 'placeholder.png') {
                $file_path_to_delete = '../assets/uploads/' . $folder . '/' . $filename;
            }
            $stmt_sel->close();
        }
    }

    $stmt = $mysqli->prepare("DELETE FROM `$table` WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            // Delete physical file if exists
            if ($file_path_to_delete && file_exists($file_path_to_delete)) {
                unlink($file_path_to_delete);
            }
            $_SESSION['success_message'] = "Data telah dihapus secara permanen.";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus data: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan pada query penghapusan.";
    }
} else {
    $_SESSION['error_message'] = "ID atau Tabel tidak ditemukan.";
}

$redirect_table = isset($_POST['table']) ? '?table=' . urlencode($_POST['table']) : '';
header("Location: keranjang_sampah.php" . $redirect_table);
exit();
?>
