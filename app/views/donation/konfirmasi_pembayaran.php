<?php
require_once 'includes/config.php';
$page_title = "Konfirmasi Pembayaran";
require_once 'includes/templates/header.php';

// 1. Validasi Sesi: Pastikan invoice_id ada di sesi.
if (!isset($_SESSION['last_invoice_id'])) {
    // Jika tidak ada, arahkan kembali ke halaman donasi.
    header('Location: ' . BASE_URL . '/program');
    exit();
}
$invoice_id = $_SESSION['last_invoice_id'];

// 2. Query Database dengan Aman
// Mengambil detail donasi dan metode pembayaran dari database.
$stmt = $mysqli->prepare(
    "SELECT d.total_transfer, d.status, m.nama_metode, m.tipe, m.detail_1, m.detail_2, m.gambar 
     FROM donasi d 
     JOIN metode_pembayaran m ON d.metode_pembayaran_id = m.id 
     WHERE d.invoice_id = ?"
);
$stmt->bind_param("s", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

// 3. [PERBAIKAN KRUSIAL] Cek Hasil Query
// Jika tidak ada data atau donasi sudah dikonfirmasi, jangan tampilkan halaman ini.
if (!$data || $data['status'] !== 'Menunggu Pembayaran') {
    // Hapus sesi yang tidak valid dan arahkan pengguna.
    unset($_SESSION['last_invoice_id']);
    // Anda bisa menambahkan pesan eror di sesi jika perlu.
    $_SESSION['error_message'] = "Invoice tidak ditemukan atau sudah dibayar.";
    header('Location: ' . BASE_URL . '/program');
    exit();
}

?>
<main class="bg-gray-50">
    <div class="container mx-auto py-12 md:py-20 px-6">
        <div class="max-w-3xl mx-auto bg-white p-6 md:p-10 rounded-2xl shadow-lg">
            
            <div class="text-center mb-8">
                <h1 class="text-3xl md:text-4xl font-bold text-dark-text">Selesaikan Pembayaran Anda</h1>
                <p class="text-gray-600 mt-3">Satu langkah lagi untuk menyelesaikan donasi Anda. Terima kasih atas kebaikan Anda!</p>
            </div>

            <!-- [FITUR BARU] Menampilkan pesan eror dari halaman upload -->
            <?php if (isset($_SESSION['upload_error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                    <strong class="font-bold">Oops! Terjadi kesalahan:</strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['upload_error']); ?></span>
                </div>
                <?php unset($_SESSION['upload_error']); // Hapus pesan setelah ditampilkan ?>
            <?php endif; ?>

            <!-- Detail Transfer -->
            <div class="border rounded-xl p-6 bg-light-bg text-center">
                <p class="text-gray-600 text-sm">Jumlah yang harus dibayar (mohon transfer sesuai nominal):</p>
                <div class="flex items-center justify-center my-3">
                    <h2 id="total-transfer" class="text-4xl font-bold text-primary-orange tracking-tight">
                        Rp <?php echo number_format($data['total_transfer'], 0, ',', '.'); ?>
                    </h2>
                    <button onclick="copyToClipboard('<?php echo $data['total_transfer']; ?>', this)" class="ml-3 p-2 rounded-full hover:bg-gray-200 transition" aria-label="Salin jumlah transfer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                    </button>
                </div>

                <div class="mt-6 pt-6 border-t">
                    <?php if ($data['tipe'] == 'Transfer Bank'): ?>
                        <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($data['nama_metode']); ?></h3>
                        <div class="flex items-center justify-center my-2">
                             <p id="nomor-rekening" class="text-2xl font-bold my-2 text-dark-text"><?php echo htmlspecialchars($data['detail_1']); ?></p>
                             <button onclick="copyToClipboard('<?php echo htmlspecialchars($data['detail_1']); ?>', this)" class="ml-3 p-2 rounded-full hover:bg-gray-200 transition" aria-label="Salin nomor rekening">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                            </button>
                        </div>
                        <p class="text-gray-600">a.n. <?php echo htmlspecialchars($data['detail_2']); ?></p>
                    <?php else: // Asumsi tipe QRIS ?>
                        <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($data['nama_metode']); ?></h3>
                        <p class="text-gray-600 mb-4">Scan kode QR di bawah ini menggunakan aplikasi pembayaran Anda.</p>
                        <img src="assets/images/qris/<?php echo htmlspecialchars($data['gambar']); ?>" alt="QR Code Pembayaran" class="mx-auto my-2 rounded-lg border p-2" style="max-width: 250px;">
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form Upload Bukti -->
            <div class="mt-8 pt-8 border-t">
                 <h3 class="text-xl font-bold text-center">Sudah Bayar? Upload Bukti di Sini</h3>
                 <p class="text-center text-gray-500 mt-1 text-sm">Ini akan mempercepat proses verifikasi donasi Anda.</p>
                 <form action="<?= BASE_URL ?>/upload_bukti" method="POST" enctype="multipart/form-data" class="mt-6 max-w-lg mx-auto">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="invoice_id" value="<?php echo htmlspecialchars($invoice_id); ?>">
                    <div>
                        <label for="bukti_pembayaran" class="block text-sm font-medium text-gray-700 mb-2">Pilih file gambar:</label>
                        <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" accept=".jpg,.jpeg,.png,.webp,.pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-primary-orange hover:file:bg-orange-100" required>
                    </div>
                    <button type="submit" class="mt-6 w-full bg-primary-orange text-white font-bold py-3 px-4 rounded-full hover:bg-orange-600 transition shadow-md hover:shadow-lg">
                        Konfirmasi Pembayaran
                    </button>
                 </form>
            </div>
        </div>
    </div>
</main>

<!-- Notifikasi Toast -->
<div id="toast-notification" class="fixed bottom-5 right-5 bg-gray-800 text-white py-2 px-5 rounded-lg shadow-lg opacity-0 transition-opacity duration-300 z-50">
    Teks disalin!
</div>

<script>
// [FITUR BARU] Fungsi untuk menyalin teks ke clipboard
function copyToClipboard(text, buttonElement) {
    const textArea = document.createElement('textarea');
    textArea.value = text.replace(/\./g, ''); // Hapus titik dari angka untuk penyalinan
    document.body.appendChild(textArea);
    textArea.select();
    try {
        document.execCommand('copy');
        showToast("Teks berhasil disalin!");
    } catch (err) {
        showToast("Gagal menyalin teks.");
        console.error('Gagal menyalin teks: ', err);
    }
    document.body.removeChild(textArea);
}

// Fungsi untuk menampilkan notifikasi toast
function showToast(message) {
    const toast = document.getElementById('toast-notification');
    if (!toast) return;
    toast.textContent = message;
    toast.classList.remove('opacity-0');
    setTimeout(() => {
        toast.classList.add('opacity-0');
    }, 2000);
}
</script>

<?php require_once 'includes/templates/footer.php'; ?>

