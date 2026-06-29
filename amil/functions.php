<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_amil_login() {
    if (!isset($_SESSION['amil_logged_in']) || $_SESSION['amil_logged_in'] !== true) {
        header('Location: ../login.php');
        exit;
    }
}