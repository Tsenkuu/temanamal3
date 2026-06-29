<?php
/**
 * Migration 002: Create Views
 * Mengganti 14 tabel utama menjadi _base dan membuat view untuk soft delete.
 */

$tables = [
    'amil', 'berita', 'dokumentasi_kegiatan', 'donasi', 'kabar_program', 
    'komentar', 'kotak_infak', 'majalah', 'metode_pembayaran', 
    'riwayat_pengambilan', 'program', 'slider_images', 'laporan_transaksi', 'tugas_pengambilan'
];

foreach ($tables as $table) {
    $base_table = $table . "_base";
    
    // Check if base table already exists
    $res = $mysqli->query("SHOW TABLES LIKE '$base_table'");
    if ($res && $res->num_rows == 0) {
        if (!$mysqli->query("RENAME TABLE `$table` TO `$base_table`")) {
            throw new Exception("Gagal merename tabel $table: " . $mysqli->error);
        }
        
        if (!$mysqli->query("CREATE VIEW `$table` AS SELECT * FROM `$base_table` WHERE deleted_at IS NULL")) {
            throw new Exception("Gagal membuat view $table: " . $mysqli->error);
        }
    }
}
