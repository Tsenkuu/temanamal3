<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = 'Metode permintaan tidak valid.';
    $_SESSION['message_type'] = 'error';
    header("Location: kelola_komentar.php");
    exit();
}

require_valid_csrf();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id > 0) {
    $stmt = $mysqli->prepare("DELETE FROM komentar WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        $_SESSION['message'] = 'Komentar berhasil dihapus.';
        $_SESSION['message_type'] = 'success';
    }
    $stmt->close();
}

header("Location: kelola_komentar.php");
exit();
?>
