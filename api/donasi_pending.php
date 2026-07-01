<?php
/**
 * API: Donasi Pending (belum dikonfirmasi)
 * Dipanggil oleh wa-bot scheduler setiap pagi.
 */
require_once __DIR__ . '/../includes/config.php';

$secret = $_GET['secret'] ?? '';
if ($secret !== (getenv('BOT_SECRET') ?: 'RAHASIAPIXELYOGA')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$result = $mysqli->query("
    SELECT d.id_invoice as invoice, d.nominal, d.nama_donatur as nama,
           DATE_FORMAT(d.created_at, '%d/%m/%Y') as tanggal
    FROM donasi_base d
    WHERE d.status = 'menunggu_konfirmasi'
    ORDER BY d.created_at DESC
    LIMIT 20
");

$pending = [];
while ($row = $result->fetch_assoc()) {
    $pending[] = $row;
}

echo json_encode(['success' => true, 'pending' => $pending, 'total' => count($pending)]);
