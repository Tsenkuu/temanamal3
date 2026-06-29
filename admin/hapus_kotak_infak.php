<?php
// Memuat file konfigurasi, yang seharusnya sudah memanggil session_start()
require_once '../includes/config.php';

// Pengecekan login admin (sangat direkomendasikan)
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Cek apakah ID ada di URL dan tidak kosong
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_kotak = (int)$_GET['id'];

    // Siapkan statement DELETE untuk keamanan
    $stmt = $mysqli->prepare("DELETE FROM kotak_infak WHERE id = ?");
    $stmt->bind_param("i", $id_kotak);

    // Eksekusi statement
    if ($stmt->execute()) {
        // Jika berhasil, kirim pesan sukses
        $_SESSION['success_message'] = "Data kotak infak berhasil dihapus.";
    } else {
        // Jika gagal, kirim pesan error
        $_SESSION['error_message'] = "Gagal menghapus data kotak infak.";
    }
    $stmt->close();
} else {
    // Jika tidak ada ID, kirim pesan error
    $_SESSION['error_message'] = "Permintaan tidak valid.";
}

// Arahkan kembali ke halaman kelola kotak infak
header("Location: kelola_kotak_infak.php");
exit();
?>