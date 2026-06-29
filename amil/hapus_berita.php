<?php
require_once '../includes/config.php';

if (!isset($_SESSION['amil_id'], $_SESSION['amil_nama_lengkap'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Metode permintaan tidak valid.';
    header('Location: kelola_berita.php');
    exit();
}

require_valid_csrf();

$id_berita = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$penulis = $_SESSION['amil_nama_lengkap'];

if ($id_berita <= 0) {
    $_SESSION['error_message'] = 'Permintaan tidak valid.';
    header('Location: kelola_berita.php');
    exit();
}

$stmt_select = $mysqli->prepare("SELECT gambar FROM berita WHERE id = ? AND penulis = ? LIMIT 1");
$stmt_select->bind_param("is", $id_berita, $penulis);
$stmt_select->execute();
$result = $stmt_select->get_result();
$berita = $result->fetch_assoc();
$stmt_select->close();

if (!$berita) {
    $_SESSION['error_message'] = 'Berita tidak ditemukan atau bukan milik Anda.';
    header('Location: kelola_berita.php');
    exit();
}

$stmt_delete = $mysqli->prepare("DELETE FROM berita WHERE id = ? AND penulis = ?");
$stmt_delete->bind_param("is", $id_berita, $penulis);

if ($stmt_delete->execute() && $stmt_delete->affected_rows > 0) {
    if (!empty($berita['gambar']) && $berita['gambar'] !== 'placeholder.png') {
        $file_path = '../assets/uploads/berita/' . $berita['gambar'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    $_SESSION['success_message'] = 'Berita berhasil dihapus.';
} else {
    $_SESSION['error_message'] = 'Gagal menghapus berita.';
}

$stmt_delete->close();
header('Location: kelola_berita.php');
exit();
