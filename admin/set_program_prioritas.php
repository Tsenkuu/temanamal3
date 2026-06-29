<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_program.php');
    exit();
}

require_valid_csrf();

$id_program = (int) ($_POST['id_program'] ?? 0);
$mode = $_POST['mode'] ?? 'set';

if ($id_program <= 0) {
    $_SESSION['error_message'] = 'Program tidak valid.';
    header('Location: kelola_program.php');
    exit();
}

$stmt_program = $mysqli->prepare("SELECT id FROM program WHERE id = ? LIMIT 1");
if (!$stmt_program) {
    $_SESSION['error_message'] = 'Gagal memeriksa program.';
    header('Location: kelola_program.php');
    exit();
}

$stmt_program->bind_param("i", $id_program);
$stmt_program->execute();
$program_result = $stmt_program->get_result();
$program_exists = $program_result && $program_result->fetch_assoc();
$stmt_program->close();

if (!$program_exists) {
    $_SESSION['error_message'] = 'Program tidak ditemukan.';
    header('Location: kelola_program.php');
    exit();
}

$new_value = $mode === 'unset' ? '0' : (string) $id_program;
$stmt_setting = $mysqli->prepare("
    INSERT INTO pengaturan (nama_pengaturan, nilai_pengaturan)
    VALUES ('program_prioritas_beranda', ?)
    ON DUPLICATE KEY UPDATE nilai_pengaturan = VALUES(nilai_pengaturan)
");

if (!$stmt_setting) {
    $_SESSION['error_message'] = 'Gagal menyimpan program prioritas.';
    header('Location: kelola_program.php');
    exit();
}

$stmt_setting->bind_param("s", $new_value);

if ($stmt_setting->execute()) {
    $_SESSION['success_message'] = $mode === 'unset'
        ? 'Program prioritas beranda berhasil dilepas.'
        : 'Program prioritas beranda berhasil diperbarui.';
} else {
    $_SESSION['error_message'] = 'Program prioritas beranda gagal diperbarui.';
}

$stmt_setting->close();

header('Location: kelola_program.php');
exit();
