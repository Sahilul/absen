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
            <button type="button" onclick="showTab('info')" id="tab-info"
                class="tab-btn px-6 py-3 text-sm font-medium text-secondary-500 hover:text-secondary-700 whitespace-nowrap">
                <i data-lucide="info" class="w-4 h-4 inline mr-1"></i> Info Sekolah
            </button>
            <button type="button" onclick="showTab('brosur')" id="tab-brosur"
                class="tab-btn px-6 py-3 text-sm font-medium text-secondary-500 hover:text-secondary-700 whitespace-nowrap">
                <i data-lucide="image" class="w-4 h-4 inline mr-1"></i> Brosur
            </button>
            <button type="button" onclick="showTab('wa')" id="tab-wa"
                class="tab-btn px-6 py-3 text-sm font-medium text-secondary-500 hover:text-secondary-700 whitespace-nowrap">
                <i data-lucide="message-circle" class="w-4 h-4 inline mr-1"></i> WhatsApp
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

        <!-- Tab 2: Info Sekolah -->
        <div id="panel-info" class="tab-panel bg-white rounded-xl shadow-sm border p-6 mb-4 hidden">
            <div class="flex items-center gap-3 mb-5">
                <div class="bg-blue-100 p-2 rounded-lg">
                    <i data-lucide="building" class="w-5 h-5 text-blue-600"></i>
                </div>
                <h2 class="text-lg font-semibold text-secondary-800">Informasi Sekolah</h2>
            </div>
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Tentang Sekolah</label>
                    <textarea name="tentang_sekolah" rows="4" placeholder="Deskripsi singkat tentang sekolah..."
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"><?= htmlspecialchars($pg['tentang_sekolah'] ?? ''); ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Keunggulan Sekolah</label>
                    <textarea name="keunggulan" rows="4" placeholder="Daftar keunggulan (1 per baris)"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"><?= htmlspecialchars($pg['keunggulan'] ?? ''); ?></textarea>
                    <p class="text-xs text-secondary-400 mt-1">Gunakan baris baru untuk setiap keunggulan</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Visi & Misi</label>
                    <textarea name="visi_misi" rows="4" placeholder="Visi dan misi sekolah..."
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"><?= htmlspecialchars($pg['visi_misi'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Tab 3: Brosur -->
        <div id="panel-brosur" class="tab-panel bg-white rounded-xl shadow-sm border p-6 mb-4 hidden">
            <div class="flex items-center gap-3 mb-5">
                <div class="bg-purple-100 p-2 rounded-lg">
                    <i data-lucide="image" class="w-5 h-5 text-purple-600"></i>
                </div>
                <h2 class="text-lg font-semibold text-secondary-800">Gambar Brosur</h2>
            </div>

            <?php if (!empty($pg['brosur_gambar'])): ?>
                <div class="mb-5 p-4 bg-secondary-50 rounded-lg">
                    <p class="text-sm font-medium text-secondary-700 mb-3">Brosur Saat Ini:</p>
                    <div class="relative inline-block">
                        <img src="<?= BASEURL; ?>/uploads/psb/brosur/<?= htmlspecialchars($pg['brosur_gambar']); ?>"
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

            <div
                class="border-2 border-dashed border-secondary-300 rounded-lg p-6 text-center hover:border-primary-400 transition-colors">
                <i data-lucide="upload-cloud" class="w-12 h-12 text-secondary-400 mx-auto mb-3"></i>
                <p class="text-secondary-600 mb-2">Upload gambar brosur baru</p>
                <input type="file" name="brosur_gambar" accept="image/jpeg,image/png,image/webp" id="brosur-input"
                    class="hidden" onchange="previewBrosur(this)">
                <label for="brosur-input" class="btn-secondary px-4 py-2 cursor-pointer inline-block">
                    Pilih Gambar
                </label>
                <p class="text-xs text-secondary-400 mt-2">Format: JPG, PNG, WebP. Maks 5MB</p>
            </div>
            <div id="brosur-preview" class="mt-4 hidden">
                <p class="text-sm font-medium text-secondary-700 mb-2">Preview:</p>
                <img id="brosur-preview-img" src="" alt="Preview" class="max-w-xs rounded-lg shadow">
            </div>
        </div>

        <!-- Tab 4: WhatsApp Gateway -->
        <div id="panel-wa" class="tab-panel bg-white rounded-xl shadow-sm border p-6 mb-4 hidden">
            <div class="flex items-center gap-3 mb-5">
                <div class="bg-green-100 p-2 rounded-lg">
                    <i data-lucide="message-circle" class="w-5 h-5 text-green-600"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-secondary-800">WhatsApp Gateway</h2>
                    <p class="text-sm text-secondary-500">Konfigurasi Fonnte untuk notifikasi WA</p>
                </div>
            </div>
            <div class="space-y-4 bg-secondary-50 p-4 rounded-lg">
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">API URL Fonnte</label>
                    <input type="text" name="wa_gateway_url"
                        value="<?= htmlspecialchars($pg['wa_gateway_url'] ?? 'https://api.fonnte.com/send'); ?>"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 bg-white"
                        placeholder="https://api.fonnte.com/send">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-secondary-700 mb-2">Token API Fonnte</label>
                    <input type="password" name="wa_gateway_token"
                        value="<?= htmlspecialchars($pg['wa_gateway_token'] ?? ''); ?>"
                        class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 bg-white"
                        placeholder="Masukkan token dari dashboard Fonnte">
                    <p class="text-xs text-secondary-400 mt-1">
                        Dapatkan token di <a href="https://fonnte.com" target="_blank"
                            class="text-primary-600 hover:underline">fonnte.com</a>
                    </p>
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

    // Close modal on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeBrosurModal();
    });
</script>

<?php $this->view('templates/footer'); ?>