<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
$phpFiles = [];
foreach ($files as $file) {
    if ($file->getExtension() === 'php' && strpos($file->getPathname(), 'vendor') === false) {
        $phpFiles[] = $file->getPathname();
    }
}

$tables = [
    'amil', 'berita', 'dokumentasi_kegiatan', 'donasi', 'kabar_program', 
    'komentar', 'kotak_infak', 'majalah', 'metode_pembayaran', 
    'riwayat_pengambilan', 'program', 'slider_images', 'laporan_transaksi', 'tugas_pengambilan'
];

$table_regex = implode('|', $tables);

foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    
    // We only care about SELECT queries targeting our tables.
    // E.g., SELECT ... FROM program
    // We don't want to break existing WHERE clauses, so we need to be careful.
    // Let's just print them out first to see what we're dealing with.
    
    if (preg_match_all("/SELECT\s+(.*?)\s+FROM\s+(`?($table_regex)`?)\b/is", $content, $matches)) {
        echo basename($file) . " has " . count($matches[0]) . " SELECTs for tables.\n";
    }
}
