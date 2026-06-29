<?php
// get_history.php
require_once 'includes/config.php';

// 1. Generate Fingerprint (Sama seperti di proses_donasi.php)
$fingerprint = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);

// 2. Query Database
$stmt = $mysqli->prepare("
    SELECT d.*, p.nama_program 
    FROM donasi d 
    LEFT JOIN program p ON d.id_program = p.id 
    WHERE d.fingerprint = ? 
    ORDER BY d.created_at DESC 
    LIMIT 20
");
$stmt->bind_param("s", $fingerprint);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $statusClass = 'pending';
        $statusLabel = $row['status'];
        
        if ($row['status'] == 'Selesai') {
            $statusClass = 'paid';
        } elseif ($row['status'] == 'Dibatalkan') {
            $statusClass = 'expired';
        }

        $nama_program = $row['nama_program'] ?? 'Donasi Umum';
        $tanggal = date('d M Y H:i', strtotime($row['created_at']));
        $nominal = number_format($row['total_transfer'], 0, ',', '.');

        echo "
        <div class='history-item {$statusClass}'>
            <div class='history-date'>{$tanggal}</div>
            <div class='history-title'>{$nama_program}</div>
            <div class='flex justify-between items-center mt-1'>
                <div class='history-amount'>Rp {$nominal}</div>
                <div class='history-status status-{$statusClass}'>{$statusLabel}</div>
            </div>
            <a href='history/{$row['token']}' class='text-xs text-blue-500 hover:underline mt-1 block'>Lihat Detail &rarr;</a>
        </div>
        ";
    }
} else {
    echo "
    <div class='text-center py-8 text-gray-500'>
        <i class='bi bi-inbox text-4xl mb-2 block'></i>
        <p>Belum ada riwayat donasi di perangkat ini.</p>
    </div>
    ";
}
?>
