<?php
// Memanggil config.php untuk memulai session
require_once 'includes/config.php';

// 1. Mengosongkan semua variabel session
$_SESSION = array();

// 2. Menghancurkan session
session_destroy();

// 3. Mengarahkan kembali ke halaman login dengan pesan sukses
header("location: login.php?status=logout");
exit;