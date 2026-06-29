<?php
$mysqli = new mysqli('localhost', 'root', '', 'temanamal');
$result = $mysqli->query("SHOW COLUMNS FROM berita");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}
