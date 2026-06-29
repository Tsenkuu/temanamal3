<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id']) && !isset($_POST['id_program'])) {
    header('Location: kelola_program.php');
    exit();
}

$program_id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_POST['id_program'];

// Cek apakah program valid
$stmt = $mysqli->prepare("SELECT id, nama_program FROM program WHERE id = ?");
$stmt->bind_param("i", $program_id);
$stmt->execute();
$program = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$program) {
    header('Location: kelola_program.php');
    exit();
}

$page_title = "Tambah Kabar: " . htmlspecialchars($program['nama_program']);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul_kabar = trim($_POST['judul_kabar']);
    $konten_kabar = $_POST['konten_kabar'];

    if (empty($judul_kabar) || empty($konten_kabar)) {
        $errors[] = "Judul dan konten kabar tidak boleh kosong.";
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO kabar_program (id_program, judul_kabar, konten_kabar) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $program_id, $judul_kabar, $konten_kabar);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Kabar program berhasil ditambahkan.";
            header("Location: kelola_kabar_program.php?id=" . $program_id);
            exit();
        } else {
            $errors[] = "Gagal menyimpan kabar: " . $stmt->error;
        }
        $stmt->close();
    }
}

require_once 'templates/header_admin.php';
?>

<!-- TinyMCE Script -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.6.0/tinymce.min.js" referrerpolicy="origin"></script>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="text-2xl font-bold text-dark-text"><?php echo $page_title; ?></h1>
            <p class="text-sm text-gray-500">Tulis update terbaru untuk program ini.</p>
        </div>
    </div>

    <form action="tambah_kabar_program.php" method="POST">
        <input type="hidden" name="id_program" value="<?php echo $program_id; ?>">
        
        <div class="content-card max-w-4xl">
            <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <?php foreach ($errors as $error): ?><p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="space-y-6">
                <div>
                    <label for="judul_kabar" class="form-label">Judul Kabar</label>
                    <input type="text" class="form-input" id="judul_kabar" name="judul_kabar" required
                        placeholder="Misal: Penyaluran Bantuan Tahap 1"
                        value="<?php echo isset($_POST['judul_kabar']) ? htmlspecialchars($_POST['judul_kabar']) : ''; ?>">
                </div>

                <div>
                    <label for="konten_kabar" class="form-label">Isi Kabar / Detail Laporan</label>
                    <textarea id="konten_kabar" name="konten_kabar"><?php echo isset($_POST['konten_kabar']) ? htmlspecialchars($_POST['konten_kabar']) : ''; ?></textarea>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="submit" class="btn-primary">Simpan Kabar</button>
                    <a href="kelola_kabar_program.php?id=<?php echo $program_id; ?>" class="btn-secondary">Batal</a>
                </div>
            </div>
        </div>
    </form>
</main>

<script>
tinymce.init({
    selector: '#konten_kabar',
    height: 300,
    plugins: 'lists link wordcount',
    toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | link | removeformat',
    menubar: false,
    branding: false,
    skin: 'oxide',
    content_css: 'default',
    setup: function (editor) {
        editor.on('change', function () {
            editor.save();
        });
    }
});
</script>

<?php require_once 'templates/footer_admin.php'; ?>
