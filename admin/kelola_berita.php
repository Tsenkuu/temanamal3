<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Kelola Berita";

// --- LOGIKA PENCARIAN, FILTER, DAN PAGINASI ---
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'semua';
$type_filter = $_GET['type'] ?? 'semua';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Bangun query dasar
$sql_base = "FROM berita";
$where_clauses = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_clauses[] = "(judul LIKE ? OR penulis LIKE ?)";
    $search_param = "%{$search}%";
    array_push($params, $search_param, $search_param);
    $types .= 'ss';
}

if ($status_filter !== 'semua' && in_array($status_filter, ['published', 'pending', 'rejected'])) {
    $where_clauses[] = "status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($type_filter !== 'semua' && in_array($type_filter, ['berita', 'opini', 'kajian'])) {
    $where_clauses[] = "type = ?";
    $params[] = $type_filter;
    $types .= 's';
}

$sql_where = count($where_clauses) > 0 ? " WHERE " . implode(' AND ', $where_clauses) : '';

// Query untuk menghitung total data
$sql_total = "SELECT COUNT(id) as total " . $sql_base . $sql_where;
$stmt_total = $mysqli->prepare($sql_total);
if (!empty($types)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_results = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);
$stmt_total->close();

// Query untuk mengambil data per halaman
$sql_data = "SELECT id, judul, slug, gambar, penulis, editor, status, type, created_at " . $sql_base . $sql_where . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt_data = $mysqli->prepare($sql_data);
$stmt_data->bind_param($types, ...$params);
$stmt_data->execute();
$result_berita = $stmt_data->get_result();
$stmt_data->close();

function get_status_badge($status) {
    switch ($status) {
        case 'published': return '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Published</span>';
        case 'pending': return '<span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">Pending</span>';
        case 'rejected': return '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Rejected</span>';
        default: return '<span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Unknown</span>';
    }
}

function get_type_badge($type) {
    switch ($type) {
        case 'berita': return '<span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">Berita</span>';
        case 'opini': return '<span class="px-2 py-1 text-xs font-semibold text-purple-800 bg-purple-100 rounded-full">Opini</span>';
        case 'kajian': return '<span class="px-2 py-1 text-xs font-semibold text-emerald-800 bg-emerald-100 rounded-full">Kajian</span>';
        default: return '<span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Unknown</span>';
    }
}

require_once 'templates/header_admin.php';
?>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-display font-bold text-slate-900">Kelola Berita</h1>
            <p class="text-slate-500 mt-1">Manajemen publikasi artikel, berita, dan opini.</p>
        </div>
        <div>
            <a href="tambah_berita.php" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-orange text-white font-medium rounded-xl hover:bg-orange-600 transition-all shadow-lg shadow-orange-500/20">
                <i class="bi bi-plus-lg"></i> Tulis Berita Baru
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

    <!-- Filter & Pencarian -->
    <div class="bg-white rounded-[20px] shadow-sm border border-slate-100 p-6 mb-8">
        <form action="kelola_berita.php" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-semibold text-slate-700 mb-2">Pencarian</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-search text-slate-400"></i>
                        </div>
                        <input type="text" id="search" name="search" 
                            class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary-orange/50 focus:border-primary-orange transition-all placeholder:text-slate-400" 
                            placeholder="Cari judul atau penulis..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
                    <select id="status" name="status" class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary-orange/50 focus:border-primary-orange transition-all appearance-none cursor-pointer">
                        <option value="semua" <?php if ($status_filter == 'semua') echo 'selected'; ?>>Semua Status</option>
                        <option value="published" <?php if ($status_filter == 'published') echo 'selected'; ?>>Published</option>
                        <option value="pending" <?php if ($status_filter == 'pending') echo 'selected'; ?>>Pending</option>
                        <option value="rejected" <?php if ($status_filter == 'rejected') echo 'selected'; ?>>Rejected</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <div class="flex-1">
                        <label for="type" class="block text-sm font-semibold text-slate-700 mb-2">Tipe</label>
                        <select id="type" name="type" class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary-orange/50 focus:border-primary-orange transition-all appearance-none cursor-pointer">
                            <option value="semua" <?php if ($type_filter == 'semua') echo 'selected'; ?>>Semua Tipe</option>
                            <option value="berita" <?php if ($type_filter == 'berita') echo 'selected'; ?>>Berita</option>
                            <option value="opini" <?php if ($type_filter == 'opini') echo 'selected'; ?>>Opini</option>
                            <option value="kajian" <?php if ($type_filter == 'kajian') echo 'selected'; ?>>Kajian</option>
                        </select>
                    </div>
                    <div class="shrink-0 self-end">
                        <button type="submit" class="h-[46px] px-5 rounded-xl font-medium bg-slate-800 text-white hover:bg-slate-700 transition-colors shadow-sm">
                            Cari
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Daftar Berita -->
    <div class="bg-white rounded-[20px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 space-y-4">
            <?php if ($result_berita->num_rows > 0): ?>
                <?php while($berita = $result_berita->fetch_assoc()): ?>
                <div class="group flex flex-col md:flex-row items-start md:items-center gap-5 p-5 border border-slate-200 hover:border-orange-200 rounded-2xl bg-white hover:shadow-md transition-all">
                    
                    <!-- Image -->
                    <div class="w-full md:w-32 h-40 md:h-24 shrink-0 rounded-xl overflow-hidden bg-slate-100 border border-slate-200">
                        <?php if ($berita['gambar']): ?>
                            <img src="../assets/uploads/berita/<?php echo htmlspecialchars($berita['gambar']); ?>"
                                alt="Gambar Berita" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                <i class="bi bi-image text-3xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <?php echo get_type_badge($berita['type']); ?>
                            <?php echo get_status_badge($berita['status']); ?>
                        </div>
                        <h3 class="font-bold text-slate-800 text-lg mb-1 truncate" title="<?php echo htmlspecialchars($berita['judul']); ?>">
                            <?php echo htmlspecialchars($berita['judul']); ?>
                        </h3>
                        <p class="text-sm text-slate-500 flex items-center gap-1">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($berita['penulis']); ?> 
                            <?php if(!empty($berita['editor'])): ?>
                                <span class="mx-1">&bull;</span>
                                <i class="bi bi-pencil"></i> <?php echo htmlspecialchars($berita['editor']); ?>
                            <?php endif; ?>
                            <span class="mx-1">&bull;</span>
                            <i class="bi bi-calendar3"></i> <?php echo date('d M Y', strtotime($berita['created_at'])); ?>
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 md:pl-4 pt-4 md:pt-0 w-full md:w-auto border-t md:border-t-0 border-slate-100 justify-end shrink-0">
                        <a href="../berita/<?php echo $berita['slug']; ?>" target="_blank"
                            class="w-10 h-10 rounded-xl flex items-center justify-center bg-slate-50 text-slate-500 hover:bg-slate-100 hover:text-slate-700 border border-slate-200 transition-colors" title="Preview Artikel">
                            <i class="bi bi-box-arrow-up-right text-lg"></i>
                        </a>

                        <?php if ($berita['status'] == 'pending'): ?>
                        <div class="h-8 w-px bg-slate-200 mx-1"></div>
                        <form action="proses_persetujuan_berita.php" method="POST" class="inline">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo $berita['id']; ?>">
                            <input type="hidden" name="action" value="setujui">
                            <button type="submit" class="w-10 h-10 rounded-xl flex items-center justify-center bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors" title="Setujui">
                                <i class="bi bi-check-lg text-lg"></i>
                            </button>
                        </form>
                        <form action="proses_persetujuan_berita.php" method="POST" class="inline">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo $berita['id']; ?>">
                            <input type="hidden" name="action" value="tolak">
                            <button type="submit" class="w-10 h-10 rounded-xl flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Tolak">
                                <i class="bi bi-x-lg text-lg"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <div class="h-8 w-px bg-slate-200 mx-1"></div>
                        
                        <a href="edit_berita.php?id=<?php echo $berita['id']; ?>"
                            class="w-10 h-10 rounded-xl flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="Edit">
                            <i class="bi bi-pencil-square text-lg"></i>
                        </a>
                        
                        <form action="hapus_berita.php" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus berita ini secara permanen?');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo $berita['id']; ?>">
                            <button type="submit" class="w-10 h-10 rounded-xl flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Hapus">
                                <i class="bi bi-trash text-lg"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-16 px-4">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                        <i class="bi bi-file-earmark-x text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-1">Tidak Ditemukan</h3>
                    <p class="text-slate-500 max-w-sm mx-auto">Tidak ada data berita yang cocok dengan kriteria pencarian Anda.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Paginasi -->
        <?php if ($total_pages > 1): ?>
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-center">
            <nav class="flex items-center gap-1">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&type=<?php echo $type_filter; ?>"
                        class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-medium transition-colors 
                        <?php echo ($page == $i) 
                            ? 'bg-primary-orange text-white shadow-md shadow-orange-500/20' 
                            : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
        <?php endif; ?>
    </div>

<?php require_once 'templates/footer_admin.php'; ?>
