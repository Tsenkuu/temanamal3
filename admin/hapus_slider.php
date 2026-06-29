<?php
require_once '../includes/config.php';

// Pengecekan login admin
if (!isset($_SESSION['admin_id'])) {
    // Jika ini adalah request AJAX, kirim error. Jika tidak, redirect.
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Metode permintaan tidak valid.";
    header("Location: kelola_slider.php");
    exit();
}

require_valid_csrf();

if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id_slider = (int)$_POST['id'];

    // Ambil nama file dari database sebelum dihapus
    $stmt_select = $mysqli->prepare("SELECT nama_file FROM slider_images WHERE id = ?");
    $stmt_select->bind_param("i", $id_slider);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    if ($result->num_rows > 0) {
        $slider = $result->fetch_assoc();
        $nama_file_untuk_dihapus = $slider['nama_file'];

        // Hapus data dari database
        $stmt_delete = $mysqli->prepare("DELETE FROM slider_images WHERE id = ?");
        $stmt_delete->bind_param("i", $id_slider);
        if ($stmt_delete->execute()) {
            // Hapus file fisik dari server
            $file_path = '../assets/images/' . $nama_file_untuk_dihapus;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $_SESSION['success_message'] = "Gambar slider berhasil dihapus.";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus gambar.";
        }
        $stmt_delete->close();
    }
    $stmt_select->close();
}

header("Location: kelola_slider.php");
exit();
?>
