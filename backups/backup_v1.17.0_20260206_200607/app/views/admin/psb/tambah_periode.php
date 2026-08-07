<?php
// File: app/views/admin/psb/tambah_periode.php
?>
<?php $this->view('templates/header', $data); ?>

<div class="animate-fade-in">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= BASEURL; ?>/psb/periode" class="p-2 hover:bg-secondary-100 rounded-lg transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5 text-secondary-500"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-secondary-800">Tambah Periode PSB</h1>
            <p class="text-secondary-500 mt-1">Buat periode penerimaan baru</p>
        </div>
    </div>

    <form action="<?= BASEURL; ?>/psb/prosesTambahPeriode" method="POST" class="space-y-6 max-w-3xl">
        <!-- Info Periode -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-secondary-800 mb-4 flex items-center gap-2">
                <i data-lucide="calendar" class="w-5 h-5 text-primary-500"></i>
                Informasi Periode
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Lembaga <span
                            class="text-danger-500">*</span></label>
                    <select name="id_lembaga" required
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">-- Pilih Lembaga --</option>
                        <?php foreach ($data['lembaga'] as $l): ?>
                            <option value="<?= $l['id_lembaga']; ?>"><?= htmlspecialchars($l['nama_lembaga']); ?>
                                (<?= $l['jenjang']; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Tahun Pelajaran <span
                            class="text-danger-500">*</span></label>
                    <select name="id_tp" required
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="">-- Pilih TP --</option>
                        <?php foreach ($data['tahun_pelajaran'] as $tp): ?>
                            <option value="<?= $tp['id_tp']; ?>"><?= htmlspecialchars($tp['nama_tp']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-5">
                <label class="block text-sm font-semibold text-secondary-700 mb-2">Nama Periode <span
                        class="text-danger-500">*</span></label>
                <input type="text" name="nama_periode" required placeholder="Contoh: Gelombang 1 - 2024/2025"
                    class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Tanggal Buka <span
                            class="text-danger-500">*</span></label>
                    <input type="date" name="tanggal_buka" required
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Tanggal Tutup <span
                            class="text-danger-500">*</span></label>
                    <input type="date" name="tanggal_tutup" required
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-5">
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Kuota Total</label>
                    <input type="number" name="kuota" min="0" value="0"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Biaya Pendaftaran (Rp)</label>
                    <input type="number" name="biaya_pendaftaran" min="0" value="0"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Status</label>
                    <select name="status"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <option value="draft">Draft</option>
                        <option value="aktif">Aktif</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
            </div>

            <div class="mt-5">
                <label class="block text-sm font-semibold text-secondary-700 mb-2">Keterangan</label>
                <textarea name="keterangan" rows="2"
                    class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"></textarea>
            </div>
        </div>

        <!-- Jalur Pendaftaran -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-secondary-800 mb-4 flex items-center gap-2">
                <i data-lucide="git-branch" class="w-5 h-5 text-purple-500"></i>
                Jalur Pendaftaran
            </h2>
            <p class="text-sm text-secondary-500 mb-4">Centang jalur yang ingin diaktifkan untuk periode ini</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($data['jalur'] as $j): ?>
                    <div class="p-4 border border-secondary-200 rounded-lg hover:border-primary-300 transition-colors jalur-card"
                        data-jalur="<?= $j['id_jalur']; ?>">
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="jalur_aktif[]" value="<?= $j['id_jalur']; ?>"
                                    class="sr-only peer jalur-checkbox" onchange="toggleKuota(<?= $j['id_jalur']; ?>)">
                                <div
                                    class="w-11 h-6 bg-secondary-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-secondary-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-500">
                                </div>
                            </label>
                            <div class="flex-1">
                                <p class="font-semibold text-secondary-800"><?= htmlspecialchars($j['nama_jalur']); ?></p>
                                <p class="text-xs text-secondary-400"><?= htmlspecialchars($j['kode_jalur']); ?></p>
                            </div>
                        </div>
                        <div class="mt-3 kuota-input hidden" id="kuota-<?= $j['id_jalur']; ?>">
                            <label class="block text-xs font-medium text-secondary-600 mb-1">Kuota</label>
                            <input type="number" name="kuota_jalur[<?= $j['id_jalur']; ?>]" min="0" value="0"
                                class="w-full px-3 py-2 border border-secondary-300 rounded-lg text-center focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <script>
            function toggleKuota(idJalur) {
                const kuotaDiv = document.getElementById('kuota-' + idJalur);
                const card = document.querySelector('.jalur-card[data-jalur="' + idJalur + '"]');
                const checkbox = card.querySelector('.jalur-checkbox');

                if (checkbox.checked) {
                    kuotaDiv.classList.remove('hidden');
                    card.classList.add('border-primary-400', 'bg-primary-50');
                    card.classList.remove('border-secondary-200');
                } else {
                    kuotaDiv.classList.add('hidden');
                    card.classList.remove('border-primary-400', 'bg-primary-50');
                    card.classList.add('border-secondary-200');
                }
            }
        </script>

        <div class="flex justify-end gap-3">
            <a href="<?= BASEURL; ?>/psb/periode" class="btn-secondary px-6 py-2.5">Batal</a>
            <button type="submit" class="btn-primary px-6 py-2.5 flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i>
                Simpan Periode
            </button>
        </div>
    </form>
</div>

<script>document.addEventListener('DOMContentLoaded', function () { lucide.createIcons(); });</script>
<?php $this->view('templates/footer'); ?>