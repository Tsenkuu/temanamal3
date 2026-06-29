<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
include '../includes/config.php';
include 'functions.php';

$page_title = "Edit Majalah";
include 'templates/header_admin.php';
require_once 'templates/sidebar_admin.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: kelola_majalah.php');
    exit();
}

$id = $_GET['id'];
$stmt = $mysqli->prepare("SELECT * FROM majalah WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$majalah = $result->fetch_assoc();
$stmt->close();

if (!$majalah) {
    header('Location: kelola_majalah.php');
    exit();
}

?>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-semibold text-gray-800">Edit Majalah</h1>
        <div class="flex items-center space-x-2">
            <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">Dashboard</a>
            <span class="text-gray-400">/</span>
            <a href="kelola_majalah.php" class="text-gray-600 hover:text-gray-800">Majalah</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-800">Edit</span>
        </div>
    </div>

    <div class="content-card max-w-2xl mx-auto">
        <h2 class="card-title mb-6">Form Edit Majalah</h2>
        <form action="proses_edit_majalah.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $majalah['id']; ?>">
            <div class="mb-4">
                <label for="judul" class="form-label">Judul</label>
                <input type="text" id="judul" name="judul" class="form-input" value="<?php echo htmlspecialchars($majalah['judul']); ?>" required>
            </div>
            <div class="mb-4">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4" class="form-textarea" required><?php echo htmlspecialchars($majalah['deskripsi']); ?></textarea>
            </div>
            <div class="mb-6">
                <label for="link_majalah" class="form-label">Link Majalah (URL)</label>
                <input type="url" id="link_majalah" name="link_majalah" class="form-input" value="<?php echo htmlspecialchars($majalah['link']); ?>" placeholder="https://example.com/majalah" required>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check-lg mr-2"></i>
                    Update
                </button>
            </div>
        </form>
    </div>
</main>

<?php include 'templates/footer_admin.php'; ?>