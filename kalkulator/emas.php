<?php
// File: kalkulator/emas.php
?>
<h3 class="text-xl font-semibold mb-1">Zakat Emas</h3>
<p class="text-gray-500 mb-4">Dikenakan jika emas yang disimpan (tidak dipakai) telah mencapai nisabnya selama 1 tahun.
</p>
<div><label for="nilai_emas" class="block text-sm font-medium text-gray-700">Emas yang Dimiliki (gram)</label><input
        type="number" id="nilai_emas" class="zakat-input-no-prefix" placeholder="min. 85 gram"></div>
<script>
function hitungZakat_emas() {
    const gramEmas = getAngka('nilai_emas');
    let zakat = 0,
        wajibZakat = false;
    if (gramEmas >= 85) {
        zakat = (gramEmas * hargaEmasPerGram) * 0.025;
        wajibZakat = true;
    }
    return {
        zakat,
        wajibZakat,
        nisabInfoText: `Nisab emas: 85 gram.`
    };
}
document.querySelector('#emas .zakat-input-no-prefix').addEventListener('keyup', window.calculateZakat);
</script>