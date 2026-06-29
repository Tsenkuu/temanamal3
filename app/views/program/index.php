<?php
// Helpers
$program_link = static fn($p): string => BASE_URL . '/program/' . rawurlencode((string)(!empty($p['slug']) ? $p['slug'] : $p['id']));
$program_img = static fn($p): ?string => !empty($p['gambar']) ? BASE_URL . '/assets/uploads/program/' . rawurlencode((string)$p['gambar']) : null;
$calc_percent = static fn($terkumpul, $target): int => $target > 0 ? min(100, (int)(($terkumpul / $target) * 100)) : 0;
$format_rupiah = static fn($num): string => 'Rp ' . number_format((float)$num, 0, ',', '.');
$days_left = static function($deadline) {
    if (!$deadline) return 'Tanpa batas';
    $diff = strtotime($deadline) - time();
    return $diff > 0 ? floor($diff / (60 * 60 * 24)) . ' hari lagi' : 'Selesai';
};

require_once __DIR__ . '/../../../includes/templates/header.php';
?>

<!-- Alpine.js untuk Search & Skeleton -->
<main x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 800)" class="bg-gray-light min-h-screen pb-24">
    
    <!-- Hero Section -->
    <section class="bg-white pt-8 pb-12 shadow-sm relative z-10 rounded-b-[32px]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center gap-8">
            <div class="w-full md:w-1/2 text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-orange-50 text-primary-orange font-bold text-xs uppercase tracking-wide mb-4">
                    <i class="bi bi-star-fill text-yellow-400"></i> Program Pilihan
                </div>
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-display font-bold text-dark-text tracking-tight mb-4 leading-tight">
                    Mari Bantu Sesama, <br><span class="text-primary-orange">Raih Berkah Bersama.</span>
                </h1>
                <p class="text-gray-500 text-base md:text-lg mb-8 max-w-lg mx-auto md:mx-0">
                    Pilih program yang paling sesuai dengan panggilan hati Anda. Sedikit dari Anda, segalanya bagi mereka.
                </p>
                <!-- Desktop Search -->
                <div class="hidden md:flex relative max-w-md">
                    <input type="text" placeholder="Cari program kebaikan..." class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3.5 pl-12 pr-4 text-sm focus:ring-2 focus:ring-primary-orange focus:border-primary-orange transition-all">
                    <i class="bi bi-search absolute left-4 top-4 text-gray-400 text-lg"></i>
                    <button class="absolute right-2 top-2 bg-primary-orange text-white px-4 py-1.5 rounded-lg font-bold text-sm shadow-soft hover:bg-orange-600 transition-colors">Cari</button>
                </div>
            </div>
            <div class="w-full md:w-1/2">
                <img src="<?= BASE_URL ?>/assets/images/hero-illustration.svg" onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder.jpg'" alt="Ilustrasi Sedekah" class="w-full h-auto max-h-[350px] object-contain rounded-[24px]">
            </div>
        </div>
    </section>

    <!-- Mobile Search & Categories -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-20 md:-mt-8">
        <div class="bg-white rounded-[24px] p-2 md:p-3 shadow-card flex flex-col md:hidden gap-3">
            <div class="relative w-full px-2 pt-2">
                <input type="text" placeholder="Cari program..." class="w-full bg-gray-50 border-none rounded-xl py-3 pl-11 pr-4 text-sm focus:ring-2 focus:ring-primary-orange transition-all">
                <i class="bi bi-search absolute left-5 top-5 text-gray-400"></i>
            </div>
            <div class="w-full overflow-x-auto no-scrollbar pb-2">
                <div class="flex items-center gap-2 px-2">
                    <a href="#" class="whitespace-nowrap px-4 py-2 rounded-xl bg-orange-50 text-primary-orange font-bold text-sm transition-colors border border-orange-100">Semua</a>
                    <?php foreach($categories as $cat): ?>
                        <a href="#" class="whitespace-nowrap px-4 py-2 rounded-xl bg-gray-50 text-gray-600 hover:bg-orange-50 hover:text-primary-orange font-medium text-sm transition-colors border border-transparent">
                            <?= $cat['name'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Desktop Categories -->
        <div class="hidden md:flex justify-center gap-3 mt-12 mb-8">
            <a href="#" class="px-6 py-2.5 rounded-full bg-primary-orange text-white font-bold text-sm shadow-[0_8px_20px_-6px_rgba(249,115,22,0.6)]">Semua Program</a>
            <?php foreach($categories as $cat): ?>
                <a href="#" class="px-6 py-2.5 rounded-full bg-white text-gray-600 hover:bg-orange-50 hover:text-primary-orange font-medium text-sm shadow-sm hover:shadow-md transition-all border border-gray-100"><?= $cat['name'] ?></a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Program List -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 md:mt-0">
        
        <!-- SKELETON LOADING -->
        <div x-show="loading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
            <?php for($i=0; $i<6; $i++): ?>
            <div class="animate-pulse bg-white rounded-[24px] overflow-hidden shadow-card">
                <div class="h-52 bg-gray-200 w-full"></div>
                <div class="p-6">
                    <div class="h-6 bg-gray-200 w-full rounded-md mb-3"></div>
                    <div class="h-4 bg-gray-200 w-3/4 rounded-md mb-6"></div>
                    <div class="h-2 bg-gray-200 w-full rounded-full mb-4"></div>
                    <div class="flex justify-between mb-6">
                        <div class="h-4 bg-gray-200 w-24 rounded-md"></div>
                        <div class="h-4 bg-gray-200 w-20 rounded-md"></div>
                    </div>
                    <div class="h-12 bg-gray-200 w-full rounded-xl"></div>
                </div>
            </div>
            <?php endfor; ?>
        </div>

        <!-- ACTUAL CONTENT -->
        <div x-show="!loading" x-cloak class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
            <?php if(!empty($programs)): ?>
                <?php foreach($programs as $program): 
                    $percent = $calc_percent($program['donasi_terkumpul'], $program['target_donasi']);
                ?>
                <div class="bg-white rounded-[24px] overflow-hidden shadow-card hover:shadow-[0_20px_40px_-15px_rgba(0,0,0,0.1)] hover:-translate-y-1.5 transition-all duration-300 flex flex-col group relative">
                    <!-- Image -->
                    <a href="<?= $program_link($program) ?>" class="block relative h-52 overflow-hidden bg-gray-100">
                        <img src="<?= $program_img($program) ?: BASE_URL.'/assets/images/placeholder.jpg' ?>" alt="<?= htmlspecialchars($program['nama_program']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        <!-- Badge -->
                        <div class="absolute top-4 left-4 bg-white/95 backdrop-blur text-gray-800 text-[10px] font-bold px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1.5">
                            <i class="bi bi-bookmark-fill text-primary-orange"></i> Kemanusiaan
                        </div>
                    </a>
                    
                    <!-- Content -->
                    <div class="p-5 md:p-6 flex flex-col flex-grow">
                        <h3 class="text-lg font-bold text-dark-text leading-snug mb-2 group-hover:text-primary-orange transition-colors line-clamp-2">
                            <a href="<?= $program_link($program) ?>"><?= htmlspecialchars($program['nama_program']) ?></a>
                        </h3>
                        <p class="text-sm text-gray-500 line-clamp-2 mb-5">
                            <?= htmlspecialchars(strip_tags($program['deskripsi'])) ?>
                        </p>
                        
                        <!-- Progress Area -->
                        <div class="mt-auto">
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-xs font-bold text-primary-orange bg-orange-50 px-2 py-0.5 rounded-md"><?= $percent ?>%</span>
                                <span class="text-[11px] text-gray-500 font-medium"><i class="bi bi-clock mr-1"></i> <?= $days_left($program['batas_waktu'] ?? null) ?></span>
                            </div>
                            <!-- Premium Progress Bar -->
                            <div class="w-full h-2.5 bg-gray-100 rounded-full overflow-hidden mb-4 shadow-inner">
                                <div class="h-full bg-gradient-to-r from-primary-orange to-orange-400 rounded-full relative" style="width: <?= $percent ?>%">
                                    <div class="absolute inset-0 bg-white/20 w-full h-full animate-[shimmer_2s_infinite]"></div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold mb-0.5">Terkumpul</p>
                                    <p class="text-sm font-bold text-dark-text"><?= $format_rupiah($program['donasi_terkumpul']) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold mb-0.5">Target</p>
                                    <p class="text-sm font-bold text-dark-text"><?= $format_rupiah($program['target_donasi']) ?></p>
                                </div>
                            </div>
                            
                            <!-- Donasi Button -->
                            <a href="<?= $program_link($program) ?>" class="block w-full text-center bg-white border-2 border-primary-orange text-primary-orange px-4 py-3 rounded-xl font-bold text-sm group-hover:bg-primary-orange group-hover:text-white transition-colors duration-300 shadow-sm active:scale-95">
                                Donasi Sekarang
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-20 bg-white rounded-3xl shadow-sm">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-box-seam text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-600">Belum ada program donasi.</h3>
                    <p class="text-gray-400 mt-2">Program-program kebaikan akan segera hadir.</p>
                </div>
            <?php endif; ?>
        </div>

    </section>

</main>

<style>
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
</style>

<?php require_once __DIR__ . '/../../../includes/templates/footer.php'; ?>
