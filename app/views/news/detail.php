<?php
// Meta tags setup
$page_title = $berita['judul'];
$og_title = $berita['judul'];
$og_description = substr(strip_tags($berita['teras_berita']), 0, 155);
$og_image = BASE_URL . '/assets/uploads/berita/' . htmlspecialchars($berita['gambar']);
$og_url = BASE_URL . '/berita/' . $berita['slug'];
$og_type = "article";

// Helpers
$reading_time = static function(?string $text): int {
    $words = str_word_count(strip_tags((string)$text));
    $minutes = ceil($words / 200);
    return max(1, (int)$minutes);
};

require_once __DIR__ . '/../../../includes/templates/header.php';
?>

<!-- Alpine JS for Sticky Share / Table of Contents interactions if needed -->
<main class="bg-gray-light min-h-screen pb-24 font-sans" x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 300)">

    <!-- Breadcrumb -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <nav class="flex text-sm text-gray-500 font-medium" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-2 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="<?= BASE_URL ?>/" class="hover:text-primary-orange transition-colors"><i class="bi bi-house-door mr-2"></i>Beranda</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="bi bi-chevron-right text-gray-400 text-xs mx-1"></i>
                        <a href="<?= BASE_URL ?>/berita" class="hover:text-primary-orange transition-colors ml-1 md:ml-2">Berita</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="bi bi-chevron-right text-gray-400 text-xs mx-1"></i>
                        <span class="text-gray-400 ml-1 md:ml-2 truncate max-w-[150px] md:max-w-xs"><?= htmlspecialchars($berita['judul']) ?></span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Layout Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <div class="flex flex-col lg:flex-row gap-10">
            
            <!-- Main Content -->
            <article class="lg:w-2/3 bg-white rounded-[24px] shadow-card overflow-hidden">
                
                <div class="p-6 md:p-10 lg:p-12 pb-0">
                    <!-- Kategori -->
                    <div class="text-primary-orange text-sm font-bold uppercase tracking-wider mb-4">
                        <?= htmlspecialchars($berita['type_label']) ?>
                    </div>

                    <!-- Title -->
                    <h1 class="text-3xl md:text-4xl lg:text-[40px] font-display font-bold text-dark-text leading-tight md:leading-snug mb-6">
                        <?= htmlspecialchars($berita['judul']) ?>
                    </h1>

                    <!-- Meta Info -->
                    <div class="flex flex-wrap items-center gap-4 md:gap-6 text-sm text-gray-500 font-medium mb-8">
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-orange-50 text-primary-orange flex items-center justify-center font-bold text-lg">
                                <?= strtoupper(substr($berita['penulis'] ?? 'A', 0, 1)) ?>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-dark-text font-bold leading-none"><?= htmlspecialchars($berita['penulis'] ?? 'Redaksi') ?></span>
                                <span class="text-[11px] mt-1">Penulis</span>
                            </div>
                        </div>
                        <?php if (!empty($berita['editor'])): ?>
                        <div class="h-8 w-px bg-gray-200 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center font-bold text-lg">
                                <?= strtoupper(substr($berita['editor'], 0, 1)) ?>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-dark-text font-bold leading-none"><?= htmlspecialchars($berita['editor']) ?></span>
                                <span class="text-[11px] mt-1">Editor</span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="h-8 w-px bg-gray-200 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-calendar3 text-gray-400"></i>
                            <span><?= date('d M Y', strtotime($berita['created_at'])) ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-clock text-gray-400"></i>
                            <span><?= $reading_time($berita['tubuh_berita'] ?? '') ?> mnt baca</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-eye text-gray-400"></i>
                            <span><?= number_format($berita['views'] + 1) ?>x dibaca</span>
                        </div>
                    </div>
                </div>

                <!-- Hero Image -->
                <div class="mb-8">
                    <div class="w-full h-64 md:h-[400px] relative bg-gray-100 overflow-hidden">
                        <img src="<?= BASE_URL . '/assets/uploads/berita/' . htmlspecialchars($berita['gambar']) ?>" alt="<?= htmlspecialchars($berita['judul']) ?>" class="w-full h-full object-cover">
                    </div>
                    <?php if (!empty($berita['sumber_gambar'])): ?>
                    <p class="text-xs text-gray-500 mt-2 text-right italic px-4 md:px-10 lg:px-12">Sumber foto: <?= htmlspecialchars($berita['sumber_gambar']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="p-6 md:p-10 lg:p-12 pt-0">

                    <!-- Teras Berita (Lead Paragraph) -->
                    <div class="text-lg md:text-xl text-gray-600 font-medium leading-relaxed mb-8 pl-4 border-l-4 border-primary-orange italic">
                        <?= htmlspecialchars(strip_tags($berita['teras_berita'])) ?>
                    </div>

                    <!-- Content Body -->
                    <div class="prose prose-lg prose-orange max-w-none text-gray-700 leading-relaxed mb-10">
                        <style>
                            .prose p { margin-bottom: 1.5em; text-align: justify; }
                            .prose img { border-radius: 16px; margin: 2em auto; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
                            .prose h2 { font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-top: 2em; margin-bottom: 1em; }
                            .prose h3 { font-family: 'Poppins', sans-serif; font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-top: 1.5em; margin-bottom: 0.75em; }
                            .prose ul { list-style-type: disc; padding-left: 1.5em; margin-bottom: 1.5em; }
                            .prose li { margin-bottom: 0.5em; }
                            .prose blockquote { border-left: 4px solid #F97316; padding-left: 1em; font-style: italic; color: #4b5563; background: #fff8ef; padding: 1.5em; border-radius: 0 12px 12px 0; margin: 2em 0; }
                            .prose a { color: #F97316; text-decoration: underline; text-underline-offset: 4px; }
                        </style>
                        <?= $berita['tubuh_berita'] // Output raw HTML because it's rich text ?>
                    </div>

                    <!-- Share Section -->
                    <div class="border-t border-gray-100 pt-8 mt-10">
                        <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Bagikan Artikel Ini</h4>
                        <div class="flex gap-3">
                            <?php $shareUrl = urlencode($og_url); $shareTitle = urlencode($berita['judul']); ?>
                            <a href="https://api.whatsapp.com/send?text=<?= $shareTitle ?>%20<?= $shareUrl ?>" target="_blank" class="w-12 h-12 rounded-full bg-[#25D366]/10 text-[#25D366] flex items-center justify-center text-xl hover:bg-[#25D366] hover:text-white transition-all transform hover:scale-105">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" class="w-12 h-12 rounded-full bg-[#1877F2]/10 text-[#1877F2] flex items-center justify-center text-xl hover:bg-[#1877F2] hover:text-white transition-all transform hover:scale-105">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" target="_blank" class="w-12 h-12 rounded-full bg-gray-100 text-gray-800 flex items-center justify-center text-xl hover:bg-gray-800 hover:text-white transition-all transform hover:scale-105">
                                <i class="bi bi-twitter-x"></i>
                            </a>
                            <a href="https://t.me/share/url?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" target="_blank" class="w-12 h-12 rounded-full bg-[#229ED9]/10 text-[#229ED9] flex items-center justify-center text-xl hover:bg-[#229ED9] hover:text-white transition-all transform hover:scale-105">
                                <i class="bi bi-telegram"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Sidebar (Berita Populer & Donasi) -->
            <aside class="lg:w-1/3 space-y-8">
                
                <!-- CTA Donasi Banner -->
                <div class="bg-gradient-to-br from-primary-orange to-primary-orange-hover rounded-[24px] p-8 text-white shadow-soft relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-6">
                            <i class="bi bi-heart-fill text-2xl"></i>
                        </div>
                        <h3 class="text-2xl font-display font-bold mb-3 leading-tight">Mari Wujudkan Harapan Mereka</h3>
                        <p class="text-white/80 text-sm mb-6 leading-relaxed">Setiap donasi Anda adalah langkah nyata kebaikan untuk yang membutuhkan.</p>
                        <a href="<?= BASE_URL ?>/donasi" class="block w-full bg-white text-primary-orange text-center rounded-xl py-3.5 font-bold text-sm hover:bg-orange-50 transition-colors shadow-sm">
                            Donasi Sekarang
                        </a>
                    </div>
                </div>

                <!-- Berita Populer -->
                <div class="bg-white rounded-[24px] shadow-card p-6 md:p-8">
                    <h3 class="text-lg font-bold text-dark-text mb-6 border-l-4 border-primary-orange pl-3">Artikel Terpopuler</h3>
                    <div class="space-y-6">
                        <?php foreach($populer as $index => $pop): ?>
                        <a href="<?= BASE_URL ?>/berita/<?= $pop['slug'] ?>" class="flex gap-4 group">
                            <div class="w-20 h-20 rounded-xl bg-gray-100 flex-shrink-0 overflow-hidden relative shadow-sm">
                                <img src="<?= BASE_URL . '/assets/uploads/berita/' . htmlspecialchars($pop['gambar']) ?>" alt="" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                <div class="absolute top-0 left-0 w-6 h-6 bg-primary-orange text-white text-[10px] font-bold flex items-center justify-center rounded-br-lg"><?= $index + 1 ?></div>
                            </div>
                            <div class="flex flex-col justify-center">
                                <h4 class="text-sm font-bold text-dark-text leading-snug group-hover:text-primary-orange transition-colors line-clamp-2 mb-1">
                                    <?= htmlspecialchars($pop['judul']) ?>
                                </h4>
                                <span class="text-[11px] text-gray-400 font-medium"><i class="bi bi-eye mr-1"></i> <?= number_format($pop['views']) ?>x dibaca</span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

            </aside>
        </div>
    </div>
    
    <!-- Sticky Mobile Share Bar Removed to avoid overlapping -->

</main>

<?php require_once __DIR__ . '/../../../includes/templates/footer.php'; ?>
