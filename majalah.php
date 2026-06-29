<?php
session_start();
require_once 'includes/config.php';

$page_title = "Majalah";
include 'includes/templates/header.php';

$result = $mysqli->query("SELECT * FROM majalah ORDER BY tanggal_upload DESC");
?>

<div class="container mx-auto px-4 py-16">
    <h1 class="text-4xl font-bold text-center mb-8">Majalah Kami</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($row['judul']); ?></h2>
                <p class="text-gray-700 mb-4"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                <a href="baca_majalah.php?id=<?php echo $row['id']; ?>" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Baca Sekarang</a>
            </div>
            <div class="bg-gray-100 px-6 py-4">
                <p class="text-sm text-gray-600">Diunggah pada <?php echo date('d F Y', strtotime($row['tanggal_upload'])); ?></p>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<?php include 'includes/templates/footer.php'; ?>