<?php
/**
 * API: Cek Status Donasi berdasarkan nomor Invoice
 * Dipanggil oleh wa-bot Node.js ketika user WhatsApp mengirim nomor invoice.
 */
require_once __DIR__ . '/../includes/config.php';

// Verifikasi token
$secret = $_GET['secret'] ?? $_POST['secret'] ?? '';
if ($secret !== (getenv('BOT_SECRET') ?: 'RAHASIAPIXELYOGA')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$invoice = strtoupper(trim($_GET['invoice'] ?? ''));
if (!$invoice || !preg_match('/^[A-Z0-9]{6,20}$/', $invoice)) {
    echo json_encode(['success' => false, 'message' => 'Invoice tidak valid.']);
    exit();
}

$stmt = $mysqli->prepare("
    SELECT d.id_invoice, d.nominal, d.status, d.nama_donatur, 
           DATE_FORMAT(d.created_at, '%d %M %Y %H:%i') as tanggal,
           p.nama_program as program
    FROM donasi_base d
    LEFT JOIN program_base p ON d.program_id = p.id
    WHERE d.id_invoice = ?
    LIMIT 1
");
$stmt->bind_param('s', $invoice);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Invoice tidak ditemukan.']);
    exit();
}

echo json_encode([
    'success'      => true,
    'invoice'      => $row['id_invoice'],
    'nama_donatur' => $row['nama_donatur'],
    'nominal'      => (int)$row['nominal'],
    'program'      => $row['program'] ?? 'Umum',
    'status'       => $row['status'],
    'tanggal'      => $row['tanggal'],
]);
