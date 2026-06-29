<?php
include 'includes/config.php';

if (!isset($_GET['id'])) {
    header("Location: majalah.php");
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
    header("Location: majalah.php");
    exit();
}

$page_title = htmlspecialchars($majalah['judul']);
include 'includes/templates/header.php';
?>

<div class="container mx-auto px-4 py-8 md:py-12">
    <div class="text-center mb-6 md:mb-8">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900"><?php echo htmlspecialchars($majalah['judul']); ?></h1>

        <p class="text-base sm:text-lg text-gray-600 mt-2 max-w-3xl mx-auto">
            <?php echo htmlspecialchars($majalah['deskripsi']); ?></p>
    </div>

    <div class="max-w-6xl mx-auto bg-gray-100 rounded-lg shadow-lg p-2 sm:p-4">
        <iframe src="<?php echo htmlspecialchars($majalah['link']); ?>"
            class="w-full h-[70vh] md:h-[80vh] border-none rounded" allowfullscreen>
        </iframe>
    </div>
</div>

<?php include 'includes/templates/footer.php'; ?>