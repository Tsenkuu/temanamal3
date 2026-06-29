<?php
require_once '../includes/config.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit();
}

// Pastikan metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Metode tidak valid.']);
    exit();
}

// Ambil data JSON yang dikirim dari JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (!verify_csrf_token($data['csrf_token'] ?? null)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid.']);
    exit();
}

if (isset($data['order']) && is_array($data['order'])) {
    $order = $data['order'];
    $mysqli->begin_transaction();

    try {
        // Siapkan statement untuk update
        $stmt = $mysqli->prepare("UPDATE amil SET urutan = ? WHERE id = ?");

        foreach ($order as $index => $id) {
            $urutan_baru = $index + 1;
            $id_amil = (int)$id;
            $stmt->bind_param("ii", $urutan_baru, $id_amil);
            $stmt->execute();
        }

        $stmt->close();
        $mysqli->commit();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Urutan berhasil diperbarui.']);

    } catch (mysqli_sql_exception $exception) {
        $mysqli->rollback();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui database: ' . $exception->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Data urutan tidak valid.']);
}
?>
