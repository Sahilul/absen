<main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-4 md:p-6" x-data="{ 
          showAddModal: false, 
          showSubMenuModal: false,
          showEditModal: false,
          selectedParent: null,
          selectedParentLabel: '',
          editMenu: {
              id: null,
              label: '',
              url: '',
              parent_id: 0,
              type: 'link',
              order_index: 1
          }
      }">
    <!-- Header -->
    <div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-800">Menu Navigasi Website</h1>
                <p class="text-slate-500 text-sm mt-1">Kelola struktur menu header website sekolah.</p>
            </div>
            <button @click="showAddModal = true"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-blue-600/20 hover:-translate-y-0.5">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                <span>Tambah Menu Utama</span>
            </button>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Menu List -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <?php if (empty($data['menus'])): ?>
            <div class="p-10 text-center">
                <div class="bg-slate-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="menu" class="w-8 h-8 text-slate-400"></i>
                </div>
                <p class="text-slate-500 mb-4">Belum ada menu navigasi.</p>
                <button @click="showAddModal = true" class="text-blue-600 font-semibold hover:underline">
                    + Tambah Menu Sekarang
                </button>
            </div>
        <?php else: ?>
            <div class="divide-y divide-slate-100">
                <?php foreach ($data['menus'] as $menu): ?>
                    <?php $isParent = (!isset($menu['depth']) || $menu['depth'] == 0); ?>

                    <div
                        class="flex flex-col md:flex-row items-start md:items-center gap-3 md:gap-4 p-3 md:p-4 hover:bg-slate-50/50 transition-colors <?= $isParent ? 'bg-white' : 'bg-slate-50/30' ?>">
                        <!-- Order Badge -->
                        <div class="flex-shrink-0">
                            <span
                                class="inline-flex items-center justify-center w-7 h-7 md:w-8 md:h-8 rounded-lg <?= $isParent ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-slate-100 text-slate-600 border-slate-200' ?> font-bold text-xs md:text-sm border">
                                <?= $menu['order_index']; ?>
                            </span>
                        </div>

                        <!-- Label with Indent -->
                        <div class="flex-1 min-w-0 w-full md:w-auto"
                            style="padding-left: <?= isset($menu['depth']) ? $menu['depth'] * 16 : 0; ?>px">
                            <div class="flex flex-wrap items-center gap-2">
                                <?php if (isset($menu['depth']) && $menu['depth'] > 0): ?>
                                    <i data-lucide="corner-down-right" class="w-3 h-3 md:w-4 md:h-4 text-slate-400 flex-shrink-0"></i>
                                <?php endif; ?>
                                <span class="font-semibold <?= $isParent ? 'text-slate-800' : 'text-slate-600' ?> text-sm md:text-base">
                                    <?= htmlspecialchars($menu['label']); ?>
                                </span>
                                <?php if ($isParent): ?>
                                    <span
                                        class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full border border-blue-100 flex-shrink-0">Menu
                                        Utama</span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-1">
                                <code class="text-xs font-mono text-slate-500 bg-slate-100 px-2 py-0.5 rounded break-all">
                                    <?= htmlspecialchars($menu['url']); ?>
                                </code>
                            </div>
                        </div>

                        <!-- Type Badge -->
                        <div class="flex-shrink-0 hidden md:block">
                            <?php if (isset($menu['type']) && $menu['type'] == 'link'): ?>
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-medium border border-blue-200">
                                    <i data-lucide="link" class="w-3 h-3"></i> Link
                                </span>
                            <?php else: ?>
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-green-50 text-green-700 text-xs font-medium border border-green-200">
                                    <i data-lucide="file-text" class="w-3 h-3"></i> Halaman
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Status Toggle -->
                        <div class="flex-shrink-0">
                            <a href="<?= BASEURL; ?>/cms/toggleMenu/<?= $menu['id']; ?>/<?= $menu['is_active'] ? '0' : '1'; ?>"
                                class="relative inline-flex h-6 w-11 cursor-pointer rounded-full border-2 border-transparent transition-colors <?= $menu['is_active'] ? 'bg-green-500' : 'bg-slate-300'; ?>">
                                <span
                                    class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition <?= $menu['is_active'] ? 'translate-x-5' : 'translate-x-0'; ?>"></span>
                            </a>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 flex-shrink-0 w-full md:w-auto justify-end md:justify-start">
                            <?php
                            $menuData = json_encode([
                                'id' => (int) $menu['id'],
                                'label' => $menu['label'] ?? '',
                                'url' => $menu['url'] ?? '',
                                'parent_id' => (int) $menu['parent_id'],
                                'type' => $menu['type'] ?? 'link',
                                'order_index' => (int) $menu['order_index']
                            ]);
                            ?>
                            
                            <!-- Desktop: All Buttons Visible -->
                            <div class="hidden md:flex items-center gap-2">
                                <button @click='editMenu = <?= htmlspecialchars($menuData, ENT_QUOTES) ?>; showEditModal = true'
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 border border-blue-200 transition-colors"
                                    title="Edit">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>

                                <?php if ($isParent): ?>
                                    <button
                                        @click="selectedParent = <?= $menu['id']; ?>; selectedParentLabel = '<?= addslashes(htmlspecialchars($menu['label'])); ?>'; showSubMenuModal = true"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 border border-green-200 transition-colors"
                                        title="Tambah Sub-Menu">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                    </button>
                                <?php endif; ?>

                                <button
                                    onclick="if(confirm('Hapus menu ini?')) window.location.href='<?= BASEURL; ?>/cms/hapusMenu/<?= $menu['id']; ?>'"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 border border-red-200 transition-colors"
                                    title="Hapus">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                            
                            <!-- Mobile: Compact Buttons -->
                            <div class="flex md:hidden items-center gap-2">
                                <button @click='editMenu = <?= htmlspecialchars($menuData, ENT_QUOTES) ?>; showEditModal = true'
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 border border-blue-700 transition-colors text-sm font-medium">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    <span>Edit</span>
                                </button>
                                
                                <?php if ($isParent): ?>
                                    <button
                                        @click="selectedParent = <?= $menu['id']; ?>; selectedParentLabel = '<?= addslashes(htmlspecialchars($menu['label'])); ?>'; showSubMenuModal = true"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-600 text-white hover:bg-green-700 border border-green-700 transition-colors">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                    </button>
                                <?php endif; ?>

                                <button
                                    onclick="if(confirm('Hapus menu ini?')) window.location.href='<?= BASEURL; ?>/cms/hapusMenu/<?= $menu['id']; ?>'"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-600 text-white hover:bg-red-700 border border-red-700 transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Tambah Menu Utama -->
    <div x-show="showAddModal" class="fixed inset-0 z-[70] overflow-y-auto" style="display: none;" x-cloak>
        <div x-show="showAddModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAddModal = false"></div>

        <div class="flex min-h-screen items-center justify-center p-4">
            <div x-show="showAddModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative w-full max-w-md bg-white rounded-2xl shadow-xl">

                <div class="flex items-center justify-between p-5 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-100 p-2 rounded-lg">
                            <i data-lucide="menu" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Tambah Menu Utama</h3>
                    </div>
                    <button @click="showAddModal = false"
                        class="text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 p-2 rounded-full transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="<?= BASEURL; ?>/cms/tambahMenu" method="POST">
                    <input type="hidden" name="parent_id" value="0">
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Label Menu</label>
                            <input type="text" name="label" required placeholder="Contoh: Tentang Kami"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">URL Target</label>
                            <input type="text" name="url" required placeholder="/halaman atau https://..."
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800 font-mono">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Tipe</label>
                                <select name="type"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700">
                                    <option value="link">Tautan</option>
                                    <option value="page">Halaman</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Urutan</label>
                                <input type="number" name="order_index" value="1" min="1"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 p-5 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
                        <button type="button" @click="showAddModal = false"
                            class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-medium hover:bg-white transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-all">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Sub-Menu -->
    <div x-show="showSubMenuModal" class="fixed inset-0 z-[70] overflow-y-auto" style="display: none;" x-cloak>
        <div x-show="showSubMenuModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showSubMenuModal = false"></div>

        <div class="flex min-h-screen items-center justify-center p-4">
            <div x-show="showSubMenuModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative w-full max-w-md bg-white rounded-2xl shadow-xl">

                <div class="flex items-center justify-between p-5 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="bg-green-100 p-2 rounded-lg">
                            <i data-lucide="corner-down-right" class="w-5 h-5 text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Tambah Sub-Menu</h3>
                            <p class="text-xs text-slate-500">Di bawah: <span class="font-semibold text-green-600"
                                    x-text="selectedParentLabel"></span></p>
                        </div>
                    </div>
                    <button @click="showSubMenuModal = false"
                        class="text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 p-2 rounded-full transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="<?= BASEURL; ?>/cms/tambahMenu" method="POST">
                    <input type="hidden" name="parent_id" x-bind:value="selectedParent">
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Label Sub-Menu</label>
                            <input type="text" name="label" required placeholder="Contoh: Visi Misi"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">URL Target</label>
                            <input type="text" name="url" required placeholder="/halaman atau https://..."
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800 font-mono">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Tipe</label>
                                <select name="type"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700">
                                    <option value="link">Tautan</option>
                                    <option value="page">Halaman</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Urutan</label>
                                <input type="number" name="order_index" value="1" min="1"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 p-5 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
                        <button type="button" @click="showSubMenuModal = false"
                            class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-medium hover:bg-white transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-green-600 text-white font-medium hover:bg-green-700 shadow-lg shadow-green-600/20 transition-all">
                            Simpan Sub-Menu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Menu -->
    <div x-show="showEditModal" class="fixed inset-0 z-[70] overflow-y-auto" style="display: none;" x-cloak>
        <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showEditModal = false"></div>

        <div class="flex min-h-screen items-center justify-center p-4">
            <div x-show="showEditModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="relative w-full max-w-md bg-white rounded-2xl shadow-xl">

                <div class="flex items-center justify-between p-5 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="bg-amber-100 p-2 rounded-lg">
                            <i data-lucide="pencil" class="w-5 h-5 text-amber-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Edit Menu</h3>
                    </div>
                    <button @click="showEditModal = false"
                        class="text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 p-2 rounded-full transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="<?= BASEURL; ?>/cms/updateMenu" method="POST">
                    <input type="hidden" name="id" x-bind:value="editMenu.id">
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Label Menu</label>
                            <input type="text" name="label" required x-model="editMenu.label"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">URL Target</label>
                            <input type="text" name="url" required x-model="editMenu.url"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800 font-mono">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Induk Menu</label>
                            <select name="parent_id" x-model="editMenu.parent_id"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700">
                                <option value="0">-- Menu Utama (Tanpa Induk) --</option>
                                <?php if (!empty($data['parents'])): ?>
                                    <?php foreach ($data['parents'] as $parent): ?>
                                        <option value="<?= $parent['id'] ?>"><?= htmlspecialchars($parent['label']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Tipe</label>
                                <select name="type" x-model="editMenu.type"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-700">
                                    <option value="link">Tautan</option>
                                    <option value="page">Halaman</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Urutan</label>
                                <input type="number" name="order_index" x-model="editMenu.order_index" min="1"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 p-5 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
                        <button type="button" @click="showEditModal = false"
                            class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-medium hover:bg-white transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-amber-600 text-white font-medium hover:bg-amber-700 shadow-lg shadow-amber-600/20 transition-all">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    lucide.createIcons();
</script>