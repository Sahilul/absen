<?php
$versionInfo = $data['version_info'] ?? [];
$requirements = $data['requirements'] ?? [];
$requirementsMet = $data['requirements_met'] ?? false;
$backups = $data['backups'] ?? [];

// Format bytes helper
function formatBytes($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
?>

<main
    class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-slate-50 via-gray-50 to-slate-100 min-h-screen">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i data-lucide="download-cloud" class="w-7 h-7 text-blue-600"></i>
                    Pembaruan Aplikasi
                </h2>
                <p class="text-gray-600 mt-1">Kelola update dan backup aplikasi dengan mudah</p>
            </div>
        </div>

        <?php if (class_exists('Flasher'))
            Flasher::flash(); ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Update Check -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Current Version Card -->
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="info" class="w-5 h-5 text-blue-500"></i>
                            Informasi Versi
                        </h3>
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                            v<?= htmlspecialchars($versionInfo['version'] ?? '0.0.0') ?>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Nama Aplikasi:</span>
                            <p class="font-medium text-gray-800">
                                <?= htmlspecialchars($versionInfo['app_name'] ?? 'School App') ?></p>
                        </div>
                        <div>
                            <span class="text-gray-500">Build:</span>
                            <p class="font-medium text-gray-800">#<?= htmlspecialchars($versionInfo['build'] ?? '0') ?>
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-500">Tanggal Rilis:</span>
                            <p class="font-medium text-gray-800">
                                <?= htmlspecialchars($versionInfo['release_date'] ?? '-') ?></p>
                        </div>
                        <div>
                            <span class="text-gray-500">Channel:</span>
                            <p class="font-medium text-gray-800">
                                <?= ucfirst(htmlspecialchars($versionInfo['update_channel'] ?? 'stable')) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Update Check Card -->
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="refresh-cw" class="w-5 h-5 text-green-500"></i>
                            Cek Pembaruan
                        </h3>
                    </div>

                    <!-- Update Status -->
                    <div id="updateStatus" class="bg-gray-50 rounded-xl p-4 mb-4">
                        <p class="text-gray-600 text-center">Klik tombol di bawah untuk mengecek pembaruan terbaru</p>
                    </div>

                    <!-- Changelog -->
                    <div id="changelogSection" class="hidden mb-4">
                        <h4 class="font-medium text-gray-700 mb-2">📋 Changelog:</h4>
                        <div id="changelogContent"
                            class="bg-gray-50 rounded-xl p-4 text-sm text-gray-600 max-h-48 overflow-y-auto prose prose-sm">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <button onclick="checkForUpdates()" id="btnCheck"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl font-medium transition-all flex items-center justify-center gap-2">
                            <i data-lucide="search" class="w-4 h-4"></i>
                            <span>Cek Update</span>
                        </button>
                        <button onclick="installUpdate()" id="btnInstall" disabled
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-xl font-medium transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            <span>Install Update</span>
                        </button>
                    </div>
                </div>

                <!-- Backups Card -->
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="archive" class="w-5 h-5 text-amber-500"></i>
                            Backup Tersedia
                        </h3>
                        <span class="text-sm text-gray-500"><?= count($backups) ?> backup</span>
                    </div>

                    <?php if (empty($backups)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 opacity-50"></i>
                            <p>Belum ada backup tersedia</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            <?php foreach ($backups as $backup): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                    <div>
                                        <p class="font-medium text-gray-800 text-sm"><?= htmlspecialchars($backup['name']) ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?= date('d M Y H:i', $backup['date']) ?> •
                                            <?= formatBytes($backup['size']) ?>
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button onclick="restoreBackup('<?= htmlspecialchars($backup['name']) ?>')"
                                            class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
                                            title="Restore">
                                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="deleteBackup('<?= htmlspecialchars($backup['name']) ?>')"
                                            class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                                            title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: System Requirements -->
            <div class="space-y-6">
                <!-- Requirements Card -->
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-4">
                        <i data-lucide="check-circle" class="w-5 h-5 text-purple-500"></i>
                        Persyaratan Sistem
                    </h3>

                    <div class="space-y-3">
                        <?php foreach ($requirements as $key => $req): ?>
                            <div
                                class="flex items-center justify-between p-3 rounded-lg <?= $req['status'] ? 'bg-green-50' : 'bg-red-50' ?>">
                                <span class="text-sm <?= $req['status'] ? 'text-green-700' : 'text-red-700' ?>">
                                    <?= htmlspecialchars($req['name']) ?>
                                </span>
                                <?php if ($req['status']): ?>
                                    <i data-lucide="check" class="w-4 h-4 text-green-600"></i>
                                <?php else: ?>
                                    <i data-lucide="x" class="w-4 h-4 text-red-600"></i>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!$requirementsMet): ?>
                        <div class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
                            <strong>⚠️ Perhatian:</strong> Beberapa persyaratan belum terpenuhi. Update tidak dapat
                            dilakukan.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Help Card -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border border-blue-200 p-6">
                    <h3 class="text-lg font-semibold text-blue-800 flex items-center gap-2 mb-4">
                        <i data-lucide="help-circle" class="w-5 h-5"></i>
                        Petunjuk
                    </h3>
                    <ul class="space-y-2 text-sm text-blue-700">
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 mt-1">•</span>
                            Backup otomatis dibuat sebelum update
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 mt-1">•</span>
                            Jika update gagal, gunakan Restore
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 mt-1">•</span>
                            File config.php tidak akan ditimpa
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-500 mt-1">•</span>
                            Folder uploads aman dari perubahan
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Loading Modal -->
<div id="loadingModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-2xl p-8 max-w-sm w-full mx-4 text-center">
        <div class="animate-spin w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4">
        </div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2" id="loadingTitle">Memproses...</h3>
        <p class="text-gray-600 text-sm" id="loadingMessage">Mohon tunggu, jangan tutup halaman ini.</p>
    </div>
</div>

<script>
    const BASEURL = '<?= BASEURL ?>';
    let latestUpdateInfo = null;

    function showLoading(title, message) {
        document.getElementById('loadingTitle').textContent = title;
        document.getElementById('loadingMessage').textContent = message;
        document.getElementById('loadingModal').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingModal').classList.add('hidden');
    }

    async function checkForUpdates() {
        const statusEl = document.getElementById('updateStatus');
        const btnInstall = document.getElementById('btnInstall');
        const changelogSection = document.getElementById('changelogSection');

        statusEl.innerHTML = '<p class="text-blue-600 text-center"><i data-lucide="loader" class="w-5 h-5 animate-spin inline mr-2"></i>Mengecek pembaruan...</p>';
        lucide.createIcons();

        try {
            const response = await fetch(BASEURL + '/update/check');
            const data = await response.json();
            latestUpdateInfo = data;

            if (!data.success) {
                statusEl.innerHTML = `<p class="text-red-600 text-center">❌ ${data.error || 'Gagal mengecek update'}</p>`;
                btnInstall.disabled = true;
                return;
            }

            if (data.has_update) {
                statusEl.innerHTML = `
                <div class="text-center">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-700 rounded-full mb-2">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                        <span class="font-medium">Update Tersedia!</span>
                    </div>
                    <p class="text-gray-600">Versi terbaru: <strong class="text-green-600">v${data.latest}</strong></p>
                    <p class="text-sm text-gray-500">Versi saat ini: v${data.current}</p>
                </div>
            `;
                btnInstall.disabled = false;

                // Show changelog if available
                if (data.changelog) {
                    document.getElementById('changelogContent').innerHTML = data.changelog.replace(/\n/g, '<br>');
                    changelogSection.classList.remove('hidden');
                }
            } else {
                statusEl.innerHTML = `
                <div class="text-center">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 text-blue-700 rounded-full mb-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span class="font-medium">Sudah Terbaru!</span>
                    </div>
                    <p class="text-gray-600">Aplikasi Anda sudah menggunakan versi terbaru <strong>v${data.current}</strong></p>
                </div>
            `;
                btnInstall.disabled = true;
                changelogSection.classList.add('hidden');
            }

            lucide.createIcons();

        } catch (error) {
            statusEl.innerHTML = `<p class="text-red-600 text-center">❌ Error: ${error.message}</p>`;
            btnInstall.disabled = true;
        }
    }

    async function installUpdate() {
        if (!latestUpdateInfo || !latestUpdateInfo.has_update) {
            alert('Tidak ada update yang tersedia.');
            return;
        }

        if (!confirm(`Anda akan mengupdate ke versi ${latestUpdateInfo.latest}. Backup akan dibuat otomatis. Lanjutkan?`)) {
            return;
        }

        showLoading('Menginstall Update...', 'Proses ini mungkin membutuhkan beberapa menit. Jangan tutup halaman ini.');

        try {
            const response = await fetch(BASEURL + '/update/install', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            });
            const data = await response.json();

            hideLoading();

            if (data.success) {
                alert('✅ ' + data.message + '\n\nBackup tersimpan di: ' + data.backup_path);
                location.reload();
            } else {
                alert('❌ Gagal: ' + (data.error || 'Unknown error'));
            }

        } catch (error) {
            hideLoading();
            alert('❌ Error: ' + error.message);
        }
    }

    async function restoreBackup(backupName) {
        if (!confirm(`⚠️ PERINGATAN!\n\nAnda akan me-restore aplikasi dari backup:\n${backupName}\n\nAplikasi saat ini akan ditimpa. Lanjutkan?`)) {
            return;
        }

        showLoading('Restoring Backup...', 'Mengembalikan aplikasi dari backup...');

        try {
            const formData = new FormData();
            formData.append('backup', backupName);

            const response = await fetch(BASEURL + '/update/restore', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            hideLoading();

            if (data.success) {
                alert('✅ ' + data.message);
                location.reload();
            } else {
                alert('❌ Gagal: ' + (data.error || 'Unknown error'));
            }

        } catch (error) {
            hideLoading();
            alert('❌ Error: ' + error.message);
        }
    }

    async function deleteBackup(backupName) {
        if (!confirm(`Hapus backup ${backupName}?`)) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('backup', backupName);

            const response = await fetch(BASEURL + '/update/deleteBackup', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                location.reload();
            } else {
                alert('❌ Gagal: ' + (data.error || 'Unknown error'));
            }

        } catch (error) {
            alert('❌ Error: ' + error.message);
        }
    }

    // Initialize icons
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>