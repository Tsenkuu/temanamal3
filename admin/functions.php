<?php
// Memastikan session telah dimulai. Ini adalah safeguard jika file config.php tidak dipanggil lebih dulu.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fungsi untuk memeriksa apakah admin sudah login.
 * Fungsi ini akan dipanggil di setiap halaman admin untuk proteksi.
 * Jika session 'admin_logged_in' tidak ada atau tidak bernilai true,
 * maka pengguna akan secara otomatis diarahkan kembali ke halaman login.
 */
function check_admin_login() {
    // Cek apakah session login ada dan bernilai true
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // Simpan pesan error ke dalam session agar bisa ditampilkan di halaman login
        $_SESSION['error_message'] = "Anda harus login untuk mengakses halaman ini.";
        
        // Redirect ke halaman login. Path '../login.php' digunakan karena file ini
        // dipanggil dari dalam folder /admin.
        header('Location: ../login.php');
        exit; // Menghentikan eksekusi script setelah redirect
    }
}

/**
 * (Contoh fungsi lain yang bisa ditambahkan nanti)
 * Fungsi untuk membersihkan input dari pengguna untuk mencegah XSS.
 * * @param string $data Input yang akan dibersihkan.
 * @return string Input yang sudah bersih.
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>