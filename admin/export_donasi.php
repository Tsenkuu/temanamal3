<?php
require_once '../includes/config.php';
require_once '../vendor/autoload.php'; // pastikan path vendor benar

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['admin_id'])) {
    exit('Akses ditolak. Silakan login terlebih dahulu.');
}

$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'Semua';

$sql = "SELECT d.sapaan, d.nama_donatur, d.nominal, d.created_at, COALESCE(p.kategori, 'Infak') as kategori_donasi
        FROM donasi d
        LEFT JOIN program p ON d.id_program = p.id
        WHERE d.status = 'Selesai'";
if ($kategori != 'Semua') {
    $sql .= " AND COALESCE(p.kategori, 'Infak') = ?";
}
$sql .= " ORDER BY d.created_at DESC";

$stmt = $mysqli->prepare($sql);
if ($kategori != 'Semua') {
    $stmt->bind_param("s", $kategori);
}
$stmt->execute();
$result = $stmt->get_result();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray(['Tanggal', 'Sapaan', 'Nama Donatur', 'Nominal', 'Kategori'], NULL, 'A1');

$rowNum = 2;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sheet->fromArray([
            date('d-m-Y', strtotime($row['created_at'])),
            $row['sapaan'],
            $row['nama_donatur'],
            number_format($row['nominal'], 0, ',', '.'),
            $row['kategori_donasi']
        ], NULL, 'A' . $rowNum);
        $rowNum++;
    }
}

$filename = "laporan_donasi_" . strtolower($kategori) . "_" . date('Y-m-d') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>