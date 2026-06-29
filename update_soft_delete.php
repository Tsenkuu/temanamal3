<?php
require_once __DIR__ . '/includes/config.php';

$tables = [
    'amil', 'berita', 'dokumentasi_kegiatan', 'donasi', 'kabar_program', 
    'komentar', 'kotak_infak', 'majalah', 'metode_pembayaran', 
    'riwayat_pengambilan', 'program', 'slider_images', 'laporan_transaksi', 'tugas_pengambilan'
];

echo "Memulai migrasi Soft Delete...<br>";

foreach ($tables as $table) {
    // Cek apakah kolom deleted_at sudah ada
    $check_query = "SHOW COLUMNS FROM `$table` LIKE 'deleted_at'";
    $result = $mysqli->query($check_query);
    
    if ($result->num_rows == 0) {
        // Tambahkan kolom
        $alter_query = "ALTER TABLE `$table` ADD `deleted_at` DATETIME NULL DEFAULT NULL";
        if ($mysqli->query($alter_query)) {
            echo "Sukses menambahkan kolom 'deleted_at' pada tabel <b>$table</b>.<br>";
        } else {
            echo "Gagal menambahkan kolom pada tabel <b>$table</b>: " . $mysqli->error . "<br>";
        }
    } else {
        echo "Kolom 'deleted_at' sudah ada pada tabel <b>$table</b>. (Lewati)<br>";
    }
}

echo "<br><b>Selesai!</b>";
?>
