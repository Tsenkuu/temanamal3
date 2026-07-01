<?php
// c:\xampp\htdocs\temanamal\chat_fetch.php
// FIX CONNECTION ERROR: Matikan output error agar JSON tidak rusak
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once 'includes/config.php';
require_once 'includes/chat_logger.php'; // Load Logger

try {
    // Ambil kode_user dari GET/POST
    $kode_user = $_GET['kode_user'] ?? $_POST['kode_user'] ?? null;

    if (!$kode_user) {
        echo json_encode(['success' => false, 'message' => 'kode_user tidak boleh kosong']);
        exit;
    }

    // 1. Ambil Pesan dari tabel pesan_user
    $stmt_msg = $mysqli->prepare("SELECT sender, message, created_at FROM pesan_user WHERE kode_user = ? ORDER BY created_at ASC");
    $stmt_msg->bind_param("s", $kode_user);
    $stmt_msg->execute();
    $msgs = $stmt_msg->get_result();

    $messages = [];
    while ($row = $msgs->fetch_assoc()) {
        $messages[] = [
            'sender' => $row['sender'],
            'message' => htmlspecialchars($row['message']),
            'created_at' => date('Y-m-d H:i:s', strtotime($row['created_at']))
        ];
    }

    // Set timezone ke Asia/Jakarta
    date_default_timezone_set('Asia/Jakarta');

        // Format waktu
        foreach ($messages as &$message) {
            $message['created_at'] = date('H:i', strtotime($message['created_at']));
        }

        echo json_encode(['success' => true, 'messages' => $messages]);
} catch (Exception $e) {
    chat_log("Fetch Error: " . $e->getMessage(), 'ERROR');
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat mengambil pesan.']);
}
?>
