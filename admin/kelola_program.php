<?php
require_once '../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
$page_title = "Kelola Program Donasi";
require_once 'templates/header_admin.php';

// Menambahkan kolom 'kategori' pada query SELECT
$result_program = $mysqli->query("SELECT id, nama_program, kategori, target_donasi, donasi_terkumpul FROM program ORDER BY created_at DESC");
$priority_program_id = 0;
$stmt_priority = $mysqli->prepare("SELECT nilai_pengaturan FROM pengaturan WHERE nama_pengaturan = 'program_prioritas_beranda' LIMIT 1");
if ($stmt_priority) {
    $stmt_priority->execute();
    $priority_result = $stmt_priority->get_result();
    $priority_program_id = (int) (($priority_result ? $priority_result->fetch_assoc()['nilai_pengaturan'] : 0) ?? 0);
    $stmt_priority->close();
}
?>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-display font-bold text-slate-900"><?php echo $page_title; ?></h1>
            <p class="text-slate-500 mt-1">Manajemen data program donasi yang ditampilkan ke publik.</p>
        </div>
        <div>
            <a href="tambah_program.php" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-orange text-white font-medium rounded-xl hover:bg-orange-600 transition-all shadow-lg shadow-orange-500/20">
                <i class="bi bi-plus-lg"></i> Buat Program Baru
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 flex items-center gap-3">
            <i class="bi bi-check-circle-fill text-xl"></i>
            <span class="font-medium"><?php echo htmlspecialchars($_SESSION['success_message']); ?></span>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 flex items-center gap-3">
            <i class="bi bi-exclamation-triangle-fill text-xl"></i>
            <span class="font-medium"><?php echo htmlspecialchars($_SESSION['error_message']); ?></span>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Main Content Box -->
    <div class="bg-white rounded-[20px] shadow-sm border border-slate-100 overflow-hidden">
        
        <!-- Info Banner -->
        <div class="m-6 p-4 rounded-xl border border-orange-100 bg-orange-50/50 flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-orange-100 text-primary-orange flex items-center justify-center shrink-0">
                <i class="bi bi-star-fill"></i>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-800">Program Prioritas Beranda</h2>
                <p class="text-sm text-slate-600 mt-1">Pilih satu program untuk ditampilkan pada blok hero “Program Prioritas” di beranda. Klik ikon bintang untuk mengatur.</p>
            </div>
        </div>

        <!-- Program List -->
        <div class="px-6 pb-6 space-y-4">
            <?php if ($result_program && $result_program->num_rows > 0): ?>
                <?php while($program = $result_program->fetch_assoc()): 
                    $persentase = $program['target_donasi'] > 0 ? min(100, ($program['donasi_terkumpul'] / $program['target_donasi']) * 100) : 0;
                    $is_priority = (int) $program['id'] === $priority_program_id;
                ?>
                
                <div class="group flex flex-col md:flex-row items-start md:items-center gap-5 p-5 border border-slate-200 hover:border-orange-200 rounded-2xl bg-white hover:shadow-md transition-all">
                    
                    <!-- Program Details -->
                    <div class="flex-1 min-w-0 space-y-3">
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="inline-flex px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-semibold">
                                <?php echo htmlspecialchars($program['kategori']); ?>
                            </span>
                            <h3 class="font-bold text-slate-800 text-lg truncate" title="<?php echo htmlspecialchars($program['nama_program']); ?>">
                                <?php echo htmlspecialchars($program['nama_program']); ?>
                            </h3>
                            <?php if ($is_priority): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-orange-100 text-primary-orange text-xs font-bold uppercase tracking-wider">
                                    <i class="bi bi-star-fill text-[10px]"></i> Prioritas
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Progress Bar Area -->
                        <div class="max-w-xl">
                            <div class="flex justify-between items-end mb-2">
                                <div>
                                    <span class="text-xs text-slate-500 font-medium">Terkumpul</span>
                                    <p class="text-sm font-bold text-emerald-600">Rp <?php echo number_format($program['donasi_terkumpul'], 0, ',', '.'); ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-slate-500 font-medium">Target</span>
                                    <p class="text-sm font-semibold text-slate-700">Rp <?php echo number_format($program['target_donasi'], 0, ',', '.'); ?></p>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-primary-orange h-full rounded-full transition-all duration-1000" style="width: <?php echo $persentase; ?>%"></div>
                            </div>
                            <div class="text-right text-xs font-bold text-primary-orange mt-1.5"><?php echo round($persentase); ?>%</div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 md:pl-4 pt-4 md:pt-0 w-full md:w-auto border-t md:border-t-0 border-slate-100 justify-end shrink-0">
                        <form action="set_program_prioritas.php" method="POST" class="inline">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id_program" value="<?php echo (int) $program['id']; ?>">
                            <input type="hidden" name="mode" value="<?php echo $is_priority ? 'unset' : 'set'; ?>">
                            <button type="submit"
                                class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors <?php echo $is_priority ? 'bg-orange-100 text-primary-orange hover:bg-orange-200 shadow-inner' : 'bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600 border border-slate-200'; ?>"
                                title="<?php echo $is_priority ? 'Cabut Prioritas' : 'Jadikan Prioritas'; ?>">
                                <i class="bi <?php echo $is_priority ? 'bi-star-fill' : 'bi-star'; ?> text-lg"></i>
                            </button>
                        </form>
                        
                        <div class="h-8 w-px bg-slate-200 mx-1"></div>

                        <a href="kelola_kabar_program.php?id=<?php echo $program['id']; ?>"
                            class="w-10 h-10 rounded-xl flex items-center justify-center bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors" title="Kelola Kabar Terbaru">
                            <i class="bi bi-megaphone text-lg"></i>
                        </a>

                        <a href="edit_program.php?id=<?php echo $program['id']; ?>"
                            class="w-10 h-10 rounded-xl flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="Edit">
                            <i class="bi bi-pencil-square text-lg"></i>
                        </a>
                        
                        <form action="hapus_program.php" method="POST" class="inline" onsubmit="return confirm('Anda yakin ingin menghapus program ini beserta semua data donasi terkait?');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo $program['id']; ?>">
                            <button type="submit" class="w-10 h-10 rounded-xl flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Hapus">
                                <i class="bi bi-trash text-lg"></i>
                            </button>
                        </form>
                    </div>
                </div>
                
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-16 px-4 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm text-slate-300">
                        <i class="bi bi-folder-x text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-1">Belum ada Program</h3>
                    <p class="text-slate-500 max-w-md mx-auto mb-6">Anda belum membuat program donasi. Buat program pertama Anda untuk mulai menggalang dana.</p>
                    <a href="tambah_program.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-800 text-white font-medium rounded-xl hover:bg-slate-700 transition-colors">
                        <i class="bi bi-plus-lg"></i> Buat Program
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php require_once 'templates/footer_admin.php'; ?>
