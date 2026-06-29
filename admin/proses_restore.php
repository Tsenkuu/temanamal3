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
    
    // Pastikan tabel yang di-restore memiliki akhiran _base (keamanan)
    if (strpos($table, '_base') === false) {
        $_SESSION['error_message'] = "Tabel tidak valid.";
        header("Location: keranjang_sampah.php");
        exit();
    }

    $stmt = $mysqli->prepare("UPDATE `$table` SET deleted_at = NULL WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Data berhasil dikembalikan (di-restore).";
        } else {
            $_SESSION['error_message'] = "Gagal memulihkan data: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan pada query.";
    }
} else {
    $_SESSION['error_message'] = "ID atau Tabel tidak ditemukan.";
}

$redirect_table = isset($_POST['table']) ? '?table=' . urlencode($_POST['table']) : '';
header("Location: keranjang_sampah.php" . $redirect_table);
exit();
?>
