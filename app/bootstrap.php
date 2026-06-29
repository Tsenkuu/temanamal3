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

    // Ganti separator namespace dengan directory separator dan jadikan lowercase untuk foldernya (Case-Sensitive Linux)
    $parts = explode('\\', $relative_class);
    $class_name = array_pop($parts);
    $path = implode('/', array_map('strtolower', $parts));
    $file = $base_dir . ($path ? $path . '/' : '') . $class_name . '.php';

    // Jika file ada, require
    if (file_exists($file)) {
        require $file;
    }
});
