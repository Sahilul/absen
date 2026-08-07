<main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center gap-4">
            <a href="<?= BASEURL ?>/cms/popups"
                class="text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 p-2 rounded-lg transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-800">Edit Popup</h1>
                <p class="text-slate-500 text-sm mt-1">Perbarui informasi pengumuman popup.</p>
            </div>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 max-w-2xl">
        <form action="<?= BASEURL ?>/cms/updatePopup" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $data['popup']['id'] ?>">
            <input type="hidden" name="old_image" value="<?= $data['popup']['image'] ?>">

            <div class="space-y-5">
                <!-- Judul -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Pengumuman</label>
                    <input type="text" name="title" required value="<?= htmlspecialchars($data['popup']['title']) ?>"
                        class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800">
                </div>

                <!-- Gambar -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Gambar</label>
                    <?php if (!empty($data['popup']['image'])): ?>
                        <div class="mb-3 flex items-center gap-4">
                            <img src="<?= BASEURL ?>/public/img/cms/<?= $data['popup']['image'] ?>"
                                class="h-24 w-auto object-cover rounded-xl border border-slate-200">
                            <span class="text-xs text-slate-500">Gambar saat ini</span>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*"
                        class="w-full border border-slate-300 rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-slate-500 mt-1">Biarkan kosong jika tidak ingin mengubah gambar.</p>
                </div>

                <!-- Isi -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Isi Pengumuman</label>
                    <textarea name="content" rows="4"
                        class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800"><?= htmlspecialchars($data['popup']['content']) ?></textarea>
                </div>

                <!-- Link URL -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Link URL (Opsional)</label>
                    <input type="text" name="link_url" value="<?= htmlspecialchars($data['popup']['link_url']) ?>"
                        class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800"
                        placeholder="https://...">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Mulai Tampil</label>
                        <input type="date" name="start_date" value="<?= $data['popup']['start_date'] ?>"
                            class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Selesai Tampil</label>
                        <input type="date" name="end_date" value="<?= $data['popup']['end_date'] ?>"
                            class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800">
                    </div>
                </div>

                <!-- Frekuensi -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Frekuensi Tampil</label>
                    <select name="frequency"
                        class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700">
                        <option value="once" <?= $data['popup']['frequency'] == 'once' ? 'selected' : '' ?>>Sekali Saja
                        </option>
                        <option value="daily" <?= $data['popup']['frequency'] == 'daily' ? 'selected' : '' ?>>Sekali Sehari
                        </option>
                        <option value="always" <?= $data['popup']['frequency'] == 'always' ? 'selected' : '' ?>>Selalu
                            Muncul</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-5 border-t border-slate-100">
                <a href="<?= BASEURL ?>/cms/popups"
                    class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-medium hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</main>

<script>
    lucide.createIcons();
</script>