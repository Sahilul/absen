<main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center gap-4">
            <a href="<?= BASEURL ?>/cms/menus"
                class="text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 p-2 rounded-lg transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-800">Edit Menu</h1>
                <p class="text-slate-500 text-sm mt-1">Perbarui informasi menu navigasi.</p>
            </div>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 max-w-xl">
        <form action="<?= BASEURL ?>/cms/updateMenu" method="POST">
            <input type="hidden" name="id" value="<?= $data['menu']['id'] ?>">

            <div class="space-y-5">
                <!-- Label -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Label Menu</label>
                    <input type="text" name="label" required value="<?= htmlspecialchars($data['menu']['label']) ?>"
                        class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800">
                </div>

                <!-- URL -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">URL Target</label>
                    <input type="text" name="url" required value="<?= htmlspecialchars($data['menu']['url']) ?>"
                        class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800 font-mono">
                </div>

                <!-- Parent Menu -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Induk Menu</label>
                    <select name="parent_id"
                        class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700">
                        <option value="0" <?= $data['menu']['parent_id'] == 0 ? 'selected' : '' ?>>-- Menu Utama (Tanpa
                            Induk) --</option>
                        <?php foreach ($data['parents'] as $parent): ?>
                            <option value="<?= $parent['id'] ?>" <?= $data['menu']['parent_id'] == $parent['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($parent['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Pilih menu induk untuk menjadikan ini sebagai sub-menu.</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tipe</label>
                        <select name="type"
                            class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700">
                            <option value="link" <?= $data['menu']['type'] == 'link' ? 'selected' : '' ?>>Tautan</option>
                            <option value="page" <?= $data['menu']['type'] == 'page' ? 'selected' : '' ?>>Halaman</option>
                        </select>
                    </div>

                    <!-- Order -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Urutan</label>
                        <input type="number" name="order_index" value="<?= $data['menu']['order_index'] ?>" min="1"
                            class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-5 border-t border-slate-100">
                <a href="<?= BASEURL ?>/cms/menus"
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