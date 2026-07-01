<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_valid_csrf();

    if (!rate_limit_request('comment_submit', 4, 180)) {
        $_SESSION['comment_message'] = 'Komentar dikirim terlalu cepat. Coba lagi sebentar.';
        $_SESSION['comment_status'] = 'error';
        header("Location: " . BASE_URL . "/berita");
        exit();
    }

    $id_berita = isset($_POST['id_berita']) ? (int)$_POST['id_berita'] : 0;
    $nama_pengirim = clean_text($_POST['nama_pengirim'] ?? '', 100);
    $isi_komentar = clean_multiline_text($_POST['isi_komentar'] ?? '', 1500);
    
    // Validasi dasar
    if ($id_berita > 0 && !empty($nama_pengirim) && !empty($isi_komentar)) {
        $stmt = $mysqli->prepare("INSERT INTO komentar (id_berita, nama_pengirim, isi_komentar, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("iss", $id_berita, $nama_pengirim, $isi_komentar);
        
        if ($stmt->execute()) {
            $_SESSION['comment_message'] = 'Terima kasih! Komentar Anda telah dikirim dan akan ditampilkan setelah disetujui oleh admin.';
            $_SESSION['comment_status'] = 'success';
        } else {
            $_SESSION['comment_message'] = 'Maaf, terjadi kesalahan saat mengirim komentar.';
            $_SESSION['comment_status'] = 'error';
        }
        $stmt->close();
    } else {
        $_SESSION['comment_message'] = 'Nama dan isi komentar tidak boleh kosong.';
        $_SESSION['comment_status'] = 'error';
    }
    
    // Alihkan kembali ke halaman berita
    $stmt_slug = $mysqli->prepare("SELECT slug FROM berita WHERE id = ? LIMIT 1");
    $stmt_slug->bind_param("i", $id_berita);
    $stmt_slug->execute();
    $berita_row = $stmt_slug->get_result()->fetch_assoc();
    $stmt_slug->close();

    $redirect_target = !empty($berita_row['slug']) ? $berita_row['slug'] : $id_berita;
    header("Location: " . BASE_URL . "/berita/" . $redirect_target . "#komentar");
    exit();
    
} else {
    // Jika diakses langsung, alihkan ke halaman utama
    header("Location: " . BASE_URL);
    exit();
}
?>
