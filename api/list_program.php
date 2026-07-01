<?php
/**
 * API: List Program Aktif
 * Dipanggil oleh wa-bot ketika user ketik #program
 */
require_once __DIR__ . '/../includes/config.php';

$secret = $_GET['secret'] ?? '';
if ($secret !== (getenv('BOT_SECRET') ?: 'RAHASIAPIXELYOGA')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$result = $mysqli->query("
    SELECT p.nama_program as nama, p.target_dana as target, 
           COALESCE(SUM(d.nominal), 0) as terkumpul
    FROM program_base p
    LEFT JOIN donasi_base d ON d.program_id = p.id AND d.status = 'terkonfirmasi'
    WHERE p.status_program = 'aktif'
    GROUP BY p.id
    ORDER BY p.urutan ASC, p.created_at DESC
    LIMIT 10
");

$programs = [];
while ($row = $result->fetch_assoc()) {
    $programs[] = [
        'nama'       => $row['nama'],
        'target'     => (int)$row['target'],
        'terkumpul'  => (int)$row['terkumpul'],
    ];
}

echo json_encode(['success' => true, 'programs' => $programs]);
