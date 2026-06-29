<?php
// Memuat file konfigurasi, yang seharusnya sudah memanggil session_start()
require_once '../includes/config.php';

// Pengecekan login admin (sangat direkomendasikan)
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Metode permintaan tidak valid.";
    header("Location: kelola_amil.php");
    exit();
}

require_valid_csrf();

// Cek apakah ID ada di request dan tidak kosong
if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id_amil = (int)$_POST['id'];

    // Siapkan statement DELETE untuk menghapus data dari database
    $stmt_delete = $mysqli->prepare("UPDATE amil SET deleted_at = NOW() WHERE id = ?");
    $stmt_delete->bind_param("i", $id_amil);

    // Eksekusi statement delete
    if ($stmt_delete->execute()) {
        // Kirim pesan sukses
        $_SESSION['success_message'] = "Data amil berhasil dihapus.";
    } else {
        // Jika gagal, kirim pesan error
        $_SESSION['error_message'] = "Gagal menghapus data amil.";
    }
    $stmt_delete->close();
} else {
    // Jika tidak ada ID, kirim pesan error
    $_SESSION['error_message'] = "Permintaan tidak valid.";
}

// Arahkan kembali ke halaman kelola amil
header("Location: kelola_amil.php");
exit();
?>
