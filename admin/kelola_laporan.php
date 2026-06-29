<?php
require_once '../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
$page_title = "Laporan & Impor Data";

// Proses Upload CSV
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_csv'])) {
    // Logika proses CSV... (tidak diubah)
}

$result_laporan_file = $mysqli->query("SELECT id, judul_laporan, nama_file, tanggal_upload FROM laporan ORDER BY tanggal_upload DESC");
$result_periode_group = $mysqli->query("SELECT DISTINCT periode_laporan, jenis_laporan FROM laporan_transaksi ORDER BY periode_laporan DESC, jenis_laporan ASC");

require_once 'templates/header_admin.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-bold text-dark-text">Laporan & Impor Data</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <!-- Kolom Impor & Tambah Laporan -->
        <div class="lg:col-span-1 space-y-6">
            <div class="content-card">
                <h3 class="card-title mb-4">Impor Transaksi (CSV)</h3>
                <form action="kelola_laporan.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label for="jenis_laporan" class="form-label">Jenis Laporan</label>
                        <select name="jenis_laporan" id="jenis_laporan" class="form-select" required>
                            <option value="Zakat">Zakat</option>
                            <option value="Infaq">Infaq</option>
                            <option value="Rendangmu">Rendangmu</option>
                        </select>
                    </div>
                    <div>
                        <label for="bulan" class="form-label">Periode Laporan</label>
                        <div class="flex gap-2">
                            <select name="bulan" id="bulan" class="form-select" required>
                                <?php for ($i=1; $i<=12; $i++): ?><option
                                    value="<?php echo str_pad($i,2,'0',STR_PAD_LEFT); ?>"
                                    <?php if(date('m')==$i) echo 'selected';?>>
                                    <?php echo date('F',mktime(0,0,0,$i,10));?></option><?php endfor; ?>
                            </select>
                            <select name="tahun" id="tahun" class="form-select" required>
                                <?php for ($i=date('Y'); $i>=date('Y')-5; $i--): ?><option value="<?php echo $i; ?>">
                                    <?php echo $i; ?></option><?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="file_csv" class="form-label">Pilih File CSV</label>
                        <input class="form-input-file" type="file" id="file_csv" name="file_csv" accept=".csv" required>
                        <p class="text-xs text-gray-500 mt-1">Gunakan format: NO;NAMA;NOMINAL</p>
                    </div>
                    <button type="submit" class="btn-primary w-full">Impor Data</button>
                </form>
            </div>
            <div class="content-card">
                <h3 class="card-title mb-4">Laporan Dokumen</h3>
                <a href="tambah_laporan.php" class="btn-secondary w-full mb-4">Tambah Dokumen Laporan Baru</a>
                <div class="space-y-3">
                    <?php if ($result_laporan_file && $result_laporan_file->num_rows > 0): ?>
                    <?php while($row = $result_laporan_file->fetch_assoc()): ?>
                    <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-md">
                        <i class="bi bi-file-earmark-text-fill text-gray-400 text-xl"></i>
                        <div class="flex-1">
                            <p class="font-semibold text-sm text-dark-text">
                                <?php echo htmlspecialchars($row['judul_laporan']); ?></p>
                            <p class="text-xs text-gray-500">
                                <?php echo date('d M Y', strtotime($row['tanggal_upload'])); ?></p>
                        </div>
                        <a href="../assets/uploads/laporan/<?php echo htmlspecialchars($row['nama_file']); ?>"
                            target="_blank" class="btn-icon-sm bg-gray-200 hover:bg-gray-300" title="Download"><i
                                class="bi bi-download"></i></a>
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <p class="text-sm text-center text-gray-500 py-4">Belum ada laporan dokumen.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Kolom Daftar Transaksi Terimpor -->
        <div class="lg:col-span-2 content-card">
            <h3 class="card-title mb-4">Data Donatur Terimpor</h3>
            <div class="space-y-6">
                <?php if ($result_periode_group && $result_periode_group->num_rows > 0): ?>
                <?php while($group = $result_periode_group->fetch_assoc()): 
                        $periode = $group['periode_laporan'];
                        $jenis = $group['jenis_laporan'];
                    ?>
                <div>
                    <div class="flex justify-between items-center mb-2 pb-2 border-b">
                        <h4 class="font-semibold text-dark-text">Laporan <?php echo htmlspecialchars($jenis); ?> -
                            <?php echo date('F Y', strtotime($periode)); ?></h4>
                        <a href="hapus_transaksi.php?periode=<?php echo $periode; ?>&jenis=<?php echo $jenis; ?>"
                            class="btn-danger-sm" onclick="return confirm('Yakin hapus semua data di grup ini?');"><i class="bi bi-trash mr-1"></i> Hapus
                            Grup</a>
                    </div>
                    <div class="table-wrapper max-h-80">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2">Nama Donatur</th>
                                    <th class="px-4 py-2 text-right">Nominal</th>
                                    <th class="px-4 py-2 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt_transaksi = $mysqli->prepare("SELECT id, nama_donatur, nominal FROM laporan_transaksi WHERE periode_laporan = ? AND jenis_laporan = ? ORDER BY id ASC");
                                $stmt_transaksi->bind_param("ss", $periode, $jenis);
                                $stmt_transaksi->execute();
                                $result_transaksi = $stmt_transaksi->get_result();
                                while($row = $result_transaksi->fetch_assoc()):
                                ?>
                                <tr class="bg-white border-b hover:bg-gray-50 text-xs">
                                    <td class="px-4 py-2 font-medium text-gray-900">
                                        <?php echo htmlspecialchars($row['nama_donatur']); ?></td>
                                    <td class="px-4 py-2 text-right font-semibold">Rp
                                        <?php echo number_format($row['nominal'], 0, ',', '.'); ?></td>
                                    <td class="px-4 py-2 text-center"><a
                                            href="hapus_transaksi.php?id=<?php echo $row['id']; ?>"
                                            class="text-red-500 hover:text-red-700"
                                            onclick="return confirm('Hapus data ini?');"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; $stmt_transaksi->close(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <p class="text-center text-gray-500 py-8">Belum ada data donatur yang diimpor.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php require_once 'templates/footer_admin.php'; ?>