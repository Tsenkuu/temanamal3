<?php
// Memuat file konfigurasi untuk memulai sesi jika belum dimulai
require_once '../includes/config.php';

// Menghapus semua variabel sesi yang ada.
// Ini akan menghapus $_SESSION['admin_id'], $_SESSION['amil_id'], dan $_SESSION['user_id'].
$_SESSION = array();

// Menghancurkan sesi sepenuhnya.
session_destroy();

// Mengarahkan pengguna kembali ke halaman utama (index.php) setelah logout.
header("Location: " . BASE_URL . "/");
exit();
?>