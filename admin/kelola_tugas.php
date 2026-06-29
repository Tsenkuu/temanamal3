<?php
require_once '../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
$page_title = "Penugasan Amil";

// --- PENCARIAN & PAGINASI ---
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Query dasar
$sql_base = "FROM tugas_pengambilan t JOIN amil a ON t.id_amil = a.id JOIN kotak_infak k ON t.id_kotak_infak = k.id";
$sql_where = !empty($search) ? " WHERE (a.nama_lengkap LIKE ? OR k.nama_lokasi LIKE ?)" : '';
$search_param = "%{$search}%";

// Total data
$stmt_total = $mysqli->prepare("SELECT COUNT(t.id) as total " . $sql_base . $sql_where);
if(!empty($search)) $stmt_total->bind_param('ss', $search_param, $search_param);
$stmt_total->execute();
$total_results = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);

// Data per halaman
$sql_data = "SELECT t.id, t.tanggal_tugas, t.status, a.nama_lengkap, k.nama_lokasi " . $sql_base . $sql_where . " ORDER BY t.tanggal_tugas DESC, t.id DESC LIMIT ? OFFSET ?";
$stmt_data = $mysqli->prepare($sql_data);
if(!empty($search)) {
    $stmt_data->bind_param('ssii', $search_param, $search_param, $limit, $offset);
} else {
    $stmt_data->bind_param('ii', $limit, $offset);
}
$stmt_data->execute();
$result_tugas = $stmt_data->get_result();

// Data untuk form
$result_amil = $mysqli->query("SELECT id, nama_lengkap FROM amil WHERE status = 'Aktif' ORDER BY nama_lengkap ASC");
$result_kotak = $mysqli->query("SELECT id, kode_kotak, nama_lokasi FROM kotak_infak WHERE status = 'Aktif' ORDER BY nama_lokasi ASC");

require_once 'templates/header_admin.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-bold text-dark-text">Penugasan Amil</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <!-- Kolom Form Tambah Tugas -->
        <div class="lg:col-span-1">
            <div class="content-card">
                <h3 class="card-title mb-4">Buat Tugas Baru</h3>
                <form action="proses_tugas.php" method="POST" class="space-y-4">
                    <div>
                        <label for="id_kotak_infak" class="form-label">Pilih Kotak Infak</label>
                        <select class="form-select" id="id_kotak_infak" name="id_kotak_infak" required>
                            <option value="" disabled selected>-- Pilih Lokasi --</option>
                            <?php while($kotak = $result_kotak->fetch_assoc()): ?>
                            <option value="<?php echo $kotak['id']; ?>">
                                <?php echo htmlspecialchars($kotak['kode_kotak'] . ' - ' . $kotak['nama_lokasi']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label for="id_amil" class="form-label">Tugaskan ke Amil</label>
                        <select class="form-select" id="id_amil" name="id_amil" required>
                            <option value="" disabled selected>-- Pilih Amil --</option>
                            <?php while($amil = $result_amil->fetch_assoc()): ?>
                            <option value="<?php echo $amil['id']; ?>">
                                <?php echo htmlspecialchars($amil['nama_lengkap']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label for="tanggal_tugas" class="form-label">Tanggal Tugas</label>
                        <input type="date" class="form-input" id="tanggal_tugas" name="tanggal_tugas" required>
                    </div>
                    <button type="submit" name="tambah_tugas" class="btn-primary w-full">Simpan Tugas</button>
                </form>
            </div>
        </div>

        <!-- Kolom Daftar Tugas -->
        <div class="lg:col-span-2">
            <div class="content-card">
                <form action="kelola_tugas.php" method="GET" class="mb-4">
                    <div class="flex">
                        <input type="text" name="search" class="form-input rounded-r-none"
                            placeholder="Cari Amil atau Lokasi..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn-primary rounded-l-none">Cari</button>
                    </div>
                </form>

                <div class="table-wrapper">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Detail Tugas</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_tugas->num_rows > 0): ?>
                            <?php while($tugas = $result_tugas->fetch_assoc()): ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-dark-text">
                                        <?php echo htmlspecialchars($tugas['nama_lengkap']); ?></p>
                                    <p class="text-xs"><?php echo htmlspecialchars($tugas['nama_lokasi']); ?> |
                                        <?php echo date('d M Y', strtotime($tugas['tanggal_tugas'])); ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="badge-<?php echo $tugas['status'] == 'Ditugaskan' ? 'warning' : 'success'; ?>"><?php echo $tugas['status']; ?></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="hapus_tugas.php?id=<?php echo $tugas['id']; ?>"
                                        class="btn-icon bg-red-100 text-red-600 hover:bg-red-200"
                                        onclick="return confirm('Yakin?');" title="Hapus"><i
                                            class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr class="bg-white border-b">
                                <td colspan="3" class="px-6 py-4 text-center">Tidak ada tugas ditemukan.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="pagination mt-4">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                        class="<?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php require_once 'templates/footer_admin.php'; ?>