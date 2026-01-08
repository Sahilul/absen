<?php
// Form Section: Alamat dengan API Wilayah Indonesia
$p = $pendaftar;
?>
<div class="space-y-5">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
        <p class="text-sm text-blue-700">
            <i data-lucide="info" class="w-4 h-4 inline"></i>
            Pilih provinsi terlebih dahulu, lalu kabupaten, kecamatan, dan kelurahan akan otomatis tersedia.
        </p>
    </div>

    <!-- Alamat Lengkap -->
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat Lengkap <span
                class="text-red-500">*</span></label>
        <textarea name="alamat" rows="3" required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
            placeholder="Jalan, nomor rumah, gang, dll"><?= htmlspecialchars($p['alamat'] ?? ''); ?></textarea>
    </div>

    <!-- RT RW Dusun -->
    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">RT</label>
            <input type="text" name="rt" value="<?= htmlspecialchars($p['rt'] ?? ''); ?>" maxlength="3"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                placeholder="001">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">RW</label>
            <input type="text" name="rw" value="<?= htmlspecialchars($p['rw'] ?? ''); ?>" maxlength="3"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                placeholder="002">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Dusun/Kampung</label>
            <input type="text" name="dusun" value="<?= htmlspecialchars($p['dusun'] ?? ''); ?>"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
        </div>
    </div>

    <!-- Provinsi & Kabupaten -->
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Provinsi <span
                    class="text-red-500">*</span></label>
            <select name="provinsi_id" id="provinsi" required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white">
                <option value="">-- Pilih Provinsi --</option>
            </select>
            <input type="hidden" name="provinsi" id="provinsi_nama"
                value="<?= htmlspecialchars($p['provinsi'] ?? ''); ?>">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Kabupaten/Kota <span
                    class="text-red-500">*</span></label>
            <select name="kabupaten_id" id="kabupaten" required disabled
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white disabled:bg-gray-100">
                <option value="">-- Pilih Provinsi Dulu --</option>
            </select>
            <input type="hidden" name="kabupaten" id="kabupaten_nama"
                value="<?= htmlspecialchars($p['kabupaten'] ?? ''); ?>">
        </div>
    </div>

    <!-- Kecamatan & Kelurahan -->
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Kecamatan <span
                    class="text-red-500">*</span></label>
            <select name="kecamatan_id" id="kecamatan" required disabled
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white disabled:bg-gray-100">
                <option value="">-- Pilih Kabupaten Dulu --</option>
            </select>
            <input type="hidden" name="kecamatan" id="kecamatan_nama"
                value="<?= htmlspecialchars($p['kecamatan'] ?? ''); ?>">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Desa/Kelurahan <span
                    class="text-red-500">*</span></label>
            <select name="desa_id" id="desa" required disabled
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white disabled:bg-gray-100">
                <option value="">-- Pilih Kecamatan Dulu --</option>
            </select>
            <input type="hidden" name="desa" id="desa_nama" value="<?= htmlspecialchars($p['desa'] ?? ''); ?>">
        </div>
    </div>

    <!-- Kode Pos -->
    <div class="w-1/3">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Kode Pos</label>
        <input type="text" name="kode_pos" id="kode_pos" value="<?= htmlspecialchars($p['kode_pos'] ?? ''); ?>"
            maxlength="5" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
            placeholder="5 digit">
    </div>
</div>

<script>
    // API Base URL (menggunakan API gratis dari emsifa)
    const API_BASE = 'https://www.emsifa.com/api-wilayah-indonesia/api';

    // Data existing (untuk pre-select saat edit)
    const existing = {
        provinsi: '<?= htmlspecialchars($p['provinsi'] ?? ''); ?>',
        kabupaten: '<?= htmlspecialchars($p['kabupaten'] ?? ''); ?>',
        kecamatan: '<?= htmlspecialchars($p['kecamatan'] ?? ''); ?>',
        desa: '<?= htmlspecialchars($p['desa'] ?? ''); ?>'
    };

    // Load provinsi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', async function () {
        await loadProvinsi();
    });

    async function loadProvinsi() {
        const select = document.getElementById('provinsi');
        try {
            const response = await fetch(`${API_BASE}/provinces.json`);
            const data = await response.json();

            select.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
            data.forEach(prov => {
                const option = document.createElement('option');
                option.value = prov.id;
                option.textContent = prov.name;
                option.dataset.name = prov.name;
                // Pre-select if matches
                if (existing.provinsi && prov.name.toLowerCase() === existing.provinsi.toLowerCase()) {
                    option.selected = true;
                    loadKabupaten(prov.id);
                }
                select.appendChild(option);
            });
        } catch (error) {
            console.error('Error loading provinsi:', error);
        }
    }

    async function loadKabupaten(provId) {
        const select = document.getElementById('kabupaten');
        select.disabled = true;
        select.innerHTML = '<option value="">Memuat...</option>';

        try {
            const response = await fetch(`${API_BASE}/regencies/${provId}.json`);
            const data = await response.json();

            select.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
            data.forEach(kab => {
                const option = document.createElement('option');
                option.value = kab.id;
                option.textContent = kab.name;
                option.dataset.name = kab.name;
                if (existing.kabupaten && kab.name.toLowerCase() === existing.kabupaten.toLowerCase()) {
                    option.selected = true;
                    loadKecamatan(kab.id);
                }
                select.appendChild(option);
            });
            select.disabled = false;
        } catch (error) {
            console.error('Error loading kabupaten:', error);
            select.innerHTML = '<option value="">Gagal memuat data</option>';
        }
    }

    async function loadKecamatan(kabId) {
        const select = document.getElementById('kecamatan');
        select.disabled = true;
        select.innerHTML = '<option value="">Memuat...</option>';

        try {
            const response = await fetch(`${API_BASE}/districts/${kabId}.json`);
            const data = await response.json();

            select.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
            data.forEach(kec => {
                const option = document.createElement('option');
                option.value = kec.id;
                option.textContent = kec.name;
                option.dataset.name = kec.name;
                if (existing.kecamatan && kec.name.toLowerCase() === existing.kecamatan.toLowerCase()) {
                    option.selected = true;
                    loadDesa(kec.id);
                }
                select.appendChild(option);
            });
            select.disabled = false;
        } catch (error) {
            console.error('Error loading kecamatan:', error);
            select.innerHTML = '<option value="">Gagal memuat data</option>';
        }
    }

    async function loadDesa(kecId) {
        const select = document.getElementById('desa');
        select.disabled = true;
        select.innerHTML = '<option value="">Memuat...</option>';

        try {
            const response = await fetch(`${API_BASE}/villages/${kecId}.json`);
            const data = await response.json();

            select.innerHTML = '<option value="">-- Pilih Desa/Kelurahan --</option>';
            data.forEach(desa => {
                const option = document.createElement('option');
                option.value = desa.id;
                option.textContent = desa.name;
                option.dataset.name = desa.name;
                if (existing.desa && desa.name.toLowerCase() === existing.desa.toLowerCase()) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
            select.disabled = false;
        } catch (error) {
            console.error('Error loading desa:', error);
            select.innerHTML = '<option value="">Gagal memuat data</option>';
        }
    }

    // Event listeners untuk cascading
    document.getElementById('provinsi').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('provinsi_nama').value = selected.dataset.name || '';

        // Reset child selects
        document.getElementById('kabupaten').innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
        document.getElementById('kabupaten').disabled = true;
        document.getElementById('kecamatan').innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        document.getElementById('kecamatan').disabled = true;
        document.getElementById('desa').innerHTML = '<option value="">-- Pilih Desa/Kelurahan --</option>';
        document.getElementById('desa').disabled = true;

        if (this.value) {
            loadKabupaten(this.value);
        }
    });

    document.getElementById('kabupaten').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('kabupaten_nama').value = selected.dataset.name || '';

        document.getElementById('kecamatan').innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
        document.getElementById('kecamatan').disabled = true;
        document.getElementById('desa').innerHTML = '<option value="">-- Pilih Desa/Kelurahan --</option>';
        document.getElementById('desa').disabled = true;

        if (this.value) {
            loadKecamatan(this.value);
        }
    });

    document.getElementById('kecamatan').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('kecamatan_nama').value = selected.dataset.name || '';

        document.getElementById('desa').innerHTML = '<option value="">-- Pilih Desa/Kelurahan --</option>';
        document.getElementById('desa').disabled = true;

        if (this.value) {
            loadDesa(this.value);
        }
    });

    document.getElementById('desa').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('desa_nama').value = selected.dataset.name || '';
    });
</script>