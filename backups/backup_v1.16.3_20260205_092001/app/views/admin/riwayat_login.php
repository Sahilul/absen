<?php
// File: app/views/admin/riwayat_login.php
extract($data);

$logs = $logs ?? [];
$stats = $stats ?? ['total' => 0, 'success' => 0, 'failed' => 0, 'today' => 0];
$pagination = $pagination ?? ['current_page' => 1, 'total_pages' => 1, 'total_records' => 0, 'limit' => 25];
$filter = $filter ?? ['search' => '', 'status' => 'all', 'sort' => 'login_at', 'order' => 'DESC'];

$baseUrl = BASEURL . '/admin/riwayatLogin';

// Helper function untuk generate sort URL
function getSortUrl($column, $currentSort, $currentOrder, $baseUrl, $filter)
{
    $newOrder = ($currentSort === $column && $currentOrder === 'DESC') ? 'ASC' : 'DESC';
    $params = http_build_query([
        'search' => $filter['search'],
        'status' => $filter['status'],
        'sort' => $column,
        'order' => $newOrder,
        'limit' => $_GET['limit'] ?? 25
    ]);
    return $baseUrl . '?' . $params;
}

// Helper function untuk format user agent menjadi browser singkat
function formatUserAgent($ua)
{
    if (stripos($ua, 'Chrome') !== false && stripos($ua, 'Edg') === false)
        return 'Chrome';
    if (stripos($ua, 'Firefox') !== false)
        return 'Firefox';
    if (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false)
        return 'Safari';
    if (stripos($ua, 'Edg') !== false)
        return 'Edge';
    if (stripos($ua, 'MSIE') !== false || stripos($ua, 'Trident') !== false)
        return 'IE';
    if (stripos($ua, 'curl') !== false)
        return 'cURL';
    if (stripos($ua, 'wget') !== false)
        return 'Wget';
    return 'Other';
}
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <main class="flex-1 p-4 lg:p-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg">
                    <i data-lucide="shield-check" class="w-6 h-6 text-white"></i>
                </div>
                Riwayat Login
            </h1>
            <p class="text-gray-500 mt-1">Monitor aktivitas login pengguna sistem</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 rounded-xl">
                        <i data-lucide="users" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">
                            <?= number_format($stats['total'] ?? 0) ?>
                        </p>
                        <p class="text-xs text-gray-500">Total Login</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-green-100 rounded-xl">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-600">
                            <?= number_format($stats['success'] ?? 0) ?>
                        </p>
                        <p class="text-xs text-gray-500">Sukses</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-100 rounded-xl">
                        <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-600">
                            <?= number_format($stats['failed'] ?? 0) ?>
                        </p>
                        <p class="text-xs text-gray-500">Gagal</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-purple-100 rounded-xl">
                        <i data-lucide="calendar" class="w-5 h-5 text-purple-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-purple-600">
                            <?= number_format($stats['today'] ?? 0) ?>
                        </p>
                        <p class="text-xs text-gray-500">Hari Ini</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
            <form method="GET" action="<?= $baseUrl ?>" class="flex flex-col lg:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1 relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($filter['search']) ?>"
                        placeholder="Cari nama, username, atau IP..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Status Filter -->
                <div class="flex gap-2">
                    <a href="<?= $baseUrl ?>?search=<?= urlencode($filter['search']) ?>&status=all&limit=<?= $pagination['limit'] ?>"
                        class="px-4 py-2.5 rounded-xl text-sm font-medium transition <?= $filter['status'] === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                        Semua
                    </a>
                    <a href="<?= $baseUrl ?>?search=<?= urlencode($filter['search']) ?>&status=success&limit=<?= $pagination['limit'] ?>"
                        class="px-4 py-2.5 rounded-xl text-sm font-medium transition <?= $filter['status'] === 'success' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                        Sukses
                    </a>
                    <a href="<?= $baseUrl ?>?search=<?= urlencode($filter['search']) ?>&status=failed&limit=<?= $pagination['limit'] ?>"
                        class="px-4 py-2.5 rounded-xl text-sm font-medium transition <?= $filter['status'] === 'failed' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                        Gagal
                    </a>
                </div>

                <!-- Rows per page -->
                <select name="limit" onchange="this.form.submit()"
                    class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="10" <?= $pagination['limit'] == 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= $pagination['limit'] == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $pagination['limit'] == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $pagination['limit'] == 100 ? 'selected' : '' ?>>100</option>
                </select>

                <input type="hidden" name="status" value="<?= htmlspecialchars($filter['status']) ?>">
                <button type="submit"
                    class="px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                    <i data-lucide="search" class="w-4 h-4 inline"></i> Cari
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gradient-to-r from-slate-50 to-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">No</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                <a href="<?= getSortUrl('nama_lengkap', $filter['sort'], $filter['order'], $baseUrl, $filter) ?>"
                                    class="flex items-center gap-1 hover:text-indigo-600">
                                    Nama
                                    <?php if ($filter['sort'] === 'nama_lengkap'): ?>
                                        <i data-lucide="<?= $filter['order'] === 'ASC' ? 'chevron-up' : 'chevron-down' ?>"
                                            class="w-4 h-4"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                <a href="<?= getSortUrl('username', $filter['sort'], $filter['order'], $baseUrl, $filter) ?>"
                                    class="flex items-center gap-1 hover:text-indigo-600">
                                    Username
                                    <?php if ($filter['sort'] === 'username'): ?>
                                        <i data-lucide="<?= $filter['order'] === 'ASC' ? 'chevron-up' : 'chevron-down' ?>"
                                            class="w-4 h-4"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Role</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                <a href="<?= getSortUrl('ip_address', $filter['sort'], $filter['order'], $baseUrl, $filter) ?>"
                                    class="flex items-center gap-1 hover:text-indigo-600">
                                    IP Address
                                    <?php if ($filter['sort'] === 'ip_address'): ?>
                                        <i data-lucide="<?= $filter['order'] === 'ASC' ? 'chevron-up' : 'chevron-down' ?>"
                                            class="w-4 h-4"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Browser</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                <a href="<?= getSortUrl('status', $filter['sort'], $filter['order'], $baseUrl, $filter) ?>"
                                    class="flex items-center gap-1 hover:text-indigo-600">
                                    Status
                                    <?php if ($filter['sort'] === 'status'): ?>
                                        <i data-lucide="<?= $filter['order'] === 'ASC' ? 'chevron-up' : 'chevron-down' ?>"
                                            class="w-4 h-4"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                <a href="<?= getSortUrl('login_at', $filter['sort'], $filter['order'], $baseUrl, $filter) ?>"
                                    class="flex items-center gap-1 hover:text-indigo-600">
                                    Waktu Login
                                    <?php if ($filter['sort'] === 'login_at'): ?>
                                        <i data-lucide="<?= $filter['order'] === 'ASC' ? 'chevron-up' : 'chevron-down' ?>"
                                            class="w-4 h-4"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                                    <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                                    <p>Belum ada data riwayat login</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = ($pagination['current_page'] - 1) * $pagination['limit'] + 1; ?>
                            <?php foreach ($logs as $log): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-gray-600">
                                        <?= $no++ ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-gray-800">
                                            <?= htmlspecialchars($log['nama_lengkap'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 font-mono text-xs">
                                        <?= htmlspecialchars($log['username']) ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php
                                        $roleColor = match ($log['role'] ?? '') {
                                            'admin' => 'bg-purple-100 text-purple-700',
                                            'guru' => 'bg-blue-100 text-blue-700',
                                            'siswa' => 'bg-green-100 text-green-700',
                                            'wali_kelas' => 'bg-amber-100 text-amber-700',
                                            'kepala_madrasah' => 'bg-rose-100 text-rose-700',
                                            default => 'bg-gray-100 text-gray-700'
                                        };
                                        ?>
                                        <span class="px-2 py-1 rounded-lg text-xs font-medium <?= $roleColor ?>">
                                            <?= htmlspecialchars(ucfirst($log['role'] ?? '-')) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                        <?= htmlspecialchars($log['ip_address']) ?>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 text-xs">
                                        <?= formatUserAgent($log['user_agent'] ?? '') ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if ($log['status'] === 'success'): ?>
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-green-100 text-green-700">
                                                <i data-lucide="check-circle" class="w-3 h-3"></i> Sukses
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-red-100 text-red-700"
                                                title="<?= htmlspecialchars($log['failure_reason'] ?? '') ?>">
                                                <i data-lucide="x-circle" class="w-3 h-3"></i> Gagal
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 text-xs">
                                        <?= date('d M Y, H:i', strtotime($log['login_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div
                    class="px-4 py-3 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-sm text-gray-500">
                        Menampilkan
                        <?= number_format(($pagination['current_page'] - 1) * $pagination['limit'] + 1) ?>-
                        <?= number_format(min($pagination['current_page'] * $pagination['limit'], $pagination['total_records'])) ?>
                        dari
                        <?= number_format($pagination['total_records']) ?> data
                    </p>
                    <div class="flex gap-1">
                        <?php
                        $queryParams = http_build_query([
                            'search' => $filter['search'],
                            'status' => $filter['status'],
                            'sort' => $filter['sort'],
                            'order' => $filter['order'],
                            'limit' => $pagination['limit']
                        ]);
                        ?>
                        <!-- Prev -->
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="<?= $baseUrl ?>?<?= $queryParams ?>&page=<?= $pagination['current_page'] - 1 ?>"
                                class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 text-sm">
                                <i data-lucide="chevron-left" class="w-4 h-4 inline"></i>
                            </a>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                        $startPage = max(1, $pagination['current_page'] - 2);
                        $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                        ?>
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <a href="<?= $baseUrl ?>?<?= $queryParams ?>&page=<?= $i ?>"
                                class="px-3 py-1.5 rounded-lg text-sm <?= $i === $pagination['current_page'] ? 'bg-indigo-600 text-white' : 'border border-gray-200 text-gray-600 hover:bg-gray-100' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <!-- Next -->
                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <a href="<?= $baseUrl ?>?<?= $queryParams ?>&page=<?= $pagination['current_page'] + 1 ?>"
                                class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 text-sm">
                                <i data-lucide="chevron-right" class="w-4 h-4 inline"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info Note -->
        <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
            <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
            <strong>Info:</strong> Data riwayat login disimpan selama 90 hari dan akan dihapus otomatis setelah itu.
        </div>
    </main>
</div>

<script>
    lucide.createIcons();
</script>