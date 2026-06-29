<?php
// File: kalkulator/penghasilan.php
?>
<h3 class="text-xl font-semibold mb-1">Zakat Penghasilan</h3>
<p class="text-gray-500 mb-4">Dihitung dari penghasilan bersih setelah dikurangi kebutuhan pokok.</p>
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Hitung Per</label>
        <div class="flex items-center space-x-4">
            <label class="flex items-center"><input type="radio" name="periode_penghasilan" value="bulan"
                    class="zakat-input-radio" checked> <span class="ml-2">Bulan</span></label>
            <label class="flex items-center"><input type="radio" name="periode_penghasilan" value="tahun"
                    class="zakat-input-radio"> <span class="ml-2">Tahun</span></label>
        </div>
    </div>
    <div><label for="pendapatan" class="block text-sm font-medium text-gray-700">Penghasilan (Rp)</label><input
            type="text" id="pendapatan" class="zakat-input mt-1 w-full p-3 border border-gray-300 rounded-lg"
            placeholder="Rp 0"></div>
    <div><label for="penghasilan_lain" class="block text-sm font-medium text-gray-700">Penghasilan Lain (Bonus,
            Tunjangan) (Rp)</label><input type="text" id="penghasilan_lain"
            class="zakat-input mt-1 w-full p-3 border border-gray-300 rounded-lg" placeholder="Rp 0"></div>
    <div><label for="kebutuhan_pokok" class="block text-sm font-medium text-gray-700">Kebutuhan Pokok (Termasuk Hutang
            Jatuh Tempo) (Rp)</label><input type="text" id="kebutuhan_pokok"
            class="zakat-input mt-1 w-full p-3 border border-gray-300 rounded-lg" placeholder="Rp 0"></div>
</div>
<script>
function hitungZakat_penghasilan() {
    const periode = document.querySelector('input[name="periode_penghasilan"]:checked').value;
    const pendapatan = getAngka('pendapatan');
    const penghasilanLain = getAngka('penghasilan_lain');
    const kebutuhanPokok = getAngka('kebutuhan_pokok');
    const totalPendapatan = pendapatan + penghasilanLain;
    let zakat = 0,
        wajibZakat = false,
        nisabInfoText = '';

    if (periode === 'bulan') {
        const pendapatanBersih = totalPendapatan - kebutuhanPokok;
        const nisabBulanan = nisabEmas / 12;
        if (pendapatanBersih >= nisabBulanan) {
            zakat = pendapatanBersih * 0.025;
            wajibZakat = true;
        }
        nisabInfoText = `Nisab per bulan: ${formatRupiah(nisabBulanan)}.`;
    } else {
        const pendapatanTahunan = (totalPendapatan * 12) - (kebutuhanPokok * 12);
        if (pendapatanTahunan >= nisabEmas) {
            zakat = pendapatanTahunan * 0.025;
            wajibZakat = true;
        }
        nisabInfoText = `Nisab per tahun: ${formatRupiah(nisabEmas)}.`;
    }
    return {
        zakat,
        wajibZakat,
        nisabInfoText
    };
}
document.querySelectorAll('#penghasilan .zakat-input, #penghasilan .zakat-input-radio').forEach(input => {
    input.addEventListener('keyup', () => {
        formatInput(input);
        window.calculateZakat();
    });
    input.addEventListener('change', window.calculateZakat);
});
</script>