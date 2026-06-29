<?php
/*
|--------------------------------------------------------------------------
| File: amil/templates/header_amil.php (DIPERBARUI)
|--------------------------------------------------------------------------
|
| Header dengan logo, tulisan "Dasbor Amil", dan foto profil pengguna.
| Tombol menu untuk mobile juga telah diperbaiki.
|
*/

// Mengambil data sesi yang dibutuhkan
$nama_amil_login = $_SESSION['amil_nama_lengkap'] ?? 'Amil';
$foto_amil_login = $_SESSION['amil_foto'] ?? 'default.png';
// Ambil huruf pertama dari nama untuk inisial jika tidak ada foto
$inisial_amil = !empty($nama_amil_login) ? strtoupper(substr($nama_amil_login, 0, 1)) : 'A';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Amil Lazismu' : 'Dashboard Amil'; ?>
    </title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS untuk Desain Baru -->
    <style>
    :root {
        --primary-color: #fb8c00; /* Oranye Lazismu */
        --primary-hover: #e67e00;
        --secondary-color: #6c757d;
        --background-color: #f4f7f9;
        --text-color: #333;
        --card-bg: #ffffff;
        --border-color: #e9ecef;
        --font-family: 'Inter', sans-serif;
    }

    body {
        font-family: var(--font-family);
        background-color: var(--background-color);
        color: var(--text-color);
    }

    .app-container {
        display: flex;
        height: 100vh;
    }

    /* --- Sidebar untuk Desktop --- */
    .app-sidebar {
        width: 260px;
        background-color: var(--card-bg);
        border-right: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease;
    }

    /* --- Konten Utama --- */
    .app-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
    }

    .app-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background-color: var(--card-bg);
        border-bottom: 1px solid var(--border-color);
        position: sticky;
        top: 0;
        z-index: 1020;
    }
    
    .app-header .page-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
        display: none;
    }
    @media (min-width: 576px) {
        .app-header .page-title {
            display: block;
        }
    }

    /* --- Logo Aplikasi --- */
    .app-logo {
        color: var(--text-color);
    }
    .app-logo img {
        height: 32px;
        width: auto;
    }
    .app-logo span {
        font-size: 1.1rem;
        color: var(--text-color);
    }

    /* --- Avatar Profil --- */
    .profile-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--primary-color);
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: 600;
        text-decoration: none;
        overflow: hidden;
    }
    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    main {
        padding: 1.5rem;
    }

    /* --- Navigasi Bawah untuk Mobile --- */
    .app-footer-nav {
        display: none;
    }

    @media (max-width: 992px) {
        .app-sidebar {
            position: fixed;
            transform: translateX(-100%);
            z-index: 1030;
            height: 100%;
        }
        .sidebar-open .app-sidebar {
            transform: translateX(0);
        }
        
        .sidebar-toggler {
            display: block !important;
        }

        .app-footer-nav {
            position: fixed; bottom: 0; left: 0; right: 0;
            height: 70px; background-color: var(--card-bg);
            border-top: 1px solid var(--border-color);
            display: flex; justify-content: space-around; align-items: center;
            z-index: 1000; box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }
        .footer-nav-item {
            display: flex; flex-direction: column; align-items: center;
            text-decoration: none; color: var(--secondary-color);
            font-size: 0.7rem; font-weight: 500;
        }
        .footer-nav-item i { font-size: 1.5rem; margin-bottom: 2px; }
        .footer-nav-item.active { color: var(--primary-color); }
        .footer-nav-item.action-button { margin-top: -35px; }
        .footer-nav-item.action-button .icon-wrapper {
             width: 60px; height: 60px; border-radius: 50%;
             background-color: var(--primary-color); color: white;
             display: flex; align-items: center; justify-content: center;
             box-shadow: 0 4px 12px rgba(0,0,0,0.2); border: 3px solid white;
        }
         .footer-nav-item.action-button i { font-size: 1.8rem; margin: 0; }
        
        main {
            padding-bottom: 90px;
        }
    }

    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    </style>
</head>

<body>

    <div class="app-container">
        <?php require_once 'sidebar_amil.php'; ?>
        
        <div class="app-content">
            <header class="app-header">
                <!-- [DIPERBARUI] Bagian Kiri Header -->
                <div class="d-flex align-items-center gap-3">
                    <button class="btn d-lg-none p-0 border-0 sidebar-toggler" type="button" aria-label="Toggle sidebar">
                        <i class="bi bi-list fs-3"></i>
                    </button>
                    <a href="dashboard.php" class="app-logo text-decoration-none d-flex align-items-center gap-2">
                        <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo Dasbor Amil">
                        <span class="fw-bold d-none d-sm-inline">Dasbor Amil</span>
                    </a>
                </div>

                <h1 class="page-title text-center position-absolute top-50 start-50 translate-middle"><?php echo htmlspecialchars($page_title ?? 'Dashboard'); ?></h1>
                
                <a href="edit_profil.php" class="profile-avatar" title="Edit Profil">
                    <?php if (isset($foto_amil_login) && $foto_amil_login != 'default.png' && file_exists("../assets/uploads/amil/" . $foto_amil_login)): ?>
                        <img src="../assets/uploads/amil/<?php echo htmlspecialchars($foto_amil_login); ?>" alt="Foto Profil">
                    <?php else: ?>
                        <span><?php echo $inisial_amil; ?></span>
                    <?php endif; ?>
                </a>
            </header>

            <main>

