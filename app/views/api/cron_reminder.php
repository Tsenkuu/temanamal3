<?php
// Script ini dijalankan oleh Cron Job Server
require_once 'includes/config.php';

// 1. Cek Donasi Expired
$now = date('Y-m-d H:i:s');
$mysqli->query("UPDATE donasi SET status = 'Dibatalkan' WHERE status = 'Menunggu Pembayaran' AND expired_at < '$now'");

// 2. Kirim Reminder (Maksimal 3x dalam 24 jam sebelum expired)
$query = "SELECT * FROM donasi 
          WHERE status = 'Menunggu Pembayaran' 
          AND expired_at > '$now' 
          AND (
              (reminder_sent = 0 AND created_at < DATE_SUB(NOW(), INTERVAL 3 HOUR))
              OR
              (reminder_sent = 1 AND created_at < DATE_SUB(NOW(), INTERVAL 10 HOUR))
              OR
              (reminder_sent = 2 AND created_at < DATE_SUB(NOW(), INTERVAL 20 HOUR))
          )";

$result = $mysqli->query($query);

if ($result) {
    while ($donasi = $result->fetch_assoc()) {
        $link_history = BASE_URL . "/history/" . $donasi['token'];
        
        $pesan = "🔔 *Reminder Donasi Lazismu*\n\n" .
                 "Assalamu'alaikum {$donasi['sapaan']} {$donasi['nama_donatur']},\n" .
                 "Kami mengingatkan tagihan donasi Anda sebesar *Rp " . number_format($donasi['total_transfer'], 0, ',', '.') . "* belum terbayar.\n\n" .
                 "Segera selesaikan sebelum waktu habis melalui link:\n" .
                 "{$link_history}\n\n" .
                 "Abaikan jika sudah membayar. Terima kasih.";

        // Kirim WA
        $response = kirimNotifikasiWA($donasi['kontak_donatur'], $pesan);
        
        // Update counter reminder
        if (isset($response['status']) && $response['status'] == true) { // Sesuaikan dengan respon API WA Anda
             $mysqli->query("UPDATE donasi SET reminder_sent = reminder_sent + 1 WHERE id = " . $donasi['id']);
        } else {
             // Fallback update jika API tidak return status standar, asumsikan terkirim agar tidak spam
             $mysqli->query("UPDATE donasi SET reminder_sent = reminder_sent + 1 WHERE id = " . $donasi['id']);
        }
        
        echo "Reminder sent to ID: " . $donasi['id'] . "<br>";
    }
}
?>
