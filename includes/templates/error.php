<?php
// Default values if not set
$error_code = isset($error_code) ? $error_code : '500';
$error_title = isset($error_title) ? $error_title : 'Terjadi Kesalahan Server';
$error_message = isset($error_message) ? $error_message : 'Maaf, terjadi kesalahan pada sistem kami. Silakan coba lagi nanti.';
$base_url = defined('BASE_URL') ? BASE_URL : '/temanamal';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $error_code ?> - <?= $error_title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl overflow-hidden text-center relative p-8">
        <!-- Dekorasi latar -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-orange-100 rounded-full mix-blend-multiply filter blur-2xl opacity-70"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-blue-100 rounded-full mix-blend-multiply filter blur-2xl opacity-70"></div>

        <div class="relative z-10">
            <!-- Ikon Error -->
            <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <?php if ($error_code == '404'): ?>
                    <i class="bi bi-search text-5xl text-orange-500"></i>
                <?php else: ?>
                    <i class="bi bi-exclamation-triangle text-5xl text-red-500"></i>
                <?php endif; ?>
            </div>

            <!-- Pesan Error -->
            <h1 class="text-6xl font-extrabold text-slate-800 mb-2"><?= $error_code ?></h1>
            <h2 class="text-2xl font-bold text-slate-700 mb-4"><?= $error_title ?></h2>
            <p class="text-slate-500 mb-8 leading-relaxed">
                <?= $error_message ?>
            </p>

            <!-- Aksi -->
            <div class="space-y-4">
                <a href="<?= $base_url ?>/" class="block w-full py-3 px-4 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl transition-colors duration-200 shadow-lg shadow-orange-200">
                    <i class="bi bi-house-door mr-2"></i> Kembali ke Beranda
                </a>
                
                <a href="https://instagram.com/lazismu.tulungagung" target="_blank" class="block w-full py-3 px-4 bg-white border-2 border-slate-200 hover:border-pink-500 hover:text-pink-600 text-slate-600 font-semibold rounded-xl transition-colors duration-200">
                    <i class="bi bi-instagram mr-2"></i> Hubungi Instagram Kami
                </a>
            </div>
        </div>
    </div>

</body>
</html>
