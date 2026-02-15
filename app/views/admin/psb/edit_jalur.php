<?php
// File: app/views/admin/psb/edit_jalur.php
$j = $data['jalur'];
?>
<?php $this->view('templates/header', $data); ?>

<div class="animate-fade-in">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= BASEURL; ?>/psb/jalur" class="p-2 hover:bg-secondary-100 rounded-lg transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5 text-secondary-500"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-secondary-800">Edit Jalur Pendaftaran</h1>
            <p class="text-secondary-500 mt-1"><?= htmlspecialchars($j['nama_jalur']); ?></p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border max-w-2xl">
        <form action="<?= BASEURL; ?>/psb/prosesUpdateJalur" method="POST" class="p-6 space-y-5">
            <input type="hidden" name="id_jalur" value="<?= $j['id_jalur']; ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Kode Jalur <span
                            class="text-danger-500">*</span></label>
                    <input type="text" name="kode_jalur" required value="<?= htmlspecialchars($j['kode_jalur']); ?>"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Urutan</label>
                    <input type="number" name="urutan" min="0" value="<?= $j['urutan']; ?>"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-secondary-700 mb-2">Nama Jalur <span
                        class="text-danger-500">*</span></label>
                <input type="text" name="nama_jalur" required value="<?= htmlspecialchars($j['nama_jalur']); ?>"
                    class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-secondary-700 mb-2">Deskripsi</label>
                <textarea name="deskripsi" rows="2"
                    class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"><?= htmlspecialchars($j['deskripsi'] ?? ''); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-secondary-700 mb-2">Persyaratan</label>
                <textarea name="persyaratan" rows="3"
                    class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"><?= htmlspecialchars($j['persyaratan'] ?? ''); ?></textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="aktif" id="aktif" <?= $j['aktif'] ? 'checked' : ''; ?>
                    class="w-4 h-4 text-primary-600 border-secondary-300 rounded">
                <label for="aktif" class="text-sm font-medium text-secondary-700">Aktif</label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="<?= BASEURL; ?>/psb/jalur" class="btn-secondary px-6 py-2.5">Batal</a>
                <button type="submit" class="btn-primary px-6 py-2.5 flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>document.addEventListener('DOMContentLoaded', function () { lucide.createIcons(); });</script>
<?php $this->view('templates/footer'); ?>