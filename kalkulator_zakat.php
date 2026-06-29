<?php
// Menetapkan judul halaman
$page_title = "Kalkulator Zakat Online - Hitung Zakat Maal & Penghasilan";
$meta_description = "Gunakan Kalkulator Zakat Lazismu untuk menghitung zakat penghasilan, tabungan, emas, dan perdagangan secara akurat sesuai nishab dan haul.";
$meta_keywords = "kalkulator zakat, hitung zakat online, zakat penghasilan, zakat emas, nishab zakat";

// Memuat file konfigurasi dan template header baru
require_once 'includes/config.php';
require_once 'includes/templates/header.php';

// URL API kalkulator
$base_url = defined('BASE_URL') ? BASE_URL : '';
$api_url = $base_url . '/api/harga-emas.php';
?>

<style>
    /* Custom CSS untuk Kalkulator */
    .calc-container {
        background: #ffffff;
        border-radius: 1.5rem;
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    /* Tab Navigation */
    .nav-tabs-scroll {
        display: flex;
        overflow-x: auto;
        gap: 0.5rem;
        padding: 0.5rem;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        scrollbar-width: none; /* Firefox */
    }
    .nav-tabs-scroll::-webkit-scrollbar {
        display: none; /* Chrome/Safari */
    }
    
    .tab-btn {
        white-space: nowrap;
        padding: 0.75rem 1.25rem;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 0.9rem;
        color: #6c757d;
        transition: all 0.3s ease;
        border: 1px solid transparent;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .tab-btn:hover {
        background: #e9ecef;
        color: #495057;
    }
    
    .tab-btn.active {
        background: #fff;
        color: #fb8201; /* Primary Orange */
        border-color: #fb8201;
        box-shadow: 0 4px 6px -1px rgba(251, 130, 1, 0.1);
    }

    /* Input Styling */
    .form-group {
        margin-bottom: 1.25rem;
        position: relative;
    }
    
    .form-label {
        display: block;
        font-size: 0.9rem;
        font-weight: 600;
        color: #343a40;
        margin-bottom: 0.5rem;
    }
    
    .input-wrapper {
        position: relative;
    }
    
    .currency-symbol {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #adb5bd;
        font-weight: 500;
    }
    
    .form-input {
        width: 100%;
        padding: 0.875rem 1rem 0.875rem 2.5rem;
        border: 1px solid #ced4da;
        border-radius: 0.75rem;
        font-size: 1rem;
        transition: all 0.2s;
        background: #f8f9fa;
    }
    
    .form-input:focus {
        background: #fff;
        border-color: #fb8201;
        box-shadow: 0 0 0 3px rgba(251, 130, 1, 0.15);
        outline: none;
    }

    .form-input.error {
        border-color: #dc3545;
        background-color: #fff8f8;
    }

    .error-msg {
        color: #dc3545;
        font-size: 0.8rem;
        margin-top: 0.25rem;
        display: none;
    }

    /* Tooltip */
    .info-icon {
        color: #adb5bd;
        cursor: help;
        transition: color 0.2s;
    }
    .info-icon:hover {
        color: #fb8201;
    }

    /* Result Modal Animation */
    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    .result-card {
        animation: slideUp 0.4s ease-out forwards;
    }

    /* Loading State */
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
        border-radius: 4px;
        height: 20px;
        width: 100%;
    }
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>

<!-- Header Section -->
<section class="bg-white py-12 border-b border-gray-100">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Kalkulator Zakat</h1>
        <p class="text-gray-600 max-w-2xl mx-auto text-lg">
            Hitung kewajiban zakat Anda dengan mudah, akurat, dan sesuai syariat. 
            Bersihkan harta, sucikan jiwa.
        </p>
    </div>
</section>

<!-- Calculator Section -->
<section class="py-12 px-4 bg-gray-50 min-h-[600px]">
    <div class="container mx-auto max-w-4xl">
        
        <!-- Main Calculator Card -->
        <div class="calc-container">
            
            <!-- Tabs -->
            <div class="nav-tabs-scroll" role="tablist">
                <button class="tab-btn active" data-target="penghasilan" role="tab">
                    <i class="bi bi-briefcase"></i> Penghasilan
                </button>
                <button class="tab-btn" data-target="tabungan" role="tab">
                    <i class="bi bi-piggy-bank"></i> Tabungan
                </button>
                <button class="tab-btn" data-target="emas" role="tab">
                    <i class="bi bi-gem"></i> Emas
                </button>
                <button class="tab-btn" data-target="perdagangan" role="tab">
                    <i class="bi bi-shop"></i> Perdagangan
                </button>
                <button class="tab-btn" data-target="pertanian" role="tab">
                    <i class="bi bi-flower1"></i> Pertanian
                </button>
            </div>

            <div class="p-6 md:p-8">
                <!-- Gold Price Status -->
                <div id="gold-price-status" class="mb-8 p-4 bg-blue-50 rounded-xl border border-blue-100 flex items-start gap-3">
                    <div class="mt-1 text-blue-600">
                        <i class="bi bi-graph-up-arrow text-xl"></i>
                    </div>
                    <div class="flex-grow">
                        <h4 class="font-semibold text-blue-900 text-sm uppercase tracking-wide mb-1">Harga Emas Saat Ini</h4>
                        <div id="gold-price-display" class="flex items-center gap-2">
                            <div class="skeleton w-32 h-6"></div> <!-- Loading Skeleton -->
                        </div>
                        <p class="text-xs text-blue-600 mt-1">*Data diperbarui secara realtime dari pasar logam mulia.</p>
                    </div>
                </div>

                <!-- Forms Container -->
                <form id="zakat-form" onsubmit="return false;">
                    
                    <!-- 1. Zakat Penghasilan -->
                    <div id="penghasilan" class="tab-content">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Zakat Penghasilan</h3>
                            <p class="text-gray-500 text-sm">Dikeluarkan dari penghasilan bersih (gaji, honor, jasa) yang diperoleh.</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Periode Penghasilan</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer p-3 border rounded-lg hover:bg-gray-50 transition w-full">
                                    <input type="radio" name="periode" value="bulan" checked class="text-orange-500 focus:ring-orange-500">
                                    <span class="font-medium">Bulanan</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer p-3 border rounded-lg hover:bg-gray-50 transition w-full">
                                    <input type="radio" name="periode" value="tahun" class="text-orange-500 focus:ring-orange-500">
                                    <span class="font-medium">Tahunan</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="flex justify-between">
                                <label class="form-label" for="gaji">Total Penghasilan</label>
                                <i class="bi bi-info-circle info-icon" title="Gaji pokok + tunjangan + bonus"></i>
                            </div>
                            <div class="input-wrapper">
                                <span class="currency-symbol">Rp</span>
                                <input type="text" inputmode="numeric" id="gaji" class="form-input currency-input" placeholder="0">
                            </div>
                            <div class="error-msg">Mohon isi penghasilan Anda.</div>
                        </div>

                        <div class="form-group">
                            <div class="flex justify-between">
                                <label class="form-label" for="pendapatan_lain">Penghasilan Lain (Opsional)</label>
                            </div>
                            <div class="input-wrapper">
                                <span class="currency-symbol">Rp</span>
                                <input type="text" inputmode="numeric" id="pendapatan_lain" class="form-input currency-input" placeholder="0">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="flex justify-between">
                                <label class="form-label" for="hutang">Hutang / Cicilan Jatuh Tempo</label>
                                <i class="bi bi-info-circle info-icon" title="Pengurang penghasilan (kebutuhan pokok/cicilan)"></i>
                            </div>
                            <div class="input-wrapper">
                                <span class="currency-symbol">Rp</span>
                                <input type="text" inputmode="numeric" id="hutang" class="form-input currency-input" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <!-- 2. Zakat Tabungan -->
                    <div id="tabungan" class="tab-content hidden">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Zakat Tabungan</h3>
                            <p class="text-gray-500 text-sm">Untuk uang simpanan, deposito, atau surat berharga yang telah mengendap 1 tahun (haul).</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="saldo">Total Saldo Tabungan</label>
                            <div class="input-wrapper">
                                <span class="currency-symbol">Rp</span>
                                <input type="text" inputmode="numeric" id="saldo" class="form-input currency-input" placeholder="0">
                            </div>
                            <div class="error-msg">Mohon isi saldo tabungan.</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="bunga">Bunga / Bagi Hasil (Jika ada)</label>
                            <div class="input-wrapper">
                                <span class="currency-symbol">Rp</span>
                                <input type="text" inputmode="numeric" id="bunga" class="form-input currency-input" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <!-- 3. Zakat Emas -->
                    <div id="emas" class="tab-content hidden">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Zakat Emas & Perak</h3>
                            <p class="text-gray-500 text-sm">Emas simpanan (bukan perhiasan yang dipakai sehari-hari) yang mencapai nisab 85 gram.</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="berat_emas">Berat Emas (Gram)</label>
                            <div class="input-wrapper">
                                <span class="currency-symbol" style="left: auto; right: 1rem;">Gram</span>
                                <input type="number" inputmode="decimal" id="berat_emas" class="form-input" placeholder="0" style="padding-left: 1rem; padding-right: 3.5rem;">
                            </div>
                            <div class="error-msg">Mohon isi berat emas.</div>
                        </div>
                    </div>

                    <!-- 4. Zakat Perdagangan -->
                    <div id="perdagangan" class="tab-content hidden">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Zakat Perdagangan</h3>
                            <p class="text-gray-500 text-sm">Dihitung dari aset lancar dikurangi hutang jangka pendek.</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="aset_lancar">Nilai Aset Lancar (Uang Kas + Stok Barang)</label>
                            <div class="input-wrapper">
                                <span class="currency-symbol">Rp</span>
                                <input type="text" inputmode="numeric" id="aset_lancar" class="form-input currency-input" placeholder="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="hutang_dagang">Hutang Jatuh Tempo</label>
                            <div class="input-wrapper">
                                <span class="currency-symbol">Rp</span>
                                <input type="text" inputmode="numeric" id="hutang_dagang" class="form-input currency-input" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <!-- 5. Zakat Pertanian -->
                    <div id="pertanian" class="tab-content hidden">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Zakat Pertanian</h3>
                            <p class="text-gray-500 text-sm">Dikeluarkan setiap kali panen. Nisab setara 653kg gabah atau 524kg beras.</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="hasil_panen">Nilai Hasil Panen</label>
                            <div class="input-wrapper">
                                <span class="currency-symbol">Rp</span>
                                <input type="text" inputmode="numeric" id="hasil_panen" class="form-input currency-input" placeholder="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sistem Pengairan</label>
                            <select id="irigasi" class="form-input" style="padding-left: 1rem;">
                                <option value="5">Berbayar / Irigasi (Zakat 5%)</option>
                                <option value="10">Tadah Hujan / Alami (Zakat 10%)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="mt-8">
                        <button type="button" id="btn-hitung" class="w-full bg-primary-orange text-white font-bold py-4 rounded-xl shadow-lg hover:bg-orange-600 transition transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2">
                            <span>Hitung Zakat Sekarang</span>
                            <i class="bi bi-calculator"></i>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</section>

<!-- Result Modal -->
<div id="result-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity backdrop-blur-sm" id="modal-backdrop"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg result-card">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white" id="modal-title">Hasil Perhitungan</h3>
                    <button type="button" id="close-modal" class="text-white hover:text-gray-200 focus:outline-none">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-6">
                    
                    <!-- Status Badge -->
                    <div class="text-center mb-6">
                        <span id="status-badge" class="inline-flex items-center rounded-full px-4 py-1 text-sm font-medium ring-1 ring-inset">
                            Status
                        </span>
                    </div>

                    <!-- Summary Grid -->
                    <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-gray-500 mb-1">Jenis Zakat</p>
                            <p class="font-bold text-gray-800" id="res-jenis">-</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-gray-500 mb-1">Nisab (Batas Minimal)</p>
                            <p class="font-bold text-gray-800" id="res-nisab">-</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg col-span-2">
                            <p class="text-gray-500 mb-1">Total Harta Bersih</p>
                            <p class="font-bold text-gray-800 text-lg" id="res-harta">-</p>
                        </div>
                    </div>

                    <!-- Main Result -->
                    <div class="text-center border-t border-dashed border-gray-200 pt-6">
                        <p class="text-gray-600 mb-2">Jumlah Zakat yang Harus Dikeluarkan:</p>
                        <h2 class="text-4xl font-extrabold text-primary-orange mb-2" id="res-total">Rp 0</h2>
                        <p class="text-xs text-gray-400" id="res-note">*Perhitungan berdasarkan 2.5% dari harta wajib zakat</p>
                    </div>

                    <!-- CTA -->
                    <div class="mt-8 space-y-3">
                        <a href="#" id="btn-bayar" class="block w-full rounded-xl bg-primary-green px-3 py-3.5 text-center text-sm font-bold text-white shadow-sm hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 transition">
                            <i class="bi bi-heart-fill mr-2"></i> Tunaikan Zakat Sekarang
                        </a>
                        <button type="button" id="btn-recalc" class="block w-full rounded-xl bg-white px-3 py-3.5 text-center text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition">
                            Hitung Ulang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- CONFIGURATION ---
    const CONFIG = {
        goldApiUrl: '<?php echo $api_url; ?>',
        defaultGoldPrice: 1350000, // Fallback price
        ricePrice: 15000, // Harga beras per kg
        nisabGoldGram: 85,
        nisabRiceKg: 524
    };

    // --- STATE MANAGEMENT ---
    const state = {
        goldPrice: 0,
        activeTab: 'penghasilan',
        isLoading: true
    };

    // --- DOM ELEMENTS ---
    const els = {
        tabs: document.querySelectorAll('.tab-btn'),
        contents: document.querySelectorAll('.tab-content'),
        goldDisplay: document.getElementById('gold-price-display'),
        inputs: document.querySelectorAll('.currency-input'),
        btnHitung: document.getElementById('btn-hitung'),
        modal: document.getElementById('result-modal'),
        modalBackdrop: document.getElementById('modal-backdrop'),
        closeModal: document.getElementById('close-modal'),
        btnRecalc: document.getElementById('btn-recalc'),
        btnBayar: document.getElementById('btn-bayar')
    };

    // --- UTILS ---
    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(num);
    };

    const parseRupiah = (str) => {
        if (!str) return 0;
        return parseFloat(str.replace(/[^0-9]/g, '')) || 0;
    };

    // --- INITIALIZATION ---
    const init = async () => {
        // 1. Fetch Gold Price
        try {
            const response = await fetch(CONFIG.goldApiUrl);
            const data = await response.json();
            
            if (data && data.price_idr_gram) {
                state.goldPrice = data.price_idr_gram;
            } else {
                throw new Error("Invalid API Data");
            }
        } catch (e) {
            console.warn("Failed to fetch gold price, using fallback.", e);
            state.goldPrice = CONFIG.defaultGoldPrice;
        } finally {
            state.isLoading = false;
            updateGoldUI();
        }

        // 2. Setup Event Listeners
        setupTabs();
        setupInputs();
        setupModal();
        
        els.btnHitung.addEventListener('click', calculateZakat);
    };

    const updateGoldUI = () => {
        els.goldDisplay.innerHTML = `
            <span class="text-xl font-bold text-gray-900">${formatRupiah(state.goldPrice)}</span>
            <span class="text-sm text-gray-500">/ gram</span>
        `;
    };

    // --- TABS LOGIC ---
    const setupTabs = () => {
        els.tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Deactivate all
                els.tabs.forEach(t => t.classList.remove('active'));
                els.contents.forEach(c => c.classList.add('hidden'));
                
                // Activate clicked
                tab.classList.add('active');
                const targetId = tab.dataset.target;
                document.getElementById(targetId).classList.remove('hidden');
                
                state.activeTab = targetId;
                
                // Reset inputs in new tab
                document.querySelectorAll(`#${targetId} input`).forEach(i => i.value = '');
            });
        });
    };

    // --- INPUT HANDLING ---
    const setupInputs = () => {
        els.inputs.forEach(input => {
            input.addEventListener('input', (e) => {
                // Remove non-numeric
                let val = e.target.value.replace(/[^0-9]/g, '');
                
                // Format display
                if (val) {
                    e.target.value = new Intl.NumberFormat('id-ID').format(val);
                } else {
                    e.target.value = '';
                }
                
                // Validation visual
                if (val < 0) {
                    e.target.classList.add('error');
                } else {
                    e.target.classList.remove('error');
                }
            });
        });
    };

    // --- CALCULATION LOGIC ---
    const calculateZakat = () => {
        if (state.isLoading) return;

        let totalHarta = 0;
        let zakatAmount = 0;
        let nisab = 0;
        let isWajib = false;
        let typeLabel = '';

        // Helper to get value by ID
        const getVal = (id) => parseRupiah(document.getElementById(id)?.value);

        switch (state.activeTab) {
            case 'penghasilan':
                typeLabel = 'Zakat Penghasilan';
                const periode = document.querySelector('input[name="periode"]:checked').value;
                const gaji = getVal('gaji');
                const lain = getVal('pendapatan_lain');
                const hutang = getVal('hutang');
                
                totalHarta = gaji + lain - hutang;
                
                // Nisab Emas 85gr
                const nisabTahun = state.goldPrice * CONFIG.nisabGoldGram;
                nisab = (periode === 'bulan') ? nisabTahun / 12 : nisabTahun;
                
                if (totalHarta >= nisab) {
                    zakatAmount = totalHarta * 0.025;
                    isWajib = true;
                }
                break;

            case 'tabungan':
                typeLabel = 'Zakat Tabungan';
                const saldo = getVal('saldo');
                const bunga = getVal('bunga'); // Bunga bank konvensional biasanya tidak dihitung zakat (pendapat ulama), tapi kita masukkan sebagai total aset dulu
                
                totalHarta = saldo; // Asumsi bunga tidak dizakati atau sudah bersih
                nisab = state.goldPrice * CONFIG.nisabGoldGram;
                
                if (totalHarta >= nisab) {
                    zakatAmount = totalHarta * 0.025;
                    isWajib = true;
                }
                break;

            case 'emas':
                typeLabel = 'Zakat Emas';
                const berat = parseFloat(document.getElementById('berat_emas').value) || 0;
                
                totalHarta = berat * state.goldPrice; // Konversi ke Rupiah untuk display
                nisab = state.goldPrice * CONFIG.nisabGoldGram; // Nilai Rupiah dari 85gr
                
                // Cek nisab berdasarkan berat (85gr)
                if (berat >= CONFIG.nisabGoldGram) {
                    zakatAmount = totalHarta * 0.025;
                    isWajib = true;
                }
                break;

            case 'perdagangan':
                typeLabel = 'Zakat Perdagangan';
                const aset = getVal('aset_lancar');
                const hutangDagang = getVal('hutang_dagang');
                
                totalHarta = aset - hutangDagang;
                nisab = state.goldPrice * CONFIG.nisabGoldGram;
                
                if (totalHarta >= nisab) {
                    zakatAmount = totalHarta * 0.025;
                    isWajib = true;
                }
                break;

            case 'pertanian':
                typeLabel = 'Zakat Pertanian';
                const hasil = getVal('hasil_panen');
                const rate = parseFloat(document.getElementById('irigasi').value) / 100;
                
                totalHarta = hasil;
                nisab = CONFIG.ricePrice * CONFIG.nisabRiceKg; // 524kg beras * harga beras
                
                if (totalHarta >= nisab) {
                    zakatAmount = totalHarta * rate;
                    isWajib = true;
                }
                break;
        }

        showResult(typeLabel, totalHarta, nisab, zakatAmount, isWajib);
    };

    // --- MODAL LOGIC ---
    const showResult = (type, harta, nisab, zakat, isWajib) => {
        // Populate Data
        document.getElementById('res-jenis').textContent = type;
        document.getElementById('res-harta').textContent = formatRupiah(harta);
        document.getElementById('res-nisab').textContent = formatRupiah(nisab);
        document.getElementById('res-total').textContent = formatRupiah(zakat);
        
        const badge = document.getElementById('status-badge');
        const note = document.getElementById('res-note');
        const btnBayar = document.getElementById('btn-bayar');

        if (isWajib) {
            badge.textContent = "WAJIB ZAKAT";
            badge.className = "inline-flex items-center rounded-full px-4 py-1 text-sm font-bold bg-green-100 text-green-700 ring-1 ring-inset ring-green-600/20";
            note.textContent = "*Harta Anda telah mencapai nisab (batas minimal wajib zakat).";
            
            // Update link bayar
            // Asumsi ID Program Zakat di DB adalah 21 (sesuai kode lama) atau generic
            btnBayar.href = `<?php echo $base_url; ?>/program/21?nominal=${Math.floor(zakat)}`;
            btnBayar.classList.remove('hidden');
            btnBayar.innerHTML = `<i class="bi bi-heart-fill mr-2"></i> Tunaikan Rp ${new Intl.NumberFormat('id-ID').format(Math.floor(zakat))}`;
        } else {
            badge.textContent = "TIDAK WAJIB";
            badge.className = "inline-flex items-center rounded-full px-4 py-1 text-sm font-bold bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-500/10";
            note.textContent = "*Harta belum mencapai nisab. Namun, sangat dianjurkan untuk berinfak/sedekah.";
            
            // Ubah tombol jadi Infak
            btnBayar.href = `<?php echo $base_url; ?>/donasi?nominal=50000`; // Default infak
            btnBayar.innerHTML = `<i class="bi bi-gift-fill mr-2"></i> Tetap Berinfak (Sedekah)`;
        }

        // Show Modal
        els.modal.classList.remove('hidden');
    };

    const setupModal = () => {
        const close = () => els.modal.classList.add('hidden');
        
        els.closeModal.addEventListener('click', close);
        els.btnRecalc.addEventListener('click', close);
        els.modalBackdrop.addEventListener('click', close);
    };

    // Run
    init();
});
</script>

<?php
require_once 'includes/templates/footer.php';
?>


