<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    // Cari program_id untuk redirect
    $stmt_find = $mysqli->prepare("SELECT id_program FROM kabar_program WHERE id = ?");
    $stmt_find->bind_param("i", $id);
    $stmt_find->execute();
    $kabar = $stmt_find->get_result()->fetch_assoc();
    $stmt_find->close();

    if ($kabar) {
        $program_id = $kabar['id_program'];
        $stmt_delete = $mysqli->prepare("UPDATE kabar_program SET deleted_at = NOW() WHERE id = ?");
        $stmt_delete->bind_param("i", $id);
        
        if ($stmt_delete->execute()) {
            $_SESSION['success_message'] = "Kabar program berhasil dihapus.";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus kabar: " . $mysqli->error;
        }
        $stmt_delete->close();
        
        header("Location: kelola_kabar_program.php?id=" . $program_id);
        exit();
    }
}

header("Location: kelola_program.php");
exit();
