<?php
/**
 * Update Notification Modal Component
 * Include this in admin sidebar or header
 */

// Check for updates (cached untuk 1 jam)
$updateCheck = null;
$showUpdateModal = false;

// Jangan tampilkan modal di halaman update untuk mencegah loop
$currentUrl = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($currentUrl, '/admin/update') !== false || strpos($currentUrl, '/update') !== false) {
    return;
}

if (!isset($_SESSION['update_check_time']) || (time() - $_SESSION['update_check_time']) > 3600) {
    try {
        $updater = new Updater();
        $updateCheck = $updater->checkForUpdates();
        $_SESSION['update_check'] = $updateCheck;
        $_SESSION['update_check_time'] = time();
    } catch (Exception $e) {
        $updateCheck = null;
    }
} else {
    $updateCheck = $_SESSION['update_check'] ?? null;
}

// Check if dismissed
$dismissedVersion = $_SESSION['update_dismissed_version'] ?? null;
if ($updateCheck && $updateCheck['has_update'] && $updateCheck['latest'] !== $dismissedVersion) {
    $showUpdateModal = true;
}
?>

<?php if ($showUpdateModal && $updateCheck): ?>
    <!-- Update Available Modal -->
    <div id="updateModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 overflow-hidden animate-fade-in">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i data-lucide="download-cloud" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">Update Tersedia!</h3>
                            <p class="text-sm text-white/80">v<?= htmlspecialchars($updateCheck['current']) ?> →
                                v<?= htmlspecialchars($updateCheck['latest']) ?></p>
                        </div>
                    </div>
                    <button onclick="dismissUpdate('<?= htmlspecialchars($updateCheck['latest']) ?>')"
                        class="text-white/80 hover:text-white p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Changelog -->
            <div class="px-6 py-4 max-h-80 overflow-y-auto">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <i data-lucide="list" class="w-4 h-4 text-blue-600"></i>
                    Changelog v<?= htmlspecialchars($updateCheck['latest']) ?>
                </h4>

                <?php
                // Prioritas: changelog_data dari Updater (GitHub), lalu local file
                $latestChangelog = $updateCheck['changelog_data'] ?? null;

                if (!$latestChangelog) {
                    // Fallback: cek local changelog.json
                    $changelogPath = APPROOT . '/changelog.json';
                    if (file_exists($changelogPath)) {
                        $changelogData = json_decode(file_get_contents($changelogPath), true);
                        foreach ($changelogData['versions'] ?? [] as $ver) {
                            if ($ver['version'] === $updateCheck['latest']) {
                                $latestChangelog = $ver;
                                break;
                            }
                        }
                    }
                }
                ?>

                <?php if ($latestChangelog && !empty($latestChangelog['changes'])): ?>
                    <ul class="space-y-2">
                        <?php foreach ($latestChangelog['changes'] as $change): ?>
                            <li class="flex items-start gap-2">
                                <?php
                                $icon = 'circle';
                                $color = 'text-gray-500';
                                if ($change['type'] === 'feature') {
                                    $icon = 'plus-circle';
                                    $color = 'text-green-600';
                                } elseif ($change['type'] === 'fix') {
                                    $icon = 'wrench';
                                    $color = 'text-orange-600';
                                } elseif ($change['type'] === 'improvement') {
                                    $icon = 'arrow-up-circle';
                                    $color = 'text-blue-600';
                                }
                                ?>
                                <i data-lucide="<?= $icon ?>" class="w-4 h-4 <?= $color ?> mt-0.5 flex-shrink-0"></i>
                                <span class="text-sm text-gray-700"><?= htmlspecialchars($change['description']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-sm text-gray-600">
                        <?= htmlspecialchars($updateCheck['changelog'] ?: 'Perbaikan dan peningkatan performa.') ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 flex justify-between gap-3">
                <button onclick="dismissUpdate('<?= htmlspecialchars($updateCheck['latest']) ?>')"
                    class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                    Nanti Saja
                </button>
                <a href="<?= BASEURL ?>/update"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2 transition">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Update Sekarang
                </a>
            </div>
        </div>
    </div>

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.2s ease-out;
        }
    </style>

    <script>
        function dismissUpdate(version) {
            // Save to session via AJAX
            fetch('<?= BASEURL ?>/update/dismissNotification?version=' + encodeURIComponent(version))
                .catch(() => { });

            // Hide modal
            document.getElementById('updateModal').style.display = 'none';
        }
    </script>
<?php endif; ?>