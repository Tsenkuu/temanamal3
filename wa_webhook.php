<?php
// wa_webhook.php
// Endpoint ini menerima data dari index.js
error_reporting(E_ALL);
ini_set('display_errors', 0);
require_once 'includes/config.php';
require_once 'includes/chat_logger.php'; 

// Ambil input JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// [DEBUG] Log raw input
if ($input) {
    chat_log("[Webhook] Raw: " . $input, 'DEBUG');
}

if (isset($data['message']) && isset($data['sender'])) {
    $pesan = trim($data['message']);
    $sender = $data['sender']; // Nomor Admin (format 628xxx)

    // Pastikan sender dalam format angka saja
    $sender = preg_replace('/[^0-9]/', '', $sender);

    chat_log("[Webhook] Received from $sender: $pesan", 'INFO');

    try {
        // Cek koneksi DB
        if (!isset($mysqli) || $mysqli->connect_error) {
            throw new Exception("Database connection error");
        }

        // Regex fleksibel:
        // !jawab [spasi/|] [kode_user] [spasi/enter] [pesan]
        // Contoh: !jawab|user123 pesan
        // Contoh: !jawab user123 pesan
        if (preg_match('/^!jawab\s*[\|]?\s*(\S+)\s+(.+)$/is', $pesan, $matches)) {
            $kode_user = trim($matches[1]);
            $jawaban = trim($matches[2]);

            // 1. Cari User Database
            $stmt = $mysqli->prepare("SELECT nomor_hp FROM pesan_user WHERE kode_user = ? ORDER BY id DESC LIMIT 1");
            if (!$stmt) throw new Exception("DB Prepare Error: " . $mysqli->error);
            
            $stmt->bind_param("s", $kode_user);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $nomor_user = $row['nomor_hp'];
                
                // 2. Simpan Balasan ke DB
                $stmt_ins = $mysqli->prepare("INSERT INTO pesan_user (kode_user, sender, message, direction) VALUES (?, 'admin', ?, 'outbound')");
                if ($stmt_ins) {
                    $stmt_ins->bind_param("ss", $kode_user, $jawaban);
                    $stmt_ins->execute();
                }

                // 3. Kirim ke User via WA
                $pesan_ke_user = "💬 *Balasan Admin:*\n\n{$jawaban}\n\n_Lazismu Bot_";
                $wa_result = kirimNotifikasiWA($nomor_user, $pesan_ke_user);

                // 4. Lapor Balik ke Admin
                if (isset($wa_result['success']) && $wa_result['success']) {
                    kirimNotifikasiWA($sender, "✅ Sukses kirim ke *{$kode_user}*");
                    chat_log("[Webhook] Success reply to $kode_user", 'INFO');
                } else {
                    $err = $wa_result['message'] ?? 'Unknown Error';
                    kirimNotifikasiWA($sender, "❌ Gagal kirim ke WA User. API Error: $err");
                    chat_log("[Webhook] Failed sending to WA: $err", 'ERROR');
                }

            } else {
                kirimNotifikasiWA($sender, "❌ Kode user *{$kode_user}* tidak ditemukan di database.");
            }

        } elseif (stripos($pesan, '!jawab') === 0) {
            // Catch format salah
            kirimNotifikasiWA($sender, "⚠️ *Format Salah*\n\nGunakan format:\n`!jawab|kode_user pesan`\n\nContoh:\n`!jawab|user_123 Halo kak`");
        }
    } catch (Exception $e) {
        // Tangkap error code/db dan lapor ke admin
        chat_log("[Webhook] Exception: " . $e->getMessage(), 'ERROR');
        kirimNotifikasiWA($sender, "❌ *System Error*: " . $e->getMessage());
    }
} else {
    chat_log("[Webhook] Invalid Payload (No message/sender)", 'WARNING');
}

// Selalu return 200 OK ke Node.js
http_response_code(200);
echo json_encode(['status' => 'processed']);
?>