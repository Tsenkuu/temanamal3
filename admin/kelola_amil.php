<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Kelola Amil";
$result_amil = $mysqli->query("SELECT id, nama_lengkap, jabatan, foto, status, tampilkan_di_beranda FROM amil ORDER BY urutan ASC");

require_once 'templates/header_admin.php';
?>
<style>
.sortable-ghost {
    opacity: 0.4;
    background-color: #f0f8ff;
}

.handle {
    cursor: grab;
}

.handle:active {
    cursor: grabbing;
}
</style>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-bold text-dark-text">Kelola Amil</h1>
        <a href="tambah_amil.php" class="btn-primary">
            <i class="bi bi-person-plus-fill mr-2"></i> Tambah Amil Baru
        </a>
    </div>

    <div class="alert-info">
        <i class="bi bi-info-circle-fill mr-2"></i>
        Anda dapat mengubah urutan tim Amil di halaman depan dengan menggeser (drag & drop) kartu di bawah ini.
        Perubahan disimpan otomatis.
    </div>

    <div id="save-status" class="alert-success" style="display: none;">
        <i class="bi bi-check-circle-fill mr-2"></i>
        Urutan berhasil disimpan!
    </div>

    <div id="sortable-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-6">
        <?php if ($result_amil && $result_amil->num_rows > 0): ?>
        <?php while($amil = $result_amil->fetch_assoc()): ?>
        <div class="content-card text-center" data-id="<?php echo $amil['id']; ?>">
            <div class="handle text-gray-400 absolute top-2 right-2 p-1">
                <i class="bi bi-grip-vertical"></i>
            </div>
            <img src="../assets/uploads/amil/<?php echo htmlspecialchars($amil['foto']); ?>"
                alt="Foto <?php echo htmlspecialchars($amil['nama_lengkap']); ?>"
                class="w-24 h-24 rounded-full mx-auto object-cover mb-4 border-4 border-white shadow-md">
            <h3 class="font-bold text-lg text-dark-text"><?php echo htmlspecialchars($amil['nama_lengkap']); ?></h3>
            <p class="text-primary-orange font-semibold"><?php echo htmlspecialchars($amil['jabatan']); ?></p>
            <div class="flex justify-center gap-2 mt-2">
                <span
                    class="badge-<?php echo $amil['status'] == 'Aktif' ? 'success' : 'secondary'; ?>"><?php echo $amil['status']; ?></span>
                <span class="badge-<?php echo $amil['tampilkan_di_beranda'] == 'Ya' ? 'info' : 'secondary'; ?>">Di
                    Beranda: <?php echo $amil['tampilkan_di_beranda']; ?></span>
            </div>
            <div class="mt-4 border-t pt-4 flex justify-center gap-2">
                <a href="edit_amil.php?id=<?php echo $amil['id']; ?>"
                    class="btn-icon bg-yellow-100 text-yellow-600 hover:bg-yellow-200" title="Edit"><i
                        class="bi bi-pencil-square"></i></a>
                <form action="hapus_amil.php" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus amil ini?');">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" value="<?php echo $amil['id']; ?>">
                    <button type="submit" class="btn-icon bg-red-100 text-red-600 hover:bg-red-200" title="Hapus"><i
                            class="bi bi-trash"></i></button>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
        <?php else: ?>
        <p class="col-span-full text-center text-gray-500 py-10">Belum ada data amil.</p>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('sortable-container');
    const saveStatus = document.getElementById('save-status');

    new Sortable(container, {
        animation: 150,
        handle: '.handle',
        ghostClass: 'sortable-ghost',
        onEnd: function() {
            const order = Array.from(container.children).map(el => el.dataset.id);
            fetch('update_urutan_amil.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        order: order,
                        csrf_token: <?php echo json_encode(csrf_token()); ?>
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        saveStatus.style.display = 'flex';
                        setTimeout(() => {
                            saveStatus.style.display = 'none';
                        }, 2000);
                    } else {
                        alert('Gagal menyimpan urutan: ' + (data.message ||
                            'Error tidak diketahui'));
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    });
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>
