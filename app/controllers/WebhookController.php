<?php
namespace App\Controllers;

class WebhookController {
    public function wa() {
        global $mysqli; require_once __DIR__ . '/../views/api/wa_webhook.php';
    }

    public function midtrans() {
        global $mysqli; require_once __DIR__ . '/../views/api/notification_handler.php';
    }

    public function cronReminder() {
        global $mysqli; require_once __DIR__ . '/../views/api/cron_reminder.php';
    }
}
