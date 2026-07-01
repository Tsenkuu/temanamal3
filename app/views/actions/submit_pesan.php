<?php
// submit_pesan.php
header('Content-Type: application/json');
require_once 'includes/config.php'; // <-- Added this line

// 1. Buat Tabel Jika Belum Ada (Auto Setup)
// This part is fine, but it's better to have a separate migration script.
// For now, we'll leave it.
$mysqli->query("CREATE TABLE IF NOT EXISTS `pesan_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_user` varchar(50) NOT NULL,
  `sender` enum('user','admin') NOT NULL DEFAULT 'user',
  `nomor_hp` varchar(20) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending',
  `direction` enum('inbound', 'outbound') NOT NULL DEFAULT 'inbound',
  `metadata` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `kode_user` (`kode_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid.']);
        exit;
    }

    if (!rate_limit_request('submit_pesan', 5, 180)) {
        echo json_encode(['success' => false, 'message' => 'Terlalu banyak pesan. Coba lagi sebentar.']);
        exit;
    }

    $nomor_hp_raw = $_POST['nomor_hp'] ?? '';
    $pesan_user = clean_multiline_text($_POST['pesan'] ?? '', 1000);

    if (empty($nomor_hp_raw) || empty($pesan_user)) {
        echo json_encode(['success' => false, 'message' => 'Nomor HP dan pesan tidak boleh kosong.']);
        exit;
    }

    // Format nomor HP ke standar 62
    $nomor_hp = normalize_phone_number($nomor_hp_raw);
    if (!validate_phone_number($nomor_hp)) {
        echo json_encode(['success' => false, 'message' => 'Nomor HP tidak valid.']);
        exit;
    }

    // --- LOGIC TO FIND OR CREATE KODE_USER ---
    $kode_user = null;
    // Cek apakah sudah ada kode_user untuk nomor HP ini
    $stmt_find = $mysqli->prepare("SELECT kode_user FROM pesan_user WHERE nomor_hp = ? LIMIT 1");
    $stmt_find->bind_param("s", $nomor_hp);
    $stmt_find->execute();
    $result = $stmt_find->get_result();
    if ($row = $result->fetch_assoc()) {
        // Gunakan kode_user yang sudah ada
        $kode_user = $row['kode_user'];
    } else {
        // Buat kode_user baru jika tidak ditemukan
        $kode_user = "user_" . time() . "_" . bin2hex(random_bytes(4));
    }
    $stmt_find->close();
    // --- END LOGIC ---

    // 2. Simpan ke Database
    $stmt_insert = $mysqli->prepare("INSERT INTO pesan_user (kode_user, nomor_hp, message, sender, direction) VALUES (?, ?, ?, 'user', 'inbound')");
    $stmt_insert->bind_param("sss", $kode_user, $nomor_hp, $pesan_user);
    
    if ($stmt_insert->execute()) {
        // 3. Kirim Notifikasi ke Admin via WA
        $pesan_admin = "📩 *Pesan Baru [{$kode_user}]*\n\n" .
                       "No HP: {$nomor_hp}\n" .
                       "Pesan:\n_{$pesan_user}_\n\n" .
                       "-------------------\n" .
                       "Balas dengan format:\n" .
                       "*!jawab|{$kode_user} isi_jawaban*";
        
        // Menggunakan fungsi dari config.php
        kirimNotifikasiWA(ADMIN_WA_NUMBER, $pesan_admin);

        echo json_encode(['success' => true, 'message' => 'Pesan berhasil dikirim.', 'kode_user' => $kode_user]);
    } else {
        // Log error if possible
        error_log("Gagal menyimpan pesan ke DB: " . $stmt_insert->error);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pesan.']);
    }
    $stmt_insert->close();
}
?>
