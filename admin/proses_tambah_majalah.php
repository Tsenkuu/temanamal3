<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
include '../includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $link = $_POST['link_majalah'];

    // Validate URL
    if (filter_var($link, FILTER_VALIDATE_URL)) {
        $stmt = $mysqli->prepare("INSERT INTO majalah (judul, deskripsi, link) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $judul, $deskripsi, $link);
        if ($stmt->execute()) {
            header("Location: kelola_majalah.php");
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Link yang dimasukkan tidak valid.";
    }
}
$mysqli->close();
?>