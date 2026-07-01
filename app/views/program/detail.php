<?php
// SEO Meta Tags
$page_title = $program['nama_program'];
$og_title = $program['nama_program'];
$og_description = substr(strip_tags($program['deskripsi']), 0, 155);
$og_image = BASE_URL . '/assets/uploads/program/' . htmlspecialchars($program['gambar']);
$og_url = BASE_URL . '/program/' . ($program['slug'] ?? $program['id']);
$og_type = "article";

// Helpers
$percent = $program['target_donasi'] > 0 ? min(100, (int)(($program['donasi_terkumpul'] / $program['target_donasi']) * 100)) : 0;
$format_rupiah = static fn($num) => 'Rp ' . number_format((float)$num, 0, ',', '.');
$days_left = static function($deadline) {
    if (!$deadline) return 'Tanpa batas';
    $diff = strtotime($deadline) - time();
    return $diff > 0 ? floor($diff / (60 * 60 * 24)) . ' hari lagi' : 'Selesai';
};

require_once __DIR__ . '/../../../includes/templates/header.php';
?>

<!-- Alpine JS for interactions -->
<main class="bg-gray-light min-h-screen pb-24 font-sans" x-data="{ donasiTab: 'deskripsi', nominal: '' }">

    <!-- Hero Section with Breadcrumb Overlay -->
    <div class="relative w-full h-64 md:h-[450px] bg-gray-900">
        <img src="<?= BASE_URL . '/assets/uploads/program/' . htmlspecialchars($program['gambar']) ?>" alt="<?= htmlspecialchars($program['nama_program']) ?>" class="w-full h-full object-cover opacity-60">
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
        
        <div class="absolute inset-0 flex flex-col justify-end max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">
            <!-- Breadcrumb -->
            <nav class="flex text-xs md:text-sm text-gray-300 font-medium mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-2">
                    <li class="inline-flex items-center">
                        <a href="<?= BASE_URL ?>/" class="hover:text-white transition-colors">Beranda</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="bi bi-chevron-right text-gray-500 text-xs mx-1 md:mx-2"></i>
                            <a href="<?= BASE_URL ?>/program" class="hover:text-white transition-colors">Program</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="bi bi-chevron-right text-gray-500 text-xs mx-1 md:mx-2"></i>
                            <span class="text-white truncate max-w-[150px] md:max-w-xs"><?= htmlspecialchars($program['nama_program']) ?></span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl md:text-5xl font-display font-bold text-white leading-tight md:leading-snug max-w-3xl shadow-sm">
                <?= htmlspecialchars($program['nama_program']) ?>
            </h1>
        </div>
    </div>

    <!-- Layout Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 md:mt-12">
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12 relative">
            
            <!-- Main Content (Left) -->
            <div class="lg:w-[65%]">
                
                <!-- Nav Tabs -->
                <div class="flex border-b border-gray-200 mb-8 sticky top-[60px] md:top-[80px] bg-gray-light z-30 pt-2">
                    <button @click="donasiTab = 'deskripsi'" :class="donasiTab === 'deskripsi' ? 'border-primary-orange text-primary-orange' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-1 py-4 border-b-2 font-bold text-sm md:text-base mr-8 transition-colors">
                        Deskripsi
                    </button>
                    <button @click="donasiTab = 'donatur'" :class="donasiTab === 'donatur' ? 'border-primary-orange text-primary-orange' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-1 py-4 border-b-2 font-bold text-sm md:text-base mr-8 transition-colors">
                        Donatur <span class="bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs ml-1"><?= $total_donatur ?></span>
                    </button>
                    <button @click="donasiTab = 'update'" :class="donasiTab === 'update' ? 'border-primary-orange text-primary-orange' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-1 py-4 border-b-2 font-bold text-sm md:text-base transition-colors hidden md:block">
                        Kabar Terbaru
                    </button>
                </div>

                <!-- Tab Deskripsi -->
                <div x-show="donasiTab === 'deskripsi'" class="bg-white rounded-[24px] p-6 md:p-8 lg:p-10 shadow-card">
                    
                    <!-- Meta Info Mobile (Shows only on mobile as sticky bar is missing) -->
                    <div class="lg:hidden mb-8 pb-8 border-b border-gray-100">
                        <div class="flex justify-between items-end mb-3">
                            <span class="text-xs font-bold text-primary-orange bg-orange-50 px-2 py-1 rounded-md"><?= $percent ?>% Terkumpul</span>
                            <span class="text-[12px] text-gray-500 font-medium"><i class="bi bi-clock mr-1"></i> <?= $days_left($program['batas_waktu'] ?? null) ?></span>
                        </div>
                        <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden mb-4 shadow-inner">
                            <div class="h-full bg-gradient-to-r from-primary-orange to-orange-400 rounded-full" style="width: <?= $percent ?>%"></div>
                        </div>
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold mb-1">Terkumpul</p>
                                <p class="text-lg font-bold text-dark-text"><?= $format_rupiah($program['donasi_terkumpul']) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-[11px] text-gray-400 uppercase tracking-wide font-semibold mb-1">Target</p>
                                <p class="text-lg font-bold text-dark-text"><?= $format_rupiah($program['target_donasi']) ?></p>
                            </div>
                        </div>
                        <button @click="document.getElementById('donasi-panel').scrollIntoView({behavior: 'smooth'})" class="w-full bg-primary-orange text-white font-bold py-3.5 rounded-xl shadow-lg shadow-orange-200">Donasi Sekarang</button>
                    </div>

                    <div class="prose prose-lg prose-orange max-w-none text-gray-700 leading-relaxed" x-data="{ expanded: false }">
                        <style>
                            .prose p { margin-bottom: 1.5em; text-align: justify; }
                            .prose img { border-radius: 16px; margin: 2em auto; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
                            .prose h2, .prose h3 { font-family: 'Poppins', sans-serif; font-weight: 700; color: #1f2937; margin-top: 2em; margin-bottom: 1em; }
                        </style>
                        <div :class="expanded ? '' : 'line-clamp-[12] md:line-clamp-none relative'">
                            <?= nl2br($program['deskripsi']) ?>
                            <!-- Gradient Fade for Mobile Truncation -->
                            <div x-show="!expanded" class="md:hidden absolute bottom-0 left-0 w-full h-32 bg-gradient-to-t from-white to-transparent"></div>
                        </div>
                        <button @click="expanded = true" x-show="!expanded" class="md:hidden mt-4 w-full text-primary-orange font-bold text-sm py-2 border border-orange-200 rounded-xl">
                            Baca Selengkapnya
                        </button>
                    </div>
                </div>

                <!-- Tab Donatur -->
                <div x-show="donasiTab === 'donatur'" class="bg-white rounded-[24px] p-6 md:p-8 lg:p-10 shadow-card" x-cloak>
                    <h3 class="text-xl font-bold text-dark-text mb-6">Doa & Dukungan (<span class="text-primary-orange"><?= $total_donatur ?></span>)</h3>
                    
                    <?php if(empty($donatur)): ?>
                        <div class="text-center py-10 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <i class="bi bi-heart text-4xl text-gray-300 mb-3 block"></i>
                            <p class="text-gray-500 font-medium">Jadilah donatur pertama untuk program ini!</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach($donatur as $d): ?>
                            <div class="flex gap-4 p-5 bg-gray-50 rounded-2xl hover:bg-orange-50/50 transition-colors border border-transparent hover:border-orange-100">
                                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-primary-orange font-bold text-lg shadow-sm border border-orange-100 flex-shrink-0">
                                    <?= (!empty($d['anonim'])) ? 'H' : strtoupper(substr($d['nama'] ?? 'H', 0, 1)) ?>
                                </div>
                                <div>
                                    <h4 class="font-bold text-dark-text text-base mb-1">
                                        <?= (!empty($d['anonim'])) ? 'Hamba Allah' : htmlspecialchars($d['nama'] ?? 'Hamba Allah') ?>
                                    </h4>
                                    <p class="text-primary-orange font-bold text-sm mb-2">Berdonasi <?= $format_rupiah($d['jumlah'] ?? 0) ?></p>
                                    <p class="text-gray-600 text-sm italic bg-white p-3 rounded-lg shadow-sm">
                                        "<?= htmlspecialchars($d['doa'] ?? 'Semoga berkah dan bermanfaat.') ?>"
                                    </p>
                                    <p class="text-[11px] text-gray-400 mt-2 font-medium"><i class="bi bi-clock mr-1"></i> <?= date('d M Y, H:i', strtotime($d['created_at'])) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if($total_donatur > 5): ?>
                            <button class="w-full mt-6 text-primary-orange font-bold text-sm py-3 border border-orange-200 rounded-xl hover:bg-orange-50 transition-colors">Lihat Semua Donatur</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Tab Update -->
                <div x-show="donasiTab === 'update'" class="bg-white rounded-[24px] p-6 md:p-8 lg:p-10 shadow-card" x-cloak>
                    <?php if (!empty($kabar_program)): ?>
                        <div class="space-y-8 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-300 before:to-transparent">
                            <?php foreach($kabar_program as $kabar): ?>
                            <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                <!-- Icon -->
                                <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white bg-primary-orange text-white shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10 relative">
                                    <i class="bi bi-megaphone-fill"></i>
                                </div>
                                <!-- Content -->
                                <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-xl border border-slate-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-orange-200 relative">
                                    <div class="flex items-center justify-between mb-1">
                                        <h4 class="font-bold text-slate-800 text-lg"><?= htmlspecialchars($kabar['judul_kabar']); ?></h4>
                                    </div>
                                    <time class="block text-sm font-medium text-slate-500 mb-3"><i class="bi bi-calendar3 mr-1"></i> <?= date('d M Y', strtotime($kabar['created_at'])); ?></time>
                                    <div class="prose prose-sm max-w-none text-slate-600">
                                        <?= $kabar['konten_kabar']; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-10">
                            <div class="w-16 h-16 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="bi bi-megaphone text-2xl text-primary-orange"></i>
                            </div>
                            <h3 class="text-xl font-bold text-dark-text mb-2">Belum ada kabar terbaru</h3>
                            <p class="text-gray-500 text-sm">Update terkait penyaluran donasi akan ditampilkan di sini.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Share Section -->
                <div class="mt-8 bg-white rounded-[24px] p-6 shadow-card border border-orange-50">
                    <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="bi bi-share-fill text-primary-orange"></i> Bagikan Program Ini</h4>
                    <div class="flex gap-3">
                        <?php $shareUrl = urlencode($og_url); $shareTitle = urlencode($program['nama_program']); ?>
                        <a href="https://api.whatsapp.com/send?text=<?= $shareTitle ?>%20<?= $shareUrl ?>" target="_blank" class="flex-1 flex items-center justify-center gap-2 bg-[#25D366]/10 text-[#25D366] py-3 rounded-xl hover:bg-[#25D366] hover:text-white transition-all font-semibold">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" class="w-12 h-12 flex items-center justify-center bg-[#1877F2]/10 text-[#1877F2] rounded-xl hover:bg-[#1877F2] hover:text-white transition-all">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" target="_blank" class="w-12 h-12 flex items-center justify-center bg-gray-100 text-gray-800 rounded-xl hover:bg-gray-800 hover:text-white transition-all">
                            <i class="bi bi-twitter-x"></i>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Sticky Donation Sidebar (Right) -->
            <div class="lg:w-[35%]">
                <div class="sticky top-[100px]" id="donasi-panel">
                    
                    <div class="bg-white rounded-[32px] p-6 md:p-8 shadow-2xl shadow-gray-200/50 border border-gray-100">
                        <h3 class="text-2xl font-display font-bold text-dark-text mb-6">Pilih Nominal Donasi</h3>
                        
                        <!-- Progress Desktop -->
                        <div class="hidden lg:block mb-8">
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-xs font-bold text-white bg-primary-orange px-2 py-0.5 rounded-md shadow-sm"><?= $percent ?>%</span>
                                <span class="text-[11px] text-gray-500 font-medium"><i class="bi bi-clock mr-1"></i> <?= $days_left($program['batas_waktu'] ?? null) ?></span>
                            </div>
                            <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden mb-3">
                                <div class="h-full bg-gradient-to-r from-primary-orange to-orange-400 rounded-full" style="width: <?= $percent ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-semibold">Terkumpul</p>
                                    <p class="text-sm font-bold text-dark-text"><?= $format_rupiah($program['donasi_terkumpul']) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-gray-400 uppercase font-semibold">Target</p>
                                    <p class="text-sm font-bold text-dark-text"><?= $format_rupiah($program['target_donasi']) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Donasi -->
                        <form action="<?= BASE_URL ?>/proses_donasi" method="POST" class="space-y-5">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id_program" value="<?= $program['id'] ?>">

                            <!-- Quick Nominal Options -->
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <?php foreach([50000, 100000, 200000, 500000] as $nom): ?>
                                    <button type="button" @click="nominal = '<?= $nom ?>'" :class="nominal === '<?= $nom ?>' ? 'bg-orange-50 border-primary-orange text-primary-orange font-bold' : 'bg-white border-gray-200 text-gray-600 hover:border-primary-orange'" class="border rounded-xl py-3 text-sm transition-all shadow-sm">
                                        <?= number_format($nom, 0, ',', '.') ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <!-- Custom Nominal Input (Floating Style) -->
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-lg font-bold text-gray-400 group-focus-within:text-primary-orange transition-colors">Rp</span>
                                <input type="number" id="nominal_custom" name="nominal" x-model="nominal" class="w-full pl-12 pr-4 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:bg-white focus:ring-0 focus:border-primary-orange text-lg font-bold text-dark-text transition-all outline-none" placeholder="Nominal lainnya" required>
                                <label class="absolute -top-2.5 left-4 bg-white px-1 text-[11px] font-bold text-gray-500 uppercase tracking-wide">Nominal Donasi</label>
                            </div>

                            <!-- Sapaan & Nama -->
                            <div class="flex gap-3">
                                <div class="w-1/3 relative">
                                    <select name="sapaan" class="w-full p-4 bg-gray-50 border-2 border-gray-100 rounded-xl focus:bg-white focus:border-primary-orange transition-all outline-none appearance-none font-medium text-gray-700">
                                        <option>Bpk/Ibu</option>
                                        <option>Bapak</option>
                                        <option>Ibu</option>
                                        <option>Kak</option>
                                    </select>
                                    <i class="bi bi-chevron-down absolute right-4 top-4.5 text-gray-400 pointer-events-none"></i>
                                </div>
                                <div class="w-2/3 relative">
                                    <input type="text" name="nama_donatur" class="w-full p-4 bg-gray-50 border-2 border-gray-100 rounded-xl focus:bg-white focus:border-primary-orange transition-all outline-none font-medium text-gray-800" placeholder="Nama Lengkap" value="<?= $_SESSION['user_nama_lengkap'] ?? '' ?>" required>
                                </div>
                            </div>

                            <!-- WhatsApp -->
                            <div class="relative">
                                <i class="bi bi-whatsapp absolute left-4 top-4.5 text-gray-400"></i>
                                <input type="text" name="kontak_donatur" class="w-full pl-11 p-4 bg-gray-50 border-2 border-gray-100 rounded-xl focus:bg-white focus:border-primary-orange transition-all outline-none font-medium text-gray-800" placeholder="No. WhatsApp" value="<?= $_SESSION['user_no_telepon'] ?? '' ?>" required>
                            </div>

                            <!-- Anonim Checkbox -->
                            <label class="flex items-center cursor-pointer p-3 bg-gray-50 rounded-xl border border-gray-100 hover:border-gray-200 transition-colors">
                                <input type="checkbox" name="is_anonim" class="w-5 h-5 rounded border-gray-300 text-primary-orange focus:ring-primary-orange cursor-pointer">
                                <span class="ml-3 text-sm font-medium text-gray-700">Sembunyikan nama saya (Anonim)</span>
                            </label>

                            <!-- Metode Pembayaran -->
                            <div class="pt-2">
                                <label class="block text-sm font-bold text-dark-text mb-3">Metode Pembayaran</label>
                                <div class="space-y-2.5 max-h-48 overflow-y-auto pr-1">
                                    <?php if(!empty($metode_pembayaran)): ?>
                                        <?php foreach (array_merge(...array_values($metode_pembayaran)) as $index => $metode): ?>
                                        <label class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all has-[:checked]:bg-orange-50 has-[:checked]:border-primary-orange hover:border-orange-200">
                                            <input type="radio" name="metode_pembayaran_id" value="<?= $metode['id'] ?>" class="w-5 h-5 text-primary-orange focus:ring-primary-orange border-gray-300" required <?= $index===0 ? 'checked' : '' ?>>
                                            <span class="ml-4 font-bold text-gray-800 text-sm"><?= htmlspecialchars($metode['nama_metode']) ?></span>
                                        </label>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="p-4 bg-red-50 text-red-600 rounded-xl text-sm font-medium border border-red-100">
                                            <i class="bi bi-exclamation-circle mr-2"></i> Belum ada metode pembayaran yang aktif.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Pesan Doa -->
                            <div class="relative">
                                <textarea name="doa" rows="2" class="w-full p-4 bg-gray-50 border-2 border-gray-100 rounded-xl focus:bg-white focus:border-primary-orange transition-all outline-none text-sm text-gray-700" placeholder="Tulis doa atau pesan dukungan (opsional)"></textarea>
                            </div>

                            <button type="submit" class="w-full bg-primary-orange text-white text-lg font-bold py-4 rounded-xl shadow-[0_10px_30px_-10px_rgba(249,115,22,0.8)] hover:bg-primary-orange-hover hover:-translate-y-1 active:translate-y-0 active:scale-95 transition-all flex items-center justify-center gap-2">
                                Lanjutkan Pembayaran <i class="bi bi-arrow-right"></i>
                            </button>
                        </form>
                        
                        <div class="mt-6 flex items-center justify-center gap-2 text-[11px] text-gray-400 font-medium">
                            <i class="bi bi-shield-check text-green-500 text-lg"></i> Transaksi Aman & Terenkripsi
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <!-- Floating Mobile Donation Action (Sticky at Bottom) -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-gray-100 p-4 z-40 pb-[calc(env(safe-area-inset-bottom,16px)+16px)] shadow-[0_-10px_30px_rgba(0,0,0,0.08)]">
        <button @click="document.getElementById('donasi-panel').scrollIntoView({behavior: 'smooth'})" class="w-full bg-primary-orange text-white text-base font-bold py-3.5 rounded-xl shadow-lg active:scale-95 transition-transform">
            Donasi Sekarang
        </button>
    </div>

</main>

<?php require_once __DIR__ . '/../../../includes/templates/footer.php'; ?>
