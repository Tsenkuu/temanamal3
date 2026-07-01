<?php
namespace App\Controllers;

class MagazineController {
    public function index() {
        $page_title = "Majalah & Publikasi";
        global $mysqli; require_once __DIR__ . '/../views/magazine/majalah.php';
    }

    public function read() {
        $page_title = "Baca Majalah";
        global $mysqli; require_once __DIR__ . '/../views/magazine/baca_majalah.php';
    }

    public function laporan() {
        $page_title = "Laporan Penyaluran";
        global $mysqli; require_once __DIR__ . '/../views/magazine/laporan.php';
    }
}
