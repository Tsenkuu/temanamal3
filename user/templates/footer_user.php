<?php
// Menentukan halaman aktif untuk navigasi bawah
$current_page_footer = basename($_SERVER['PHP_SELF']);
$current_path = $_SERVER['REQUEST_URI'];
?>
</main> <!-- End of main content from header -->

<!-- Footer Section -->
<footer id="kontak" class="site-footer bg-white border-t border-gray-100 pt-16 pb-28 md:pb-12 px-4 md:px-8 mt-auto relative z-10">
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-16 mb-12">
        
        <!-- Kolom Brand & Informasi -->
        <div class="md:col-span-5 lg:col-span-4">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-primary-orange rounded-xl flex items-center justify-center shadow-soft">
                    <i class="bi bi-heart-fill text-white text-2xl"></i>
                </div>
                <span class="text-2xl font-display font-bold text-gray-900 tracking-tight">Teman<span class="text-primary-orange">Amal</span></span>
            </div>
            <p class="text-gray-500 mb-6 text-sm leading-relaxed pr-4">
                Platform donasi online terpercaya yang berkhidmat dalam pemberdayaan masyarakat melalui pendayagunaan dana zakat, infaq, wakaf dan dana kemanusiaan lainnya.
            </p>
            <div class="space-y-3 text-sm text-gray-600">
                <p class="flex items-start gap-3 group">
                    <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center flex-shrink-0 group-hover:bg-primary-orange transition-colors">
                        <i class="bi bi-geo-alt-fill text-primary-orange group-hover:text-white transition-colors"></i>
                    </div>
                    <span class="pt-1">Jl. Ade Irma Suryani No.16, Sembung, Tulungagung, Jawa Timur 66219</span>
                </p>
                <p class="flex items-center gap-3 group">
                    <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center flex-shrink-0 group-hover:bg-primary-orange transition-colors">
                        <i class="bi bi-whatsapp text-primary-orange group-hover:text-white transition-colors"></i>
                    </div>
                    <span>0821-2599-1199</span>
                </p>
            </div>
        </div>

        <!-- Kolom Tautan Cepat -->
        <div class="md:col-span-3 lg:col-span-3 lg:col-start-6">
            <h5 class="text-base font-bold mb-5 text-gray-900 tracking-wide uppercase">Jelajahi</h5>
            <ul class="space-y-3 text-sm">
                <li><a href="<?php echo BASE_URL; ?>/program" class="text-gray-500 hover:text-primary-orange transition-colors flex items-center gap-2"><i class="bi bi-chevron-right text-xs"></i> Program Donasi</a></li>
                <li><a href="<?php echo BASE_URL; ?>/berita" class="text-gray-500 hover:text-primary-orange transition-colors flex items-center gap-2"><i class="bi bi-chevron-right text-xs"></i> Kabar Terbaru</a></li>
                <li><a href="<?php echo BASE_URL; ?>/kalkulator_zakat" class="text-gray-500 hover:text-primary-orange transition-colors flex items-center gap-2"><i class="bi bi-chevron-right text-xs"></i> Kalkulator Zakat</a></li>
                <li><a href="<?php echo BASE_URL; ?>/laporan" class="text-gray-500 hover:text-primary-orange transition-colors flex items-center gap-2"><i class="bi bi-chevron-right text-xs"></i> Laporan Keuangan</a></li>
            </ul>
        </div>

        <!-- Kolom Dukungan & Aksi -->
        <div class="md:col-span-4 lg:col-span-3">
            <h5 class="text-base font-bold mb-5 text-gray-900 tracking-wide uppercase">Dukungan</h5>
            <ul class="space-y-3 text-sm mb-6">
                <li><a href="<?php echo BASE_URL; ?>/tentang_kami" class="text-gray-500 hover:text-primary-orange transition-colors flex items-center gap-2"><i class="bi bi-chevron-right text-xs"></i> Tentang Kami</a></li>
                <li><a href="<?php echo BASE_URL; ?>/faq" class="text-gray-500 hover:text-primary-orange transition-colors flex items-center gap-2"><i class="bi bi-chevron-right text-xs"></i> Syarat & Ketentuan</a></li>
                <li><a href="<?php echo BASE_URL; ?>/kebijakan" class="text-gray-500 hover:text-primary-orange transition-colors flex items-center gap-2"><i class="bi bi-chevron-right text-xs"></i> Kebijakan Privasi</a></li>
            </ul>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="border-t border-gray-100 pt-8 mt-4">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm text-gray-500 font-medium text-center md:text-left">
                &copy; <span id="current-year"></span> TemanAmal. Memberi untuk Negeri.
            </p>
            
            <!-- Social Media Icons -->
            <div class="flex space-x-4">
                <a href="#" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-[#1877F2] hover:text-white transition-all shadow-sm">
                    <i class="bi bi-facebook text-lg"></i>
                </a>
                <a href="#" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-[#E4405F] hover:text-white transition-all shadow-sm">
                    <i class="bi bi-instagram text-lg"></i>
                </a>
                <a href="#" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-[#FF0000] hover:text-white transition-all shadow-sm">
                    <i class="bi bi-youtube text-lg"></i>
                </a>
            </div>
        </div>
    </div>

</footer>

<!-- Mobile Bottom Navigation (App-like feel) -->
<nav class="fixed bottom-0 left-0 right-0 bg-white z-50 md:hidden shadow-[0_-8px_30px_rgba(0,0,0,0.08)] rounded-t-[32px]">
    <style>
        .pb-safe { padding-bottom: calc(env(safe-area-inset-bottom, 12px) + 12px); }
    </style>

    <div class="flex justify-around items-end px-2 pt-3 pb-safe relative">
        
        <a href="<?php echo BASE_URL; ?>/" class="flex flex-col items-center justify-center w-[18%] pb-2 group <?php echo (strpos($current_path, '/index.php') !== false || $current_path == BASE_URL) ? 'text-primary-orange' : 'text-slate-400'; ?>">
            <div class="relative w-12 h-12 flex items-center justify-center rounded-full transition-all duration-300 <?php echo (strpos($current_path, '/index.php') !== false || $current_path == BASE_URL) ? 'bg-orange-50' : 'bg-transparent'; ?>">
                <i class="bi bi-house-door<?php echo (strpos($current_path, '/index.php') !== false || $current_path == BASE_URL) ? '-fill' : ''; ?> text-[24px]"></i>
            </div>
            <span class="text-[11px] font-medium mt-0.5 tracking-tight">Beranda</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>/program" class="flex flex-col items-center justify-center w-[18%] pb-2 group <?php echo (strpos($current_path, '/program') !== false) ? 'text-primary-orange' : 'text-slate-400'; ?>">
            <div class="relative w-12 h-12 flex items-center justify-center rounded-full transition-all duration-300 <?php echo (strpos($current_path, '/program') !== false) ? 'bg-orange-50' : 'bg-transparent'; ?>">
                <i class="bi bi-grid<?php echo (strpos($current_path, '/program') !== false) ? '-fill' : ''; ?> text-[24px]"></i>
            </div>
            <span class="text-[11px] font-medium mt-0.5 tracking-tight">Program</span>
        </a>
        
        <!-- Tempat Kosong Untuk Tombol Tengah -->
        <div class="w-[20%]"></div>
        
        <!-- Center Floating Action Button -->
        <div class="absolute left-1/2 -translate-x-1/2 -top-10 z-[60]">
            <a href="<?php echo BASE_URL; ?>/program" class="flex items-center justify-center w-[72px] h-[72px] bg-primary-orange text-white rounded-[24px] shadow-[0_15px_30px_-5px_rgba(251,130,1,0.5)] transform hover:scale-105 active:scale-95 transition-all border-[6px] border-white">
                <i class="bi bi-heart-fill text-[28px]"></i>
            </a>
        </div>
        
        <a href="<?php echo BASE_URL; ?>/berita" class="flex flex-col items-center justify-center w-[18%] pb-2 group <?php echo (strpos($current_path, '/berita') !== false) ? 'text-primary-orange' : 'text-slate-400'; ?>">
            <div class="relative w-12 h-12 flex items-center justify-center rounded-full transition-all duration-300 <?php echo (strpos($current_path, '/berita') !== false) ? 'bg-orange-50' : 'bg-transparent'; ?>">
                <i class="bi bi-newspaper text-[24px]"></i>
            </div>
            <span class="text-[11px] font-medium mt-0.5 tracking-tight">Berita</span>
        </a>
        
        <a href="<?php echo BASE_URL; ?>/user/dashboard" class="flex flex-col items-center justify-center w-[18%] pb-2 group <?php echo (strpos($current_path, '/user') !== false) ? 'text-primary-orange' : 'text-slate-400'; ?>">
            <div class="relative w-12 h-12 flex items-center justify-center rounded-full transition-all duration-300 <?php echo (strpos($current_path, '/user') !== false) ? 'bg-orange-50' : 'bg-transparent'; ?>">
                <i class="bi bi-person<?php echo (strpos($current_path, '/user') !== false) ? '-fill' : ''; ?> text-[24px]"></i>
            </div>
            <span class="text-[11px] font-medium mt-0.5 tracking-tight">Akun</span>
        </a>
    </div>
</nav>

<!-- Floating Buttons (Tampil di semua perangkat) -->
<div class="fixed bottom-[110px] md:bottom-24 right-4 z-40 flex flex-col gap-3">
    <button id="fab-chat" class="w-14 h-14 bg-[#25D366] text-white rounded-full shadow-[0_8px_20px_rgba(37,211,102,0.4)] flex items-center justify-center hover:scale-105 active:scale-95 transition-transform" title="Tanya Admin">
        <i class="bi bi-whatsapp text-2xl"></i>
    </button>
</div>

<?php require_once __DIR__ . '/../../app/views/components/chat_modal.php'; ?>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const yearEl = document.getElementById('current-year');
    if (yearEl) {
        yearEl.textContent = new Date().getFullYear();
    }
    
    // Script untuk dropdown menu (disalin dari header_user.php / footer_user.php sebelumnya)
    const userMenuButton = document.getElementById('user-menu-button');
    const userMenu = document.getElementById('user-menu');

    if (userMenuButton && userMenu) {
        userMenuButton.addEventListener('click', function () {
            const isExpanded = userMenuButton.getAttribute('aria-expanded') === 'true';
            userMenuButton.setAttribute('aria-expanded', !isExpanded);
            userMenu.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (event) {
            if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                userMenu.classList.add('hidden');
                userMenuButton.setAttribute('aria-expanded', 'false');
            }
        });
    }
});
</script>
</body>
</html>
