<?php
/**
 * Auto-Migration Runner
 * Mengeksekusi file migrasi database secara otomatis.
 */

function run_auto_migrations($mysqli) {
    // Pastikan tabel _migrations_log ada
    $check_table = $mysqli->query("SHOW TABLES LIKE '_migrations_log'");
    if ($check_table && $check_table->num_rows == 0) {
        $mysqli->query("
            CREATE TABLE `_migrations_log` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `migration_name` VARCHAR(255) NOT NULL UNIQUE,
                `executed_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    // Ambil daftar migrasi yang sudah dieksekusi
    $applied_migrations = [];
    $result = $mysqli->query("SELECT migration_name FROM `_migrations_log`");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $applied_migrations[] = $row['migration_name'];
        }
    }

    $migrations_dir = __DIR__ . '/../database/migrations/';
    if (!is_dir($migrations_dir)) {
        return; // Direktori tidak ditemukan, abaikan
    }

    $files = scandir($migrations_dir);
    sort($files); // Urutkan berdasarkan abjad (001_, 002_, dst)

    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
            continue;
        }

        $migration_name = basename($file, '.php');

        if (!in_array($migration_name, $applied_migrations)) {
            // Eksekusi skrip migrasi
            // Skrip migrasi harus menggunakan variabel $mysqli yang ada
            try {
                require_once $migrations_dir . $file;
                
                // Jika sukses, catat ke log
                $stmt = $mysqli->prepare("INSERT INTO `_migrations_log` (migration_name) VALUES (?)");
                if ($stmt) {
                    $stmt->bind_param("s", $migration_name);
                    $stmt->execute();
                    $stmt->close();
                }
                error_log("Migration ran successfully: " . $migration_name);
            } catch (Exception $e) {
                error_log("Migration failed: " . $migration_name . " - " . $e->getMessage());
                // Jangan hentikan aplikasi sepenuhnya, biarkan berjalan tapi log error-nya
            }
        }
    }
}

// Jalankan otomatis
run_auto_migrations($mysqli);
