<?php
// Memuat file konfigurasi, yang seharusnya sudah memanggil session_start()
require_once '../includes/config.php';

// Pengecekan login amil (sangat direkomendasikan)
if (!isset($_SESSION['amil_id'])) {
    header('Location: ../login.php'); // Arahkan ke halaman login utama jika belum login
    exit();
}

// Ambil ID amil yang sedang login dari sesi
$id_amil_login = $_SESSION['amil_id'];
$nama_amil_login = $_SESSION['amil_nama_lengkap'] ?? 'Amil';

$page_title = "Tugas Saya";

// --- Query untuk mengambil data tugas yang ditugaskan untuk amil yang sedang login ---
$sql = "SELECT 
            t.id AS id_tugas,
            t.tanggal_tugas,
            k.kode_kotak,
            k.nama_lokasi,
            k.alamat,
            k.pic_nama,
            k.pic_kontak,
            k.link_gmaps
        FROM 
            tugas_pengambilan t
        JOIN 
            kotak_infak k ON t.id_kotak_infak = k.id
        WHERE 
            t.id_amil = ? AND t.status = 'Ditugaskan'
        ORDER BY 
            t.tanggal_tugas ASC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id_amil_login);
$stmt->execute();
$result_tugas = $stmt->get_result(); // Menggunakan get_result() karena lebih mudah dibaca

// Memuat header amil
require_once 'templates/header_amil.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php 
        // Memuat sidebar khusus untuk amil
        require_once 'templates/sidebar_amil.php'; 
        ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
            </div>

            <?php
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['success_message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['error_message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            }
            ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal Tugas</th>
                                    <th>Kode Kotak</th>
                                    <th>Lokasi</th>
                                    <th>PIC Lokasi</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_tugas && $result_tugas->num_rows > 0): ?>
                                <?php while($tugas = $result_tugas->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($tugas['tanggal_tugas'])); ?></td>
                                    <td><span
                                            class="badge bg-secondary"><?php echo htmlspecialchars($tugas['kode_kotak']); ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($tugas['nama_lokasi']); ?></strong><br>
                                        <small
                                            class="text-muted"><?php echo htmlspecialchars($tugas['alamat']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($tugas['pic_nama']); ?>
                                        (<?php echo htmlspecialchars($tugas['pic_kontak']); ?>)</td>
                                    <td class="text-center">
                                        <a href="<?php echo htmlspecialchars($tugas['link_gmaps']); ?>" target="_blank"
                                            class="btn btn-sm btn-info me-2" title="Lihat Peta">
                                            <i class="bi bi-geo-alt-fill"></i> Peta
                                        </a>
                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                            data-bs-target="#selesaikanModal"
                                            data-id-tugas="<?php echo $tugas['id_tugas']; ?>"
                                            data-lokasi="<?php echo htmlspecialchars($tugas['nama_lokasi']); ?>">
                                            <i class="bi bi-check-circle me-1"></i> Selesaikan
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Tidak ada tugas aktif yang perlu
                                        dikerjakan.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal untuk Form Penyelesaian Tugas -->
<div class="modal fade" id="selesaikanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../admin/proses_tugas.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Form Penyelesaian Tugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Anda akan menyelesaikan tugas untuk lokasi: <strong id="modal-lokasi-text"></strong></p>
                    <input type="hidden" name="id_tugas" id="modal_id_tugas">
                    <div class="mb-3">
                        <label for="jumlah_terkumpul" class="form-label">Jumlah Terkumpul (Rp)</label>
                        <input type="number" class="form-control" id="jumlah_terkumpul" name="jumlah_terkumpul"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="catatan" class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="selesaikan_tugas" class="btn btn-primary">Simpan & Selesaikan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selesaikanModal = document.getElementById('selesaikanModal');
    if (selesaikanModal) {
        selesaikanModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const idTugas = button.getAttribute('data-id-tugas');
            const namaLokasi = button.getAttribute('data-lokasi');
            const modalLokasiText = selesaikanModal.querySelector('#modal-lokasi-text');
            const modalInputIdTugas = selesaikanModal.querySelector('#modal_id_tugas');
            modalLokasiText.textContent = namaLokasi;
            modalInputIdTugas.value = idTugas;
        });
    }
});
</script>

<?php 
require_once 'templates/footer_amil.php'; 
?>