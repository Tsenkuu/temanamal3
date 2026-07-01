<?php
namespace App\Controllers;

class PageController {
    public function tentangKami() {
        $page_title = "Tentang Kami";
        global $mysqli; require_once __DIR__ . '/../views/pages/tentang_kami.php';
    }

    public function personalia() {
        $page_title = "Susunan Personalia";
        global $mysqli; require_once __DIR__ . '/../views/pages/personalia.php';
    }

    public function kalkulatorZakat() {
        $page_title = "Kalkulator Zakat";
        global $mysqli; require_once __DIR__ . '/../views/pages/kalkulator_zakat.php';
    }

    public function search() {
        $page_title = "Hasil Pencarian";
        global $mysqli; require_once __DIR__ . '/../views/pages/search.php';
    }

    public function sitemap() {
        global $mysqli; require_once __DIR__ . '/../views/pages/sitemap.php';
    }

    public function terimaKasih() {
        $page_title = "Terima Kasih";
        global $mysqli; require_once __DIR__ . '/../views/pages/terima_kasih.php';
    }

    public function error() {
        $page_title = "Error";
        global $mysqli; require_once __DIR__ . '/../views/errors/error.php';
    }
}
