<?php
require 'includes/config.php';

$mysqli->query("CREATE TABLE IF NOT EXISTS test_base (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), deleted_at DATETIME NULL)");
$mysqli->query("CREATE OR REPLACE VIEW test AS SELECT * FROM test_base WHERE deleted_at IS NULL");

$mysqli->query("INSERT INTO test (name) VALUES ('hello')");
$res = $mysqli->query("SELECT * FROM test");
print_r($res->fetch_assoc());

$mysqli->query("UPDATE test SET name = 'world' WHERE id = 1");
$res = $mysqli->query("SELECT * FROM test");
print_r($res->fetch_assoc());

$mysqli->query("DROP VIEW test");
$mysqli->query("DROP TABLE test_base");
