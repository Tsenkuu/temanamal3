<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Metode permintaan tidak valid.";
    header("Location: kelola_pembayaran.php");
    exit();
}

require_valid_csrf();

if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id_metode = (int)$_POST['id'];

    // Ambil nama file gambar sebelum dihapus dari DB
    $stmt_select = $mysqli->prepare("SELECT gambar FROM metode_pembayaran WHERE id = ?");
    $stmt_select->bind_param("i", $id_metode);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $metode = $result->fetch_assoc();
    $stmt_select->close();

    // Hapus dari database
    $stmt_delete = $mysqli->prepare("DELETE FROM metode_pembayaran WHERE id = ?");
    $stmt_delete->bind_param("i", $id_metode);
    if ($stmt_delete->execute()) {
        // Jika berhasil, hapus file gambar QRIS jika ada
        if ($metode && !empty($metode['gambar'])) {
            $file_path = '../assets/images/qris/' . $metode['gambar'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        $_SESSION['success_message'] = "Metode pembayaran berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus metode pembayaran.";
    }
    $stmt_delete->close();
} else {
    $_SESSION['error_message'] = "Permintaan tidak valid.";
}

header("Location: kelola_pembayaran.php");
exit();
?>
