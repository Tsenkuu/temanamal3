<?php
require_once __DIR__ . '/../includes/config.php';

echo "Database connection successful!\n";

// List all tables
$result = $mysqli->query("SHOW TABLES");
echo "Tables in DB:\n";
while ($row = $result->fetch_array()) {
    echo "- " . $row[0] . "\n";
}
