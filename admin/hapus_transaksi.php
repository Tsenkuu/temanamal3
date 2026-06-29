<?php
require_once '../includes/config.php';

// Pengecekan login admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Cek apakah ada parameter ID untuk menghapus satu data
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_transaksi = (int)$_GET['id'];
    $stmt = $mysqli->prepare("DELETE FROM laporan_transaksi WHERE id = ?");
    $stmt->bind_param("i", $id_transaksi);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Satu data transaksi berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus data transaksi.";
    }
    $stmt->close();
} 
// Cek apakah ada parameter PERIODE dan JENIS untuk menghapus semua data dalam satu grup
else if (isset($_GET['periode']) && !empty($_GET['periode']) && isset($_GET['jenis']) && !empty($_GET['jenis'])) {
    $periode = $_GET['periode'];
    $jenis = $_GET['jenis'];
    $stmt = $mysqli->prepare("DELETE FROM laporan_transaksi WHERE periode_laporan = ? AND jenis_laporan = ?");
    $stmt->bind_param("ss", $periode, $jenis);
    if ($stmt->execute()) {
        $jumlah_terhapus = $stmt->affected_rows;
        $_SESSION['success_message'] = "Berhasil menghapus semua ($jumlah_terhapus) data untuk laporan $jenis periode " . date('F Y', strtotime($periode)) . ".";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus data untuk grup yang dipilih.";
    }
    $stmt->close();
}
else {
    $_SESSION['error_message'] = "Permintaan tidak valid.";
}

header("Location: kelola_laporan.php");
exit();
?>