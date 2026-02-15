<main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-4 md:p-6" x-data="{ 
          showAddModal: false, 
          showEditModal: false,
          editPopup: {
              id: null,
              title: '',
              content: '',
              link_url: '',
              start_date: '',
              end_date: '',
              frequency: 'once'
          }
      }">
    <!-- Header -->
    <div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-slate-800">Popup Informasi</h1>
                <p class="text-slate-500 text-sm mt-1">Kelola pengumuman pop-up yang muncul saat website diakses.</p>
            </div>
            <button @click="showAddModal = true"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-lg shadow-blue-600/20 hover:-translate-y-0.5">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                <span>Buat Pengumuman Baru</span>
            </button>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Empty State -->
    <?php if (empty($data['popups'])): ?>
        <div class="bg-white rounded-2xl p-10 text-center shadow-sm border border-slate-100">
            <div class="bg-blue-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="message-square-dashed" class="w-8 h-8 text-blue-600"></i>
            </div>
            <h5 class="text-lg font-bold text-slate-800 mb-2">Belum ada popup aktif</h5>
            <p class="text-slate-500 mb-6 max-w-sm mx-auto">Buat pengumuman penting yang akan langsung dilihat pengunjung
                website.</p>
            <button @click="showAddModal = true" class="text-blue-600 font-semibold hover:underline">
                + Buat Pengumuman Sekarang
            </button>
        </div>
    <?php else: ?>

        <!-- List Popups -->
        <div class="space-y-4">
            <?php foreach ($data['popups'] as $popup):
                $isActive = $popup['is_active'] == 1;
                $today = date('Y-m-d');
                $isExpired = $popup['end_date'] < $today;
                $statusColor = $isActive ? ($isExpired ? 'text-orange-700 bg-orange-50 border-orange-200' : 'text-green-700 bg-green-50 border-green-200') : 'text-slate-600 bg-slate-50 border-slate-200';
                $statusText = $isActive ? ($isExpired ? 'Expired' : 'Aktif') : 'Nonaktif';
                ?>
                <div
                    class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 flex flex-col md:flex-row gap-4 items-start md:items-center hover:shadow-md transition-shadow">
                    <!-- Image Thumbnail -->
                    <div
                        class="w-full md:w-28 h-28 flex-shrink-0 bg-slate-100 rounded-xl overflow-hidden border border-slate-200">
                        <?php if (!empty($popup['image'])): ?>
                            <img src="<?= BASEURL ?>/public/img/cms/<?= $popup['image'] ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                <i data-lucide="file-text" class="w-8 h-8"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span
                                class="px-2.5 py-1 text-xs font-bold rounded-lg border <?= $statusColor ?>"><?= $statusText ?></span>
                            <span class="text-xs text-slate-500 flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                <?= date('d M Y', strtotime($popup['start_date'])) ?> -
                                <?= date('d M Y', strtotime($popup['end_date'])) ?>
                            </span>
                            <span class="text-xs text-slate-500 border-l border-slate-300 pl-2">
                                Frekuensi: <?= ucfirst($popup['frequency']) ?>
                            </span>
                        </div>
                        <h5 class="font-bold text-slate-800 text-lg truncate mb-1"><?= htmlspecialchars($popup['title']) ?></h5>
                        <p class="text-slate-500 text-sm line-clamp-2"><?= htmlspecialchars($popup['content']) ?></p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3 ml-auto">
                        <a href="<?= BASEURL ?>/cms/togglePopup/<?= $popup['id'] ?>/<?= $isActive ? 0 : 1 ?>"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out <?= $isActive ? 'bg-green-500' : 'bg-slate-300' ?>">
                            <span
                                class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200 ease-in-out <?= $isActive ? 'translate-x-5' : 'translate-x-0' ?>"></span>
                        </a>

                        <!-- Edit Button -->
                        <?php
                        $popupData = json_encode([
                            'id' => (int) $popup['id'],
                            'title' => $popup['title'] ?? '',
                            'content' => $popup['content'] ?? '',
                            'link_url' => $popup['link_url'] ?? '',
                            'start_date' => $popup['start_date'] ?? '',
                            'end_date' => $popup['end_date'] ?? '',
                            'frequency' => $popup['frequency'] ?? 'once'
                        ]);
                        ?>
                        <button @click='editPopup = <?= htmlspecialchars($popupData, ENT_QUOTES) ?>; showEditModal = true'
                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 hover:text-blue-600 border border-blue-200 transition-colors"
                            title="Edit">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </button>

                        <a href="<?= BASEURL ?>/cms/hapusPopup/<?= $popup['id'] ?>"
                            onclick="return confirm('Hapus pengumuman ini?')"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 border border-red-200 transition-colors"
                            title="Hapus">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Modal Tambah Popup -->
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
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-100 p-2 rounded-lg">
                            <i data-lucide="message-square-plus" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Buat Popup Informasi</h3>
                    </div>
                    <button @click="showAddModal = false"
                        class="text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 p-2 rounded-full transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="<?= BASEURL ?>/cms/tambahPopup" method="POST" enctype="multipart/form-data">
                    <div class="p-5 space-y-4 max-h-[60vh] overflow-y-auto">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Judul</label>
                            <input type="text" name="title" required placeholder="Judul pengumuman"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Isi Pengumuman</label>
                            <textarea name="content" rows="3" placeholder="Deskripsi singkat..."
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Gambar</label>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-600 file:font-medium hover:file:bg-blue-100 cursor-pointer border border-slate-300 rounded-xl p-2">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Link (Opsional)</label>
                            <input type="text" name="link_url" placeholder="https://..."
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai</label>
                                <input type="date" name="start_date" required value="<?= date('Y-m-d') ?>"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Akhir</label>
                                <input type="date" name="end_date" required
                                    value="<?= date('Y-m-d', strtotime('+7 days')) ?>"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Frekuensi Tampil</label>
                            <select name="frequency"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="once">Sekali saja</option>
                                <option value="daily">Setiap hari</option>
                                <option value="always">Selalu tampil</option>
                            </select>
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

    <!-- Modal Edit Popup -->
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
                class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl">

                <div class="flex items-center justify-between p-5 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="bg-amber-100 p-2 rounded-lg">
                            <i data-lucide="pencil" class="w-5 h-5 text-amber-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Edit Popup</h3>
                    </div>
                    <button @click="showEditModal = false"
                        class="text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 p-2 rounded-full transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="<?= BASEURL ?>/cms/updatePopup" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" x-bind:value="editPopup.id">
                    <input type="hidden" name="old_image" value="">
                    <div class="p-5 space-y-4 max-h-[60vh] overflow-y-auto">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Judul</label>
                            <input type="text" name="title" required x-model="editPopup.title"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Isi Pengumuman</label>
                            <textarea name="content" rows="3" x-model="editPopup.content"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Gambar Baru
                                (Opsional)</label>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-600 file:font-medium hover:file:bg-blue-100 cursor-pointer border border-slate-300 rounded-xl p-2">
                            <p class="text-xs text-slate-500 mt-1">Kosongkan jika tidak ingin mengganti gambar.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Link (Opsional)</label>
                            <input type="text" name="link_url" x-model="editPopup.link_url"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai</label>
                                <input type="date" name="start_date" required x-model="editPopup.start_date"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Akhir</label>
                                <input type="date" name="end_date" required x-model="editPopup.end_date"
                                    class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Frekuensi Tampil</label>
                            <select name="frequency" x-model="editPopup.frequency"
                                class="w-full border border-slate-300 rounded-xl py-2.5 px-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="once">Sekali saja</option>
                                <option value="daily">Setiap hari</option>
                                <option value="always">Selalu tampil</option>
                            </select>
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