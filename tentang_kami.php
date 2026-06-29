<?php
// Menetapkan judul halaman
$page_title = "Tentang Kami";

// Memuat file konfigurasi dan template header baru
require_once 'includes/config.php';
require_once 'includes/templates/header.php';
?>

<!-- Hero Section -->
<section class="bg-white py-12">
    <div class="container mx-auto px-6 text-center scroll-animate">
        <h1 class="text-4xl font-bold text-dark-text">Tentang Lazismu Tulungagung</h1>
        <p class="text-gray-600 mt-2 max-w-3xl mx-auto">Mengenal lebih dekat lembaga amil zakat, infak, dan sedekah
            Muhammadiyah yang berkhidmat untuk memberdayakan umat.</p>
    </div>
</section>

<!-- Konten Utama -->
<section class="py-16 px-4 md:px-12 bg-light-bg">
    <div class="container mx-auto max-w-6xl">
        <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12 scroll-animate">

            <!-- Bagian Pengenalan -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <div>
                    <h2 class="text-3xl font-bold text-primary-orange mb-4">Memberi untuk Negeri</h2>
                    <p class="text-gray-700 leading-relaxed">
                        LAZISMU adalah lembaga zakat tingkat nasional yang berkhidmat dalam pemberdayaan masyarakat
                        melalui pendayagunaan dana zakat, infak, wakaf dan dana kedermawanan lainnya baik dari
                        perseorangan, lembaga, perusahaan dan instansi lainnya.
                    </p>
                    <p class="text-gray-700 leading-relaxed mt-4">
                        Hadir di Tulungagung, Lazismu berkomitmen untuk menjadi lembaga yang amanah, profesional, dan
                        transparan dalam mengelola dana umat demi terwujudnya masyarakat yang lebih adil dan sejahtera.
                    </p>
                </div>
                <div>
                    <img src="https://placehold.co/600x400/FFBE79/333333?text=Tim+Lazismu" alt="Tim Lazismu Tulungagung"
                        class="rounded-xl shadow-md w-full h-full object-cover">
                </div>
            </div>

            <!-- Bagian Visi & Misi -->
            <div class="mt-16 border-t pt-12">
                <h3 class="text-2xl font-bold text-dark-text mb-6 text-center">Visi & Misi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Visi -->
                    <div class="bg-orange-50 p-6 rounded-lg">
                        <h4 class="text-xl font-semibold text-primary-orange mb-2">Visi</h4>
                        <p class="text-gray-600">Menjadi Lembaga Amil Zakat Terpercaya.</p>
                    </div>
                    <!-- Misi -->
                    <div class="bg-orange-50 p-6 rounded-lg">
                        <h4 class="text-xl font-semibold text-primary-orange mb-2">Misi</h4>
                        <ul class="list-disc list-inside text-gray-600 space-y-1">
                            <li>Optimalisasi kualitas pengelolaan ZIS yang amanah, profesional dan transparan.</li>
                            <li>Optimalisasi pendayagunaan ZIS yang kreatif, inovatif, dan produktif.</li>
                            <li>Optimalisasi pelayanan donatur.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Bagian Sejarah Singkat -->
            <div class="mt-16 border-t pt-12">
                <h3 class="text-2xl font-bold text-dark-text mb-6 text-center">Sejarah Singkat</h3>
                <p class="text-gray-700 leading-relaxed max-w-3xl mx-auto text-center">
                    Lazismu didirikan oleh Pimpinan Pusat Muhammadiyah pada tahun 2002, dan selanjutnya dikukuhkan oleh
                    Menteri Agama Republik Indonesia sebagai Lembaga Amil Zakat Skala Nasional. Kehadiran Lazismu di
                    Tulungagung merupakan bagian dari upaya untuk memperluas jangkauan kebaikan dan memberdayakan
                    masyarakat secara lebih dekat dan efektif.
                </p>
            </div>

        </div>
    </div>
</section>

<?php
// Memuat footer baru
require_once 'includes/templates/footer.php';
?>