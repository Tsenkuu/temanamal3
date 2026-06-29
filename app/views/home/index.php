<?php
$page_title = "Beranda";

$truncate = static function (?string $text, int $limit = 120): string {
    $plain = trim(strip_tags((string) $text));
    if ($plain === '') return '';
    if (function_exists('mb_strlen') && function_exists('mb_substr'))
        return mb_strlen($plain) > $limit ? mb_substr($plain, 0, $limit - 1) . '…' : $plain;
    return strlen($plain) > $limit ? substr($plain, 0, $limit - 1) . '…' : $plain;
};

$program_link   = static fn(array $p): string => BASE_URL . '/program/' . rawurlencode((string)(!empty($p['slug']) ? $p['slug'] : ($p['id'] ?? '')));
$donation_link  = static fn(array $p): string => BASE_URL . '/donasi?id_program=' . (int)($p['id'] ?? 0);
$news_link      = static fn(array $n): string => BASE_URL . '/berita/' . rawurlencode((string)(!empty($n['slug']) ? $n['slug'] : ($n['id'] ?? '')));
$program_image  = static fn(array $p): ?string => !empty($p['gambar']) ? BASE_URL . '/assets/uploads/program/' . rawurlencode((string)$p['gambar']) : null;
$news_image     = static fn(array $n): ?string => !empty($n['gambar']) ? BASE_URL . '/assets/uploads/berita/' . rawurlencode((string)$n['gambar']) : null;
$progress_pct   = static function(array $p): int {
    $t = (float)($p['target_donasi'] ?? 0);
    $c = (float)($p['donasi_terkumpul'] ?? 0);
    return $t <= 0 ? 0 : (int)min(100, round(($c / $t) * 100));
};

$faqs = [
    ['q' => 'Apakah donasi saya langsung tercatat?', 'a' => 'Ya. Setelah pembayaran masuk dan diverifikasi, status donasi akan diperbarui otomatis di sistem.'],
    ['q' => 'Bisakah saya memilih program tertentu?', 'a' => 'Bisa. Masuk ke detail program dan berdonasi langsung ke program yang Anda pilih.'],
    ['q' => 'Apakah tersedia kalkulator zakat?', 'a' => 'Tersedia. Hitung estimasi zakat dari halaman kalkulator sebelum melanjutkan donasi.'],
    ['q' => 'Bagaimana jika ingin konsultasi?', 'a' => 'Gunakan tombol WhatsApp admin agar tim dapat membantu pilihan program atau kendala pembayaran.'],
];

require_once __DIR__ . '/../../../includes/templates/header.php';
?>

<main class="bg-gray-50 min-h-screen pb-24">
    <!-- Hero Banner -->
    <section class="relative bg-gradient-to-b from-orange-50/50 to-white pt-12 pb-32 overflow-hidden rounded-b-[40px] border-b border-gray-100">
        <!-- Dekorasi Background -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-[500px] h-[500px] rounded-full bg-orange-400/10 blur-[80px] z-0"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-[400px] h-[400px] rounded-full bg-green-400/10 blur-[80px] z-0"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <div class="text-center lg:text-left order-2 lg:order-1">
                    <div class="inline-flex items-center gap-2 py-1.5 px-3 rounded-full bg-orange-100/80 border border-orange-200 text-primary-orange font-bold text-xs tracking-wide mb-6">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                        </span>
                        MEMBERI UNTUK NEGERI
                    </div>
                    <h1 class="text-4xl md:text-5xl lg:text-[64px] font-display font-extrabold text-slate-900 leading-[1.1] mb-6">
                        Berbagi Kebaikan,<br> Wujudkan <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-orange to-orange-400">Harapan</span>.
                    </h1>
                    <p class="text-slate-600 text-lg md:text-xl mb-10 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-medium">
                        Salurkan kepedulian Anda untuk mereka yang membutuhkan. Bersama TemanAmal, setiap donasi Anda dikelola secara transparan, aman, dan berdampak nyata.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="<?= BASE_URL ?>/donasi" class="bg-primary-orange text-white rounded-2xl px-8 py-4 font-bold text-lg hover:bg-orange-600 transform hover:-translate-y-1 transition-all shadow-[0_20px_40px_-15px_rgba(251,130,1,0.5)] text-center flex items-center justify-center gap-2">
                            <i class="bi bi-heart-fill"></i> Donasi Sekarang
                        </a>
                        <a href="<?= BASE_URL ?>/kalkulator_zakat" class="bg-white text-slate-700 border border-slate-200 rounded-2xl px-8 py-4 font-bold text-lg hover:bg-slate-50 hover:border-slate-300 transition-all text-center shadow-sm flex items-center justify-center gap-2">
                            <i class="bi bi-calculator"></i> Hitung Zakat
                        </a>
                    </div>
                    
                    <div class="mt-10 flex items-center justify-center lg:justify-start gap-4 text-sm font-medium text-slate-500">
                        <div class="flex -space-x-2">
                            <img class="w-8 h-8 rounded-full border-2 border-white" src="https://ui-avatars.com/api/?name=Budi&background=random" alt="Donatur">
                            <img class="w-8 h-8 rounded-full border-2 border-white" src="https://ui-avatars.com/api/?name=Siti&background=random" alt="Donatur">
                            <img class="w-8 h-8 rounded-full border-2 border-white" src="https://ui-avatars.com/api/?name=Agus&background=random" alt="Donatur">
                            <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600">+1k</div>
                        </div>
                        <p>Orang baik telah bergabung hari ini</p>
                    </div>
                </div>
                
                <!-- Hero Image -->
                <div class="order-1 lg:order-2 relative w-full aspect-[4/3] md:aspect-[16/10] lg:aspect-[4/3] rounded-[32px] overflow-hidden shadow-[0_20px_50px_-20px_rgba(0,0,0,0.15)] ring-1 ring-black/5">
                    <?php if (!empty($hero_slides)): ?>
                        <img src="/assets/gambardepan/amal.jpeg" alt="Hero Image" class="w-full h-full object-cover transform hover:scale-105 transition-transform duration-700" loading="lazy">
                    <?php else: ?>
                        <div class="w-full h-full bg-gradient-to-tr from-orange-200 to-green-100 flex items-center justify-center">
                            <span class="text-white font-bold text-2xl">TemanAmal</span>
                        </div>
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-8">
                        <div class="inline-block bg-white/20 backdrop-blur-md border border-white/30 rounded-2xl p-4 text-white">
                            <h3 class="font-bold text-xl mb-1">Berbagi Kasih</h3>
                            <p class="text-white/90 text-sm font-medium">Mari tebarkan kebaikan hari ini.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistik Donasi (Floating) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-20 mb-16">
        <div class="bg-white rounded-[32px] shadow-[0_20px_40px_-15px_rgba(0,0,0,0.05)] border border-slate-100 p-6 md:p-10 flex flex-col md:flex-row justify-around items-center gap-8 md:gap-4 backdrop-blur-xl bg-white/90">
            <div class="text-center w-full md:w-1/3 flex flex-col items-center">
                <div class="w-12 h-12 bg-green-50 text-primary-green rounded-2xl flex items-center justify-center mb-4">
                    <i class="bi bi-wallet2 text-2xl"></i>
                </div>
                <div class="text-3xl lg:text-4xl font-display font-extrabold text-slate-900 mb-1">Rp <?= number_format($total_disalurkan, 0, ',', '.') ?></div>
                <div class="text-sm font-bold text-slate-500 uppercase tracking-wider">Total Disalurkan</div>
            </div>
            <div class="hidden md:block w-px h-24 bg-gradient-to-b from-transparent via-slate-200 to-transparent"></div>
            
            <div class="text-center w-full md:w-1/3 flex flex-col items-center">
                <div class="w-12 h-12 bg-orange-50 text-primary-orange rounded-2xl flex items-center justify-center mb-4">
                    <i class="bi bi-grid-fill text-2xl"></i>
                </div>
                <div class="text-3xl lg:text-4xl font-display font-extrabold text-slate-900 mb-1"><?= number_format($program_count) ?></div>
                <div class="text-sm font-bold text-slate-500 uppercase tracking-wider">Program Aktif</div>
            </div>
            <div class="hidden md:block w-px h-24 bg-gradient-to-b from-transparent via-slate-200 to-transparent"></div>
            
            <div class="text-center w-full md:w-1/3 flex flex-col items-center">
                <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center mb-4">
                    <i class="bi bi-newspaper text-2xl"></i>
                </div>
                <div class="text-3xl lg:text-4xl font-display font-extrabold text-slate-900 mb-1"><?= number_format($news_count) ?></div>
                <div class="text-sm font-bold text-slate-500 uppercase tracking-wider">Berita Edukasi</div>
            </div>
        </div>
    </section>

    <!-- Program Pilihan / Unggulan -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 text-primary-orange font-bold text-sm tracking-wider uppercase mb-2">
                    <i class="bi bi-star-fill text-yellow-400"></i> Donasi Mendesak
                </span>
                <h2 class="text-3xl md:text-[40px] font-display font-extrabold text-slate-900 leading-tight">Program Pilihan</h2>
            </div>
            <a href="<?= BASE_URL ?>/program" class="inline-flex items-center gap-2 text-primary-green font-bold hover:text-green-700 transition-colors bg-green-50 hover:bg-green-100 px-5 py-2.5 rounded-xl">
                Lihat Semua <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <?php if ($featured): ?>
        <div class="bg-white rounded-[32px] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.08)] border border-slate-100 overflow-hidden flex flex-col lg:flex-row mb-16 transform hover:-translate-y-1 transition-all duration-300">
            <div class="w-full lg:w-[55%] h-72 lg:h-auto relative overflow-hidden group">
                <img src="<?= $program_image($featured) ?: BASE_URL.'/assets/images/placeholder.jpg' ?>" alt="<?= htmlspecialchars($featured['nama_program']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                <div class="absolute top-6 left-6 bg-red-500 text-white text-xs font-bold px-4 py-2 rounded-xl uppercase tracking-wide shadow-lg flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                    </span>
                    Paling Mendesak
                </div>
            </div>
            <div class="w-full lg:w-[45%] p-8 lg:p-12 flex flex-col justify-center relative">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 bg-slate-100 self-start px-3 py-1.5 rounded-lg"><?= htmlspecialchars($featured['kategori'] ?? 'UMUM') ?></span>
                <h3 class="text-2xl lg:text-3xl font-display font-bold text-slate-900 leading-tight mb-4"><?= htmlspecialchars($featured['nama_program']) ?></h3>
                <p class="text-slate-600 mb-8 line-clamp-3 text-lg"><?= htmlspecialchars(strip_tags($featured['deskripsi'] ?? '')) ?></p>
                
                <div class="mb-8 p-6 bg-slate-50 rounded-2xl border border-slate-100">
                    <div class="flex justify-between text-sm mb-3">
                        <span class="text-slate-500 font-medium">Terkumpul</span>
                        <span class="font-bold text-primary-green text-lg">Rp <?= number_format($featured['donasi_terkumpul'] ?? 0, 0, ',', '.') ?></span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-4 mb-3 overflow-hidden shadow-inner">
                        <div class="bg-primary-green h-4 rounded-full transition-all duration-1000 relative overflow-hidden" style="width: <?= $progress_pct($featured) ?>%">
                            <div class="absolute inset-0 bg-white/20 w-full animate-[shimmer_2s_infinite]"></div>
                        </div>
                    </div>
                    <div class="flex justify-between text-xs text-slate-500 font-bold uppercase tracking-wide">
                        <span class="text-slate-700"><?= $progress_pct($featured) ?>% <span class="font-medium text-slate-500">Tercapai</span></span>
                        <span>Target: Rp <?= number_format($featured['target_donasi'] ?? 0, 0, ',', '.') ?></span>
                    </div>
                </div>

                <a href="<?= $donation_link($featured) ?>" class="block w-full bg-primary-orange text-white text-center rounded-2xl py-4 font-bold text-lg hover:bg-orange-600 transition-all shadow-[0_10px_20px_-10px_rgba(251,130,1,0.5)] flex items-center justify-center gap-2">
                    <i class="bi bi-heart-fill"></i> Donasi Sekarang
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Grid Program Lainnya -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach (array_slice($program_cards, 0, 4) as $prog): ?>
            <div class="bg-white rounded-2xl shadow-card hover:shadow-soft transition-all duration-300 overflow-hidden flex flex-col group">
                <a href="<?= $program_link($prog) ?>" class="block relative h-48 overflow-hidden">
                    <img src="<?= $program_image($prog) ?: BASE_URL.'/assets/images/placeholder.jpg' ?>" alt="<?= htmlspecialchars($prog['nama_program']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-primary-orange text-xs font-bold px-2.5 py-1 rounded-md">
                        <?= htmlspecialchars($prog['kategori'] ?? '') ?>
                    </div>
                </a>
                <div class="p-5 flex flex-col flex-1">
                    <a href="<?= $program_link($prog) ?>" class="text-lg font-bold text-dark-text leading-snug mb-3 hover:text-primary-orange transition-colors line-clamp-2">
                        <?= htmlspecialchars($prog['nama_program']) ?>
                    </a>
                    <div class="mt-auto">
                        <div class="w-full bg-gray-100 rounded-full h-2 mb-2 overflow-hidden">
                            <div class="bg-primary-green h-2 rounded-full" style="width: <?= $progress_pct($prog) ?>%"></div>
                        </div>
                        <div class="flex justify-between items-end mb-4">
                            <div>
                                <div class="text-[11px] text-gray-500 font-medium mb-0.5">Terkumpul</div>
                                <div class="font-bold text-sm text-primary-green">Rp <?= number_format($prog['donasi_terkumpul'] ?? 0, 0, ',', '.') ?></div>
                            </div>
                            <div class="text-xs font-bold text-gray-400"><?= $progress_pct($prog) ?>%</div>
                        </div>
                        <a href="<?= $donation_link($prog) ?>" class="block w-full border-2 border-primary-orange text-primary-orange text-center rounded-xl py-2.5 font-bold text-sm hover:bg-primary-orange hover:text-white transition-colors">
                            Donasi
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-8 text-center sm:hidden">
            <a href="<?= BASE_URL ?>/program" class="inline-block bg-white text-primary-green font-bold px-6 py-3 rounded-xl shadow-sm border border-gray-100">
                Lihat Semua Program
            </a>
        </div>
    </section>

    <!-- Aktivitas Donasi Terbaru -->
    <section class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-display font-bold text-dark-text mb-8 text-center">Donasi Terbaru</h2>
            <div class="flex overflow-x-auto pb-4 snap-x snap-mandatory hide-scrollbar md:grid md:grid-cols-2 lg:grid-cols-3 md:overflow-visible gap-4">
                <?php foreach($recent_donations as $don): 
                    $nama_tampil = !empty($don['anonim']) ? 'Hamba Allah' : ($don['nama_donatur'] ?? 'Hamba Allah');
                    $initial = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $nama_tampil), 0, 1));
                    if (!$initial) $initial = 'H';
                ?>
                <div class="snap-start flex-none w-[85%] sm:w-[60%] md:w-auto flex items-center gap-4 bg-gray-50 rounded-2xl p-4 border border-gray-100 hover:border-orange-200 transition-colors">
                    <div class="w-12 h-12 rounded-full bg-orange-100 text-primary-orange flex items-center justify-center font-bold text-xl flex-shrink-0">
                        <?= $initial ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-dark-text truncate"><?= htmlspecialchars($nama_tampil) ?></div>
                        <div class="text-xs text-gray-500 truncate mt-0.5">Mendukung: <?= htmlspecialchars($don['nama_program']) ?></div>
                    </div>
                    <div class="text-primary-green font-bold text-sm whitespace-nowrap">
                        Rp <?= number_format($don['nominal'] ?? 0, 0, ',', '.') ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <style>
                .hide-scrollbar::-webkit-scrollbar {
                    display: none;
                }
                .hide-scrollbar {
                    -ms-overflow-style: none;
                    scrollbar-width: none;
                }
            </style>
        </div>
    </section>

    <!-- Berita Terkini -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="flex justify-between items-end mb-8">
            <div>
                <span class="text-primary-green font-bold text-sm tracking-wider uppercase">Info Terkini</span>
                <h2 class="text-3xl font-display font-bold text-dark-text mt-2">Kabar Terbaru</h2>
            </div>
            <a href="<?= BASE_URL ?>/berita" class="hidden sm:flex items-center text-primary-green font-semibold hover:text-green-700 transition-colors">
                Lihat Semua <i class="bi bi-arrow-right ml-2"></i>
            </a>
        </div>
        
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($news_items as $news): ?>
            <a href="<?= $news_link($news) ?>" class="group block bg-white rounded-2xl overflow-hidden shadow-card hover:shadow-soft transition-all duration-300">
                <div class="relative h-40 overflow-hidden bg-gray-100">
                    <img src="<?= $news_image($news) ?: BASE_URL.'/assets/images/placeholder.jpg' ?>" alt="<?= htmlspecialchars($news['judul']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-dark-text text-[10px] font-bold px-2 py-1 rounded">
                        <?= date('d M Y', strtotime($news['created_at'])) ?>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-dark-text leading-snug mb-2 group-hover:text-primary-green transition-colors line-clamp-2">
                        <?= htmlspecialchars($news['judul']) ?>
                    </h3>
                    <p class="text-xs text-gray-500 line-clamp-2"><?= htmlspecialchars(strip_tags($news['teras_berita'] ?? '')) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

</main>

<?php require_once __DIR__ . '/../../../includes/templates/footer.php'; ?>