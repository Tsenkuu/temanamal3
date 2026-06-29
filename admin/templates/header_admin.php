<?php
// Ambil nama admin dari session untuk ditampilkan
$admin_nama_lengkap = $_SESSION['admin_nama_lengkap'] ?? 'Admin';

// Mengambil jumlah notifikasi
$notif_donasi = $mysqli->query("SELECT COUNT(id) as total FROM donasi WHERE status = 'Menunggu Konfirmasi'")->fetch_assoc()['total'] ?? 0;
$notif_berita = $mysqli->query("SELECT COUNT(id) as total FROM berita WHERE status = 'pending'")->fetch_assoc()['total'] ?? 0;
$total_notif = $notif_donasi + $notif_berita;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Admin Lazismu' : 'Dashboard Admin'; ?></title>

    <!-- Favicon -->
    <link rel="icon" href="../assets/images/icon.png" type="image/png">
    <link rel="apple-touch-icon" href="../assets/images/icon.png">

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

    <!-- Alpine.js Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Kustom untuk Desain Baru -->
    <style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
        /* bg-slate-50 */
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    ::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    /* Layout Utama */
    .admin-layout {
        display: flex;
        min-height: 100vh;
    }

    .sidebar {
        width: 260px;
        background-color: #ffffff;
        border-right: 1px solid #e5e7eb;
        transition: transform 0.3s ease;
        z-index: 50;
    }

    @media (max-width: 1024px) {
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            transform: translateX(-100%);
        }

        .sidebar.is-open {
            transform: translateX(0);
        }
    }

    .main-content {
        flex-grow: 1;
        padding: 1.5rem;
        /* Disesuaikan padding */
        overflow-y: auto;
    }

    .admin-header {
        background-color: #ffffff;
        border-bottom: 1px solid #e5e7eb;
        position: sticky;
        top: 0;
        z-index: 40;
    }

    /* Komponen UI Kustom */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .content-card {
        background-color: #ffffff;
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
    }

    .card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #374151;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #fb8201;
        box-shadow: 0 0 0 3px rgba(251, 130, 1, 0.2);
    }

    .form-input-file {
        display: block;
        width: 100%;
        padding: 8px;
        border: 1px solid #d1d5db;
        border-radius: .5rem;
        font-size: .875rem;
    }

    .btn-primary {
        background-color: #fb8201;
        color: white;
        padding: 0.75rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        transition: background-color 0.2s ease;
    }

    .btn-primary:hover {
        background-color: #f57400;
    }

    .btn-danger {
        background-color: #ef4444; /* bg-red-500 */
        color: white;
        padding: 0.75rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        transition: background-color 0.2s ease;
    }
    .btn-danger:hover {
        background-color: #dc2626; /* bg-red-600 */
    }
    .btn-danger-sm {
        background-color: #fee2e2; /* bg-red-100 */
        color: #b91c1c; /* text-red-700 */
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        transition: background-color 0.2s ease;
    }

    .btn-secondary {
        background-color: #e5e7eb;
        color: #374151;
        padding: 0.75rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        transition: background-color 0.2s ease;
    }

    .btn-secondary:hover {
        background-color: #d1d5db;
    }

    .btn-icon {
        width: 36px;
        height: 36px;
        border-radius: 0.375rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s ease;
    }

    .btn-icon-sm {
        width: 32px;
        height: 32px;
        border-radius: 0.375rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s ease;
    }

    .alert-info {
        background-color: #eff6ff;
        color: #1d4ed8;
        padding: 1rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
    }

    .alert-success {
        background-color: #f0fdf4;
        color: #166534;
        padding: 1rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }

    .badge-success {
        background-color: #dcfce7;
        color: #166534;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
    }

    .badge-warning {
        background-color: #fef9c3;
        color: #854d0e;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
    }

    .badge-info {
        background-color: #e0f2fe;
        color: #0284c7;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
    }

    .badge-secondary {
        background-color: #f3f4f6;
        color: #4b5563;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1.5rem;
    }

    .pagination a {
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        background-color: #e5e7eb;
        color: #374151;
        text-decoration: none;
    }

    .pagination a:hover {
        background-color: #d1d5db;
    }

    .pagination a.active {
        background-color: #fb8201;
        color: white;
        font-weight: 600;
    }

    /* Komponen Dashboard */
    .header-welcome {
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .stat-card {
        color: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }
    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.875rem;
        flex-shrink: 0;
    }
    .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .quick-action-btn {
        border-radius: 0.75rem;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-weight: 600;
        text-align: center;
        transition: all 0.2s ease;
    }
    .quick-action-btn:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }
    .table-wrapper {
        overflow-x: auto;
    }
    </style>
</head>

<body class="bg-slate-50 text-gray-800" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        
        <?php require_once 'sidebar_admin.php'; ?>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col overflow-hidden relative">
            
            <!-- Topbar -->
            <header class="h-16 bg-white shadow-sm border-b border-gray-100 flex items-center justify-between px-4 lg:px-8 z-30 relative">
                
                <div class="flex items-center gap-4">
                    <!-- Mobile Hamburger -->
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 rounded-lg text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-orange">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <!-- Search placeholder / Breadcrumbs could go here -->
                    <div class="hidden md:flex items-center text-sm font-medium text-gray-500">
                        <span class="text-primary-orange"><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?></span>
                    </div>
                </div>

                <!-- Right Menu -->
                <div class="flex items-center gap-4 lg:gap-6">
                    
                    <!-- Notification Bell -->
                    <div class="relative" x-data="{ notifOpen: false }" @click.away="notifOpen = false">
                        <button @click="notifOpen = !notifOpen" class="relative p-2 text-gray-500 hover:text-primary-orange transition-colors focus:outline-none">
                            <i class="bi bi-bell text-xl"></i>
                            <?php if ($total_notif > 0): ?>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border border-white animate-pulse"></span>
                            <?php endif; ?>
                        </button>

                        <!-- Notification Dropdown -->
                        <div x-show="notifOpen" 
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-3 w-72 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50" style="display: none;">
                            
                            <div class="px-4 py-2 border-b border-gray-100">
                                <h3 class="text-sm font-bold text-gray-800">Notifikasi (<?php echo $total_notif; ?>)</h3>
                            </div>
                            
                            <div class="max-h-64 overflow-y-auto">
                                <?php if ($total_notif == 0): ?>
                                <div class="px-4 py-6 text-center text-gray-500 text-sm">
                                    <i class="bi bi-bell-slash text-2xl mb-2 block text-gray-300"></i>
                                    Tidak ada notifikasi baru
                                </div>
                                <?php else: ?>
                                    
                                    <?php if ($notif_donasi > 0): ?>
                                    <a href="konfirmasi_donasi.php" class="flex items-start gap-3 px-4 py-3 hover:bg-orange-50 transition-colors border-b border-gray-50">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                            <i class="bi bi-wallet2 text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">Konfirmasi Donasi</p>
                                            <p class="text-xs text-gray-500 mt-0.5">Ada <?php echo $notif_donasi; ?> donasi menunggu konfirmasi Anda.</p>
                                        </div>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($notif_berita > 0): ?>
                                    <a href="kelola_berita.php" class="flex items-start gap-3 px-4 py-3 hover:bg-orange-50 transition-colors border-b border-gray-50">
                                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                                            <i class="bi bi-newspaper text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">Persetujuan Berita</p>
                                            <p class="text-xs text-gray-500 mt-0.5">Ada <?php echo $notif_berita; ?> berita menunggu tinjauan Anda.</p>
                                        </div>
                                    </a>
                                    <?php endif; ?>
                                    
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                        <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-3 focus:outline-none pl-2 lg:pl-4 lg:border-l border-gray-200">
                            <div class="w-8 h-8 rounded-full bg-orange-100 text-primary-orange flex items-center justify-center font-bold text-sm">
                                <?php echo strtoupper(substr($admin_nama_lengkap, 0, 1)); ?>
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-bold text-dark-text leading-tight"><?php echo htmlspecialchars($admin_nama_lengkap); ?></p>
                                <p class="text-xs text-gray-500">Administrator</p>
                            </div>
                            <i class="bi bi-chevron-down text-xs text-gray-400 transition-transform duration-200" :class="{'rotate-180': dropdownOpen}"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div x-show="dropdownOpen" 
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50" style="display: none;">
                            
                            <a href="ganti_sandi.php" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-primary-orange transition-colors">
                                <i class="bi bi-shield-lock"></i> Ganti Sandi
                            </a>
                            <div class="h-px bg-gray-100 my-1"></div>
                            <a href="../logout.php" class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto bg-slate-50 p-4 lg:p-8">