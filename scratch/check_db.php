<?php
require 'includes/config.php';
$mysqli->query("ALTER TABLE donasi ADD COLUMN doa TEXT NULL AFTER sapaan, ADD COLUMN anonim TINYINT(1) DEFAULT 0 AFTER doa");
echo "Done";
