<!-- =================================================================== -->

<?php
// File: kalkulator/tabungan.php
?>
<h3 class="text-xl font-semibold mb-1">Zakat Tabungan</h3>
<p class="text-gray-500 mb-4">Berlaku untuk tabungan, deposito, dan aset simpanan sejenis yang telah mencapai haul (1
    tahun).</p>
<div><label for="saldo_tabungan" class="block text-sm font-medium text-gray-700">Saldo Akhir Tabungan (Rp)</label><input
        type="text" id="saldo_tabungan" class="zakat-input mt-1 w-full p-3 border border-gray-300 rounded-lg"
        placeholder="Rp 0"></div>
<script>
function hitungZakat_tabungan() {
    const totalHarta = getAngka('saldo_tabungan');
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
document.querySelector('#tabungan .zakat-input').addEventListener('keyup', () => {
    formatInput(document.getElementById('saldo_tabungan'));
    window.calculateZakat();
});
</script>

<!-- =================================================================== -->