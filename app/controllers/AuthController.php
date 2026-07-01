<?php
namespace App\Controllers;

class AuthController {
    public function login() {
        $page_title = "Masuk";
        global $mysqli; require_once __DIR__ . '/../views/auth/login.php';
    }

    public function register() {
        $page_title = "Daftar Akun";
        global $mysqli; require_once __DIR__ . '/../views/auth/register.php';
    }

    public function logout() {
        $_SESSION = array();
        session_destroy();
        header("Location: " . BASE_URL . "/login?status=logout");
        exit;
    }

    public function lupaSandi() {
        $page_title = "Lupa Kata Sandi";
        global $mysqli; require_once __DIR__ . '/../views/auth/lupa_sandi.php';
    }

    public function resetSandi() {
        $page_title = "Reset Kata Sandi";
        global $mysqli; require_once __DIR__ . '/../views/auth/reset_sandi.php';
    }
}
