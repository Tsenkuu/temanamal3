<?php
// Fallback jika BASE_URL tidak terdefinisi
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $script_name = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    define('BASE_URL', rtrim($protocol . $host . $script_name, '/'));
}

// --- Logika Meta Tags ---
$default_title = 'Lazismu Tulungagung';
$default_description = 'Lembaga Amil Zakat terpercaya di Tulungagung.';
$default_keywords = 'zakat, infak, sedekah, lazismu, tulungagung';
$default_og_image = BASE_URL . '/assets/images/og-default.jpg';
$default_url = BASE_URL . $_SERVER['REQUEST_URI'];
$site_name = 'Lazismu Tulungagung';

$final_title = htmlspecialchars((isset($page_title) ? $page_title . ' - ' : '') . $site_name);
$final_description = htmlspecialchars(strip_tags(isset($og_description) ? $og_description : (isset($meta_description) ? $meta_description : $default_description)));
$final_keywords = htmlspecialchars(isset($meta_keywords) ? $meta_keywords : $default_keywords);
$final_url = htmlspecialchars(isset($og_url) ? $og_url : (isset($canonical_url) ? $canonical_url : $default_url));
$final_image = htmlspecialchars(isset($og_image) ? $og_image : $default_og_image);

$current_path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$base_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($base_dir && $base_dir !== '/') {
    $current_path = substr($current_path, strlen($base_dir));
}

// Logika User
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['admin_id']) || isset($_SESSION['amil_id']);
$dashboard_link = isset($_SESSION['admin_id']) ? BASE_URL . '/admin/dashboard.php' : (isset($_SESSION['amil_id']) ? BASE_URL . '/amil/dashboard.php' : BASE_URL . '/user/dashboard.php');
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $final_title; ?></title>
    <meta name="description" content="<?php echo $final_description; ?>">
    <meta name="keywords" content="<?php echo $final_keywords; ?>">
    <meta name="theme-color" content="#fb8201">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="canonical" href="<?php echo $final_url; ?>">

    <!-- Favicon -->
    <link rel="icon" href="<?php echo BASE_URL; ?>/assets/images/icon.png" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>/assets/images/icon.png">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?php echo isset($og_type) ? $og_type : 'website'; ?>">
    <meta property="og:url" content="<?php echo $final_url; ?>">
    <meta property="og:title" content="<?php echo $final_title; ?>">
    <meta property="og:description" content="<?php echo $final_description; ?>">
    <meta property="og:image" content="<?php echo $final_image; ?>">
    <meta property="og:site_name" content="<?php echo $site_name; ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo $final_url; ?>">
    <meta property="twitter:title" content="<?php echo $final_title; ?>">
    <meta property="twitter:description" content="<?php echo $final_description; ?>">
    <meta property="twitter:image" content="<?php echo $final_image; ?>">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'primary-orange': '#fb8201',
                    'primary-green': '#009846',
                    'light-bg': '#f8f9fa',
                    'accent-cream': '#fff8ef',
                    'dark-text': '#1f2937',
                    'gray-light': '#f3f4f6',
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                    display: ['Poppins', 'sans-serif']
                },
                boxShadow: {
                    'soft': '0 10px 40px -10px rgba(0,0,0,0.08)',
                    'card': '0 4px 20px rgba(0,0,0,0.05)',
                },
                borderRadius: {
                    'xl': '20px',
                    '2xl': '24px',
                }
            }
        }
    }
    </script>
    <style>
        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar { -ms-overflow-style: none;  scrollbar-width: none; }
        
        #preloader-container { position: fixed; inset: 0; z-index: 9999; background: radial-gradient(circle at top, rgba(251,130,1,0.16), transparent 30%), linear-gradient(135deg, #fffdf7, #f4f7f1); display: flex; justify-content: center; align-items: center; transition: opacity 0.5s; }
        #preloader-container.hiding { opacity: 0; visibility: hidden; }
        .loading-bar { width: 0; height: 4px; background-color: #fb8201; animation: loading-progress 1.5s ease-out forwards; }
        @keyframes loading-progress { to { width: 100%; } }
        .mobile-app-header {
            background: rgba(255,255,255,0.96);
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
        .mobile-app-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.75rem;
            padding: 0.75rem 1rem;
            border-radius: 0.95rem;
            font-size: 0.95rem;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
        }
        .mobile-app-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>

<body class="site-shell bg-light-bg mb-16 md:mb-0"> <div id="preloader-container">
        <div class="text-center w-32">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" class="w-20 mx-auto mb-4 animate-pulse">
            <div class="h-1 bg-gray-200 rounded-full overflow-hidden"><div class="loading-bar"></div></div>
        </div>
    </div>

    <script>
    window.APP_CONFIG = {
        baseUrl: <?php echo json_encode(BASE_URL); ?>,
        csrfToken: <?php echo json_encode(csrf_token()); ?>
    };
    </script>

    <header class="site-header sticky top-0 z-50">
        
        <div class="px-0 md:px-8 py-0 md:py-3">
            
            <!-- Mobile Header (Android App Style) -->
            <div class="md:hidden w-full">
                <div class="bg-white shadow-sm px-4 py-3 flex items-center justify-between border-b border-gray-100">
                    <a href="<?php echo BASE_URL; ?>/" class="flex items-center gap-2">
                        <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo" class="h-9 w-auto">
                        <div class="flex flex-col">
                            <span class="text-[14px] font-bold text-gray-800 leading-none">Teman Amal</span>
                            <span class="text-[9px] uppercase tracking-wider text-primary-green mt-0.5">Lazismu Tulungagung</span>
                        </div>
                    </a>

                    <div class="flex items-center gap-3">
                        <a href="<?php echo BASE_URL; ?>/search" class="w-9 h-9 flex items-center justify-center text-gray-500 hover:bg-gray-50 rounded-full transition-colors">
                            <i class="bi bi-search text-lg"></i>
                        </a>
                        <button type="button" id="mobile-menu-toggle" class="w-9 h-9 flex items-center justify-center text-gray-800 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200 transition-colors" aria-label="Menu">
                            <i class="bi bi-list text-2xl"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="hidden md:flex justify-between items-center">
                <div class="flex items-center gap-8">
                    <a href="<?php echo BASE_URL; ?>/" class="flex items-center gap-3">
                        <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Lazismu" class="h-10">
                        <div>
                            <span class="block text-xl font-bold text-gray-800">Teman Amal</span>
                            <span class="block text-xs uppercase tracking-[0.25em] text-primary-green">Lazismu Tulungagung</span>
                        </div>
                    </a>
                    <form action="<?php echo BASE_URL; ?>/search" class="relative w-72">
                        <input type="search" name="q" class="w-full pl-4 pr-10 py-2 rounded-full bg-gray-50 border border-gray-200 text-sm focus:outline-none focus:border-primary-green" placeholder="Cari program...">
                        <button class="absolute right-3 top-2 text-gray-400"><i class="bi bi-search"></i></button>
                    </form>
                </div>

                <div class="flex items-center gap-6">
                    <nav class="flex gap-6 text-sm font-medium text-gray-600">
                        <a href="<?php echo BASE_URL; ?>/" class="hover:text-primary-green">Beranda</a>
                        <a href="<?php echo BASE_URL; ?>/program" class="hover:text-primary-green">Program</a>
                        <a href="<?php echo BASE_URL; ?>/berita" class="hover:text-primary-green">Berita</a>
                        <a href="<?php echo BASE_URL; ?>/personalia" class="hover:text-primary-green">Personalia</a>
                        <a href="<?php echo BASE_URL; ?>/majalah" class="hover:text-primary-green">Majalah</a>
                        <a href="<?php echo BASE_URL; ?>/kalkulator_zakat" class="hover:text-primary-green">Kalkulator</a>
                    </nav>
                    <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
                        <?php if ($is_logged_in): ?>
                            <a href="<?php echo $dashboard_link; ?>" class="text-sm font-semibold text-gray-700 hover:text-primary-green">Dasbor</a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/login" class="text-sm font-semibold text-gray-600 hover:text-primary-orange">Masuk</a>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>/donasi" class="px-5 py-2 bg-primary-orange text-white text-sm font-bold rounded-full hover:bg-orange-600 shadow-md transition">Donasi</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div id="mobile-nav-panel" class="hidden md:hidden bg-white border-b border-gray-100 absolute w-full left-0 shadow-lg">
            <nav class="flex flex-col py-2 px-4 space-y-1">
                <a href="<?php echo BASE_URL; ?>/" class="flex items-center py-3 px-3 rounded-xl <?php echo ($current_path == '/' || $current_path == '/index.php') ? 'bg-orange-50 text-primary-orange font-bold' : 'text-gray-600 font-medium'; ?>">
                    <i class="bi bi-house-door mr-3 text-lg"></i> Beranda
                </a>
                <a href="<?php echo BASE_URL; ?>/program" class="flex items-center py-3 px-3 rounded-xl <?php echo (strpos($current_path, '/program') === 0) ? 'bg-orange-50 text-primary-orange font-bold' : 'text-gray-600 font-medium'; ?>">
                    <i class="bi bi-grid mr-3 text-lg"></i> Program Donasi
                </a>
                <a href="<?php echo BASE_URL; ?>/kalkulator_zakat" class="flex items-center py-3 px-3 rounded-xl <?php echo (strpos($current_path, '/kalkulator_zakat') === 0) ? 'bg-green-50 text-primary-green font-bold' : 'text-gray-600 font-medium'; ?>">
                    <i class="bi bi-calculator mr-3 text-lg"></i> Kalkulator Zakat
                </a>
                <a href="<?php echo BASE_URL; ?>/berita" class="flex items-center py-3 px-3 rounded-xl <?php echo (strpos($current_path, '/berita') === 0) ? 'bg-orange-50 text-primary-orange font-bold' : 'text-gray-600 font-medium'; ?>">
                    <i class="bi bi-newspaper mr-3 text-lg"></i> Kabar & Berita
                </a>
                <a href="<?php echo BASE_URL; ?>/majalah" class="flex items-center py-3 px-3 rounded-xl <?php echo (strpos($current_path, '/majalah') === 0) ? 'bg-orange-50 text-primary-orange font-bold' : 'text-gray-600 font-medium'; ?>">
                    <i class="bi bi-book mr-3 text-lg"></i> Majalah
                </a>
                <hr class="my-2 border-gray-100">
                <?php if ($is_logged_in): ?>
                    <a href="<?php echo $dashboard_link; ?>" class="flex items-center py-3 px-3 rounded-xl text-gray-700 font-semibold bg-gray-50">
                        <i class="bi bi-person-circle mr-3 text-lg"></i> Dasbor Akun
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login" class="flex items-center py-3 px-3 rounded-xl text-gray-700 font-semibold bg-gray-50">
                        <i class="bi bi-box-arrow-in-right mr-3 text-lg"></i> Masuk / Daftar
                    </a>
                <?php endif; ?>
            </nav>
        </div>

    </header>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const preloader = document.getElementById('preloader-container');
        const mobileToggle = document.getElementById('mobile-menu-toggle');
        const mobilePanel = document.getElementById('mobile-nav-panel');
        if (preloader) {
            // Langsung hide preloader ketika DOM ready, jangan tunggu semua gambar yang bisa bikin stuck
            preloader.classList.add('hiding');
            preloader.addEventListener('transitionend', () => preloader.remove());
        }
        if (mobileToggle && mobilePanel) {
            mobileToggle.addEventListener('click', function() {
                mobilePanel.classList.toggle('hidden');
            });
        }
    });
    </script>
