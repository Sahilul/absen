<?php
// File: app/views/admin/psb/pengaturan.php
$pg = $data['pengaturan'] ?? [];
?>
<?php $this->view('templates/header', $data); ?>

<div class="animate-fade-in">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= BASEURL; ?>/psb/dashboard" class="p-2 hover:bg-secondary-100 rounded-lg transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5 text-secondary-500"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-secondary-800">Pengaturan PSB</h1>
            <p class="text-secondary-500 mt-1">Konfigurasi halaman pendaftaran publik</p>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-xl shadow-sm border mb-4">
        <div class="flex border-b overflow-x-auto">
            <button type="button" onclick="showTab('konten')" id="tab-konten"
                class="tab-btn px-6 py-3 text-sm font-medium text-primary-600 border-b-2 border-primary-500 whitespace-nowrap">
                <i data-lucide="file-text" class="w-4 h-4 inline mr-1"></i> Konten Halaman
            </button>
            <button type="button" onclick="showTab('popup')" id="tab-popup"
                class="tab-btn px-6 py-3 text-sm font-medium text-secondary-500 hover:text-secondary-700 whitespace-nowrap">
                <i data-lucide="square-asterisk" class="w-4 h-4 inline mr-1"></i> Popup
            </button>
            <button type="button" onclick="showTab('brosur')" id="tab-brosur"
                class="tab-btn px-6 py-3 text-sm font-medium text-secondary-500 hover:text-secondary-700 whitespace-nowrap">
                <i data-lucide="image" class="w-4 h-4 inline mr-1"></i> Brosur
            </button>
        </div>
    </div>

    <form action="<?= BASEURL; ?>/psb/simpanPengaturan" method="POST" enctype="multipart/form-data">

        <!-- Tab 1: Konten Halaman -->
        <div id="panel-konten" class="tab-panel bg-white rounded-xl shadow-sm border p-6 mb-4">
            <div class="flex items-center gap-3 mb-5">
                <div class="bg-primary-100 p-2 rounded-lg">
                    <i data-lucide="file-text" class="w-5 h-5 text-primary-600"></i>
                </div>
                <h2 class="text-lg font-semibold text-secondary-800">Konten Halaman PSB</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Judul Halaman</label>
                    <input type="text" name="judul_halaman"
                        value="<?= htmlspecialchars($pg['judul_halaman'] ?? 'Penerimaan Siswa Baru'); ?>"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Deskripsi</label>
                    <input type="text" name="deskripsi" value="<?= htmlspecialchars($pg['deskripsi'] ?? ''); ?>"
                        placeholder="Deskripsi singkat"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Syarat Pendaftaran</label>
                    <textarea name="syarat_pendaftaran" rows="5" placeholder="Daftar syarat (1 per baris)"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"><?= htmlspecialchars($pg['syarat_pendaftaran'] ?? ''); ?></textarea>
                    <p class="text-xs text-secondary-400 mt-1">Gunakan baris baru untuk setiap poin</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Alur Pendaftaran</label>
                    <textarea name="alur_pendaftaran" rows="5" placeholder="Langkah-langkah pendaftaran"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"><?= htmlspecialchars($pg['alur_pendaftaran'] ?? ''); ?></textarea>
                </div>
            </div>
            <div class="mt-5">
                <label class="block text-sm font-semibold text-secondary-700 mb-2">Informasi Kontak</label>
                <textarea name="kontak_info" rows="3" placeholder="Kontak panitia PSB"
                    class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"><?= htmlspecialchars($pg['kontak_info'] ?? ''); ?></textarea>
            </div>
        </div>

        <!-- Tab 2: Popup Gambar -->
        <div id="panel-popup" class="tab-panel bg-white rounded-xl shadow-sm border p-6 mb-4 hidden">
            <div class="flex items-center gap-3 mb-5">
                <div class="bg-amber-100 p-2 rounded-lg">
                    <i data-lucide="image-plus" class="w-5 h-5 text-amber-600"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-secondary-800">Popup Gambar</h2>
                    <p class="text-sm text-secondary-500">Tampilkan popup gambar di halaman pendaftaran</p>
                </div>
            </div>

            <!-- Toggle Aktif & Frekuensi -->
            <div class="grid md:grid-cols-2 gap-5 mb-5">
                <div class="p-4 bg-secondary-50 rounded-lg h-full">
                    <label class="flex items-center gap-3 cursor-pointer h-full">
                        <input type="checkbox" name="popup_aktif" value="1" <?= ($pg['popup_aktif'] ?? 0) ? 'checked' : ''; ?>
                            class="w-5 h-5 rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                        <div>
                            <span class="font-semibold text-secondary-700">Aktifkan Popup</span>
                            <p class="text-xs text-secondary-500">Popup akan muncul saat pengunjung membuka halaman pendaftaran</p>
                        </div>
                    </label>
                </div>
                <div class="p-4 bg-secondary-50 rounded-lg">
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Frekuensi Tampil</label>
                    <select name="popup_frequency" class="w-full px-3 py-2 border border-secondary-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                        <option value="once_session" <?= ($pg['popup_frequency'] ?? 'once_session') == 'once_session' ? 'selected' : ''; ?>>Sekali per Sesi (Browser)</option>
                        <option value="once_day" <?= ($pg['popup_frequency'] ?? '') == 'once_day' ? 'selected' : ''; ?>>Sekali Sehari (24 Jam)</option>
                        <option value="always" <?= ($pg['popup_frequency'] ?? '') == 'always' ? 'selected' : ''; ?>>Selalu Tampil (Setiap Reload)</option>
                    </select>
                    <p class="text-xs text-secondary-500 mt-1">Mengatur seberapa sering popup muncul untuk orang yang sama</p>
                </div>
            </div>

            <!-- Gambar Popup Saat Ini -->
            <?php if (!empty($pg['popup_gambar'])): ?>
                <div class="mb-5 p-4 bg-secondary-50 rounded-lg">
                    <p class="text-sm font-medium text-secondary-700 mb-3">Gambar Popup Saat Ini:</p>
                    <div class="relative inline-block">
                        <img src="<?= BASEURL; ?>/public/uploads/psb/popup/<?= htmlspecialchars($pg['popup_gambar']); ?>"
                            alt="Popup" class="max-w-xs rounded-lg shadow cursor-pointer hover:opacity-90"
                            onclick="openPopupPreview(this.src)">
                        <p class="text-xs text-secondary-500 mt-2">Klik gambar untuk memperbesar</p>
                    </div>
                    <div class="mt-3">
                        <label class="inline-flex items-center gap-2 text-sm text-red-600 cursor-pointer">
                            <input type="checkbox" name="hapus_popup" value="1" class="rounded">
                            Hapus gambar popup ini
                        </label>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Upload Gambar Baru -->
            <div class="mb-5">
                <label class="block text-sm font-semibold text-secondary-700 mb-2">Upload Gambar Popup</label>
                <div
                    class="border-2 border-dashed border-secondary-300 rounded-lg p-6 text-center hover:border-primary-400 transition-colors">
                    <i data-lucide="upload-cloud" class="w-10 h-10 text-secondary-400 mx-auto mb-3"></i>
                    <p class="text-secondary-600 mb-2">Pilih gambar untuk popup</p>
                    <input type="file" name="popup_gambar" accept="image/jpeg,image/png,image/webp,image/gif" id="popup-input"
                        class="hidden" onchange="previewPopup(this)">
                    <label for="popup-input" class="btn-secondary px-4 py-2 cursor-pointer inline-block">
                        Pilih Gambar
                    </label>
                    <p class="text-xs text-secondary-400 mt-2">Format: JPG, PNG, WebP, GIF. Maks 5MB. Rekomendasi: 600x800px</p>
                </div>
                <div id="popup-preview" class="mt-4 hidden">
                    <p class="text-sm font-medium text-secondary-700 mb-2">Preview:</p>
                    <img id="popup-preview-img" src="" alt="Preview" class="max-w-xs rounded-lg shadow">
                </div>
            </div>

            <!-- Judul dan Link -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Judul Popup (opsional)</label>
                    <input type="text" name="popup_judul"
                        value="<?= htmlspecialchars($pg['popup_judul'] ?? ''); ?>"
                        placeholder="Contoh: Info Pendaftaran"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Link Tujuan (opsional)</label>
                    <input type="url" name="popup_link"
                        value="<?= htmlspecialchars($pg['popup_link'] ?? ''); ?>"
                        placeholder="https://wa.me/6281234567890"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    <p class="text-xs text-secondary-400 mt-1">Jika diisi, gambar dapat diklik untuk membuka link ini</p>
                </div>
            </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Brosur -->
        <div id="panel-brosur" class="tab-panel bg-white rounded-xl shadow-sm border p-6 mb-4 hidden">
            <div class="flex items-center gap-3 mb-5">
                <div class="bg-indigo-100 p-2 rounded-lg">
                    <i data-lucide="image" class="w-5 h-5 text-indigo-600"></i>
                </div>
                <h2 class="text-lg font-semibold text-secondary-800">Brosur Digital</h2>
            </div>
            
            <?php if (!empty($pg['brosur_gambar'])): ?>
                <div class="mb-5 p-4 bg-secondary-50 rounded-lg">
                    <p class="text-sm font-medium text-secondary-700 mb-3">Brosur Saat Ini:</p>
                    <div class="relative inline-block">
                        <img src="<?= BASEURL; ?>/public/uploads/psb/brosur/<?= htmlspecialchars($pg['brosur_gambar']); ?>" 
                             alt="Brosur" class="max-w-xs rounded-lg shadow cursor-pointer hover:opacity-90"
                             onclick="openBrosurModal(this.src)">
                        <p class="text-xs text-secondary-500 mt-2">Klik gambar untuk memperbesar</p>
                    </div>
                    <div class="mt-3">
                        <label class="inline-flex items-center gap-2 text-sm text-red-600 cursor-pointer">
                            <input type="checkbox" name="hapus_brosur" value="1" class="rounded">
                            Hapus brosur ini
                        </label>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mb-5">
                <label class="block text-sm font-semibold text-secondary-700 mb-2">Upload Brosur Baru</label>
                <div class="border-2 border-dashed border-secondary-300 rounded-lg p-6 text-center hover:border-primary-400 transition-colors">
                    <i data-lucide="upload-cloud" class="w-10 h-10 text-secondary-400 mx-auto mb-3"></i>
                    <p class="text-secondary-600 mb-2">Pilih file gambar brosur</p>
                    <input type="file" name="brosur_gambar" accept="image/jpeg,image/png,image/webp" id="brosur-input" class="hidden" onchange="previewBrosur(this)">
                    <label for="brosur-input" class="btn-secondary px-4 py-2 cursor-pointer inline-block">
                        Pilih Gambar
                    </label>
                    <p class="text-xs text-secondary-400 mt-2">Format: JPG, PNG, WebP. Maks 5MB.</p>
                </div>
                <div id="brosur-preview" class="mt-4 hidden">
                    <p class="text-sm font-medium text-secondary-700 mb-2">Preview:</p>
                    <img id="brosur-preview-img" src="" alt="Preview" class="max-w-xs rounded-lg shadow">
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
            <a href="<?= BASEURL; ?>/psb/dashboard" class="btn-secondary px-6 py-2.5">Batal</a>
            <button type="submit" class="btn-primary px-6 py-2.5 flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i>
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>

<!-- Brosur Modal -->
<div id="brosur-modal" class="fixed inset-0 bg-black/70 z-50 hidden items-center justify-center p-4"
    onclick="closeBrosurModal()">
    <div class="relative max-w-4xl max-h-[90vh]">
        <button type="button" onclick="closeBrosurModal()"
            class="absolute -top-10 right-0 text-white hover:text-secondary-300">
            <i data-lucide="x" class="w-8 h-8"></i>
        </button>
        <img id="brosur-modal-img" src="" alt="Brosur" class="max-w-full max-h-[85vh] rounded-lg"
            onclick="event.stopPropagation()">
    </div>
</div>

<script>
    function showTab(tabName) {
        // Hide all panels
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
        // Reset all tab buttons
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('text-primary-600', 'border-b-2', 'border-primary-500');
            b.classList.add('text-secondary-500');
        });
        // Show selected panel
        document.getElementById('panel-' + tabName).classList.remove('hidden');
        // Highlight selected tab
        const activeTab = document.getElementById('tab-' + tabName);
        activeTab.classList.remove('text-secondary-500');
        activeTab.classList.add('text-primary-600', 'border-b-2', 'border-primary-500');
    }

    function previewBrosur(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('brosur-preview-img').src = e.target.result;
                document.getElementById('brosur-preview').classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function openBrosurModal(src) {
        document.getElementById('brosur-modal-img').src = src;
        document.getElementById('brosur-modal').classList.remove('hidden');
        document.getElementById('brosur-modal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeBrosurModal() {
        document.getElementById('brosur-modal').classList.add('hidden');
        document.getElementById('brosur-modal').classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('DOMContentLoaded', function () {
        lucide.createIcons();
    });

    // Preview popup image before upload
    function previewPopup(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('popup-preview-img').src = e.target.result;
                document.getElementById('popup-preview').classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Open popup preview modal (reuse brosur modal)
    function openPopupPreview(src) {
        document.getElementById('brosur-modal-img').src = src;
        document.getElementById('brosur-modal').classList.remove('hidden');
        document.getElementById('brosur-modal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeBrosurModal();
    });
</script>

<?php $this->view('templates/footer'); ?>