<?php
require_once __DIR__ . '/includes/config.php';
$res = $mysqli->query('SHOW TABLES');
$tables = [];
while ($row = $res->fetch_row()) {
    $tables[] = $row[0];
}

$hapusFiles = glob(__DIR__ . '/admin/hapus_*.php');
$hapusTables = [];
foreach ($hapusFiles as $file) {
    $name = basename($file, '.php');
    $table = str_replace('hapus_', '', $name);
    $hapusTables[] = $table;
}

echo "Tables in DB:\n";
print_r($tables);

echo "\nTables from hapus_*.php:\n";
print_r($hapusTables);

echo "\nMissing Tables:\n";
print_r(array_diff($hapusTables, $tables));
