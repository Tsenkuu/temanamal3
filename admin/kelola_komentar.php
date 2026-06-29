<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Kelola Komentar";

// Ambil semua komentar, urutkan berdasarkan yang terbaru dan status pending
$result = $mysqli->query("
    SELECT k.id, k.nama_pengirim, k.isi_komentar, k.created_at, k.status, b.judul AS judul_berita, b.id AS id_berita
    FROM komentar k
    JOIN berita b ON k.id_berita = b.id
    ORDER BY k.status ASC, k.created_at DESC
");

require_once 'templates/header_admin.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-bold text-dark-text"><?php echo $page_title; ?></h1>
    </div>

    <?php
    if (isset($_SESSION['message'])) {
        $alert_type = $_SESSION['message_type'] === 'success' ? 'alert-success' : 'alert-danger';
        echo '<div class="' . $alert_type . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message'], $_SESSION['message_type']);
    }
    ?>

    <div class="content-card mt-6">
        <div class="space-y-4">
            <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <div class="p-4 border rounded-lg bg-white flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-dark-text">
                                <?php echo htmlspecialchars($row['nama_pengirim']); ?></p>
                            <p class="text-xs text-gray-500">Pada berita: <a
                                    href="../berita/<?php echo $row['id_berita']; ?>" target="_blank"
                                    class="hover:underline"><?php echo htmlspecialchars($row['judul_berita']); ?></a>
                            </p>
                        </div>
                        <span
                            class="text-xs text-gray-500"><?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?></span>
                    </div>
                    <p class="mt-2 text-gray-700"><?php echo nl2br(htmlspecialchars($row['isi_komentar'])); ?></p>
                </div>
                <div class="flex-shrink-0 flex flex-row md:flex-col items-center justify-end md:justify-start gap-2">
                    <?php if ($row['status'] == 'pending'): ?>
                    <form action="setujui_komentar.php" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="btn-icon bg-green-100 text-green-600 hover:bg-green-200" title="Setujui"><i
                                class="bi bi-check-lg"></i></button>
                    </form>
                    <?php endif; ?>
                    <form action="hapus_komentar.php" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus komentar ini?');">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="btn-icon bg-red-100 text-red-600 hover:bg-red-200" title="Hapus"><i
                                class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <p class="text-center text-gray-500 py-8">Belum ada komentar.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'templates/footer_admin.php'; ?>
