<?php
// Form Section: Sekolah Asal dengan Pencarian NPSN
$p = $pendaftar;
?>
<div class="space-y-5">
    <div class="bg-violet-50 border border-violet-200 rounded-lg p-3 mb-4">
        <p class="text-sm text-violet-700">
            <i data-lucide="search" class="w-4 h-4 inline"></i>
            Masukkan <strong>NPSN</strong> sekolah untuk mengisi data otomatis, atau ketik manual jika NPSN tidak
            diketahui.
        </p>
    </div>

    <!-- Pencarian NPSN -->
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Cari dengan NPSN</label>
        <div class="flex gap-2">
            <input type="text" id="npsn_search" maxlength="8"
                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500"
                placeholder="Masukkan 8 digit NPSN" value="<?= htmlspecialchars($p['npsn_sekolah_asal'] ?? ''); ?>">
            <button type="button" onclick="cariNPSN()"
                class="px-5 py-3 bg-violet-500 hover:bg-violet-600 text-white rounded-lg font-medium flex items-center gap-2">
                <i data-lucide="search" class="w-4 h-4"></i>
                Cari
            </button>
        </div>
        <div id="npsn_result" class="mt-3 hidden">
            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                <p class="text-sm text-green-700" id="npsn_result_text"></p>
            </div>
        </div>
        <div id="npsn_error" class="mt-3 hidden">
            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                <p class="text-sm text-red-700" id="npsn_error_text"></p>
            </div>
        </div>
        <div id="npsn_loading" class="mt-3 hidden">
            <div class="flex items-center gap-2 text-gray-500">
                <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                        fill="none"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                Mencari sekolah...
            </div>
        </div>
    </div>

    <!-- Data Sekolah -->
    <div class="grid md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Sekolah Asal <span
                    class="text-red-500">*</span></label>
            <input type="text" name="nama_sekolah_asal" id="nama_sekolah_asal"
                value="<?= htmlspecialchars($p['nama_sekolah_asal'] ?? ''); ?>" required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500"
                placeholder="Contoh: SD Negeri 1 Surabaya">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">NPSN Sekolah</label>
            <input type="text" name="npsn_sekolah_asal" id="npsn_sekolah_asal"
                value="<?= htmlspecialchars($p['npsn_sekolah_asal'] ?? ''); ?>" maxlength="8"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 bg-gray-50"
                placeholder="8 digit NPSN" readonly>
            <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari pencarian</p>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Lulus <span
                    class="text-red-500">*</span></label>
            <select name="tahun_lulus" required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500">
                <option value="">-- Pilih --</option>
                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                    <option value="<?= $y; ?>" <?= ($p['tahun_lulus'] ?? date('Y')) == $y ? 'selected' : ''; ?>><?= $y; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat Sekolah Asal</label>
            <textarea name="alamat_sekolah_asal" id="alamat_sekolah_asal" rows="2"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500"
                placeholder="Alamat lengkap sekolah asal"><?= htmlspecialchars($p['alamat_sekolah_asal'] ?? ''); ?></textarea>
        </div>
    </div>
</div>

<script>
    async function cariNPSN() {
        const npsn = document.getElementById('npsn_search').value.trim();
        const resultDiv = document.getElementById('npsn_result');
        const errorDiv = document.getElementById('npsn_error');
        const loadingDiv = document.getElementById('npsn_loading');

        // Hide all feedback
        resultDiv.classList.add('hidden');
        errorDiv.classList.add('hidden');

        if (npsn.length !== 8) {
            document.getElementById('npsn_error_text').textContent = 'NPSN harus 8 digit';
            errorDiv.classList.remove('hidden');
            return;
        }

        loadingDiv.classList.remove('hidden');

        try {
            const response = await fetch('<?= BASEURL; ?>/psb/cariNPSN/' + npsn);
            const data = await response.json();
            
            console.log('API Response:', data); // Debug

            loadingDiv.classList.add('hidden');

            if (data.success && data.sekolah) {
                const sekolah = data.sekolah;
                
                console.log('Sekolah data:', sekolah); // Debug

                // Fill form dengan pengecekan null/undefined
                const namaEl = document.getElementById('nama_sekolah_asal');
                const npsnEl = document.getElementById('npsn_sekolah_asal');
                const alamatEl = document.getElementById('alamat_sekolah_asal');
                
                if (namaEl && sekolah.nama) {
                    namaEl.value = sekolah.nama;
                }
                if (npsnEl && sekolah.npsn) {
                    npsnEl.value = sekolah.npsn;
                }
                if (alamatEl) {
                    alamatEl.value = sekolah.alamat || '';
                }

                // Tampilkan hasil dengan info lengkap
                let resultHtml = '✓ Ditemukan: <strong>' + (sekolah.nama || 'Nama tidak tersedia') + '</strong>';
                if (sekolah.bentuk) {
                    resultHtml += ' (' + sekolah.bentuk + ')';
                }
                if (sekolah.status) {
                    resultHtml += ' - ' + sekolah.status;
                }
                if (sekolah.alamat) {
                    resultHtml += '<br><span class="text-xs">' + sekolah.alamat + '</span>';
                }
                
                document.getElementById('npsn_result_text').innerHTML = resultHtml;
                resultDiv.classList.remove('hidden');
            } else {
                document.getElementById('npsn_error_text').textContent = data.message || 'Sekolah tidak ditemukan';
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            loadingDiv.classList.add('hidden');
            console.error('Error:', error); // Debug
            document.getElementById('npsn_error_text').textContent = 'Gagal menghubungi server: ' + error.message;
            errorDiv.classList.remove('hidden');
        }
    }

    // Auto search saat enter
    document.getElementById('npsn_search').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            cariNPSN();
        }
    });
</script>