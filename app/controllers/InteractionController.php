<?php
namespace App\Controllers;

class InteractionController {
    public function submitPesan() {
        global $mysqli; require_once __DIR__ . '/../views/actions/submit_pesan.php';
    }

    public function prosesKomentar() {
        global $mysqli; require_once __DIR__ . '/../views/actions/proses_komentar.php';
    }
}
