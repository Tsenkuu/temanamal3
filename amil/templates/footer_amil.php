<?php
/*
|--------------------------------------------------------------------------
| File: amil/templates/footer_amil.php (DIPERBARUI)
|--------------------------------------------------------------------------
|
| Footer untuk semua halaman di area Amil.
| [BARU] Menambahkan logika JavaScript untuk tombol hamburger sidebar.
|
*/

// Menentukan halaman aktif untuk navigasi bawah
$current_page_footer = basename($_SERVER['PHP_SELF']);
$nav_items = [
    'dashboard.php' => ['icon' => 'bi-house-door-fill', 'label' => 'Beranda'],
    'kelola_kotak_infaq.php' => ['icon' => 'bi-collection-fill', 'label' => 'Kotak Infak'],
    'tambah_kotak_infak.php' => ['icon' => 'bi-plus-circle-fill', 'label' => 'Tambah', 'action' => true],
    'tugas_saya.php' => ['icon' => 'bi-card-checklist', 'label' => 'Tugas'],
    'peta_kotak_infak.php' => ['icon' => 'bi-map-fill', 'label' => 'Peta'],
];
?>

            </main>
        </div>
    </div>

    <!-- [BARU] Overlay untuk sidebar mobile -->
    <div class="sidebar-overlay"></div>

    <!-- Navigasi Bawah untuk Mobile -->
    <nav class="app-footer-nav shadow">
        <?php foreach ($nav_items as $page => $item): ?>
            <a href="<?php echo $page; ?>" class="footer-nav-item <?php echo ($current_page_footer == $page) ? 'active' : ''; ?> <?php echo isset($item['action']) ? 'action-button' : ''; ?>">
                <?php if (isset($item['action'])): ?>
                    <div class="icon-wrapper">
                        <i class="bi <?php echo $item['icon']; ?>"></i>
                    </div>
                <?php else: ?>
                    <i class="bi <?php echo $item['icon']; ?>"></i>
                    <span><?php echo $item['label']; ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- [BARU] Skrip untuk fungsionalitas Sidebar -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggler = document.querySelector('.sidebar-toggler');
    const appContainer = document.querySelector('.app-container');
    const sidebarOverlay = document.querySelector('.sidebar-overlay');

    if (sidebarToggler && appContainer) {
        // Event listener untuk tombol hamburger
        sidebarToggler.addEventListener('click', function () {
            appContainer.classList.toggle('sidebar-open');
        });

        // Event listener untuk overlay (menutup sidebar saat diklik)
        sidebarOverlay.addEventListener('click', function () {
            appContainer.classList.remove('sidebar-open');
        });
    }
});
</script>

</body>
</html>

