<?php
// Memulai sesi jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mengambil data pengguna dari sesi
$user_id = $_SESSION['user_id'] ?? null;
$user_nama = $_SESSION['user_nama_lengkap'] ?? 'Tamu';
$user_foto = $_SESSION['user_foto'] ?? 'default.png';
$inisial_user = !empty($user_nama) ? strtoupper(substr($user_nama, 0, 1)) : 'T';

// Mendefinisikan BASE_URL jika belum ada
if (!defined('BASE_URL')) {
    // Sesuaikan path ini dengan struktur direktori Anda
    // Contoh: http://localhost/nama_projek
    define('BASE_URL', '/'); 
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Teman Amal' : 'Teman Amal'; ?></title>

    <!-- Favicon -->
    <link rel="icon" href="<?php echo BASE_URL; ?>/assets/images/icon.png" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>/assets/images/icon.png">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-orange': '#fb8c00',
                        'secondary-orange': '#ffa726',
                        'background-light': '#f4f7f9',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS untuk Desain Canggih -->
    <style>
        :root {
            --primary-orange: #fb8c00;
            --secondary-orange: #ffa726;
            --background-light: #f4f7f9;
            --text-dark: #1a202c;
            --text-muted: #718096;
            --card-bg: #ffffff;
            --font-sans: 'Inter', sans-serif;
        }
        body {
            font-family: var(--font-sans);
            background-color: var(--background-light);
            color: var(--text-dark);
        }
        /* Custom scrollbar for better look */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: var(--background-light);
        }
        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        /* Main content padding for bottom nav */
        main {
            padding-bottom: 90px;
        }
    </style>
</head>
<body class="antialiased">

<header class="bg-white/80 backdrop-blur-lg sticky top-0 z-40 shadow-sm">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="<?php echo BASE_URL; ?>/" class="flex items-center gap-2.5 group">
                    <div class="bg-white p-1 rounded-xl shadow-sm border border-gray-100 group-hover:shadow-md transition-shadow">
                        <img class="h-8 md:h-10 w-auto object-contain" src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo Teman Amal">
                    </div>
                    <span class="font-display font-bold text-xl md:text-2xl tracking-tight hidden sm:block text-gray-900">Teman<span class="text-primary-orange">Amal</span></span>
                </a>
            </div>

            <!-- Profile Dropdown -->
            <div class="flex items-center">
                <?php if ($user_id): ?>
                    <div class="relative">
                        <button type="button" class="flex items-center gap-2 text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-orange" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                            <span class="sr-only">Open user menu</span>
                            <img class="h-9 w-9 rounded-full object-cover" src="<?php echo BASE_URL; ?>/assets/uploads/user/<?php echo htmlspecialchars($user_foto); ?>" alt="Foto Profil">
                             <span class="hidden md:block font-semibold"><?php echo htmlspecialchars($user_nama); ?></span>
                             <i class="bi bi-chevron-down hidden md:block"></i>
                        </button>
                        
                        <div id="user-menu" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                            <a href="dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">Dashboard</a>
                            <a href="edit_profil" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">Edit Profil</a>
                            <a href="ganti_sandi" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">Ganti Sandi</a>
                            <div class="border-t my-1"></div>
                            <a href="logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100" role="menuitem" tabindex="-1">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login" class="text-sm font-semibold leading-6 text-gray-900">Login <span aria-hidden="true">&rarr;</span></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<main>
