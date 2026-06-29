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
    header("Location: kelola_berita.php");
    exit();
}

require_valid_csrf();

// Cek apakah ID ada di request dan tidak kosong
if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id_berita = (int)$_POST['id'];

    // 1. Ambil nama file gambar dari database sebelum dihapus
    $stmt_select = $mysqli->prepare("SELECT gambar FROM berita WHERE id = ?");
    $stmt_select->bind_param("i", $id_berita);
    $stmt_select->execute();
    $stmt_select->store_result();
    $stmt_select->bind_result($nama_gambar);
    $stmt_select->fetch();
    $stmt_select->close();

    // 2. Siapkan statement DELETE untuk menghapus data dari database
    $stmt_delete = $mysqli->prepare("DELETE FROM berita WHERE id = ?");
    $stmt_delete->bind_param("i", $id_berita);

    // 3. Eksekusi statement delete
    if ($stmt_delete->execute()) {
        // 4. Jika data di database berhasil dihapus, hapus juga file gambarnya
        if ($nama_gambar && $nama_gambar != 'placeholder.png') {
            $file_path = '../assets/uploads/berita/' . $nama_gambar;
            if (file_exists($file_path)) {
                unlink($file_path); // Hapus file gambar dari server
            }
        }
        // Kirim pesan sukses
        $_SESSION['success_message'] = "Berita berhasil dihapus.";
    } else {
        // Jika gagal, kirim pesan error
        $_SESSION['error_message'] = "Gagal menghapus berita.";
    }
    $stmt_delete->close();
} else {
    // Jika tidak ada ID, kirim pesan error
    $_SESSION['error_message'] = "Permintaan tidak valid.";
}

// Arahkan kembali ke halaman kelola berita
header("Location: kelola_berita.php");
exit();
?>
