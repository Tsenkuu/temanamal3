<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: kelola_tugas.php'); // Redirect ke halaman asal
    exit();
}

// Hapus satu tugas berdasarkan ID
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_tugas = (int)$_GET['id'];
    $stmt = $mysqli->prepare("DELETE FROM tugas_pengambilan WHERE id = ?");
    $stmt->bind_param("i", $id_tugas);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Satu tugas berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus tugas.";
    }
    $stmt->close();
} 
// Hapus semua tugas
elseif (isset($_GET['hapus']) && $_GET['hapus'] == 'semua') {
    if ($mysqli->query("TRUNCATE TABLE tugas_pengambilan")) {
        $_SESSION['success_message'] = "Semua riwayat tugas berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus semua riwayat tugas.";
    }
}
// Hapus tugas berdasarkan periode bulan dan tahun
elseif (isset($_GET['hapus']) && $_GET['hapus'] == 'periode' && isset($_GET['bulan']) && isset($_GET['tahun'])) {
    $bulan = $_GET['bulan'];
    $tahun = $_GET['tahun'];
    $stmt = $mysqli->prepare("DELETE FROM tugas_pengambilan WHERE MONTH(tanggal_tugas) = ? AND YEAR(tanggal_tugas) = ?");
    $stmt->bind_param("ss", $bulan, $tahun);
    if ($stmt->execute()) {
        $jumlah_terhapus = $stmt->affected_rows;
        $_SESSION['success_message'] = "Berhasil menghapus $jumlah_terhapus tugas untuk periode " . date('F Y', mktime(0,0,0,$bulan,1,$tahun)) . ".";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus tugas untuk periode yang dipilih.";
    }
    $stmt->close();
}
else {
    $_SESSION['error_message'] = "Permintaan tidak valid.";
}

header("Location: kelola_tugas.php");
exit();
?>