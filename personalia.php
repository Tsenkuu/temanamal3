<?php
// Menetapkan judul halaman
$page_title = "Susunan Personalia";

// Memuat file konfigurasi dan template header
// Pastikan path ini sesuai dengan struktur folder Anda
require_once 'includes/config.php';
require_once 'includes/templates/header.php';
?>

<!-- Bagian Header Judul -->
<section class="bg-white py-12">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800">Susunan Personalia Lazismu Tulungagung</h1>
        <p class="text-gray-600 mt-2 max-w-2xl mx-auto">Tim kami yang berdedikasi untuk melayani dan memberdayakan umat dengan amanah dan profesional.</p>
    </div>
</section>

<!-- Konten Struktur Organisasi (Desain Akordeon) -->
<section class="bg-gray-50 py-16">
    <div class="container mx-auto max-w-4xl px-4">

        <!-- Wadah untuk semua item akordeon -->
        <div class="space-y-4" id="accordion-container">

            <!-- Item 1: Badan Pengurus -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <h2>
                    <button type="button" class="accordion-toggle flex items-center justify-between w-full p-6 font-semibold text-left text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-400 transition-colors duration-300">
                        <span class="text-xl text-gray-800">Badan Pengurus</span>
                        <!-- Ikon Panah SVG -->
                        <svg class="accordion-arrow w-6 h-6 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                </h2>
                <div class="accordion-content hidden p-6 border-t border-gray-200">
                    <div class="space-y-4 text-gray-600">
                        <div class="grid grid-cols-3 gap-4">
                            <strong class="font-medium text-gray-500 col-span-1">Ketua</strong>
                            <span class="col-span-2">: Aminuddin Aziz, S.P</span>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <strong class="font-medium text-gray-500 col-span-1">Wakil Ketua 1</strong>
                            <span class="col-span-2">: Syahroni, S.H</span>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <strong class="font-medium text-gray-500 col-span-1">Wakil Ketua 2</strong>
                            <span class="col-span-2">: Harjito</span>
                        </div>
                         <div class="grid grid-cols-3 gap-4">
                            <strong class="font-medium text-gray-500 col-span-1">Wakil Ketua 3</strong>
                            <span class="col-span-2">: Agus Amanudin</span>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <strong class="font-medium text-gray-500 col-span-1">Sekretaris</strong>
                            <span class="col-span-2">: Eko Asyhari Hidayat, S.E</span>
                        </div>
                        <div class="pt-2">
                            <p class="font-medium text-gray-500">Anggota:</p>
                            <ul class="list-disc list-inside ml-4 mt-2 space-y-2">
                                <li>Abizar Ramadhani, S.E</li>
                                <li>Fajar Kurniadi, S.Ag</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item 2: Dewan Pengawas Syariah -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <h2>
                    <button type="button" class="accordion-toggle flex items-center justify-between w-full p-6 font-semibold text-left text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-400 transition-colors duration-300">
                        <span class="text-xl text-gray-800">Dewan Pengawas Syariah</span>
                        <svg class="accordion-arrow w-6 h-6 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                </h2>
                <div class="accordion-content hidden p-6 border-t border-gray-200">
                    <div class="space-y-4 text-gray-600">
                        <div class="grid grid-cols-3 gap-4">
                            <strong class="font-medium text-gray-500 col-span-1">Ketua</strong>
                            <span class="col-span-2">: Dr. Aji Damanuri, M.EI</span>
                        </div>
                        <div class="pt-2">
                            <p class="font-medium text-gray-500">Anggota:</p>
                            <ul class="list-disc list-inside ml-4 mt-2 space-y-2">
                                <li>Bastomi, S.Ag</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Item 3: Badan Eksekutif -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <h2>
                    <button type="button" class="accordion-toggle flex items-center justify-between w-full p-6 font-semibold text-left text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-400 transition-colors duration-300">
                        <span class="text-xl text-gray-800">Badan Eksekutif</span>
                        <svg class="accordion-arrow w-6 h-6 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                </h2>
                <div class="accordion-content hidden p-6 border-t border-gray-200">
                     <div class="space-y-4 text-gray-600">
                        <div class="grid grid-cols-3 gap-4">
                            <strong class="font-medium text-gray-500 col-span-1">Manager</strong>
                            <span class="col-span-2">: Hendra Pornama, S.H</span>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <strong class="font-medium text-gray-500 col-span-1">Keuangan</strong>
                            <span class="col-span-2">: Azizah Ratna Shalihah, S.Pd</span>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <strong class="font-medium text-gray-500 col-span-1">Front Office</strong>
                            <span class="col-span-2">: Ratna Wilisia, S.Pd</span>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <strong class="font-medium text-gray-500 col-span-1">Program</strong>
                            <span class="col-span-2">: Aprilia Dwi Rahmawati S.Pd</span>
                        </div>
                        <div class="pt-2">
                            <p class="font-medium text-gray-500">Fundraising:</p>
                            <ul class="list-disc list-inside ml-4 mt-2 space-y-2">
                                <li>Muhammad Bima Athar Rasydan</li>
                                <li>Ahmad Panca Sakti Hidayatullah, S.H</li>
                                <li>Pixel Yoga Pratama</li>
                                <li>Muhammad Khoirun Nizam S.Sos</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- JavaScript untuk Accordion -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const accordionToggles = document.querySelectorAll('.accordion-toggle');

        accordionToggles.forEach(button => {
            button.addEventListener('click', () => {
                const content = button.parentElement.nextElementSibling;
                const arrow = button.querySelector('.accordion-arrow');

                // Toggle konten
                content.classList.toggle('hidden');
                
                // Animasi buka-tutup dan rotasi panah
                if (!content.classList.contains('hidden')) {
                    // Beri tinggi maksimal agar ada efek transisi saat membuka
                    content.style.maxHeight = content.scrollHeight + 'px';
                    arrow.classList.add('rotate-180');
                } else {
                    content.style.maxHeight = null;
                    arrow.classList.remove('rotate-180');
                }
            });
        });

        // Style tambahan untuk transisi yang mulus
        const style = document.createElement('style');
        style.innerHTML = `
            .accordion-content {
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.4s ease-out;
            }
            .accordion-content:not(.hidden) {
                max-height: 1000px; /* Nilai yang cukup besar */
                transition: max-height 0.5s ease-in;
            }
        `;
        document.head.appendChild(style);
    });
</script>

<?php
// Memuat footer
require_once 'includes/templates/footer.php';
?>
