<?php
// File: kalkulator/pertanian.php
?>
<h3 class="text-xl font-semibold mb-1">Zakat Pertanian</h3>
<p class="text-gray-500 mb-4">Dibayarkan saat panen jika hasil panen telah mencapai nisab.</p>
<div class="space-y-4">
    <div><label for="hasil_panen" class="block text-sm font-medium text-gray-700">Nilai Hasil Panen (Rp)</label><input
            type="text" id="hasil_panen" class="zakat-input mt-1 w-full p-3 border border-gray-300 rounded-lg"
            placeholder="Rp 0"></div>
    <div>
        <label for="jenis_irigasi" class="block text-sm font-medium text-gray-700">Jenis Irigasi</label>
        <select id="jenis_irigasi" class="zakat-input-no-prefix">
            <option value="0.05">Irigasi (Berbayar)</option>
            <option value="0.1">Tadah Hujan (Gratis)</option>
        </select>
    </div>
</div>
<script>
function hitungZakat_pertanian() {
    const hasilPanen = getAngka('hasil_panen');
    const jenisIrigasi = parseFloat(document.getElementById('jenis_irigasi').value);
    let zakat = 0,
        wajibZakat = false;
    if (hasilPanen >= nisabPertanian) {
        zakat = hasilPanen * jenisIrigasi;
        wajibZakat = true;
    }
    return {
        zakat,
        wajibZakat,
        nisabInfoText: `Nisab pertanian (653 kg gabah): ${formatRupiah(nisabPertanian)}.`
    };
}
document.querySelector('#pertanian .zakat-input').addEventListener('keyup', () => {
    formatInput(document.getElementById('hasil_panen'));
    window.calculateZakat();
});
document.querySelector('#pertanian .zakat-input-no-prefix').addEventListener('change', window.calculateZakat);
</script>