<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once 'includes/config.php';
require_once 'includes/chat_logger.php';

$mysqli->query("CREATE TABLE IF NOT EXISTS `pesan_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_user` varchar(50) NOT NULL,
  `sender` enum('user','admin') NOT NULL,
  `nomor_hp` varchar(20) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending',
  `direction` enum('inbound', 'outbound') NOT NULL,
  `metadata` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `kode_user` (`kode_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
    exit;
}

try {
    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, true);
    if (!$data) {
        $data = $_POST;
    }

    if (!verify_csrf_token($data['csrf_token'] ?? null)) {
        echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid.']);
        exit;
    }

    if (!rate_limit_request('chat_send_secure', 6, 120)) {
        echo json_encode(['success' => false, 'message' => 'Pesan terlalu sering dikirim.']);
        exit;
    }

    $nama = clean_text($data['nama'] ?? '', 100);
    $nomor_hp = normalize_phone_number($data['nomor_hp'] ?? '');
    $pesan = clean_multiline_text($data['pesan'] ?? '', 1000);

    if ($nomor_hp === '' || $pesan === '') {
        echo json_encode(['success' => false, 'message' => 'Nomor HP dan pesan wajib diisi.']);
        exit;
    }

    if (!validate_phone_number($nomor_hp)) {
        echo json_encode(['success' => false, 'message' => 'Nomor WhatsApp tidak valid.']);
        exit;
    }

    $stmt_dup = $mysqli->prepare("SELECT kode_user FROM pesan_user WHERE nomor_hp = ? AND message = ? AND created_at > (NOW() - INTERVAL 10 SECOND) LIMIT 1");
    $stmt_dup->bind_param("ss", $nomor_hp, $pesan);
    $stmt_dup->execute();
    $res_dup = $stmt_dup->get_result();
    if ($row_dup = $res_dup->fetch_assoc()) {
        echo json_encode(['success' => true, 'kode_user' => $row_dup['kode_user'], 'info' => 'duplicate_prevented']);
        exit;
    }
    $stmt_dup->close();

    $kode_user = null;
    if (!empty($data['kode_user']) && $data['kode_user'] !== 'null' && preg_match('/^[A-Za-z0-9_-]+$/', $data['kode_user'])) {
        $kode_user = $data['kode_user'];
    } else {
        $stmt = $mysqli->prepare("SELECT kode_user FROM pesan_user WHERE nomor_hp = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("s", $nomor_hp);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $kode_user = $row['kode_user'];
        }
        $stmt->close();
    }

    if (!$kode_user) {
        $kode_user = "user_" . time() . "_" . bin2hex(random_bytes(3));
    }

    $stmt_msg = $mysqli->prepare("INSERT INTO pesan_user (kode_user, sender, nama, nomor_hp, message, direction) VALUES (?, 'user', ?, ?, ?, 'inbound')");
    $stmt_msg->bind_param("ssss", $kode_user, $nama, $nomor_hp, $pesan);

    if (!$stmt_msg->execute()) {
        throw new Exception($stmt_msg->error);
    }
    $stmt_msg->close();

    $pesan_admin = "Chat Website Baru\n" .
        "ID: {$kode_user}\n" .
        "Nama: {$nama}\n" .
        "WA: {$nomor_hp}\n\n" .
        "Pesan:\n{$pesan}\n\n" .
        "--------------------------------\n" .
        "Balas cepat: *!jawab|{$kode_user} pesan*";

    if (defined('ADMIN_WA_NUMBER')) {
        kirimNotifikasiWA(ADMIN_WA_NUMBER, $pesan_admin);
    }

    echo json_encode(['success' => true, 'kode_user' => $kode_user]);
} catch (Exception $e) {
    chat_log("Error secure chat send: " . $e->getMessage(), 'ERROR');
    echo json_encode(['success' => false, 'message' => 'Gagal mengirim pesan.']);
}
