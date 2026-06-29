<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Ambil semua data lokasi kotak infak
    $sql = "SELECT id, nama_lokasi, alamat, latitude, longitude, penanggung_jawab, terakhir_diambil FROM kotak_infak WHERE status = 'aktif'";
    $result = $mysqli->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);

} elseif ($method === 'POST') {
    // Cek aksi yang diminta (tambah baru atau update status)
    if (isset($_POST['action']) && $_POST['action'] == 'update_status') {
        // Aksi: Tandai sudah diambil
        $id = $_POST['id'];
        $today = date('Y-m-d');
        
        $stmt = $mysqli->prepare("UPDATE kotak_infak SET terakhir_diambil = ? WHERE id = ?");
        $stmt->bind_param("si", $today, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
        $stmt->close();

    } else {
        // Aksi: Tambah lokasi baru
        $nama_lokasi = $_POST['nama_lokasi'];
        $alamat = $_POST['alamat'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        $penanggung_jawab = $_POST['penanggung_jawab'];

        if (empty($nama_lokasi) || empty($latitude) || empty($longitude)) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
            exit;
        }

        $stmt = $mysqli->prepare("INSERT INTO kotak_infak (nama_lokasi, alamat, latitude, longitude, penanggung_jawab) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nama_lokasi, $alamat, $latitude, $longitude, $penanggung_jawab);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
        $stmt->close();
    }
}

$mysqli->close();
?>