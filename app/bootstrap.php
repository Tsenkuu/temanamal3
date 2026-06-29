<?php
// Autoloader sederhana untuk namespace App\
spl_autoload_register(function ($class) {
    // Prefix namespace proyek
    $prefix = 'App\\';
    // Base directory untuk namespace prefix
    $base_dir = __DIR__ . '/';

    // Apakah class menggunakan namespace prefix ini?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Ambil nama class tanpa prefix
    $relative_class = substr($class, $len);

    // Ganti separator namespace dengan directory separator
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Jika file ada, require
    if (file_exists($file)) {
        require $file;
    }
});
