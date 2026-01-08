<?php
function getStatusBadgeReview($status) {
    $badges = [
        'draft' => '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700">Draft</span>',
        'submitted' => '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">Menunggu Review</span>',
        'approved' => '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Disetujui</span>',
        'revision' => '<span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">Perlu Revisi</span>'
    ];
    return $badges[$status] ?? $badges['draft'];
}
?>

<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold">Review RPP</h2>
            <p class="text-sm text-secondary-500 mt-1">Kelola dan review Rencana Pelaksanaan Pembelajaran dari guru</p>
        </div>
        <a href="<?= BASEURL; ?>/admin/pengaturanRPP" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-secondary-600 text-white rounded-lg hover:bg-secondary-700 transition-colors">
            <i data-lucide="settings" class="w-4 h-4"></i>
            Pengaturan Template
        </a>
    </div>

    <!-- Status Tabs -->
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="<?= BASEURL; ?>/admin/listRPPReview" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= empty($data['current_status']) ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
            Semua
            <span class="ml-1 px-2 py-0.5 rounded-full text-xs <?= empty($data['current_status']) ? 'bg-white/20' : 'bg-gray-200'; ?>">
                <?= count($data['list_rpp'] ?? []); ?>
            </span>
        </a>
        <a href="<?= BASEURL; ?>/admin/listRPPReview?status=submitted" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= ($data['current_status'] ?? '') === 'submitted' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'; ?>">
            Menunggu Review
            <span class="ml-1 px-2 py-0.5 rounded-full text-xs <?= ($data['current_status'] ?? '') === 'submitted' ? 'bg-white/20' : 'bg-blue-100'; ?>">
                <?= $data['count_submitted'] ?? 0; ?>
            </span>
        </a>
        <a href="<?= BASEURL; ?>/admin/listRPPReview?status=approved" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= ($data['current_status'] ?? '') === 'approved' ? 'bg-green-600 text-white' : 'bg-green-50 text-green-700 hover:bg-green-100'; ?>">
            Disetujui
            <span class="ml-1 px-2 py-0.5 rounded-full text-xs <?= ($data['current_status'] ?? '') === 'approved' ? 'bg-white/20' : 'bg-green-100'; ?>">
                <?= $data['count_approved'] ?? 0; ?>
            </span>
        </a>
        <a href="<?= BASEURL; ?>/admin/listRPPReview?status=revision" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= ($data['current_status'] ?? '') === 'revision' ? 'bg-yellow-600 text-white' : 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100'; ?>">
            Perlu Revisi
            <span class="ml-1 px-2 py-0.5 rounded-full text-xs <?= ($data['current_status'] ?? '') === 'revision' ? 'bg-white/20' : 'bg-yellow-100'; ?>">
                <?= $data['count_revision'] ?? 0; ?>
            </span>
        </a>
        <a href="<?= BASEURL; ?>/admin/listRPPReview?status=draft" 
           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= ($data['current_status'] ?? '') === 'draft' ? 'bg-gray-600 text-white' : 'bg-gray-50 text-gray-700 hover:bg-gray-100'; ?>">
            Draft
            <span class="ml-1 px-2 py-0.5 rounded-full text-xs <?= ($data['current_status'] ?? '') === 'draft' ? 'bg-white/20' : 'bg-gray-100'; ?>">
                <?= $data['count_draft'] ?? 0; ?>
            </span>
        </a>
    </div>

    <div class="glass-effect rounded-lg overflow-hidden">
        <?php if (empty($data['list_rpp'])): ?>
            <div class="p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <i data-lucide="file-x" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-700 mb-2">Tidak ada RPP</h3>
                <p class="text-gray-500">Belum ada RPP yang <?= $data['current_status'] ? 'berstatus ' . $data['current_status'] : 'tersedia'; ?> untuk semester ini.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Guru</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mata Pelajaran</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kelas</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tahun Pelajaran</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Terakhir Update</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($data['list_rpp'] as $r): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                                            <span class="text-primary-700 font-medium text-sm">
                                                <?= strtoupper(substr($r['nama_guru'], 0, 1)); ?>
                                            </span>
                                        </div>
                                        <span class="font-medium text-gray-900"><?= htmlspecialchars($r['nama_guru']); ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($r['nama_mapel']); ?></td>
                                <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($r['nama_kelas']); ?></td>
                                <td class="px-4 py-3 text-gray-700 text-sm">
                                    <?= htmlspecialchars($r['nama_tp'] ?? '-'); ?> - <?= htmlspecialchars($r['semester'] ?? '-'); ?>
                                </td>
                                <td class="px-4 py-3"><?= getStatusBadgeReview($r['status']); ?></td>
                                <td class="px-4 py-3 text-gray-500 text-sm">
                                    <?= date('d M Y H:i', strtotime($r['updated_at'])); ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="<?= BASEURL; ?>/admin/detailRPPReview/<?= htmlspecialchars($r['id_rpp']); ?>" 
                                       class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                        <?= $r['status'] === 'submitted' ? 'Review' : 'Lihat'; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>