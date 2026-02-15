<?php // Admin - Detail Pesan ?>
<div class="p-4 sm:p-6">
    <!-- Header Section -->
    <div class="flex items-center gap-4 mb-6">
        <a href="<?= BASEURL ?>/admin/pesan" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5 text-gray-600"></i>
        </a>
        <div class="flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                <i data-lucide="mail-open" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Detail Pesan</h1>
                <p class="text-sm text-gray-500"><?= date('d M Y H:i', strtotime($data['pesan']['created_at'])) ?></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <!-- Subject -->
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($data['pesan']['judul']) ?></h2>
                    <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                        <span class="flex items-center gap-1">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            Dari: Administrator
                        </span>
                        <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded text-xs">
                            <?= str_replace('_', ' ', ucfirst($data['pesan']['target_type'])) ?>
                        </span>
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

                <!-- Actions -->
                <div class="px-6 pb-6">
                    <form action="<?= BASEURL ?>/admin/hapusPesan/<?= $data['pesan']['id_pesan'] ?>" method="POST"
                        onsubmit="return confirm('Yakin ingin menghapus pesan ini?')">
                        <button type="submit"
                            class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            Hapus Pesan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar - Penerima -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        Penerima (<?= count($data['penerima']) ?>)
                    </h3>
                </div>
                <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                    <?php foreach ($data['penerima'] as $p): ?>
                        <div class="p-3 flex items-center gap-3">
                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                <i data-lucide="<?= $p['penerima_type'] === 'guru' ? 'briefcase' : 'user' ?>"
                                    class="w-4 h-4 text-gray-500"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">
                                    <?= htmlspecialchars($p['nama_penerima'] ?? 'Unknown') ?></p>
                                <p class="text-xs text-gray-400"><?= ucfirst($p['penerima_type']) ?></p>
                            </div>
                            <?php if ($p['dibaca']): ?>
                                <span class="text-xs text-green-600 flex items-center gap-1">
                                    <i data-lucide="check-check" class="w-3 h-3"></i>
                                    Dibaca
                                </span>
                            <?php else: ?>
                                <span class="text-xs text-gray-400">Belum dibaca</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>