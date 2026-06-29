<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: kelola_program.php');
    exit();
}

$program_id = (int)$_GET['id'];
$stmt = $mysqli->prepare("SELECT id, nama_program FROM program WHERE id = ?");
$stmt->bind_param("i", $program_id);
$stmt->execute();
$program = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$program) {
    header('Location: kelola_program.php');
    exit();
}

$page_title = "Kabar Terbaru: " . htmlspecialchars($program['nama_program']);

// Ambil data kabar
$stmt = $mysqli->prepare("SELECT * FROM kabar_program WHERE id_program = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $program_id);
$stmt->execute();
$result_kabar = $stmt->get_result();

require_once 'templates/header_admin.php';
?>

<main class="main-content">
    <div class="page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-dark-text"><?php echo $page_title; ?></h1>
            <p class="text-sm text-gray-500">Kelola update atau perkembangan penyaluran donasi program ini.</p>
        </div>
        <div class="flex gap-2">
            <a href="kelola_program.php" class="btn-secondary">
                <i class="bi bi-arrow-left mr-2"></i> Kembali
            </a>
            <a href="tambah_kabar_program.php?id=<?php echo $program_id; ?>" class="btn-primary">
                <i class="bi bi-plus-lg mr-2"></i> Kabar Baru
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

    <div class="bg-white rounded-[20px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 space-y-4">
            <?php if ($result_kabar->num_rows > 0): ?>
                <?php while($kabar = $result_kabar->fetch_assoc()): ?>
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between p-5 border border-slate-200 hover:border-orange-200 rounded-2xl transition-all">
                    <div class="flex-1 min-w-0 mb-4 md:mb-0 pr-4">
                        <h3 class="font-bold text-slate-800 text-lg mb-1 truncate">
                            <?php echo htmlspecialchars($kabar['judul_kabar']); ?>
                        </h3>
                        <p class="text-sm text-slate-500 flex items-center gap-1">
                            <i class="bi bi-calendar3"></i> <?php echo date('d M Y H:i', strtotime($kabar['created_at'])); ?>
                        </p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="edit_kabar_program.php?id=<?php echo $kabar['id']; ?>"
                            class="w-10 h-10 rounded-xl flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="Edit">
                            <i class="bi bi-pencil-square text-lg"></i>
                        </a>
                        <form action="hapus_kabar_program.php" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus kabar ini?');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo $kabar['id']; ?>">
                            <button type="submit" class="w-10 h-10 rounded-xl flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Hapus">
                                <i class="bi bi-trash text-lg"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-inbox text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-1">Belum Ada Kabar</h3>
                    <p class="text-slate-500 max-w-sm mx-auto">Tambahkan pembaruan atau laporan terkini terkait program ini agar donatur mengetahui perkembangannya.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'templates/footer_admin.php'; ?>
