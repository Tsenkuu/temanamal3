<?php
// File: admin/proses_tambah_berita.php (Contoh)
session_start();
require_once '../includes/config.php'; // Sesuaikan path ke file koneksi

// Pastikan pengguna sudah login
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once 'templates/header_admin.php';
// ... (kode untuk validasi input dan upload gambar) ...

$judul = $_POST['judul'];
$konten = $_POST['konten'];
$gambar = 'nama_file_gambar.jpg'; // Nama file dari proses upload

// Tentukan status berdasarkan peran pengguna (role)
// Asumsi: $_SESSION['user_role'] berisi 'admin' atau 'amil'
$status = 'pending'; // Status default untuk amil
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $status = 'published'; // Admin bisa langsung menerbitkan
}

// Gunakan prepared statement untuk keamanan
$stmt = $mysqli->prepare("INSERT INTO berita (judul, konten, gambar, status) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $judul, $konten, $gambar, $status);

if ($stmt->execute()) {
    // Berhasil
    header("Location: dashboard.php?status=sukses");
} else {
    // Gagal
    header("Location: tambah_berita.php?status=gagal");
}

$stmt->close();
$mysqli->close();
?>