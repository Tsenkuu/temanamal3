<?php
// c:\xampp\htdocs\temanamal\includes\chat_logger.php

// Pastikan timezone sesuai
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Asia/Jakarta');
}

// Tentukan lokasi file log (folder logs di root project)
$logDir = dirname(__DIR__) . '/logs';
$logFile = $logDir . '/chat_system.log';

// Buat folder logs jika belum ada
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

/**
 * Mencatat log ke file khusus chat_system.log
 * @param string $message Pesan error atau info
 * @param string $level Level log (INFO, ERROR, WARNING, DEBUG)
 */
function chat_log($message, $level = 'INFO') {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $formattedMessage = "[$timestamp] [$level] $message" . PHP_EOL;
    // Tulis ke file (flag 3 = append)
    error_log($formattedMessage, 3, $logFile);
}
?>