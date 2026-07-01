<?php
/**
 * API: Laporan Harian Donasi
 * Dipanggil oleh wa-bot Node.js untuk scheduler laporan harian.
 */
require_once __DIR__ . '/../includes/config.php';

// Verifikasi token
$secret = $_GET['secret'] ?? $_POST['secret'] ?? '';
if ($secret !== (getenv('BOT_SECRET') ?: 'RAHASIAPIXELYOGA')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$tanggal = date('d F Y');
$today   = date('Y-m-d');

// Total donasi hari ini
$stmt = $mysqli->prepare("SELECT COUNT(*) as total, SUM(nominal) as nominal FROM donasi_base WHERE DATE(created_at) = ? AND status = 'terkonfirmasi'");
$stmt->bind_param('s', $today);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$total_donasi  = (int)($row['total']  ?? 0);
$total_nominal = (int)($row['nominal'] ?? 0);

// Donatur unik
$stmt2 = $mysqli->prepare("SELECT COUNT(DISTINCT nomor_hp) as unik FROM donasi_base WHERE DATE(created_at) = ? AND status = 'terkonfirmasi'");
$stmt2->bind_param('s', $today);
$stmt2->execute();
$total_donatur_unik = (int)($stmt2->get_result()->fetch_assoc()['unik'] ?? 0);

// Top program
$stmt3 = $mysqli->prepare("
    SELECT p.nama_program as nama, SUM(d.nominal) as total
    FROM donasi_base d
    JOIN program_base p ON d.program_id = p.id
    WHERE DATE(d.created_at) = ? AND d.status = 'terkonfirmasi'
    GROUP BY d.program_id
    ORDER BY total DESC
    LIMIT 5
");
$stmt3->bind_param('s', $today);
$stmt3->execute();
$top_result = $stmt3->get_result();
$top_program = [];
while ($r = $top_result->fetch_assoc()) {
    $top_program[] = ['nama' => $r['nama'], 'total' => (int)$r['total']];
}

echo json_encode([
    'success'            => true,
    'tanggal'            => $tanggal,
    'total_donasi'       => $total_donasi,
    'total_nominal'      => $total_nominal,
    'total_donatur_unik' => $total_donatur_unik,
    'top_program'        => $top_program,
]);
