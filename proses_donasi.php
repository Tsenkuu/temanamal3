<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_valid_csrf();

    if (!rate_limit_request('donation_submit', 6, 300)) {
        http_response_code(429);
        $error_code = '429';
        $error_title = 'Terlalu Banyak Permintaan';
        $error_message = 'Anda telah melakukan terlalu banyak percobaan donasi. Silakan tunggu sebentar sebelum mencoba lagi.';
        include __DIR__ . '/includes/templates/error.php';
        exit();
    }

    // 1. Ambil & Sanitasi Data
    $sapaan = clean_text($_POST['sapaan'] ?? '', 20);
    $allowedSapaan = ['Bapak', 'Ibu', 'Kak'];
    if (!in_array($sapaan, $allowedSapaan, true)) {
        $sapaan = 'Bapak';
    }

    $nama_donatur = clean_text($_POST['nama_donatur'] ?? '', 120);
    $nama_lengkap_donatur = $sapaan . ' ' . $nama_donatur;
    $kontak_donatur = normalize_phone_number($_POST['kontak_donatur'] ?? '');
    
    $nominal = (float) preg_replace('/[^\d]/', '', (string) ($_POST['nominal'] ?? ''));
    $id_program = !empty($_POST['id_program']) && is_numeric($_POST['id_program']) ? (int)$_POST['id_program'] : NULL;
    $metode_pembayaran_id = isset($_POST['metode_pembayaran_id']) ? (int) $_POST['metode_pembayaran_id'] : 0;

    if ($nama_donatur === '' || !validate_phone_number($kontak_donatur) || $nominal < 1000 || $metode_pembayaran_id <= 0) {
        http_response_code(422);
        $error_code = '422';
        $error_title = 'Data Tidak Valid';
        $error_message = 'Data donasi yang Anda kirim belum lengkap atau tidak valid. Pastikan semua form terisi dengan benar.';
        include __DIR__ . '/includes/templates/error.php';
        exit();
    }
    
    // 2. Generate Data Sistem
    $kode_unik = 0;
    $total_transfer = $nominal;
    $invoice_id = 'LZM' . date('ymd') . rand(1000, 9999);
    
    // Token Unik (untuk URL History)
    $token = bin2hex(random_bytes(16)); 
    
    // Fingerprint Device (Browser + IP)
    $fingerprint = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
    
    // Expired dalam 24 Jam
    $expired_at = date('Y-m-d H:i:s', strtotime('+1 day'));

    // Pesan Doa dan Anonim
    $doa = clean_text($_POST['doa'] ?? '', 500);
    $is_anonim = isset($_POST['is_anonim']) ? 1 : 0;

    // 3. Simpan ke Database
    $stmt = $mysqli->prepare("INSERT INTO donasi (invoice_id, token, fingerprint, nama_donatur, sapaan, kontak_donatur, doa, anonim, id_program, nominal, kode_unik, total_transfer, metode_pembayaran_id, expired_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu Pembayaran')");
    
    $stmt->bind_param("sssssssiididis", $invoice_id, $token, $fingerprint, $nama_lengkap_donatur, $sapaan, $kontak_donatur, $doa, $is_anonim, $id_program, $nominal, $kode_unik, $total_transfer, $metode_pembayaran_id, $expired_at);
    
    if ($stmt->execute()) {
        // 4. Ambil Detail Program & Metode untuk Notifikasi
        $nama_program = "Donasi Umum";
        if ($id_program) {
            $stmt_program = $mysqli->prepare("SELECT nama_program FROM program WHERE id = ? LIMIT 1");
            $stmt_program->bind_param("i", $id_program);
            $stmt_program->execute();
            $prog = $stmt_program->get_result()->fetch_assoc();
            $stmt_program->close();
            if ($prog) {
                $nama_program = $prog['nama_program'];
            }
        }

        $stmt_metode = $mysqli->prepare("SELECT nama_metode, detail_1, detail_2 FROM metode_pembayaran WHERE id = ? LIMIT 1");
        $stmt_metode->bind_param("i", $metode_pembayaran_id);
        $stmt_metode->execute();
        $metode = $stmt_metode->get_result()->fetch_assoc();
        $stmt_metode->close();

        if (!$metode) {
            http_response_code(422);
            exit('Metode pembayaran tidak ditemukan.');
        }

        $info_pembayaran = $metode['nama_metode'] . "\n" . $metode['detail_1'] . " a.n " . $metode['detail_2'];

        // 5. Kirim Notifikasi WhatsApp
        $link_history = BASE_URL . "/history/" . $token;
        $pesan = "Assalamu'alaikum *{$sapaan} {$nama_donatur}*,\n\n" .
                 "Terima kasih atas niat baik Anda berdonasi untuk program:\n" .
                 "_{$nama_program}_\n\n" .
                 "Mohon selesaikan pembayaran sebesar:\n" .
                 "*Rp " . number_format($total_transfer, 0, ',', '.') . "*\n\n" .
                 "Melalui:\n" .
                 "{$info_pembayaran}\n\n" .
                 "Lihat detail & upload bukti transfer disini:\n" .
                 "{$link_history}\n\n" .
                 "Batas waktu: " . date('d M Y H:i', strtotime($expired_at)) . "\n\n" .
                 "_Lazismu Tulungagung_";

        kirimNotifikasiWA($kontak_donatur, $pesan);

        // 6. Redirect ke Halaman History
        header("Location: " . $link_history);
        exit();
    } else {
        error_log("Gagal menyimpan donasi: " . $stmt->error);
        exit('Terjadi kendala saat memproses donasi. Silakan coba lagi.');
    }
}
?>
