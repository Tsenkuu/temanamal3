<?php
require_once '../includes/config.php';
// (Tambahkan logika otentikasi dan otorisasi di sini)
// Pengecekan login admin (sangat direkomendasikan)
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Sertakan autoloader dari Composer
// Pastikan Anda sudah menjalankan `composer require phpoffice/phpspreadsheet`
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['upload'])) {
    $file_mimes = array('application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    if (isset($_FILES['file_excel']['name']) && in_array($_FILES['file_excel']['type'], $file_mimes)) {
        
        $file_tmp = $_FILES['file_excel']['tmp_name'];
        
        try {
            $spreadsheet = IOFactory::load($file_tmp);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $berhasil = 0;
            $gagal = 0;

            // Mulai dari baris ke-2 untuk melewati header
            for ($i = 2; $i <= count($sheetData); $i++) {
                $nama_donatur = $sheetData[$i]['A'];
                $jumlah = $sheetData[$i]['B'];
                $tanggal_donasi = $sheetData[$i]['C'];
                $keterangan = $sheetData[$i]['D'];

                // Validasi dasar
                if (!empty($nama_donatur) && is_numeric($jumlah) && !empty($tanggal_donasi)) {
                    // Coba konversi tanggal
                    try {
                        $tanggal_db = new DateTime($tanggal_donasi);
                        $tanggal_donasi_formatted = $tanggal_db->format('Y-m-d');

                        $stmt = $mysqli->prepare("INSERT INTO donasi (nama_donatur, jumlah, tanggal_donasi, keterangan) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("sdss", $nama_donatur, $jumlah, $tanggal_donasi_formatted, $keterangan);
                        
                        if ($stmt->execute()) {
                            $berhasil++;
                        } else {
                            $gagal++;
                        }
                        $stmt->close();

                    } catch (Exception $e) {
                        $gagal++; // Gagal karena format tanggal salah
                    }
                } else {
                    $gagal++;
                }
            }
            $_SESSION['message'] = "Upload selesai. Berhasil memasukkan $berhasil data, gagal $gagal data.";

        } catch (Exception $e) {
            $_SESSION['message'] = 'Error loading file: ' . $e->getMessage();
        }

    } else {
        $_SESSION['message'] = 'Tipe file tidak valid. Harap upload file Excel (.xls atau .xlsx).';
    }

    header("Location: laporan_donatur.php");
    exit();
}
?>