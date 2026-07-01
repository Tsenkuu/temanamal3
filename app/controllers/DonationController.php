<?php
namespace App\Controllers;

class DonationController {
    public function history($token = null) {
        if ($token) {
            $_GET['token'] = $token; // Inject for compatibility with old view
        }
        $page_title = "Riwayat Pembayaran";
        global $mysqli; require_once __DIR__ . '/../views/donation/history.php';
    }

    public function getHistory() {
        // API Endpoint for fetching history JSON
        global $mysqli; require_once __DIR__ . '/../views/donation/get_history.php';
    }

    public function konfirmasiPembayaran() {
        $page_title = "Konfirmasi Pembayaran";
        global $mysqli; require_once __DIR__ . '/../views/donation/konfirmasi_pembayaran.php';
    }

    public function konfirmasiDonasi() {
        $page_title = "Konfirmasi Donasi";
        global $mysqli; require_once __DIR__ . '/../views/donation/konfirmasi_donasi.php';
    }

    public function prosesDonasi() {
        global $mysqli; require_once __DIR__ . '/../views/donation/proses_donasi.php';
    }

    public function uploadBukti() {
        global $mysqli; require_once __DIR__ . '/../views/donation/upload_bukti.php';
    }
}
