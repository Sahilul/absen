<?php // Guru - Inbox Pesan ?>
<div class="p-4 sm:p-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                <i data-lucide="inbox" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Kotak Masuk</h1>
                <p class="text-sm text-gray-500">Pesan dari admin</p>
            </div>
        </div>

        <?php if (($data['unread_count'] ?? 0) > 0): ?>
            <span class="px-4 py-2 bg-red-100 text-red-600 rounded-lg text-sm font-medium">
                <?= $data['unread_count'] ?> pesan belum dibaca
            </span>
        <?php endif; ?>
    </div>

    <!-- Flasher -->
    <?php Flasher::flash(); ?>

    <!-- Message List -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <?php if (empty($data['pesan'])): ?>
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
                </div>
                <p class="text-gray-500">Belum ada pesan</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($data['pesan'] as $pesan): ?>
                    <a href="<?= BASEURL ?>/guru/detailPesan/<?= $pesan['id_pesan'] ?>"
                        class="block p-4 hover:bg-gray-50 transition-colors group <?= !$pesan['dibaca'] ? 'bg-indigo-50/50' : '' ?>">
                        <div class="flex items-start gap-4">
                            <!-- Unread indicator -->
                            <div class="pt-1">
                                <?php if (!$pesan['dibaca']): ?>
                                    <div class="w-3 h-3 bg-indigo-500 rounded-full"></div>
                                <?php else: ?>
                                    <div class="w-3 h-3 bg-gray-200 rounded-full"></div>
                                <?php endif; ?>
                            </div>

                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i data-lucide="user" class="w-5 h-5 text-indigo-600"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3
                                        class="font-semibold text-gray-800 truncate group-hover:text-indigo-600 transition-colors <?= !$pesan['dibaca'] ? 'text-indigo-900' : '' ?>">
                                        <?= htmlspecialchars($pesan['judul']) ?>
                                    </h3>
                                    <?php if (!empty($pesan['lampiran'])): ?>
                                        <i data-lucide="paperclip" class="w-4 h-4 text-gray-400"></i>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-gray-500 truncate">
                                    <?= htmlspecialchars(substr(strip_tags($pesan['isi']), 0, 80)) ?>...</p>
                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        <?= date('d M Y H:i', strtotime($pesan['created_at'])) ?>
                                    </span>
                                    <span>Dari: Administrator</span>
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