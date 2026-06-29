<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
include '../includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $link = $_POST['link_majalah'];

    // Validate URL
    if (filter_var($link, FILTER_VALIDATE_URL)) {
        $stmt = $mysqli->prepare("UPDATE majalah SET judul = ?, deskripsi = ?, link = ? WHERE id = ?");
        $stmt->bind_param("sssi", $judul, $deskripsi, $link, $id);
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