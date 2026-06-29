<?php
// File: kalkulator/perusahaan.php
?>
<h3 class="text-xl font-semibold mb-1">Zakat Perusahaan</h3>
<p class="text-gray-500 mb-4">Dikenakan atas aset perusahaan yang telah berjalan selama 1 tahun dan mencapai nisab.</p>
<div class="space-y-4">
    <div><label for="aset_perusahaan" class="block text-sm font-medium text-gray-700">Aset Kena Zakat (Rp)</label><input
            type="text" id="aset_perusahaan" class="zakat-input mt-1 w-full p-3 border border-gray-300 rounded-lg"
            placeholder="Rp 0"></div>
    <div><label for="utang_perusahaan" class="block text-sm font-medium text-gray-700">Utang Perusahaan
            (Rp)</label><input type="text" id="utang_perusahaan"
            class="zakat-input mt-1 w-full p-3 border border-gray-300 rounded-lg" placeholder="Rp 0"></div>
</div>
<script>
function hitungZakat_perusahaan() {
    const totalHarta = getAngka('aset_perusahaan') - getAngka('utang_perusahaan');
    let zakat = 0,
        wajibZakat = false;
    if (totalHarta >= nisabEmas) {
        zakat = totalHarta * 0.025;
        wajibZakat = true;
    }
    return {
        zakat,
        wajibZakat,
        nisabInfoText: `Nisab per tahun: ${formatRupiah(nisabEmas)}.`
    };
}
document.querySelectorAll('#perusahaan .zakat-input').forEach(input => {
    input.addEventListener('keyup', () => {
        formatInput(input);
        window.calculateZakat();
    });
});
</script>