<?php
require_once 'includes/config.php';

// 1. Ambil Token dari URL
$token = clean_text($_GET['token'] ?? '', 80);
if (empty($token)) {
    header("Location: " . BASE_URL);
    exit();
}

// 2. Ambil Data Donasi
$stmt = $mysqli->prepare("
    SELECT d.*, p.nama_program, m.nama_metode, m.tipe, m.detail_1, m.detail_2, m.gambar as gambar_metode 
    FROM donasi d 
    LEFT JOIN program p ON d.id_program = p.id 
    JOIN metode_pembayaran m ON d.metode_pembayaran_id = m.id 
    WHERE d.token = ?
");
$stmt->bind_param("s", $token);
$stmt->execute();
$donasi = $stmt->get_result()->fetch_assoc();

if (!$donasi) {
    header("Location: " . BASE_URL . "/error.php?code=404");
    exit();
}

// Cek Status Expired
if ($donasi['status'] == 'Menunggu Pembayaran' && strtotime($donasi['expired_at']) < time()) {
    $mysqli->query("UPDATE donasi SET status = 'Dibatalkan' WHERE id = " . $donasi['id']);
    $donasi['status'] = 'Dibatalkan';
}

$page_title = "History Donasi #" . $donasi['invoice_id'];
require_once 'includes/templates/header.php';
?>

<main class="container mx-auto my-12 px-4 md:px-6">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden">
        
        <!-- Header Status -->
        <div class="p-6 text-center <?php 
            echo ($donasi['status'] == 'Selesai') ? 'bg-green-500' : 
                 (($donasi['status'] == 'Dibatalkan') ? 'bg-red-500' : 'bg-primary-orange'); 
            ?> text-white">
            <h1 class="text-2xl font-bold mb-1"><?php echo strtoupper($donasi['status']); ?></h1>
            <p class="opacity-90 text-sm">Invoice: <?php echo $donasi['invoice_id']; ?></p>
        </div>

        <div class="p-6 md:p-8">
            <!-- Detail Program -->
            <div class="text-center mb-8">
                <p class="text-gray-500 text-sm">Donasi untuk Program</p>
                <h2 class="text-xl font-bold text-dark-text"><?php echo htmlspecialchars($donasi['nama_program'] ?? 'Donasi Umum'); ?></h2>
            </div>

            <!-- Nominal -->
            <div class="bg-gray-50 rounded-xl p-6 mb-8 text-center border border-gray-200">
                <p class="text-gray-500 text-sm mb-1">Total Pembayaran</p>
                <div class="flex justify-center items-center gap-2">
                    <span class="text-3xl font-bold text-primary-orange">Rp <?php echo number_format($donasi['total_transfer'], 0, ',', '.'); ?></span>
                    <button onclick="navigator.clipboard.writeText('<?php echo $donasi['total_transfer']; ?>')" class="text-gray-400 hover:text-gray-600"><i class="bi bi-copy"></i></button>
                </div>
                <?php if ($donasi['status'] == 'Menunggu Pembayaran'): ?>
                    <p class="text-xs text-red-500 mt-2">Bayar sebelum: <?php echo date('d M Y H:i', strtotime($donasi['expired_at'])); ?></p>
                <?php endif; ?>
            </div>

            <!-- Instruksi Pembayaran -->
            <?php if ($donasi['status'] == 'Menunggu Pembayaran'): ?>
            <div class="mb-8">
                <h3 class="font-bold text-gray-800 mb-4">Instruksi Pembayaran</h3>
                <div class="border rounded-lg p-4 flex items-center gap-4">
                    <?php if ($donasi['tipe'] == 'QRIS' && !empty($donasi['gambar_metode'])): ?>
                        <img src="<?php echo BASE_URL; ?>/assets/images/qris/<?php echo $donasi['gambar_metode']; ?>" class="w-32 h-32 object-contain mx-auto">
                    <?php else: ?>
                        <div class="flex-1">
                            <p class="text-sm text-gray-500"><?php echo $donasi['nama_metode']; ?></p>
                            <p class="text-lg font-bold"><?php echo $donasi['detail_1']; ?></p>
                            <p class="text-sm text-gray-600">a.n <?php echo $donasi['detail_2']; ?></p>
                        </div>
                        <button onclick="navigator.clipboard.writeText('<?php echo $donasi['detail_1']; ?>')" class="text-primary-orange font-semibold text-sm">Salin</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form Upload Bukti -->
            <form action="<?php echo BASE_URL; ?>/upload_bukti.php" method="POST" enctype="multipart/form-data" class="mt-6">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="invoice_id" value="<?php echo $donasi['invoice_id']; ?>">
                <label class="block text-sm font-medium text-gray-700 mb-2">Upload Bukti Pembayaran</label>
                <input type="file" name="bukti_pembayaran" accept=".jpg,.jpeg,.png,.webp,.pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-primary-orange hover:file:bg-orange-100" required>
                <button type="submit" class="mt-4 w-full bg-primary-orange text-white font-bold py-3 rounded-full hover:bg-orange-600 transition shadow-lg">
                    Konfirmasi Pembayaran
                </button>
            </form>
            <?php elseif ($donasi['status'] == 'Menunggu Konfirmasi'): ?>
                <div class="text-center py-8">
                    <i class="bi bi-hourglass-split text-5xl text-yellow-500 mb-4 block"></i>
                    <h3 class="text-xl font-bold">Sedang Diverifikasi</h3>
                    <p class="text-gray-600">Terima kasih! Bukti pembayaran Anda sedang kami cek.</p>
                </div>
            <?php elseif ($donasi['status'] == 'Selesai'): ?>
                <div class="text-center py-8">
                    <i class="bi bi-check-circle-fill text-5xl text-green-500 mb-4 block"></i>
                    <h3 class="text-xl font-bold">Donasi Berhasil</h3>
                    <p class="text-gray-600">Terima kasih atas kebaikan Anda. Semoga menjadi amal jariyah.</p>
                    <a href="<?php echo BASE_URL; ?>/donasi" class="inline-block mt-4 text-primary-orange font-semibold hover:underline">Donasi Lagi</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'includes/templates/footer.php'; ?>
