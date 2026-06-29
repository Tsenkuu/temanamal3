<?php
// Autoloader sederhana untuk kelas-kelas PhpSpreadsheet
spl_autoload_register(function ($class) {
    $prefix = 'PhpOffice\\PhpSpreadsheet\\';
    $base_dir = __DIR__ . '/libs/phpspreadsheet/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

require_once '../includes/config.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Keamanan: Hanya 'owner' yang bisa mengekspor
if (!isset($_SESSION['admin_id']) || (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] != 'owner')) {
    die("Akses ditolak. Anda tidak memiliki izin untuk melakukan tindakan ini.");
}

$export_type = $_GET['type'] ?? '';

// Tentukan data yang akan diekspor berdasarkan struktur lazismu2.sql
if ($export_type === 'admin') {
    $title = 'Daftar_Akun_Admin';
    // Kolom di tabel admin: id, username, password, nama_lengkap, role
    $sql = "SELECT nama_lengkap, username, role FROM admin ORDER BY nama_lengkap ASC";
} elseif ($export_type === 'amil') {
    $title = 'Daftar_Akun_Amil';
    // Kolom di tabel amil: id, nama_lengkap, jabatan, foto, username, password, no_telepon, status, dll.
    $sql = "SELECT nama_lengkap, jabatan, no_telepon, username FROM amil ORDER BY nama_lengkap ASC";
} else {
    die("Tipe ekspor tidak valid.");
}

$result = $mysqli->query($sql);
if (!$result) {
    die("Query ke database gagal: " . $mysqli->error);
}

// Ambil password default dari pengaturan
$result_pass = $mysqli->query("SELECT nilai_pengaturan FROM pengaturan WHERE nama_pengaturan = 'default_password'");
$default_password = $result_pass ? $result_pass->fetch_assoc()['nilai_pengaturan'] ?? 'lazismu125' : 'lazismu125';

// Buat objek Spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle(substr(str_replace('_', ' ', $title), 0, 31));

// Buat Header Tabel
if ($export_type === 'admin') {
    $sheet->setCellValue('A1', 'Nama Lengkap');
    $sheet->setCellValue('B1', 'Username');
    $sheet->setCellValue('C1', 'Peran');
    $sheet->setCellValue('D1', 'Password Default (untuk login pertama kali)');
} else { // Amil
    $sheet->setCellValue('A1', 'Nama Lengkap');
    $sheet->setCellValue('B1', 'Jabatan');
    $sheet->setCellValue('C1', 'No. Telepon');
    $sheet->setCellValue('D1', 'Username');
    $sheet->setCellValue('E1', 'Password Default (untuk login pertama kali)');
}

// Isi data ke dalam baris
$rowNumber = 2;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($export_type === 'admin') {
            $sheet->setCellValue('A' . $rowNumber, $row['nama_lengkap']);
            $sheet->setCellValue('B' . $rowNumber, $row['username']);
            $sheet->setCellValue('C' . $rowNumber, $row['role']);
            $sheet->setCellValue('D' . $rowNumber, $default_password);
        } else { // Amil
            $sheet->setCellValue('A' . $rowNumber, $row['nama_lengkap']);
            $sheet->setCellValue('B' . $rowNumber, $row['jabatan']);
            $sheet->setCellValue('C' . $rowNumber, $row['no_telepon']);
            $sheet->setCellValue('D' . $rowNumber, $row['username']);
            $sheet->setCellValue('E' . $rowNumber, $default_password);
        }
        $rowNumber++;
    }
}

// Atur lebar kolom secara otomatis
foreach (range('A', 'E') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Buat file dan kirim ke browser
$writer = new Xlsx($spreadsheet);
$filename = $title . "_" . date('Ymd') . ".xlsx";

// Kirim file ke browser
$writer->save($filename);

$result->free();
$mysqli->close();
exit();
?>

