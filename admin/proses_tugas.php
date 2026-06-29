<?php
// Memulai sesi untuk menyimpan pesan notifikasi (feedback)
session_start();

// Memuat file konfigurasi untuk koneksi database
require_once '../includes/config.php';

// (Opsional) Tambahkan pengecekan login admin di sini
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: login.php');
//     exit();
// }

// --- LOGIKA PEMROSESAN FORM ---

// 1. Proses Tambah Penugasan Baru untuk Kotak Infak
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_tugas'])) {
    $id_kotak_infak = $_POST['id_kotak_infak'];
    $id_amil = $_POST['id_amil'];
    $tanggal_tugas = $_POST['tanggal_tugas'];

    // Validasi dasar agar semua field terisi
    if (empty($id_kotak_infak) || empty($id_amil) || empty($tanggal_tugas)) {
        $_SESSION['error_message'] = "Semua kolom wajib diisi untuk menambah tugas.";
        header('Location: kelola_tugas.php'); // Arahkan kembali ke halaman kelola tugas
        exit();
    }

    // Insert ke tabel tugas_pengambilan menggunakan prepared statement
    $stmt = $mysqli->prepare("INSERT INTO tugas_pengambilan (id_kotak_infak, id_amil, tanggal_tugas, status) VALUES (?, ?, ?, 'Ditugaskan')");
    $stmt->bind_param("iis", $id_kotak_infak, $id_amil, $tanggal_tugas);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Tugas baru berhasil ditambahkan.";
    } else {
        $_SESSION['error_message'] = "Gagal menambah tugas: " . $stmt->error;
    }
    $stmt->close();
}

// 2. Proses Selesaikan Tugas Pengambilan Kotak Infak
else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selesaikan_tugas'])) {
    $id_tugas = $_POST['id_tugas'];
    $jumlah_terkumpul = $_POST['jumlah_terkumpul'];
    $catatan = htmlspecialchars($_POST['catatan']);

    // Validasi dasar
    if (empty($id_tugas) || !isset($jumlah_terkumpul)) {
        $_SESSION['error_message'] = "Data tidak lengkap untuk menyelesaikan tugas.";
        header('Location: kelola_tugas.php');
        exit();
    }

    // Ambil detail tugas dari tabel tugas_pengambilan
    $stmt_get_tugas = $mysqli->prepare("SELECT id_amil, id_kotak_infak FROM tugas_pengambilan WHERE id = ?");
    $stmt_get_tugas->bind_param("i", $id_tugas);
    $stmt_get_tugas->execute();
    $result_tugas = $stmt_get_tugas->get_result();
    
    if ($result_tugas->num_rows > 0) {
        $tugas = $result_tugas->fetch_assoc();
        $id_amil = $tugas['id_amil'];
        $id_kotak_infak = $tugas['id_kotak_infak'];
        $stmt_get_tugas->close();

        // Mulai transaksi untuk memastikan integritas data
        $mysqli->begin_transaction();

        try {
            // 1. Insert ke riwayat_pengambilan
            $stmt_riwayat = $mysqli->prepare("INSERT INTO riwayat_pengambilan (id_tugas, id_amil, id_kotak_infak, jumlah_terkumpul, tanggal_pengambilan, catatan) VALUES (?, ?, ?, ?, NOW(), ?)");
            $stmt_riwayat->bind_param("iiids", $id_tugas, $id_amil, $id_kotak_infak, $jumlah_terkumpul, $catatan);
            $stmt_riwayat->execute();
            $stmt_riwayat->close();

            // 2. Update status di tugas_pengambilan menjadi 'Selesai'
            $stmt_update_tugas = $mysqli->prepare("UPDATE tugas_pengambilan SET status = 'Selesai' WHERE id = ?");
            $stmt_update_tugas->bind_param("i", $id_tugas);
            $stmt_update_tugas->execute();
            $stmt_update_tugas->close();
            
            // Jika semua query berhasil, commit transaksi
            $mysqli->commit();
            $_SESSION['success_message'] = "Tugas berhasil diselesaikan dan dicatat dalam riwayat.";

        } catch (mysqli_sql_exception $exception) {
            // Jika ada error, rollback semua perubahan
            $mysqli->rollback();
            $_SESSION['error_message'] = "Gagal menyelesaikan tugas: " . $exception->getMessage();
        }

    } else {
        $_SESSION['error_message'] = "Tugas tidak ditemukan.";
        $stmt_get_tugas->close();
    }
}

// Jika file diakses tanpa metode POST yang sesuai, kembalikan ke halaman utama
header('Location: kelola_tugas.php');
exit();
?>