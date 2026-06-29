<?php
require_once '../includes/config.php';

// Pengecekan login Admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$id_berita = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_berita > 0) {
    $stmt = $mysqli->prepare("UPDATE berita SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $id_berita);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Berita telah ditolak.";
    }
    $stmt->close();
}

header('Location: kelola_berita.php');
exit();
?>
