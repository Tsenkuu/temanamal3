<?php
// Menentukan grup mana yang aktif berdasarkan judul halaman
$current_page_title = $page_title ?? '';
$active_group = '';
$page_groups = [
    'donasi' => ['Konfirmasi Donasi', 'Riwayat Donasi', 'Laporan & Impor Data'],
    'konten' => ['Kelola Program Donasi', 'Kelola Berita', 'Kelola Dokumentasi Kegiatan', 'Kelola Majalah', 'Kelola Slider Halaman Depan', 'Kelola Komentar'],
    'kotak' => ['Kelola Kotak Infak', 'Penugasan Amil', 'Riwayat Pengambilan Kotak Infak', 'Peta Navigasi Kotak Infak'],
    'manajemen' => ['Kelola Amil', 'Kelola Donatur & Blast WA', 'Kelola Metode Pembayaran'],
    'sistem' => ['Pengaturan Website', 'Statistik', 'Ganti Password']
];

foreach ($page_groups as $group => $titles) {
    if (in_array($current_page_title, $titles)) {
        $active_group = $group;
        break;
    }
}
?>

<!-- Sidebar Backdrop (Mobile) -->
<div x-show="sidebarOpen" 
     x-transition:enter="transition-opacity ease-linear duration-300" 
     x-transition:enter-start="opacity-0" 
     x-transition:enter-end="opacity-100" 
     x-transition:leave="transition-opacity ease-linear duration-300" 
     x-transition:leave-start="opacity-100" 
     x-transition:leave-end="opacity-0" 
     @click="sidebarOpen = false"
     class="fixed inset-0 bg-gray-900/80 z-40 lg:hidden" style="display: none;"></div>

<!-- Sidebar -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
       class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col shadow-2xl">
    
    <!-- Logo Area -->
    <div class="flex items-center justify-between h-16 px-6 bg-slate-950 border-b border-slate-800 shrink-0">
        <div class="flex items-center gap-3">
            <img src="../assets/images/logo.png" alt="Logo" class="h-8 w-auto brightness-0 invert">
            <span class="font-bold text-lg text-white tracking-wide">Admin Panel</span>
        </div>
        <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
            <i class="bi bi-x-lg text-xl"></i>
        </button>
    </div>

    <!-- Menu Items -->
    <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1 custom-scrollbar" x-data="{ activeAccordion: '<?php echo $active_group; ?>' }">
        
        <!-- Dashboard -->
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo ($page_title == 'Dashboard Admin') ? 'bg-primary-orange text-white shadow-lg shadow-orange-500/30' : 'hover:bg-slate-800 hover:text-white'; ?>">
            <i class="bi bi-grid-1x2-fill text-lg"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <div class="pt-4 pb-2">
            <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Modul Utama</p>
        </div>

        <!-- Donasi Group -->
        <div class="space-y-1">
            <button @click="activeAccordion = activeAccordion === 'donasi' ? '' : 'donasi'" 
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 <?php echo ($active_group == 'donasi') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800/50 hover:text-white'; ?>">
                <div class="flex items-center gap-3">
                    <i class="bi bi-wallet2 text-lg"></i>
                    <span class="font-medium">Manajemen Donasi</span>
                </div>
                <i class="bi bi-chevron-down text-sm transition-transform duration-300" :class="activeAccordion === 'donasi' ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="activeAccordion === 'donasi'" x-collapse>
                <div class="pl-11 pr-4 py-2 space-y-1">
                    <a href="konfirmasi_donasi.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Konfirmasi Donasi') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Konfirmasi Donasi</a>
                    <a href="riwayat_donasi.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Riwayat Donasi') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Riwayat Donasi</a>
                    <a href="kelola_laporan.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Laporan & Impor Data') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Laporan & Impor</a>
                </div>
            </div>
        </div>

        <!-- Program & Konten -->
        <div class="space-y-1">
            <button @click="activeAccordion = activeAccordion === 'konten' ? '' : 'konten'" 
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 <?php echo ($active_group == 'konten') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800/50 hover:text-white'; ?>">
                <div class="flex items-center gap-3">
                    <i class="bi bi-megaphone text-lg"></i>
                    <span class="font-medium">Konten Publik</span>
                </div>
                <i class="bi bi-chevron-down text-sm transition-transform duration-300" :class="activeAccordion === 'konten' ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="activeAccordion === 'konten'" x-collapse>
                <div class="pl-11 pr-4 py-2 space-y-1">
                    <a href="kelola_program.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Kelola Program Donasi') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Program Donasi</a>
                    <a href="kelola_berita.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Kelola Berita') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Berita & Artikel</a>
                    <a href="kelola_dokumentasi.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Kelola Dokumentasi Kegiatan') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Dokumentasi</a>
                    <a href="kelola_majalah.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Kelola Majalah') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Majalah (PDF)</a>
                    <a href="kelola_slider.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Kelola Slider Halaman Depan') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Banner Slider</a>
                    <a href="kelola_komentar.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Kelola Komentar') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Moderasi Komentar</a>
                </div>
            </div>
        </div>

        <!-- Kotak Infak -->
        <div class="space-y-1">
            <button @click="activeAccordion = activeAccordion === 'kotak' ? '' : 'kotak'" 
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 <?php echo ($active_group == 'kotak') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800/50 hover:text-white'; ?>">
                <div class="flex items-center gap-3">
                    <i class="bi bi-box-seam text-lg"></i>
                    <span class="font-medium">Kotak Infak</span>
                </div>
                <i class="bi bi-chevron-down text-sm transition-transform duration-300" :class="activeAccordion === 'kotak' ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="activeAccordion === 'kotak'" x-collapse>
                <div class="pl-11 pr-4 py-2 space-y-1">
                    <a href="kelola_kotak_infak.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Kelola Kotak Infak') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Data Kotak</a>
                    <a href="kelola_tugas.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Penugasan Amil') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Penugasan Amil</a>
                    <a href="riwayat_pengambilan.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Riwayat Pengambilan Kotak Infak') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Riwayat Ambil</a>
                    <a href="peta_kotak_infak.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Peta Navigasi Kotak Infak') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Peta Sebaran (Maps)</a>
                </div>
            </div>
        </div>

        <div class="pt-4 pb-2">
            <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Konfigurasi</p>
        </div>

        <!-- Manajemen -->
        <div class="space-y-1">
            <button @click="activeAccordion = activeAccordion === 'manajemen' ? '' : 'manajemen'" 
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 <?php echo ($active_group == 'manajemen') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800/50 hover:text-white'; ?>">
                <div class="flex items-center gap-3">
                    <i class="bi bi-people text-lg"></i>
                    <span class="font-medium">User & Amil</span>
                </div>
                <i class="bi bi-chevron-down text-sm transition-transform duration-300" :class="activeAccordion === 'manajemen' ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="activeAccordion === 'manajemen'" x-collapse>
                <div class="pl-11 pr-4 py-2 space-y-1">
                    <a href="kelola_amil.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Kelola Amil') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Data Amil</a>
                    <a href="kelola_user.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Kelola Donatur & Blast WA') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">User & Blast WA</a>
                    <a href="kelola_pembayaran.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Kelola Metode Pembayaran') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Metode Bayar</a>
                </div>
            </div>
        </div>

        <!-- Sistem -->
        <div class="space-y-1">
            <button @click="activeAccordion = activeAccordion === 'sistem' ? '' : 'sistem'" 
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 <?php echo ($active_group == 'sistem') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800/50 hover:text-white'; ?>">
                <div class="flex items-center gap-3">
                    <i class="bi bi-gear text-lg"></i>
                    <span class="font-medium">Pengaturan</span>
                </div>
                <i class="bi bi-chevron-down text-sm transition-transform duration-300" :class="activeAccordion === 'sistem' ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="activeAccordion === 'sistem'" x-collapse>
                <div class="pl-11 pr-4 py-2 space-y-1">
                    <a href="pengaturan.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Pengaturan Website') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Identitas Web</a>
                    <a href="keranjang_sampah.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Keranjang Sampah') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Keranjang Sampah</a>
                    <a href="statistik.php" class="block py-2 text-sm transition-colors <?php echo ($page_title == 'Statistik') ? 'text-primary-orange font-medium' : 'text-slate-400 hover:text-white'; ?>">Statistik Detail</a>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Sidebar Footer / Logout -->
    <div class="p-4 border-t border-slate-800 shrink-0">
        <a href="../logout.php" class="flex items-center justify-center gap-2 w-full py-3 px-4 bg-slate-800 hover:bg-red-500/10 text-slate-300 hover:text-red-500 rounded-xl transition-colors font-medium">
            <i class="bi bi-power"></i> Keluar
        </a>
    </div>

</aside>

<style>
/* Sidebar Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #334155;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #475569;
}
</style>