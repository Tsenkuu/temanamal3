<?php
// File: kalkulator/perdagangan.php
?>
<h3 class="text-xl font-semibold mb-1">Zakat Perdagangan</h3>
<p class="text-gray-500 mb-4">Dihitung dari aset lancar usaha setelah dikurangi utang jatuh tempo dan telah mencapai
    haul (1 tahun).</p>
<div class="space-y-4">
    <div><label for="aset_lancar" class="block text-sm font-medium text-gray-700">Aset Lancar (Modal + Keuntungan)
            (Rp)</label><input type="text" id="aset_lancar"
            class="zakat-input mt-1 w-full p-3 border border-gray-300 rounded-lg" placeholder="Rp 0"></div>
    <div><label for="utang_dagang" class="block text-sm font-medium text-gray-700">Utang Jatuh Tempo (Rp)</label><input
            type="text" id="utang_dagang" class="zakat-input mt-1 w-full p-3 border border-gray-300 rounded-lg"
            placeholder="Rp 0"></div>
</div>
<script>
function hitungZakat_perdagangan() {
    const totalHarta = getAngka('aset_lancar') - getAngka('utang_dagang');
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
document.querySelectorAll('#perdagangan .zakat-input').forEach(input => {
    input.addEventListener('keyup', () => {
        formatInput(input);
        window.calculateZakat();
    });
});
</script>