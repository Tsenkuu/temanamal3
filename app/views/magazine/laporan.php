<?php
require_once 'includes/config.php';
require_once 'includes/templates/header.php';

$page_title = "Laporan Publik Lazismu";

// --- Ambil data untuk filter ---
$result_jenis = $mysqli->query("SELECT DISTINCT jenis_laporan FROM laporan_transaksi ORDER BY jenis_laporan ASC");
$result_periode = $mysqli->query("SELECT DISTINCT periode_laporan FROM laporan_transaksi ORDER BY periode_laporan DESC");

// --- Tentukan filter yang dipilih dengan validasi ---
$jenis_terpilih = 'Zakat'; // Nilai default
$periode_terpilih = '';

// Validasi dan sanitasi input GET
if (isset($_GET['jenis']) && is_string($_GET['jenis'])) {
    $jenis_terpilih = $mysqli->real_escape_string($_GET['jenis']);
}

// Ambil periode terbaru sebagai default jika tidak ada yang dipilih
$latest_period_query = $mysqli->query("SELECT periode_laporan FROM laporan_transaksi ORDER BY periode_laporan DESC LIMIT 1");
if ($latest_period_query && $latest_period_query->num_rows > 0) {
    $latest_period = $latest_period_query->fetch_assoc()['periode_laporan'];
    $periode_terpilih = isset($_GET['periode']) && is_string($_GET['periode']) ? 
                        $mysqli->real_escape_string($_GET['periode']) : $latest_period;
}

// --- Query untuk mengambil data laporan transaksi berdasarkan filter ---
$result_laporan_transaksi = null;
$total_nominal = 0;
$error_message = '';

if (!empty($jenis_terpilih) && !empty($periode_terpilih)) {
    $sql_transaksi = "SELECT nama_donatur, nominal 
                      FROM laporan_transaksi
                      WHERE jenis_laporan = ? AND periode_laporan = ?
                      ORDER BY nama_donatur ASC";
    
    if ($stmt = $mysqli->prepare($sql_transaksi)) {
        $stmt->bind_param("ss", $jenis_terpilih, $periode_terpilih);
        
        if ($stmt->execute()) {
            $result_laporan_transaksi = $stmt->get_result();
        } else {
            $error_message = "Terjadi kesalahan saat mengambil data: " . $stmt->error;
        }
    } else {
        $error_message = "Terjadi kesalahan dalam persiapan query: " . $mysqli->error;
    }
} else {
    $error_message = "Parameter jenis dan periode harus diisi.";
}
?>

<main class="container mx-auto my-12 px-4 md:px-6">
    <div class="text-center mb-10 scroll-animate">
        <h1 class="text-3xl md:text-4xl font-bold text-dark-text">Laporan Publik Lazismu</h1>
        <p class="text-gray-600 mt-2">Transparansi dan akuntabilitas adalah komitmen kami.</p>
    </div>

    <section id="laporan-donatur" class="mb-5">
        <div class="bg-white p-6 md:p-8 rounded-2xl shadow-lg scroll-animate">
            <h3 class="text-xl md:text-2xl font-bold text-dark-text mb-6">Laporan Donatur</h3>
            
            <!-- Form Filter -->
            <form id="filter-laporan-form" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end mb-6">
                <div>
                    <label for="jenis" class="block text-sm font-medium text-gray-700">Jenis Laporan</label>
                    <select name="jenis" id="jenis" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-orange focus:ring focus:ring-primary-orange focus:ring-opacity-50 py-2 px-3">
                        <?php if ($result_jenis && $result_jenis->num_rows > 0) : 
                            $result_jenis->data_seek(0); 
                            while ($row = $result_jenis->fetch_assoc()) : ?>
                                <option value="<?php echo htmlspecialchars($row['jenis_laporan']); ?>"
                                    <?php if ($row['jenis_laporan'] == $jenis_terpilih) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row['jenis_laporan']); ?>
                                </option>
                            <?php endwhile; 
                        endif; ?>
                    </select>
                </div>
                <div>
                    <label for="periode" class="block text-sm font-medium text-gray-700">Periode</label>
                    <select name="periode" id="periode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-orange focus:ring focus:ring-primary-orange focus:ring-opacity-50 py-2 px-3">
                        <?php if ($result_periode && $result_periode->num_rows > 0) : 
                            $result_periode->data_seek(0); 
                            while ($row = $result_periode->fetch_assoc()) : ?>
                                <option value="<?php echo htmlspecialchars($row['periode_laporan']); ?>"
                                    <?php if ($row['periode_laporan'] == $periode_terpilih) echo 'selected'; ?>>
                                    <?php echo date('F Y', strtotime($row['periode_laporan'])); ?>
                                </option>
                            <?php endwhile; 
                        endif; ?>
                    </select>
                </div>
                <button type="submit" class="bg-primary-orange text-white px-6 py-2 rounded-full font-bold hover:bg-orange-600 transition duration-300">
                    Tampilkan
                </button>
            </form>

            <!-- Tabel Donatur -->
            <div class="overflow-x-auto rounded-lg shadow">
                <table class="w-full text-left">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3 font-semibold text-gray-700">No</th>
                            <th class="p-3 font-semibold text-gray-700">Nama Donatur</th>
                            <th class="p-3 font-semibold text-gray-700 text-right">Nominal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($error_message)) : ?>
                            <tr>
                                <td colspan="3" class="text-center text-red-500 py-8">
                                    <?php echo $error_message; ?>
                                </td>
                            </tr>
                        <?php elseif ($result_laporan_transaksi && $result_laporan_transaksi->num_rows > 0) : 
                            $nomor = 1; 
                            $total_nominal = 0; 
                            while ($transaksi = $result_laporan_transaksi->fetch_assoc()) : 
                                $total_nominal += $transaksi['nominal']; ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-3"><?php echo $nomor++; ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($transaksi['nama_donatur']); ?></td>
                                    <td class="p-3 text-right font-medium">
                                        <?php echo number_format($transaksi['nominal'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <tr class="bg-primary-orange bg-opacity-10 font-bold">
                                <td colspan="2" class="p-3 text-right">TOTAL</td>
                                <td class="p-3 text-right"><?php echo number_format($total_nominal, 0, ',', '.'); ?></td>
                            </tr>
                        <?php else : ?>
                            <tr>
                                <td colspan="3" class="text-center text-gray-500 py-8">
                                    Data tidak ditemukan untuk filter yang dipilih.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Informasi Filter -->
            <div class="mt-4 text-sm text-gray-600">
                Menampilkan laporan <strong><?php echo htmlspecialchars($jenis_terpilih); ?></strong> 
                untuk periode <strong><?php echo !empty($periode_terpilih) ? date('F Y', strtotime($periode_terpilih)) : ''; ?></strong>
            </div>
        </div>
    </section>
</main>

<script>
document.getElementById('filter-laporan-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const jenis = document.getElementById('jenis').value;
    const periodeValue = document.getElementById('periode').value;
    
    // Pastikan nilai periode valid
    if (periodeValue) {
        const periode = periodeValue.substring(0, 7); // Mengambil "YYYY-MM"
        window.location.href = `<?php echo BASE_URL; ?>/laporan/${jenis}/${periode}`;
    } else {
        alert('Silakan pilih periode yang valid.');
    }
});
</script>

<?php 
// Tutup koneksi database
if (isset($stmt)) {
    $stmt->close();
}
$mysqli->close();

require_once 'includes/templates/footer.php'; 
?>