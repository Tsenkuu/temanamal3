<?php
namespace App\Controllers;

class UploadController {
    public function ckeditor() {
        global $mysqli; require_once __DIR__ . '/../views/api/upload_ckeditor.php';
    }

    public function cek() {
        global $mysqli; require_once __DIR__ . '/../views/api/cek_upload.php';
    }
}
