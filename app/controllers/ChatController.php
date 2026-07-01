<?php
namespace App\Controllers;

class ChatController {
    public function send() {
        global $mysqli; require_once __DIR__ . '/../views/chat/chat_send.php';
    }

    public function sendSecure() {
        global $mysqli; require_once __DIR__ . '/../views/chat/chat_send_secure.php';
    }

    public function fetch() {
        global $mysqli; require_once __DIR__ . '/../views/chat/chat_fetch.php';
    }
}
