<?php
// Mengatur header respons sebagai JSON
header('Content-Type: application/json');

require_once '../includes/config.php';

// Pengecekan login admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit();
}

// Mengambil data JSON yang dikirim dari JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (!verify_csrf_token($data['csrf_token'] ?? null)) {
    echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid.']);
    exit();
}

if (isset($data['order']) && is_array($data['order'])) {
    $slideOrder = $data['order'];

    $mysqli->begin_transaction();
    try {
        // Loop melalui array urutan baru dan update database
        foreach ($slideOrder as $index => $slideId) {
            $urutan_baru = $index;
            $id = (int)$slideId;

            $stmt = $mysqli->prepare("UPDATE slider_images SET urutan = ? WHERE id = ?");
            $stmt->bind_param("ii", $urutan_baru, $id);
            $stmt->execute();
        }
        $mysqli->commit();
        
        $_SESSION['success_message'] = "Urutan slider berhasil disimpan.";
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan urutan: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Data urutan tidak valid.']);
}
?>
