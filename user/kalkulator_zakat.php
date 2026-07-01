<?php
// Menetapkan judul halaman
$page_title = "Kalkulator Zakat";
require_once '../includes/config.php';
require_once 'templates/header_user.php';
$api_url = 'https://logam-mulia-api.vercel.app/prices/hargaemas-com';
?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-text-dark">Kalkulator Zakat</h1>
            <p class="text-text-muted mt-2">Hitung kewajiban zakat Anda dengan mudah dan akurat.</p>
        </div>

        <!-- Calculator Card -->
        <div class="bg-white rounded-2xl shadow-lg">
            <!-- Tabs -->
            <div class="p-2 sm:p-3 border-b border-gray-200">
                <div class="overflow-x-auto">
                    <nav class="flex space-x-2" aria-label="Tabs">
                        <button class="tab-btn active" data-target="penghasilan">💼 <span class="hidden sm:inline ml-2">Penghasilan</span></button>
                        <button class="tab-btn" data-target="tabungan">🏦 <span class="hidden sm:inline ml-2">Tabungan</span></button>
                        <button class="tab-btn" data-target="emas">🥇 <span class="hidden sm:inline ml-2">Emas</span></button>
                        <button class="tab-btn" data-target="perdagangan">🛒 <span class="hidden sm:inline ml-2">Dagang</span></button>
                    </nav>
                </div>
            </div>

            <div class="p-6 md:p-8">
                <!-- Gold Price Info -->
                <div id="harga-emas-info" class="mb-6 p-3 bg-blue-50 rounded-lg flex items-center text-sm">
                    <i class="bi bi-info-circle-fill text-blue-500 mr-2"></i>
                    <span id="harga-emas-text" class="text-blue-700">Memuat harga emas terbaru...</span>
                </div>

                <!-- Form Zakat Penghasilan -->
                <div id="penghasilan" class="tab-content space-y-4">
                    <!-- Form content as in the original file -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hitung Per</label>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center"><input type="radio" name="periode_penghasilan" value="bulan" class="zakat-input-radio" checked> <span class="ml-2">Bulan</span></label>
                            <label class="flex items-center"><input type="radio" name="periode_penghasilan" value="tahun" class="zakat-input-radio"> <span class="ml-2">Tahun</span></label>
                        </div>
                    </div>
                    <div>
                        <label for="pendapatan" class="block text-sm font-medium text-gray-700">Penghasilan (Rp)</label>
                        <div class="relative mt-1"><span class="absolute left-3 top-3 text-gray-500">Rp</span><input type="text" id="pendapatan" class="zakat-input pl-10" placeholder="0"></div>
                    </div>
                    <div>
                        <label for="penghasilan_lain" class="block text-sm font-medium text-gray-700">Penghasilan Lain (Bonus, dll) (Rp)</label>
                        <div class="relative mt-1"><span class="absolute left-3 top-3 text-gray-500">Rp</span><input type="text" id="penghasilan_lain" class="zakat-input pl-10" placeholder="0"></div>
                    </div>
                    <div>
                        <label for="kebutuhan_pokok" class="block text-sm font-medium text-gray-700">Kebutuhan Pokok / Utang (Rp)</label>
                        <div class="relative mt-1"><span class="absolute left-3 top-3 text-gray-500">Rp</span><input type="text" id="kebutuhan_pokok" class="zakat-input pl-10" placeholder="0"></div>
                    </div>
                </div>

                <!-- Form Zakat Tabungan -->
                <div id="tabungan" class="tab-content hidden space-y-4">
                    <p class="text-sm text-gray-500">Untuk simpanan (tabungan, deposito) yang telah mencapai 1 tahun (haul).</p>
                    <div>
                        <label for="saldo_tabungan" class="block text-sm font-medium text-gray-700">Saldo Akhir Tabungan (Rp)</label>
                        <div class="relative mt-1"><span class="absolute left-3 top-3 text-gray-500">Rp</span><input type="text" id="saldo_tabungan" class="zakat-input pl-10" placeholder="0"></div>
                    </div>
                </div>

                <!-- Form Zakat Emas -->
                <div id="emas" class="tab-content hidden space-y-4">
                    <p class="text-sm text-gray-500">Hanya untuk emas yang disimpan (tidak dipakai) dan telah mencapai 1 tahun.</p>
                    <div>
                        <label for="nilai_emas" class="block text-sm font-medium text-gray-700">Emas yang Dimiliki (gram)</label>
                        <input type="number" id="nilai_emas" class="zakat-input mt-1" placeholder="min. 85 gram" min="0" step="0.1">
                        <p class="text-xs text-gray-500 mt-1">Nisab: 85 gram emas murni.</p>
                    </div>
                </div>
                
                <!-- Form Zakat Perdagangan -->
                <div id="perdagangan" class="tab-content hidden space-y-4">
                     <p class="text-sm text-gray-500">Dihitung dari aset lancar usaha setelah dikurangi utang jatuh tempo.</p>
                    <div>
                        <label for="aset_lancar" class="block text-sm font-medium text-gray-700">Aset Lancar (Modal + Keuntungan) (Rp)</label>
                         <div class="relative mt-1"><span class="absolute left-3 top-3 text-gray-500">Rp</span><input type="text" id="aset_lancar" class="zakat-input pl-10" placeholder="0"></div>
                    </div>
                    <div>
                        <label for="utang_dagang" class="block text-sm font-medium text-gray-700">Utang Jatuh Tempo (Rp)</label>
                        <div class="relative mt-1"><span class="absolute left-3 top-3 text-gray-500">Rp</span><input type="text" id="utang_dagang" class="zakat-input pl-10" placeholder="0"></div>
                    </div>
                </div>

                <!-- Calculate Button -->
                <div class="mt-8 border-t pt-6">
                    <button id="hitung-btn" class="w-full text-center bg-primary-orange text-white px-8 py-3 rounded-full font-bold hover:bg-orange-600 transition duration-300 shadow-lg">Hitung Zakat</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Result -->
<div id="hasil-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 max-w-md w-full relative transform transition-all duration-300 scale-95 opacity-0">
        <button id="close-modal-btn" class="absolute top-3 right-4 text-gray-400 hover:text-gray-700 text-3xl">&times;</button>
        <div class="text-center">
            <h3 class="text-2xl font-bold text-text-dark mb-4">Hasil Perhitungan Zakat</h3>
            <div id="hasil-zakat" class="bg-gray-50 p-4 rounded-lg">
                <p id="jumlah-zakat" class="text-3xl font-bold text-primary-orange">Rp 0</p>
                <p id="nisab-info" class="text-sm text-text-muted mt-2"></p>
            </div>
            <a href="#" id="tunaikan-btn" class="hidden mt-6 inline-block bg-primary-orange text-white px-8 py-3 rounded-full font-bold hover:bg-orange-600 transition duration-300 shadow-lg">Tunaikan Zakat Sekarang</a>
        </div>
    </div>
</div>

<style>
.tab-btn {
    padding: 0.5rem 1rem; font-weight: 600; color: var(--text-muted); border-radius: 9999px;
    transition: all 0.2s; white-space: nowrap;
}
.tab-btn.active {
    background-color: var(--primary-orange); color: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
}
.zakat-input {
    @apply w-full p-3 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-orange/50 focus:border-primary-orange transition;
}
.zakat-input-radio {
    @apply h-4 w-4 text-primary-orange focus:ring-primary-orange border-gray-300;
}
#hasil-modal.flex { display: flex; }
#hasil-modal.flex .relative { transform: scale(1); opacity: 1; }
</style>

<!-- JavaScript Logic -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // All original JavaScript logic from kalkulator_zakat.php is preserved here.
    // I'm adding modal animation logic.
    const hitungBtn = document.getElementById('hitung-btn');
    const hasilModal = document.getElementById('hasil-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const modalContent = hasilModal.querySelector('.relative');

    function openModal() {
        hasilModal.classList.remove('hidden');
        hasilModal.classList.add('flex');
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }
    function closeModal() {
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            hasilModal.classList.add('hidden');
            hasilModal.classList.remove('flex');
        }, 300);
    }
    
    // ... (rest of the calculator logic from the original file)
    // This part is omitted for brevity but should be copied from the original file.
    // Start of copied JS
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');
    const hargaEmasInfo = document.getElementById('harga-emas-info');
    const hargaEmasText = document.getElementById('harga-emas-text');
    
    let hargaEmasPerGram = 0;
    
    const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    const getAngka = (id) => parseFloat(document.getElementById(id).value.replace(/[^\d]/g, '')) || 0;
    const formatInput = (input) => {
        if (!input) return;
        let value = input.value.replace(/[^\d]/g, '');
        input.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
    };

    async function getHargaEmas() {
        try {
            const response = await fetch('<?php echo $api_url; ?>');
            if (!response.ok) throw new Error('Network error');
            const data = await response.json();
            hargaEmasPerGram = data.data[0].sell;
            hargaEmasText.innerHTML = `✅ Harga emas terbaru: <strong>${formatRupiah(hargaEmasPerGram)}/gram</strong>. Nisab: ${formatRupiah(85 * hargaEmasPerGram)}`;
        } catch (error) {
            console.error('Fetch error:', error);
            hargaEmasPerGram = 1350000;
            hargaEmasText.innerHTML = `⚠️ Gagal ambil data. Menggunakan harga default: <strong>${formatRupiah(hargaEmasPerGram)}/gram</strong>. Nisab: ${formatRupiah(85 * hargaEmasPerGram)}`;
        }
    }

    function updateHasil(hasil) {
        const jumlahZakatEl = document.getElementById('jumlah-zakat');
        const nisabInfoEl = document.getElementById('nisab-info');
        const tunaikanBtn = document.getElementById('tunaikan-btn');
        const baseUrl = '<?php echo BASE_URL; ?>/program'; // Arahkan ke daftar program

        if (hasil.wajibZakat) {
            jumlahZakatEl.textContent = formatRupiah(hasil.zakat);
            nisabInfoEl.textContent = hasil.nisabInfoText;
            tunaikanBtn.href = `${baseUrl}?nominal=${Math.round(hasil.zakat)}&kategori=zakat`;
            tunaikanBtn.classList.remove('hidden');
        } else {
            jumlahZakatEl.innerHTML = '<span class="text-xl text-red-500">Belum Wajib Zakat</span>';
            nisabInfoEl.textContent = `Anda dapat tetap berinfak. ${hasil.nisabInfoText}`;
            tunaikanBtn.classList.add('hidden');
        }
    }

    async function hitungZakat() {
        if (hargaEmasPerGram === 0) await getHargaEmas();
        
        const nisabEmas = 85 * hargaEmasPerGram;
        const activeTab = document.querySelector('.tab-btn.active').dataset.target;
        let zakat = 0, wajibZakat = false, nisabInfoText = '';

        if (activeTab === 'penghasilan') {
            const periode = document.querySelector('input[name="periode_penghasilan"]:checked').value;
            const pendapatan = getAngka('pendapatan') + getAngka('penghasilan_lain');
            const kebutuhan = getAngka('kebutuhan_pokok');
            const nisab = periode === 'bulan' ? nisabEmas / 12 : nisabEmas;
            const pendapatanBersih = pendapatan - kebutuhan;

            if (pendapatanBersih >= nisab) {
                zakat = pendapatanBersih * 0.025;
                wajibZakat = true;
                nisabInfoText = `Nisab per ${periode}: ${formatRupiah(nisab)}.`;
            } else {
                nisabInfoText = `Penghasilan bersih (${formatRupiah(pendapatanBersih)}) belum mencapai nisab (${formatRupiah(nisab)}).`;
            }
        } else if (['tabungan', 'perdagangan'].includes(activeTab)) {
            let totalHarta = 0;
            if (activeTab === 'tabungan') totalHarta = getAngka('saldo_tabungan');
            if (activeTab === 'perdagangan') totalHarta = getAngka('aset_lancar') - getAngka('utang_dagang');

            if (totalHarta >= nisabEmas) {
                zakat = totalHarta * 0.025;
                wajibZakat = true;
                nisabInfoText = `Nisab ${activeTab}: ${formatRupiah(nisabEmas)}.`;
            } else {
                nisabInfoText = `Harta Anda (${formatRupiah(totalHarta)}) belum mencapai nisab (${formatRupiah(nisabEmas)}).`;
            }
        } else if (activeTab === 'emas') {
            const gramEmas = parseFloat(document.getElementById('nilai_emas').value) || 0;
            if (gramEmas >= 85) {
                zakat = (gramEmas * hargaEmasPerGram) * 0.025;
                wajibZakat = true;
                nisabInfoText = `Nisab emas: 85 gram.`;
            } else {
                nisabInfoText = `Emas Anda (${gramEmas} gram) belum mencapai nisab (85 gram).`;
            }
        }
        
        updateHasil({ zakat, wajibZakat, nisabInfoText });
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.add('hidden'));
            this.classList.add('active');
            document.getElementById(this.dataset.target).classList.remove('hidden');
        });
    });

    document.querySelectorAll('.zakat-input').forEach(input => {
        input.addEventListener('input', () => formatInput(input));
    });

    hitungBtn.addEventListener('click', async () => {
        await hitungZakat();
        openModal();
    });
    closeModalBtn.addEventListener('click', closeModal);
    hasilModal.addEventListener('click', (e) => {
        if (e.target === hasilModal) closeModal();
    });

    getHargaEmas();
    // End of copied JS
});
</script>

<?php
require_once 'templates/footer_user.php';
?>
