<?php
// Mengatur header respons sebagai JSON, karena JavaScript akan membaca respons ini
header('Content-Type: application/json');

require_once '../includes/config.php';

// Pengecekan sesi admin untuk keamanan
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit();
}

// Inisialisasi respons default
$response = ['success' => false, 'message' => 'Permintaan tidak valid'];

// Memproses hanya jika ada data nomor dan pesan yang dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nomor']) && isset($_POST['pesan'])) {
    $nomor = $_POST['nomor'];
    $pesan_template = $_POST['pesan'];
    $gambar_url = null;

    // Proses upload gambar jika ada file yang dikirim
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../assets/uploads/blast/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $nama_file = time() . '_' . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $nama_file;
        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            // Dapatkan URL lengkap dari gambar untuk dikirim ke bot
            $gambar_url = BASE_URL . '/assets/uploads/blast/' . $nama_file;
        }
    }
    
    // Ambil detail user/donatur untuk personalisasi
    $nama_lengkap = "Donatur";
    $sapaan = "Bapak/Ibu";
    
    // Siapkan format nomor untuk pencarian
    $nomor_bersih = preg_replace('/[^\d]/', '', $nomor);
    $nomor_format_0 = '0' . substr($nomor_bersih, 2);

    // Prioritas 1: Cari di tabel user
    $stmt_user = $mysqli->prepare("SELECT nama_lengkap, sapaan FROM user WHERE no_telepon = ? OR no_telepon = ? LIMIT 1");
    $stmt_user->bind_param("ss", $nomor_bersih, $nomor_format_0);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($result_user && $result_user->num_rows > 0) {
        $user = $result_user->fetch_assoc();
        $nama_lengkap = $user['nama_lengkap'];
        $sapaan = $user['sapaan'] ?: "Bapak/Ibu";
    } else {
        // Prioritas 2: Jika tidak ada di user, cari di tabel donasi
        $stmt_donasi = $mysqli->prepare("SELECT nama_donatur FROM donasi WHERE kontak_donatur = ? OR kontak_donatur = ? ORDER BY created_at DESC LIMIT 1");
        $stmt_donasi->bind_param("ss", $nomor_bersih, $nomor_format_0);
        $stmt_donasi->execute();
        $result_donasi = $stmt_donasi->get_result();
        if ($result_donasi && $result_donasi->num_rows > 0) {
            $donatur = $result_donasi->fetch_assoc();
            // Ekstrak sapaan dari nama donatur jika ada
            $parts = explode(' ', $donatur['nama_donatur'], 2);
            if (count($parts) > 1 && in_array(strtolower($parts[0]), ['bapak', 'ibu', 'kak'])) {
                $sapaan = $parts[0];
                $nama_lengkap = $parts[1];
            } else {
                $nama_lengkap = $donatur['nama_donatur'];
            }
        }
    }
    $stmt_user->close();

    // Buat pesan yang dipersonalisasi dengan mengganti placeholder
    $pesan_pribadi = str_replace(['[nama]', '[sapaan]'], [$nama_lengkap, $sapaan], $pesan_template);

    // Kirim notifikasi
    $wa_response_json = kirimNotifikasiWA($nomor, $pesan_pribadi, $gambar_url);
    $wa_response = json_decode($wa_response_json, true);

    if ($wa_response && isset($wa_response['success']) && $wa_response['success']) {
        $response = ['success' => true, 'message' => 'Berhasil dikirim'];
    } else {
        $response = ['success' => false, 'message' => $wa_response['message'] ?? 'Gagal mengirim'];
    }
}

// Mengirimkan respons dalam format JSON kembali ke JavaScript
echo json_encode($response);
exit();
?>