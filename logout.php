<?php
// 1. Memanggil file config.php untuk bisa mengakses dan memulai session.
require_once 'includes/config.php';

// 2. Mengosongkan semua variabel yang tersimpan di dalam session.
// Ini akan menghapus data seperti 'admin_logged_in', 'user_id', dll.
$_SESSION = array();

// 3. Menghancurkan session secara permanen.
// Langkah ini memastikan sesi benar-benar berakhir.
session_destroy();

// 4. Mengarahkan (redirect) pengguna kembali ke halaman login.
// Parameter ?status=logout ditambahkan agar halaman login bisa menampilkan pesan "Anda telah berhasil logout".
header("location: login.php?status=logout");
exit; // Menghentikan eksekusi script setelah redirect.