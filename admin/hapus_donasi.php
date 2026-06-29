<?php
require_once '../includes/config.php';

// Pengecekan login admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Fungsi untuk menghapus satu donasi dan filenya
function hapusSatuDonasi($mysqli, $id) {
    // Ambil nama file bukti sebelum dihapus
    $stmt_select = $mysqli->prepare("SELECT bukti_pembayaran FROM donasi WHERE id = ?");
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $donasi = $result->fetch_assoc();
    $stmt_select->close();

    // Hapus dari database
    $stmt_delete = $mysqli->prepare("DELETE FROM donasi WHERE id = ?");
    $stmt_delete->bind_param("i", $id);
    if ($stmt_delete->execute()) {
        // Jika berhasil, hapus file bukti jika ada
        if ($donasi && !empty($donasi['bukti_pembayaran'])) {
            $file_path = '../assets/uploads/bukti/' . $donasi['bukti_pembayaran'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        return true;
    }
    return false;
}

// Logika untuk menghapus banyak donasi yang dipilih
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hapus_dipilih'])) {
    if (!empty($_POST['donasi_ids']) && is_array($_POST['donasi_ids'])) {
        $jumlah_dihapus = 0;
        foreach ($_POST['donasi_ids'] as $id) {
            if (hapusSatuDonasi($mysqli, (int)$id)) {
                $jumlah_dihapus++;
            }
        }
        if ($jumlah_dihapus > 0) {
            $_SESSION['success_message'] = "Berhasil menghapus $jumlah_dihapus riwayat donasi.";
        } else {
            $_SESSION['error_message'] = "Tidak ada donasi yang dipilih atau gagal menghapus.";
        }
    } else {
        $_SESSION['error_message'] = "Tidak ada donasi yang dipilih untuk dihapus.";
    }
} 
// Logika untuk menghapus satu donasi (dari link)
elseif (isset($_GET['id']) && !empty($_GET['id'])) {
    if (hapusSatuDonasi($mysqli, (int)$_GET['id'])) {
        $_SESSION['success_message'] = "Satu riwayat donasi berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus riwayat donasi.";
    }
} 
else {
    $_SESSION['error_message'] = "Permintaan tidak valid.";
}

header("Location: riwayat_donasi.php");
exit();
?>