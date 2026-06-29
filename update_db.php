<?php
require_once 'includes/config.php';

$messages = [];

// 1. Modifikasi tabel berita untuk menambahkan ENUM 'kajian'
$sql_alter = "ALTER TABLE berita MODIFY COLUMN type ENUM('berita', 'opini', 'kajian') NOT NULL DEFAULT 'berita'";
if ($mysqli->query($sql_alter)) {
    $messages[] = "Sukses: Kolom 'type' pada tabel 'berita' berhasil diperbarui.";
} else {
    $messages[] = "Error Alter Table: " . $mysqli->error;
}

// 2. Buat tabel kabar_program
$sql_create = "CREATE TABLE IF NOT EXISTS kabar_program (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_program INT NOT NULL,
    judul_kabar VARCHAR(255) NOT NULL,
    konten_kabar TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_program) REFERENCES program(id) ON DELETE CASCADE
)";
if ($mysqli->query($sql_create)) {
    $messages[] = "Sukses: Tabel 'kabar_program' berhasil dibuat/sudah ada.";
} else {
    $messages[] = "Error Create Table: " . $mysqli->error;
}

foreach ($messages as $msg) {
    echo $msg . "\n";
}
