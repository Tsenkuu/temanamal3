<?php
require_once '../includes/config.php';
require_once 'functions.php';
check_amil_login();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_tugas = (int)$_POST['id_tugas'];
    $jumlah_terkumpul = $_POST['jumlah_terkumpul'];
    $catatan = $_POST['catatan'];
    $id_amil = $_SESSION['amil_id'];

    // Ambil id_kotak_infak dari tugas
    $stmt_tugas = $mysqli->prepare("SELECT id_kotak_infak FROM tugas_pengambilan WHERE id = ?");
    $stmt_tugas->bind_param("i", $id_tugas);
    $stmt_tugas->execute();
    $result_tugas = $stmt_tugas->get_result();
    $tugas = $result_tugas->fetch_assoc();
    $id_kotak_infak = $tugas['id_kotak_infak'];
    $stmt_tugas->close();

    // 1. Masukkan ke riwayat
    $stmt_riwayat = $mysqli->prepare("INSERT INTO riwayat_pengambilan (id_tugas, id_amil, id_kotak_infak, jumlah_terkumpul, catatan) VALUES (?, ?, ?, ?, ?)");
    $stmt_riwayat->bind_param("iiids", $id_tugas, $id_amil, $id_kotak_infak, $jumlah_terkumpul, $catatan);
    $stmt_riwayat->execute();
    $stmt_riwayat->close();

    // 2. Update status tugas menjadi 'Selesai'
    $stmt_update = $mysqli->prepare("UPDATE tugas_pengambilan SET status = 'Selesai' WHERE id = ?");
    $stmt_update->bind_param("i", $id_tugas);
    $stmt_update->execute();
    $stmt_update->close();

    header("Location: tugas_saya.php");
    exit;
}