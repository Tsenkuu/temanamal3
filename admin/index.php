<?php
// Memuat file konfigurasi untuk memulai sesi dan pengaturan lainnya
require_once '../includes/config.php';

// Pengecekan login admin (sangat direkomendasikan)
// Ini untuk memastikan hanya admin yang sudah login yang bisa mengakses area ini.
if (!isset($_SESSION['admin_id'])) {
    // Jika belum login, arahkan ke halaman login utama
    header('Location: ../login.php');
    exit();
}

// Arahkan secara otomatis ke file dashboard utama
// Ini akan menjadikan 'dashboard.php' sebagai halaman utama yang sebenarnya di area admin.
header('Location: dashboard.php');
exit();

?>