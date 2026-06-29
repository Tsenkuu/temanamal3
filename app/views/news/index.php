<?php
// Helpers (Bisa dipindah ke Helper class nanti)
$news_link = static fn($n): string => BASE_URL . '/berita/' . rawurlencode((string)(!empty($n['slug']) ? $n['slug'] : ($n['id'] ?? '')));
$news_image = static fn($n): ?string => !empty($n['gambar']) ? BASE_URL . '/assets/uploads/berita/' . rawurlencode((string)$n['gambar']) : null;
$truncate = static function (?string $text, int $limit = 120): string {
    $plain = trim(strip_tags((string) $text));
    if ($plain === '') return '';
    return (strlen($plain) > $limit) ? substr($plain, 0, $limit - 1) . '…' : $plain;
};

// Calculate reading time (roughly 200 words per minute)
$reading_time = static function(?string $text): int {
    $words = str_word_count(strip_tags((string)$text));
    $minutes = ceil($words / 200);
    return max(1, (int)$minutes);
};

require_once __DIR__ . '/../../../includes/templates/header.php';
?>

<!-- Alpine.js untuk Search & Skeleton Interactivity -->
<main x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 800)" class="bg-gray-light min-h-screen pb-24">
    
    <!-- Hero Header Berita -->
    <section class="bg-white pt-8 pb-10 shadow-sm relative z-10 rounded-b-[32px]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center md:text-left">
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-display font-bold text-dark-text tracking-tight mb-4">
                Kabar <span class="text-primary-orange">Kebaikan</span>
            </h1>
            <p class="text-gray-500 max-w-2xl text-base md:text-lg mx-auto md:mx-0">
                Ikuti kisah inspiratif, laporan penyaluran donasi, dan artikel edukasi dari setiap langkah kebaikan yang kita ciptakan bersama.
            </p>
        </div>
    </section>

    <!-- Kategori & Search Filter -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-20">
        <div class="bg-white rounded-[20px] p-2 md:p-4 shadow-card flex flex-col md:flex-row justify-between items-center gap-4">
            <!-- Scrollable Categories (Mobile Friendly) -->
            <div class="w-full md:w-auto overflow-x-auto no-scrollbar pb-2 md:pb-0">
                <div class="flex items-center gap-2 px-2">
                    <a href="#" class="whitespace-nowrap px-5 py-2.5 rounded-xl bg-orange-50 text-primary-orange font-bold text-sm transition-colors border border-orange-100">Semua Berita</a>
                    <?php foreach($categories as $cat): ?>
                        <a href="#" class="whitespace-nowrap px-5 py-2.5 rounded-xl bg-gray-50 text-gray-600 hover:bg-orange-50 hover:text-primary-orange font-medium text-sm transition-colors border border-transparent hover:border-orange-100">
                            <?= ucfirst($cat['type']) ?> <span class="ml-1 opacity-60 text-xs">(<?= $cat['count'] ?>)</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="w-full md:w-72 px-2">
                <div class="relative">
                    <input type="text" placeholder="Cari artikel..." class="w-full bg-gray-50 border-none rounded-xl py-3 pl-11 pr-4 text-sm focus:ring-2 focus:ring-primary-orange transition-all">
                    <i class="bi bi-search absolute left-4 top-3.5 text-gray-400"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Konten Berita -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- SKELETON LOADING STATE -->
        <div x-show="loading" class="space-y-12">
            <!-- Featured Skeleton -->
            <div class="animate-pulse bg-white rounded-[24px] overflow-hidden flex flex-col lg:flex-row shadow-soft">
                <div class="w-full lg:w-3/5 h-64 lg:h-96 bg-gray-200"></div>
                <div class="w-full lg:w-2/5 p-8 flex flex-col justify-center">
                    <div class="h-4 bg-gray-200 w-24 rounded-full mb-4"></div>
                    <div class="h-8 bg-gray-200 w-full rounded-md mb-3"></div>
                    <div class="h-8 bg-gray-200 w-3/4 rounded-md mb-6"></div>
                    <div class="h-4 bg-gray-200 w-full rounded-md mb-2"></div>
                    <div class="h-4 bg-gray-200 w-full rounded-md mb-2"></div>
                    <div class="h-4 bg-gray-200 w-2/3 rounded-md"></div>
                </div>
            </div>
            <!-- Grid Skeleton -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php for($i=0; $i<6; $i++): ?>
                <div class="animate-pulse bg-white rounded-2xl overflow-hidden shadow-card">
                    <div class="h-48 bg-gray-200 w-full"></div>
                    <div class="p-5">
                        <div class="h-3 bg-gray-200 w-20 rounded-full mb-3"></div>
                        <div class="h-5 bg-gray-200 w-full rounded-md mb-2"></div>
                        <div class="h-5 bg-gray-200 w-4/5 rounded-md mb-4"></div>
                        <div class="h-3 bg-gray-200 w-full rounded-md mb-1.5"></div>
                        <div class="h-3 bg-gray-200 w-3/4 rounded-md"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- ACTUAL CONTENT -->
        <div x-show="!loading" x-cloak>
            
            <?php if($featured_news): ?>
            <!-- Featured News (Hero) -->
            <div class="mb-12">
                <h2 class="text-xl font-bold text-dark-text mb-6 border-l-4 border-primary-orange pl-3">Sorotan Utama</h2>
                <a href="<?= $news_link($featured_news) ?>" class="group block bg-white rounded-[24px] overflow-hidden flex flex-col lg:flex-row shadow-soft hover:shadow-xl transition-all duration-500">
                    <div class="w-full lg:w-3/5 h-64 lg:h-96 relative overflow-hidden bg-gray-100">
                        <img src="<?= $news_image($featured_news) ?: BASE_URL.'/assets/images/placeholder.jpg' ?>" alt="<?= htmlspecialchars($featured_news['judul']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent lg:hidden"></div>
                        <div class="absolute top-4 left-4 lg:top-6 lg:left-6">
                            <span class="bg-primary-orange text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-md uppercase tracking-wider">
                                <?= ucfirst($featured_news['type']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="w-full lg:w-2/5 p-6 md:p-8 lg:p-10 flex flex-col justify-center relative">
                        <div class="flex items-center gap-3 text-xs text-gray-500 font-medium mb-3">
                            <span class="flex items-center gap-1.5"><i class="bi bi-calendar3 text-gray-400"></i> <?= date('d M Y', strtotime($featured_news['created_at'])) ?></span>
                            <span>&bull;</span>
                            <span class="flex items-center gap-1.5"><i class="bi bi-clock text-gray-400"></i> <?= $reading_time($featured_news['teras_berita']) ?> mnt baca</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-bold text-dark-text leading-tight mb-4 group-hover:text-primary-orange transition-colors">
                            <?= htmlspecialchars($featured_news['judul']) ?>
                        </h3>
                        <p class="text-gray-600 mb-8 line-clamp-3 leading-relaxed">
                            <?= htmlspecialchars(strip_tags($featured_news['teras_berita'])) ?>
                        </p>
                        
                        <div class="flex items-center justify-between mt-auto">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500"><i class="bi bi-person-fill"></i></div>
                                <span class="text-sm font-semibold text-gray-700">Redaksi Lazismu</span>
                            </div>
                            <span class="text-primary-orange font-bold text-sm group-hover:translate-x-1 transition-transform flex items-center gap-1">
                                Baca <i class="bi bi-arrow-right"></i>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <!-- Berita Lainnya Grid -->
            <?php if(!empty($other_news)): ?>
            <div class="mb-10 flex justify-between items-end">
                <h2 class="text-xl font-bold text-dark-text border-l-4 border-primary-orange pl-3">Artikel Lainnya</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <?php foreach($other_news as $news): ?>
                <article class="bg-white rounded-2xl overflow-hidden shadow-card hover:shadow-soft transition-all duration-300 flex flex-col group">
                    <a href="<?= $news_link($news) ?>" class="block relative h-52 overflow-hidden bg-gray-100">
                        <img src="<?= $news_image($news) ?: BASE_URL.'/assets/images/placeholder.jpg' ?>" alt="<?= htmlspecialchars($news['judul']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        <div class="absolute top-3 left-3 bg-white/95 backdrop-blur text-primary-orange text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wide">
                            <?= ucfirst($news['type']) ?>
                        </div>
                        <!-- Hover Overlay -->
                        <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </a>
                    <div class="p-5 flex flex-col flex-grow">
                        <div class="flex justify-between items-center text-xs text-gray-400 font-medium mb-3">
                            <span class="flex items-center gap-1.5"><i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($news['created_at'])) ?></span>
                            <span class="flex items-center gap-1.5"><i class="bi bi-eye"></i> <?= number_format($news['views'] ?? 0) ?></span>
                        </div>
                        <h3 class="text-lg font-bold text-dark-text leading-snug mb-3 group-hover:text-primary-orange transition-colors line-clamp-2">
                            <a href="<?= $news_link($news) ?>"><?= htmlspecialchars($news['judul']) ?></a>
                        </h3>
                        <p class="text-sm text-gray-500 line-clamp-2 mb-4 flex-grow">
                            <?= htmlspecialchars(strip_tags($news['teras_berita'] ?? '')) ?>
                        </p>
                        <a href="<?= $news_link($news) ?>" class="inline-flex items-center text-primary-orange font-semibold text-sm hover:text-orange-700 transition-colors mt-auto group-hover:underline underline-offset-4">
                            Selengkapnya <i class="bi bi-arrow-right ml-1"></i>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination / Load More -->
            <div class="mt-12 text-center">
                <button class="bg-white text-primary-orange font-bold px-8 py-3 rounded-xl border-2 border-orange-100 hover:bg-orange-50 hover:border-orange-200 transition-colors shadow-sm active:scale-95">
                    Muat Lebih Banyak
                </button>
            </div>
            <?php else: ?>
                <?php if(!$featured_news): ?>
                <div class="text-center py-20 bg-white rounded-3xl shadow-sm">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-newspaper text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-600">Belum ada berita.</h3>
                    <p class="text-gray-400 mt-2">Berita dan kabar terbaru akan segera hadir.</p>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/../../../includes/templates/footer.php'; ?>
