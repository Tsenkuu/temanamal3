<?php
require_once '../includes/config.php';

if (!isset($_SESSION['amil_id'])) {
    header('Location: ../login.php');
    exit();
}
$id_amil_login = $_SESSION['amil_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Metode permintaan tidak valid.";
    header("Location: riwayat_pengambilan.php");
    exit();
}

require_valid_csrf();

if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id_riwayat = (int)$_POST['id'];

    // Hapus riwayat HANYA jika milik amil yang sedang login
    $stmt = $mysqli->prepare("DELETE FROM riwayat_pengambilan WHERE id = ? AND id_amil = ?");
    $stmt->bind_param("ii", $id_riwayat, $id_amil_login);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success_message'] = "Riwayat pengambilan berhasil dihapus.";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus riwayat (mungkin bukan milik Anda).";
        }
    } else {
        $_SESSION['error_message'] = "Gagal menghapus riwayat.";
    }
    $stmt->close();
} else {
    $_SESSION['error_message'] = "Permintaan tidak valid.";
}

header("Location: riwayat_pengambilan.php");
exit();
?>
