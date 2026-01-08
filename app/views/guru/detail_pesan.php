<?php // Guru - Detail Pesan ?>
<div class="p-4 sm:p-6">
    <!-- Header Section -->
    <div class="flex items-center gap-4 mb-6">
        <a href="<?= BASEURL ?>/guru/pesan" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5 text-gray-600"></i>
        </a>
        <div class="flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                <i data-lucide="mail-open" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($data['pesan']['judul']) ?></h1>
                <p class="text-sm text-gray-500"><?= date('d M Y H:i', strtotime($data['pesan']['created_at'])) ?></p>
            </div>
        </div>
    </div>

    <!-- Message Content -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Sender Info -->
        <div class="p-4 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                <i data-lucide="user" class="w-5 h-5 text-indigo-600"></i>
            </div>
            <div>
                <p class="font-medium text-gray-800">Administrator</p>
                <p class="text-xs text-gray-500">Pengirim</p>
            </div>
        </div>

        <!-- Body -->
        <div class="p-6">
            <div class="prose max-w-none text-gray-700 whitespace-pre-wrap">
                <?= nl2br(htmlspecialchars($data['pesan']['isi'])) ?></div>
        </div>

        <!-- Attachment -->
        <?php if (!empty($data['pesan']['lampiran'])): ?>
            <div class="px-6 pb-6">
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i data-lucide="file" class="w-6 h-6 text-indigo-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($data['pesan']['lampiran']) ?></p>
                        <p class="text-xs text-gray-500">Lampiran</p>
                    </div>
                    <a href="<?= BASEURL ?>/public/uploads/pesan/<?= $data['pesan']['lampiran'] ?>" download
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        Download
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>