<?php
require_once 'includes/config.php'; // Pastikan ada $mysqli

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['bukti_pembayaran']) && isset($_POST['invoice_id'])) {
    require_valid_csrf();

    if (!rate_limit_request('upload_bukti', 5, 300)) {
        http_response_code(429);
        exit("Terlalu banyak upload. Silakan tunggu sebentar.");
    }

    $invoice_id = clean_text($_POST['invoice_id'] ?? '', 40);
    $file = $_FILES['bukti_pembayaran'];

    if (!preg_match('/^[A-Z0-9]+$/', $invoice_id)) {
        exit("Invoice tidak valid.");
    }

    try {
        $upload = secure_upload_file(
            $file,
            __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'bukti',
            [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'application/pdf' => 'pdf',
            ],
            5 * 1024 * 1024
        );
        $nama_file = $upload['filename'];

        // Simpan ke database
        $stmt = $mysqli->prepare("UPDATE donasi SET bukti_pembayaran = ?, status = 'Menunggu Konfirmasi' WHERE invoice_id = ?");
        if (!$stmt) {
            die("Query error: " . $mysqli->error);
        }
        $stmt->bind_param("ss", $nama_file, $invoice_id);
        $stmt->execute();
        $stmt->close();

        // Ambil detail donasi
        $stmt_get = $mysqli->prepare("SELECT nama_donatur, nominal FROM donasi WHERE invoice_id = ?");
        $stmt_get->bind_param("s", $invoice_id);
        $stmt_get->execute();
        $donasi = $stmt_get->get_result()->fetch_assoc();
        $stmt_get->close();

        if ($donasi) {
            $pesan_notifikasi = "🔔 *Konfirmasi Donasi Baru*\n\n" .
                "*Invoice ID:* {$invoice_id}\n" .
                "*Nama Donatur:* {$donasi['nama_donatur']}\n" .
                "*Nominal:* Rp " . number_format($donasi['nominal'], 0, ',', '.') . "\n\n" .
                "Silakan cek bukti pembayaran di halaman admin.";
            kirimNotifikasiWA(ADMIN_WA_NUMBER, $pesan_notifikasi);
        }

        unset($_SESSION['last_invoice_id']);
        header('Location: terima_kasih.php');
        exit();
    } catch (RuntimeException $exception) {
        error_log("Upload bukti gagal: " . $exception->getMessage());
        exit("Maaf, bukti pembayaran gagal diunggah. Pastikan file berupa JPG, PNG, WEBP, atau PDF maksimal 5 MB.");
    }
} else {
    header('Location: donasi.php');
    exit();
}
