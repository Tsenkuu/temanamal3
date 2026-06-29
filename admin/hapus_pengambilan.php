<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Metode permintaan tidak valid.";
    header("Location: riwayat_pengambilan.php");
    exit();
}

require_valid_csrf();

if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id_riwayat = (int)$_POST['id'];
    $stmt = $mysqli->prepare("DELETE FROM riwayat_pengambilan WHERE id = ?");
    $stmt->bind_param("i", $id_riwayat);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Riwayat pengambilan berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus riwayat.";
    }
    $stmt->close();
} 
elseif (isset($_POST['hapus']) && $_POST['hapus'] === 'semua') {
    if ($mysqli->query("TRUNCATE TABLE riwayat_pengambilan")) {
        $_SESSION['success_message'] = "Semua riwayat pengambilan berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus semua riwayat.";
    }
}
else {
    $_SESSION['error_message'] = "Permintaan tidak valid.";
}

header("Location: riwayat_pengambilan.php");
exit();
?>
