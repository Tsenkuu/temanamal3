<?php
$hapusFiles = glob(__DIR__ . '/admin/hapus_*.php');

$table_map = [
    'dokumentasi' => 'dokumentasi_kegiatan',
    'pengambilan' => 'riwayat_pengambilan',
    'slider' => 'slider_images',
    'transaksi' => 'laporan_transaksi',
    'tugas' => 'tugas_pengambilan'
];

foreach ($hapusFiles as $file) {
    $content = file_get_contents($file);
    
    $name = basename($file, '.php');
    $table = str_replace('hapus_', '', $name);
    if (isset($table_map[$table])) {
        $table = $table_map[$table];
    }
    
    // Replace DELETE FROM table WHERE id = ? with UPDATE table SET deleted_at = NOW() WHERE id = ?
    $deletePattern = '/DELETE\s+FROM\s+`?' . preg_quote($table, '/') . '`?\s+WHERE\s+id\s*=\s*\?/i';
    $updateStmt = "UPDATE $table SET deleted_at = NOW() WHERE id = ?";
    
    $newContent = preg_replace($deletePattern, $updateStmt, $content);
    
    // Comment out unlink
    $newContent = preg_replace('/(\s+)unlink\(/', '$1// unlink(', $newContent);
    
    // Comment out file_exists if it's used in if (file_exists)
    // Actually just commenting out unlink is enough so it doesn't delete the file physically.
    
    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        echo "Updated $file\n";
    } else {
        echo "No changes or pattern not found in $file\n";
    }
}
