<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}
include '../includes/config.php';
include 'functions.php';

$page_title = "Tambah Majalah";
include 'templates/header_admin.php';
require_once 'templates/sidebar_admin.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="text-2xl font-semibold text-gray-800">Tambah Majalah</h1>
        <div class="flex items-center space-x-2">
            <a href="dashboard.php" class="text-gray-600 hover:text-gray-800">Dashboard</a>
            <span class="text-gray-400">/</span>
            <a href="kelola_majalah.php" class="text-gray-600 hover:text-gray-800">Majalah</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-800">Tambah</span>
        </div>
    </div>

    <div class="content-card max-w-2xl mx-auto">
        <h2 class="card-title mb-6">Form Tambah Majalah</h2>
        <form action="proses_tambah_majalah.php" method="POST">
            <div class="mb-4">
                <label for="judul" class="form-label">Judul</label>
                <input type="text" id="judul" name="judul" class="form-input" required>
            </div>
            <div class="mb-4">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4" class="form-textarea" required></textarea>
            </div>
            <div class="mb-6">
                <label for="link_majalah" class="form-label">Link Majalah (URL)</label>
                <input type="url" id="link_majalah" name="link_majalah" class="form-input" placeholder="https://example.com/majalah" required>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check-lg mr-2"></i>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</main>

<?php include 'templates/footer_admin.php'; ?>