<?php
require_once '../includes/config.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['admin_id'])) {
    exit('Akses ditolak.');
}

// Query untuk mengambil data admin dan amil
$sql = "(SELECT username, nama_lengkap, 'Admin' as role FROM admin WHERE username IS NOT NULL AND username != '')
        UNION ALL
        (SELECT username, nama_lengkap, 'Amil' as role FROM amil WHERE username IS NOT NULL AND username != '')
        ORDER BY role, nama_lengkap";

$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// Buat spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Daftar User');

// Buat header tabel
$sheet->fromArray(['Username', 'Nama Lengkap', 'Role'], NULL, 'A1');

// Atur style untuk header
$headerStyle = $sheet->getStyle('A1:C1');
$headerStyle->getFont()->setBold(true);
$headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');

// Isi data dari database
$rowNum = 2;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sheet->fromArray([
            $row['username'],
            $row['nama_lengkap'],
            $row['role']
        ], NULL, 'A' . $rowNum);
        $rowNum++;
    }
}

// Atur lebar kolom otomatis
foreach (range('A', 'C') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Siapkan file untuk di-download
$filename = "daftar_user_admin_amil_" . date('Y-m-d') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>
