<?php

function app_is_local_environment(): bool
{
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

    return in_array($serverName, ['localhost', '127.0.0.1'], true)
        || in_array($remoteAddr, ['127.0.0.1', '::1'], true);
}

function app_bootstrap_security(): void
{
    $isLocal = app_is_local_environment();

    ini_set('display_errors', $isLocal ? '1' : '0');
    ini_set('display_startup_errors', $isLocal ? '1' : '0');
    error_reporting(E_ALL);

    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    app_send_security_headers();
}

function app_send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf_token(?string $token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function require_valid_csrf(): void
{
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        http_response_code(419);
        exit('Permintaan tidak valid. Silakan muat ulang halaman dan coba lagi.');
    }
}

function rate_limit_request(string $key, int $maxAttempts, int $windowSeconds): bool
{
    $now = time();
    $bucket = $_SESSION['rate_limit'][$key] ?? [];

    $bucket = array_values(array_filter($bucket, static function ($timestamp) use ($now, $windowSeconds) {
        return ($now - (int) $timestamp) < $windowSeconds;
    }));

    if (count($bucket) >= $maxAttempts) {
        $_SESSION['rate_limit'][$key] = $bucket;
        return false;
    }

    $bucket[] = $now;
    $_SESSION['rate_limit'][$key] = $bucket;
    return true;
}

function clean_text(?string $value, int $maxLength = 255): string
{
    $value = trim((string) $value);
    $value = preg_replace('/\s+/u', ' ', $value) ?? '';

    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength);
    }

    return substr($value, 0, $maxLength);
}

function clean_multiline_text(?string $value, int $maxLength = 5000): string
{
    $value = trim((string) $value);
    $value = preg_replace("/\r\n|\r/u", "\n", $value) ?? '';

    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength);
    }

    return substr($value, 0, $maxLength);
}

function normalize_phone_number(?string $value): string
{
    $digits = preg_replace('/\D+/', '', (string) $value) ?? '';

    if ($digits === '') {
        return '';
    }

    if (strpos($digits, '62') === 0) {
        return $digits;
    }

    if ($digits[0] === '0') {
        return '62' . substr($digits, 1);
    }

    if ($digits[0] === '8') {
        return '62' . $digits;
    }

    return $digits;
}

function validate_phone_number(string $phone, int $minDigits = 10, int $maxDigits = 15): bool
{
    $length = strlen($phone);
    return $length >= $minDigits && $length <= $maxDigits && ctype_digit($phone);
}

function secure_upload_file(array $file, string $targetDirectory, array $allowedMimeMap, int $maxBytes = 5242880): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload file gagal.');
    }

    if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > $maxBytes) {
        throw new RuntimeException('Ukuran file tidak valid.');
    }

    $tmpName = $file['tmp_name'] ?? '';
    if (!is_uploaded_file($tmpName)) {
        throw new RuntimeException('Sumber file upload tidak valid.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $tmpName) : false;
    if ($finfo) {
        finfo_close($finfo);
    }

    if (!$mime || !isset($allowedMimeMap[$mime])) {
        throw new RuntimeException('Tipe file tidak diizinkan.');
    }

    if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
        throw new RuntimeException('Folder upload tidak dapat dibuat.');
    }

    if (!is_writable($targetDirectory)) {
        throw new RuntimeException('Folder upload tidak bisa ditulisi.');
    }

    $extension = $allowedMimeMap[$mime];
    $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = rtrim($targetDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($tmpName, $destination)) {
        throw new RuntimeException('File gagal disimpan.');
    }

    return [
        'filename' => $fileName,
        'path' => $destination,
        'mime' => $mime,
    ];
}

