<main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-4 md:p-6" x-data="{ showAddModal: false }">
    <!-- Header -->
    <div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-800">Slider Halaman Depan</h1>
                <p class="text-slate-500 text-sm mt-1">Kelola gambar slide show yang muncul di halaman utama website.
                </p>
            </div>
            <button @click="showAddModal = true"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-blue-600/20 hover:-translate-y-0.5">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                <span>Tambah Slider Baru</span>
            </button>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Empty State -->
    <?php if (empty($data['sliders'])): ?>
        <div class="bg-white rounded-2xl p-10 text-center shadow-sm border border-slate-100">
            <div class="bg-blue-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="image" class="w-8 h-8 text-blue-600"></i>
            </div>
            <h5 class="text-lg font-bold text-slate-800 mb-2">Belum ada slider</h5>
            <p class="text-slate-500 mb-6 max-w-sm mx-auto">Tambahkan slider gambar untuk mempercantik halaman depan website
                sekolah.</p>
            <button @click="showAddModal = true" class="text-blue-600 font-semibold hover:underline">
                + Tambah Slider Sekarang
            </button>
        </div>
    <?php else: ?>

        <!-- Grid Sliders -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($data['sliders'] as $slider):
                $isActive = $slider['is_active'] == 1;
                ?>
                <div
                    class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden group hover:shadow-md transition-shadow">
                    <!-- Image -->
                    <div class="relative h-48 bg-slate-100">
                        <img src="<?= BASEURL ?>/public/img/cms/<?= $slider['image'] ?>"
                            alt="<?= htmlspecialchars($slider['title']) ?>" class="w-full h-full object-cover">
                        <div class="absolute top-3 right-3">
                            <span
                                class="px-2.5 py-1 text-xs font-bold rounded-lg border <?= $isActive ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200' ?>">
                                <?= $isActive ? 'Aktif' : 'Tidak Aktif' ?>
                            </span>
                        </div>
                        <div class="absolute bottom-3 left-3 bg-black/60 text-white text-xs px-2.5 py-1 rounded-lg">
                            Urutan: <?= $slider['order_index'] ?>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <h5 class="font-bold text-slate-800 mb-1 truncate"><?= htmlspecialchars($slider['title']) ?></h5>
                        <p class="text-slate-500 text-sm mb-4 line-clamp-2 h-10"><?= htmlspecialchars($slider['description']) ?>
                        </p>

                        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                            <!-- Toggle Status -->
                            <a href="<?= BASEURL ?>/cms/toggleSlider/<?= $slider['id'] ?>/<?= $isActive ? 0 : 1 ?>"
                                class="flex items-center gap-1.5 text-sm font-medium <?= $isActive ? 'text-green-600 hover:text-green-800' : 'text-slate-400 hover:text-slate-600' ?>">
                                <i data-lucide="<?= $isActive ? 'toggle-right' : 'toggle-left' ?>" class="w-5 h-5"></i>
                                <?= $isActive ? 'Aktif' : 'Nonaktif' ?>
                            </a>

                            <!-- Delete -->
                            <a href="<?= BASEURL ?>/cms/hapusSlider/<?= $slider['id'] ?>"
                                onclick="return confirm('Yakin ingin menghapus slider ini?')"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 border border-red-200 transition-colors">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Modal Tambah Slider -->
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
                class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl">

                <div class="flex items-center justify-between p-5 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800">Tambah Slider Baru</h3>
                    <button @click="showAddModal = false"
                        class="text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 p-2 rounded-full transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="<?= BASEURL ?>/cms/tambahSlider" method="POST" enctype="multipart/form-data">
                    <div class="p-5 space-y-4">
                        <!-- Judul -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Slider</label>
                            <input type="text" name="title" required placeholder="Contoh: PPDB Telah Dibuka"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800 placeholder:text-slate-400">
                        </div>

                        <!-- Gambar -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Gambar Slider</label>
                            <input type="file" name="image" required accept="image/*"
                                class="w-full border border-slate-300 rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-slate-500 mt-1">Format: JPG/PNG/WebP. Ukuran disarankan 1920x600px.
                            </p>
                        </div>

                        <!-- Deskripsi -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Singkat</label>
                            <textarea name="description" rows="2" placeholder="Keterangan singkat..."
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800 placeholder:text-slate-400"></textarea>
                        </div>

                        <!-- Row Link & Text Button -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Link Tujuan
                                    (Opsional)</label>
                                <input type="text" name="link_url" placeholder="http://..."
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Teks Tombol</label>
                                <input type="text" name="button_text" value="Selengkapnya"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800">
                            </div>
                        </div>

                        <!-- Order -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Urutan Tampil</label>
                            <input type="number" name="order_index" value="1" min="1"
                                class="w-24 border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-800">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 p-5 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
                        <button type="button" @click="showAddModal = false"
                            class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-medium hover:bg-white transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-all">
                            Simpan Slider
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