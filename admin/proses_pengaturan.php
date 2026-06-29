<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Proses simpan pengaturan umum
if (isset($_POST['simpan_umum'])) {
    $total_disalurkan = preg_replace('/[^\d]/', '', $_POST['total_donasi_disalurkan']);
    $admin_wa_number = preg_replace('/[^\d]/', '', $_POST['admin_wa_number']);

    $stmt1 = $mysqli->prepare("INSERT INTO pengaturan (nama_pengaturan, nilai_pengaturan) VALUES ('total_donasi_disalurkan', ?) ON DUPLICATE KEY UPDATE nilai_pengaturan = ?");
    $stmt1->bind_param("ss", $total_disalurkan, $total_disalurkan);
    $stmt1->execute();

    $stmt2 = $mysqli->prepare("INSERT INTO pengaturan (nama_pengaturan, nilai_pengaturan) VALUES ('admin_wa_number', ?) ON DUPLICATE KEY UPDATE nilai_pengaturan = ?");
    $stmt2->bind_param("ss", $admin_wa_number, $admin_wa_number);
    $stmt2->execute();

    $_SESSION['success_message'] = "Pengaturan berhasil disimpan.";
    header("Location: pengaturan.php");
    exit();
}

// Proses reset bot
if (isset($_POST['reset_bot'])) {
    // Panggil endpoint /reset menggunakan fungsi helper
    $response = callWhatsappAPI('/reset', 'POST');

    if ($response && $response['success']) {
        // Setelah reset berhasil, coba polling /qr beberapa detik untuk mendapatkan QR langsung
        $qr_found = false;
        $qr_data = null;
        // Polling selama 15 detik
        for ($i = 0; $i < 30; $i++) { 
            usleep(500000); // 0.5s
            $qr_response = callWhatsappAPI('/qr', 'GET');
            if (isset($qr_response['success']) && $qr_response['success'] && !empty($qr_response['data']['qr'])) {
                $qr_found = true;
                $qr_data = $qr_response['data']['qr'];
                break;
            }
        }

        $_SESSION['success_message'] = "Sesi bot berhasil direset.";
        if ($qr_found) {
            // QR data sudah dalam format data URL, tidak perlu disimpan di session
            // Halaman pengaturan akan mengambilnya via AJAX
            $_SESSION['success_message'] .= " Memuat QR baru...";
        } else {
            $_SESSION['success_message'] .= " Gagal memuat QR secara otomatis. Coba segarkan halaman.";
        }
    } else {
        $error_msg = $response['message'] ?? 'Pastikan aplikasi bot sedang berjalan.';
        $_SESSION['error_message'] = "Gagal mereset sesi bot. " . $error_msg;
    }
    header("Location: pengaturan.php");
    exit();
}
?> <?php require_once 'templates/footer_admin.php'; ?>