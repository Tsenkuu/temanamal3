<?php
/*
|--------------------------------------------------------------------------
| File: amil/templates/sidebar_amil.php (DIROMBAK)
|--------------------------------------------------------------------------
|
| Sidebar baru yang modern, hanya untuk tampilan desktop.
|
*/
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="app-sidebar">
    <div class="sidebar-header p-3 text-center border-bottom">
        <a href="dashboard.php" class="text-decoration-none">
            <h4 class="fw-bold text-dark mb-0">LAZISMU</h4>
            <small class="text-muted">Panel Amil</small>
        </a>
    </div>

    <div class="sidebar-menu p-3">
        <ul class="nav flex-column">
             <li class="nav-item-header text-muted small text-uppercase mb-2">Utama</li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-grid-1x2-fill me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'tugas_saya.php') ? 'active' : ''; ?>" href="tugas_saya.php">
                    <i class="bi bi-card-checklist me-2"></i> Tugas Saya
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo in_array($current_page, ['riwayat_pengambilan.php', 'edit_pengambilan.php']) ? 'active' : ''; ?>" href="riwayat_pengambilan.php">
                    <i class="bi bi-clock-history me-2"></i> Riwayat
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'peta_kotak_infak.php') ? 'active' : ''; ?>" href="peta_kotak_infak.php">
                    <i class="bi bi-map-fill me-2"></i> Peta Navigasi
                </a>
            </li>
            
            <li class="nav-item-header text-muted small text-uppercase my-2 pt-2">Manajemen</li>
            <li class="nav-item">
                <a class="nav-link <?php echo in_array($current_page, ['kelola_kotak_infaq.php', 'tambah_kotak_infak.php', 'edit_kotak_infak.php']) ? 'active' : ''; ?>" href="kelola_kotak_infaq.php">
                    <i class="bi bi-box-seam-fill me-2"></i> Kotak Infak
                </a>
            </li>
             <li class="nav-item">
                <a class="nav-link <?php echo in_array($current_page, ['kelola_berita.php', 'tambah_berita.php', 'edit_berita.php']) ? 'active' : ''; ?>" href="kelola_berita.php">
                    <i class="bi bi-newspaper me-2"></i> Berita
                </a>
            </li>

            <li class="nav-item-header text-muted small text-uppercase my-2 pt-2">Akun</li>
             <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'edit_profil.php') ? 'active' : ''; ?>" href="edit_profil.php">
                    <i class="bi bi-person-circle me-2"></i> Profil Saya
                </a>
            </li>
        </ul>
    </div>
    <div class="mt-auto p-3 border-top">
         <a class="btn btn-outline-secondary w-100" href="../logout.php">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
        </a>
    </div>
</nav>

<style>
.app-sidebar .nav-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    color: #555;
    font-weight: 500;
    margin-bottom: 4px;
}
.app-sidebar .nav-link:hover {
    background-color: #fcece3;
    color: var(--primary-color);
}
.app-sidebar .nav-link.active {
    background-color: var(--primary-color);
    color: white;
    font-weight: 600;
}
</style>
