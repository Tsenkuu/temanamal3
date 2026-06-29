<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Tambah Amil Baru";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $jabatan = trim($_POST['jabatan']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $no_telepon = trim($_POST['no_telepon']);
    $status = $_POST['status'];
    $tampilkan_di_beranda = $_POST['tampilkan_di_beranda'];
    $nama_foto = 'default.png'; // Foto default

    // Cek username
    $stmt_check = $mysqli->prepare("SELECT id FROM amil WHERE username = ?");
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $_SESSION['error_message'] = "Username sudah digunakan.";
    } else {
        // Proses upload foto jika ada
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $target_dir = "../assets/uploads/amil/";
            $nama_foto = time() . '_' . basename($_FILES["foto"]["name"]);
            $target_file = $target_dir . $nama_foto;
            if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                $nama_foto = 'default.png'; // Kembali ke default jika upload gagal
            }
        }

        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO amil (nama_lengkap, jabatan, foto, username, password, no_telepon, status, tampilkan_di_beranda) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $nama_lengkap, $jabatan, $nama_foto, $username, $password_hashed, $no_telepon, $status, $tampilkan_di_beranda);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Amil baru berhasil ditambahkan.";
            header("Location: kelola_amil.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Gagal menambahkan amil.";
        }
    }
    $stmt_check->close();
}

require_once 'templates/header_admin.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'templates/sidebar_admin.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h1 class="h2 pt-3 pb-2 mb-3 border-bottom"><?php echo $page_title; ?></h1>
            <?php if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']);
            } ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="tambah_amil.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3"><label for="nama_lengkap" class="form-label">Nama Lengkap</label><input
                                type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required></div>
                        <div class="mb-3"><label for="jabatan" class="form-label">Jabatan</label><input type="text"
                                class="form-control" id="jabatan" name="jabatan" placeholder="Contoh: Amil Lapangan">
                        </div>
                        <div class="mb-3"><label for="foto" class="form-label">Foto Profil</label><input
                                class="form-control" type="file" id="foto" name="foto"></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="username" class="form-label">Username</label><input
                                    type="text" class="form-control" id="username" name="username" required></div>
                            <div class="col-md-6 mb-3"><label for="password" class="form-label">Password</label><input
                                    type="password" class="form-control" id="password" name="password" required></div>
                        </div>
                        <div class="mb-3"><label for="no_telepon" class="form-label">No. Telepon</label><input
                                type="text" class="form-control" id="no_telepon" name="no_telepon"></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="status" class="form-label">Status Akun</label><select
                                    class="form-select" id="status" name="status" required>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Tidak Aktif">Tidak Aktif</option>
                                </select></div>
                            <div class="col-md-6 mb-3"><label for="tampilkan_di_beranda" class="form-label">Tampilkan di
                                    Beranda</label><select class="form-select" id="tampilkan_di_beranda"
                                    name="tampilkan_di_beranda" required>
                                    <option value="Tidak" selected>Tidak</option>
                                    <option value="Ya">Ya</option>
                                </select></div>
                        </div>
                        <hr>
                        <a href="kelola_amil.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Amil</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<?php require_once 'templates/footer_admin.php'; ?>