<?php
require 'includes/config.php';

$tables = [
    'amil', 'berita', 'dokumentasi_kegiatan', 'donasi', 'kabar_program', 
    'komentar', 'kotak_infak', 'majalah', 'metode_pembayaran', 
    'riwayat_pengambilan', 'program', 'slider_images', 'laporan_transaksi', 'tugas_pengambilan'
];

foreach ($tables as $table) {
    $base_table = $table . "_base";
    
    // Check if base table already exists
    $res = $mysqli->query("SHOW TABLES LIKE '$base_table'");
    if ($res->num_rows == 0) {
        echo "Renaming $table to $base_table...\n";
        $mysqli->query("RENAME TABLE `$table` TO `$base_table`");
        
        echo "Creating view $table...\n";
        $mysqli->query("CREATE VIEW `$table` AS SELECT * FROM `$base_table` WHERE deleted_at IS NULL");
    } else {
        echo "$base_table already exists. Skipping...\n";
    }
}
echo "Done!\n";
