<?php
$versionInfo = $data['version_info'] ?? [];
$requirements = $data['requirements'] ?? [];
$requirementsMet = $data['requirements_met'] ?? false;
$backups = $data['backups'] ?? [];
function formatBytes($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < 3) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
?>
<main
    class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-slate-50 via-gray-50 to-slate-100 min-h-screen">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i data-lucide="download-cloud" class="w-7 h-7 text-blue-600"></i>
                    Pembaruan Aplikasi
                </h2>
                <p class="text-gray-600 mt-1">Kelola update dan backup aplikasi</p>
            </div>
        </div>
        <?php if (class_exists('Flasher'))
            Flasher::flash(); ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="info" class="w-5 h-5 text-blue-500"></i> Informasi Versi
                        </h3>
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                            v<?= htmlspecialchars($versionInfo['version'] ?? '0.0.0') ?>
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><span class="text-gray-500">Aplikasi:</span>
                            <p class="font-medium"><?= htmlspecialchars($versionInfo['app_name'] ?? 'App') ?></p>
                        </div>
                        <div><span class="text-gray-500">Build:</span>
                            <p class="font-medium">#<?= $versionInfo['build'] ?? 0 ?></p>
                        </div>
                        <div><span class="text-gray-500">Tanggal:</span>
                            <p class="font-medium"><?= $versionInfo['release_date'] ?? '-' ?></p>
                        </div>
                        <div><span class="text-gray-500">Channel:</span>
                            <p class="font-medium"><?= ucfirst($versionInfo['update_channel'] ?? 'stable') ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-4">
                        <i data-lucide="refresh-cw" class="w-5 h-5 text-green-500"></i> Cek Pembaruan
                    </h3>
                    <div id="updateStatus" class="bg-gray-50 rounded-xl p-4 mb-4 text-center text-gray-600">Klik tombol
                        untuk cek update</div>
                    <div id="changelogSection" class="hidden mb-4">
                        <h4 class="font-medium text-gray-700 mb-2">📋 Changelog:</h4>
                        <div id="changelogContent" class="bg-gray-50 rounded-xl p-4 text-sm max-h-48 overflow-y-auto">
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="checkForUpdates()"
                            class="flex-1 px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-medium flex items-center justify-center gap-2">
                            <i data-lucide="search" class="w-4 h-4"></i> Cek Update
                        </button>
                        <button onclick="installUpdate()" id="btnInstall" disabled
                            class="flex-1 px-4 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-medium flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i data-lucide="download" class="w-4 h-4"></i> Install Update
                        </button>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="archive" class="w-5 h-5 text-amber-500"></i> Backup
                        </h3>
                        <span class="text-sm text-gray-500"><?= count($backups) ?> backup</span>
                    </div>
                    <?php if (empty($backups)): ?>
                        <p class="text-center py-8 text-gray-500">Belum ada backup</p>
                    <?php else: ?>
                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            <?php foreach ($backups as $b): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                    <div>
                                        <p class="font-medium text-gray-800 text-sm"><?= htmlspecialchars($b['name']) ?></p>
                                        <p class="text-xs text-gray-500"><?= date('d M Y H:i', $b['date']) ?> •
                                            <?= formatBytes($b['size']) ?>
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button onclick="restoreBackup('<?= $b['name'] ?>')"
                                            class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg" title="Restore"><i
                                                data-lucide="rotate-ccw" class="w-4 h-4"></i></button>
                                        <button onclick="deleteBackup('<?= $b['name'] ?>')"
                                            class="p-2 text-red-600 hover:bg-red-100 rounded-lg" title="Hapus"><i
                                                data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Persyaratan Sistem</h3>
                    <div class="space-y-3">
                        <?php foreach ($requirements as $r): ?>
                            <div
                                class="flex items-center justify-between p-3 rounded-lg <?= $r['status'] ? 'bg-green-50' : 'bg-red-50' ?>">
                                <span
                                    class="text-sm <?= $r['status'] ? 'text-green-700' : 'text-red-700' ?>"><?= $r['name'] ?></span>
                                <i data-lucide="<?= $r['status'] ? 'check' : 'x' ?>"
                                    class="w-4 h-4 <?= $r['status'] ? 'text-green-600' : 'text-red-600' ?>"></i>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="bg-blue-50 rounded-2xl border border-blue-200 p-6">
                    <h3 class="text-lg font-semibold text-blue-800 mb-2">Petunjuk</h3>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• Backup otomatis dibuat sebelum update</li>
                        <li>• config.php tidak akan ditimpa</li>
                        <li>• Folder uploads aman</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>
<div id="loadingModal" class="fixed inset-0 bg-black/50 z-[9999] flex items-center justify-center hidden">
    <div class="bg-white rounded-2xl p-8 max-w-sm w-full mx-4 text-center">
        <div class="animate-spin w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4">
        </div>
        <h3 id="loadingTitle" class="text-lg font-semibold">Memproses...</h3>
        <p id="loadingMessage" class="text-gray-600 text-sm">Mohon tunggu</p>
    </div>
</div>
<script>
    const BASEURL = '<?= BASEURL ?>';
    let latestInfo = null;
    function showLoading(t, m) { document.getElementById('loadingTitle').textContent = t; document.getElementById('loadingMessage').textContent = m; document.getElementById('loadingModal').classList.remove('hidden'); }
    function hideLoading() { document.getElementById('loadingModal').classList.add('hidden'); }
    async function checkForUpdates() {
        const s = document.getElementById('updateStatus'), b = document.getElementById('btnInstall');
        s.innerHTML = '<span class="text-blue-600">Mengecek...</span>';
        try {
            const r = await fetch(BASEURL + '/update/check'), d = await r.json(); latestInfo = d;
            if (!d.success) { s.innerHTML = '<span class="text-red-600">❌ ' + (d.error || 'Error') + '</span>'; b.disabled = true; return; }
            if (d.has_update) {
                s.innerHTML = '<div class="text-green-600 font-medium text-lg mb-1">✨ Update tersedia: v' + d.latest + '</div><div class="text-sm text-gray-500">Saat ini: v' + d.current + '</div>';
                b.disabled = false;

                if (d.changelog) {
                    // Format changelog
                    let changelogHtml = '';
                    if (d.changelog_data) {
                        // Structured changelog from changelog.json
                        changelogHtml = '<ul class="space-y-2">';
                        d.changelog_data.changes.forEach(change => {
                            let icon = 'circle';
                            let color = 'text-gray-500';
                            if (change.type === 'feature') { icon = 'plus-circle'; color = 'text-green-600'; }
                            else if (change.type === 'fix') { icon = 'wrench'; color = 'text-orange-600'; }
                            else if (change.type === 'improvement') { icon = 'arrow-up-circle'; color = 'text-blue-600'; }

                            changelogHtml += `<li class="flex items-start gap-2">
                                <i data-lucide="${icon}" class="w-4 h-4 ${color} mt-0.5 flex-shrink-0"></i>
                                <span class="text-sm text-gray-700">${change.description}</span>
                             </li>`;
                        });
                        changelogHtml += '</ul>';
                    } else {
                        // Plain text changelog (e.g. from commit message)
                        // Split by newlines and create list
                        const lines = d.changelog.split('\n').filter(line => line.trim() !== '');
                        changelogHtml = '<ul class="space-y-1">';
                        lines.forEach(line => {
                            changelogHtml += `<li class="flex items-start gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0"></i>
                                <span class="text-sm text-gray-700">${line}</span>
                             </li>`;
                        });
                        changelogHtml += '</ul>';
                    }

                    document.getElementById('changelogContent').innerHTML = changelogHtml;
                    document.getElementById('changelogSection').classList.remove('hidden');
                    lucide.createIcons();
                }
            } else {
                s.innerHTML = '<div class="text-blue-600 font-medium text-lg">✓ Aplikasi Anda sudah yang terbaru</div><div class="text-sm text-gray-500 mt-1">Versi: v' + d.current + '</div>';
                b.disabled = true; document.getElementById('changelogSection').classList.add('hidden');
            }
            lucide.createIcons();
        } catch (e) { s.innerHTML = '<span class="text-red-600">❌ ' + e.message + '</span>'; b.disabled = true; }
    }
    async function installUpdate() {
        if (!latestInfo?.has_update) { alert('Tidak ada update'); return; }
        if (!confirm('Update ke v' + latestInfo.latest + '? Backup akan dibuat otomatis.')) return;
        showLoading('Menginstall Update...', 'Jangan tutup halaman ini');
        try {
            const r = await fetch(BASEURL + '/update/install', { method: 'POST' }), d = await r.json();
            hideLoading();
            alert(d.success ? '✅ ' + d.message : '❌ ' + d.error);
            if (d.success) location.reload();
        } catch (e) { hideLoading(); alert('❌ ' + e.message); }
    }
    async function restoreBackup(n) {
        if (!confirm('Restore dari ' + n + '?')) return;
        showLoading('Restoring...', '');
        try {
            const f = new FormData(); f.append('backup', n);
            const r = await fetch(BASEURL + '/update/restore', { method: 'POST', body: f }), d = await r.json();
            hideLoading(); alert(d.success ? '✅ ' + d.message : '❌ ' + d.error);
            if (d.success) location.reload();
        } catch (e) { hideLoading(); alert('❌ ' + e.message); }
    }
    async function deleteBackup(n) {
        if (!confirm('Hapus backup ' + n + '?')) return;
        const f = new FormData(); f.append('backup', n);
        const r = await fetch(BASEURL + '/update/deleteBackup', { method: 'POST', body: f }), d = await r.json();
        if (d.success) location.reload(); else alert('❌ ' + d.error);
    }
    document.addEventListener('DOMContentLoaded', () => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
</script>