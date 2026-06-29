<?php
/**
 * Migration 001: Setup Soft Delete
 * Menambahkan kolom deleted_at ke 14 tabel utama.
 */

$tables = [
    'amil', 'berita', 'dokumentasi_kegiatan', 'donasi', 'kabar_program', 
    'komentar', 'kotak_infak', 'majalah', 'metode_pembayaran', 
    'riwayat_pengambilan', 'program', 'slider_images', 'laporan_transaksi', 'tugas_pengambilan'
];

foreach ($tables as $table) {
    // Cek apakah tabel sudah berupa _base (jika create_views.php pernah dijalankan)
    $check_base = $mysqli->query("SHOW TABLES LIKE '{$table}_base'");
    $actual_table = ($check_base && $check_base->num_rows > 0) ? "{$table}_base" : $table;

    // Cek apakah kolom deleted_at sudah ada
    $check_query = "SHOW COLUMNS FROM `$actual_table` LIKE 'deleted_at'";
    $result = $mysqli->query($check_query);
    
    if ($result && $result->num_rows == 0) {
        // Tambahkan kolom
        $alter_query = "ALTER TABLE `$actual_table` ADD `deleted_at` DATETIME NULL DEFAULT NULL";
        if (!$mysqli->query($alter_query)) {
            throw new Exception("Gagal menambahkan kolom deleted_at pada tabel $actual_table: " . $mysqli->error);
        }
    }
}
