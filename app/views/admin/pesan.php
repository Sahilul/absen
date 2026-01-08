<?php // Admin - Daftar Pesan ?>
<div class="p-4 sm:p-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                <i data-lucide="mail" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Pesan</h1>
                <p class="text-sm text-gray-500">Kelola pesan ke guru dan siswa</p>
            </div>
        </div>

        <a href="<?= BASEURL ?>/admin/kirimPesan"
            class="px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all w-fit">
            <i data-lucide="send" class="w-4 h-4"></i>
            <span>Tulis Pesan Baru</span>
        </a>
    </div>

    <!-- Flasher -->
    <?php Flasher::flash(); ?>

    <!-- Tabs -->
    <div x-data="{ activeTab: 'terkirim' }"
        class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Tab Headers -->
        <div class="flex border-b border-gray-200">
            <button @click="activeTab = 'terkirim'"
                :class="activeTab === 'terkirim' ? 'border-indigo-500 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="flex-1 px-6 py-4 text-sm font-medium border-b-2 transition-colors flex items-center justify-center gap-2">
                <i data-lucide="send" class="w-4 h-4"></i>
                Pesan Terkirim
                <span
                    class="bg-gray-200 text-gray-600 text-xs px-2 py-0.5 rounded-full"><?= count($data['pesan_terkirim'] ?? []) ?></span>
            </button>
        </div>

        <!-- Tab Content: Terkirim -->
        <div x-show="activeTab === 'terkirim'" class="p-4">
            <?php if (empty($data['pesan_terkirim'])): ?>
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <p class="text-gray-500">Belum ada pesan terkirim</p>
                    <a href="<?= BASEURL ?>/admin/kirimPesan"
                        class="text-indigo-600 hover:underline text-sm mt-2 inline-block">Tulis pesan pertama →</a>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-100">
                    <?php foreach ($data['pesan_terkirim'] as $pesan): ?>
                        <a href="<?= BASEURL ?>/admin/detailPesan/<?= $pesan['id_pesan'] ?>"
                            class="block p-4 hover:bg-gray-50 transition-colors group">
                            <div class="flex items-start gap-4">
                                <div
                                    class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="<?= $pesan['target_type'] === 'semua_guru' ? 'users' : ($pesan['target_type'] === 'semua_siswa' ? 'graduation-cap' : 'user') ?>"
                                        class="w-5 h-5 text-indigo-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3
                                            class="font-semibold text-gray-800 truncate group-hover:text-indigo-600 transition-colors">
                                            <?= htmlspecialchars($pesan['judul']) ?>
                                        </h3>
                                        <?php if (!empty($pesan['lampiran'])): ?>
                                            <i data-lucide="paperclip" class="w-4 h-4 text-gray-400"></i>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-sm text-gray-500 truncate">
                                        <?= htmlspecialchars(substr(strip_tags($pesan['isi']), 0, 100)) ?>...</p>
                                    <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            <?= date('d M Y H:i', strtotime($pesan['created_at'])) ?>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="users" class="w-3 h-3"></i>
                                            <?= $pesan['sudah_dibaca'] ?>/<?= $pesan['total_penerima'] ?> dibaca
                                        </span>
                                        <span class="px-2 py-0.5 bg-gray-100 rounded text-gray-600">
                                            <?= str_replace('_', ' ', ucfirst($pesan['target_type'])) ?>
                                        </span>
                                    </div>
                                </div>
                                <i data-lucide="chevron-right"
                                    class="w-5 h-5 text-gray-300 group-hover:text-indigo-500 transition-colors"></i>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>