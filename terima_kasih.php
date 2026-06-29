<?php
require_once 'includes/config.php';
$page_title = "Terima Kasih";
require_once 'includes/templates/header.php';
?>
<main class="container mx-auto my-12 px-6 text-center">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white p-8 rounded-2xl shadow-lg">
            <h1 class="text-3xl font-bold text-green-500">Terima Kasih!</h1>
            <p class="text-gray-600 mt-4">Konfirmasi pembayaran Anda telah kami terima. Donasi Anda akan segera kami
                verifikasi.</p>
            <a href="index.php"
                class="mt-6 inline-block bg-primary-orange text-white font-bold py-3 px-6 rounded-full hover:bg-orange-600 transition">Kembali
                ke Beranda</a>
        </div>

    </div>
</main>
<?php require_once 'includes/templates/footer.php'; ?>