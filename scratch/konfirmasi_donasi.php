<?php
require_once '../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
$page_title = "Konfirmasi Donasi";
require_once 'templates/header_admin.php';

// Logika untuk ACC donasi
if (isset($_GET['acc']) && !empty($_GET['acc'])) {
    $id_donasi = (int)$_GET['acc'];
    // Gunakan transaksi untuk update donasi dan program
    $mysqli->begin_transaction();
    try {
        // Ambil data donasi lengkap untuk notifikasi (Join dengan tabel program)
        $stmt_get = $mysqli->prepare("
            SELECT d.nominal, d.id_program, d.nama_donatur, d.sapaan, d.kontak_donatur, p.nama_program 
            FROM donasi d 
            LEFT JOIN program p ON d.id_program = p.id 
            WHERE d.id = ?
        ");
        $stmt_get->bind_param("i", $id_donasi);
        $stmt_get->execute();
        $donasi = $stmt_get->get_result()->fetch_assoc();
        
        if ($donasi) {
            if($donasi['id_program']){
                $stmt_prog = $mysqli->prepare("UPDATE program SET donasi_terkumpul = donasi_terkumpul + ? WHERE id = ?");
                $stmt_prog->bind_param("di", $donasi['nominal'], $donasi['id_program']);
                $stmt_prog->execute();
            }

            $stmt_update = $mysqli->prepare("UPDATE donasi SET status = 'Selesai' WHERE id = ?");
            $stmt_update->bind_param("i", $id_donasi);
            $stmt_update->execute();
            
            $mysqli->commit();

            // Kirim Pesan Terima Kasih & Doa via WhatsApp
            $nama_program = $donasi['nama_program'] ?? 'Donasi Umum';
            $pesan = "Alhamdulillah, pembayaran donasi Anda telah terverifikasi.\n\n" .
                     "Terima kasih *{$donasi['sapaan']} {$donasi['nama_donatur']}*,\n" .
                     "Donasi sebesar *Rp " . number_format($donasi['nominal'], 0, ',', '.') . "* untuk program *{$nama_program}* telah kami terima.\n\n" .
                     "\"Semoga Allah memberikan pahala atas apa yang engkau berikan, dan memberikan keberkahan atas harta yang engkau simpan, dan menjadikannya sebagai pembersih bagimu.\"\n\n" .
                     "_Lazismu Tulungagung_";
            
            kirimNotifikasiWA($donasi['kontak_donatur'], $pesan);

            $_SESSION['success_message'] = "Donasi berhasil dikonfirmasi dan notifikasi terkirim.";
        } else {
            throw new Exception("Data donasi tidak ditemukan.");
        }
    } catch (Exception $e) {
        $mysqli->rollback();
        $_SESSION['error_message'] = "Gagal mengonfirmasi donasi: " . $e->getMessage();
    }
    header("Location: konfirmasi_donasi.php");
    exit();
}

$result_pending = $mysqli->query("SELECT * FROM donasi WHERE status = 'Menunggu Konfirmasi' ORDER BY created_at ASC");
?>
<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_admin.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h1 class="h2 pt-3 pb-2 mb-3 border-bottom"><?php echo $page_title; ?></h1>
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Donatur</th>
                                <th>Nominal</th>
                                <th>Bukti</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_pending && $result_pending->num_rows > 0): ?>
                            <?php while($donasi = $result_pending->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $donasi['invoice_id']; ?></td>
                                <td><?php echo $donasi['nama_donatur']; ?></td>
                                <td>Rp <?php echo number_format($donasi['nominal'], 0, ',', '.'); ?></td>
                                <td><a href="../assets/uploads/bukti/<?php echo $donasi['bukti_pembayaran']; ?>"
                                        target="_blank">Lihat Bukti</a></td>
                                <td>
                                    <a href="?acc=<?php echo $donasi['id']; ?>" class="btn btn-success btn-sm"
                                        onclick="return confirm('Anda yakin ingin menyetujui donasi ini?');">ACC</a>
                                    <!-- Tombol Tolak -->
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada donasi yang menunggu konfirmasi.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
<?php require_once 'templates/footer_admin.php'; ?>