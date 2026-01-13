<?php
// Admin - Antrian Pesan WhatsApp 
// Extract data from controller
$stats = $data['stats'] ?? [];
$stats_by_jenis = $data['stats_by_jenis'] ?? [];
$messages = $data['messages'] ?? [];
$filter_status = $data['filter_status'] ?? 'all';
?>

<!-- Page Content -->
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-gray-50 to-gray-100 p-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="bg-gradient-to-br from-green-500 to-green-600 p-4 rounded-xl shadow-lg">
                <i data-lucide="message-circle" class="w-7 h-7 text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Antrian Pesan WhatsApp</h1>
                <p class="text-sm text-gray-500 mt-1">Monitor dan kelola antrian pesan WA</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="<?= BASEURL ?>/admin/prosesAntrianWa"
                class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-xl text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all"
                onclick="return confirm('Proses antrian sekarang? (Maks 5 pesan)')">
                <i data-lucide="play" class="w-4 h-4"></i>
                Proses Manual
            </a>
            <?php if (($stats['failed'] ?? 0) > 0): ?>
                <a href="<?= BASEURL ?>/admin/retryAllWaMessages"
                    class="px-5 py-2.5 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white rounded-xl text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all"
                    onclick="return confirm('Retry semua pesan gagal?')">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Retry Gagal
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Flasher -->
    <?php Flasher::flash(); ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <!-- Total 24 Jam -->
        <div
            class="group bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-blue-200">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="bg-gradient-to-br from-blue-500 to-blue-600 p-3 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="mail" class="w-6 h-6 text-white"></i>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-500 mb-1">Total 24 Jam</p>
                    <p
                        class="text-3xl font-extrabold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
                        <?= $stats['total'] ?? 0 ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div
            class="group bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-yellow-200">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="bg-gradient-to-br from-yellow-500 to-yellow-600 p-3 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="clock" class="w-6 h-6 text-white"></i>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-500 mb-1">Pending</p>
                    <p
                        class="text-3xl font-extrabold bg-gradient-to-r from-yellow-600 to-yellow-800 bg-clip-text text-transparent">
                        <?= $stats['pending'] ?? 0 ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Processing -->
        <div
            class="group bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-orange-200">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="bg-gradient-to-br from-orange-500 to-orange-600 p-3 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="loader" class="w-6 h-6 text-white"></i>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-500 mb-1">Processing</p>
                    <p
                        class="text-3xl font-extrabold bg-gradient-to-r from-orange-600 to-orange-800 bg-clip-text text-transparent">
                        <?= $stats['processing'] ?? 0 ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Terkirim -->
        <div
            class="group bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-green-200">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="bg-gradient-to-br from-green-500 to-green-600 p-3 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="check-circle" class="w-6 h-6 text-white"></i>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-500 mb-1">Terkirim</p>
                    <p
                        class="text-3xl font-extrabold bg-gradient-to-r from-green-600 to-green-800 bg-clip-text text-transparent">
                        <?= $stats['sent'] ?? 0 ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Gagal -->
        <div
            class="group bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-red-200">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="bg-gradient-to-br from-red-500 to-red-600 p-3 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <i data-lucide="x-circle" class="w-6 h-6 text-white"></i>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-500 mb-1">Gagal</p>
                    <p
                        class="text-3xl font-extrabold bg-gradient-to-r from-red-600 to-red-800 bg-clip-text text-transparent">
                        <?= $stats['failed'] ?? 0 ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-200 rounded-2xl p-6 mb-8">
        <div class="flex gap-4">
            <div class="bg-white p-3 rounded-xl shadow-sm">
                <i data-lucide="info" class="w-6 h-6 text-indigo-600"></i>
            </div>
            <div>
                <p class="font-bold text-indigo-800">Cara Kerja Sistem Queue</p>
                <p class="text-sm text-indigo-700 mt-1">
                    Pesan masuk antrian → Cron job kirim 5 pesan/menit dengan jeda 10 detik → Mencegah blokir WA
                </p>
                <p class="text-xs text-indigo-600 mt-3 font-mono bg-white/50 px-3 py-2 rounded-lg inline-block">
                    Cron: <?= BASEURL ?>/public/cron_wa_processor.php?token=wa_queue_secret_2026
                </p>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
        <a href="?status=all"
            class="px-5 py-2.5 rounded-xl text-sm font-medium transition whitespace-nowrap <?= $filter_status === 'all' ? 'bg-gradient-to-r from-indigo-600 to-indigo-700 text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' ?>">
            Semua
        </a>
        <a href="?status=pending"
            class="px-5 py-2.5 rounded-xl text-sm font-medium transition whitespace-nowrap <?= $filter_status === 'pending' ? 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' ?>">
            Pending (<?= $stats['pending'] ?? 0 ?>)
        </a>
        <a href="?status=sent"
            class="px-5 py-2.5 rounded-xl text-sm font-medium transition whitespace-nowrap <?= $filter_status === 'sent' ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' ?>">
            Terkirim (<?= $stats['sent'] ?? 0 ?>)
        </a>
        <a href="?status=failed"
            class="px-5 py-2.5 rounded-xl text-sm font-medium transition whitespace-nowrap <?= $filter_status === 'failed' ? 'bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' ?>">
            Gagal (<?= $stats['failed'] ?? 0 ?>)
        </a>
    </div>

    <!-- Messages Table -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No.
                            WA</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Jenis</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">
                            Pesan</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">
                            Waktu</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div
                                    class="bg-gray-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
                                </div>
                                <p class="text-gray-500 font-medium">Tidak ada pesan dalam antrian</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span
                                        class="font-mono text-xs bg-gray-100 px-2 py-1 rounded"><?= htmlspecialchars($msg['no_wa']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-xs rounded-full font-medium
                                <?php
                                switch ($msg['jenis']) {
                                    case 'absensi':
                                        echo 'bg-blue-100 text-blue-800';
                                        break;
                                    case 'pembayaran':
                                        echo 'bg-green-100 text-green-800';
                                        break;
                                    case 'pesan_bulk':
                                    case 'pesan_admin':
                                        echo 'bg-purple-100 text-purple-800';
                                        break;
                                    default:
                                        echo 'bg-gray-100 text-gray-800';
                                }
                                ?>">
                                        <?= ucfirst($msg['jenis']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 hidden md:table-cell">
                                    <p class="text-xs text-gray-600 truncate max-w-xs"
                                        title="<?= htmlspecialchars($msg['pesan']) ?>">
                                        <?= htmlspecialchars(mb_substr($msg['pesan'], 0, 80)) ?>...
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php
                                    switch ($msg['status']) {
                                        case 'pending':
                                            echo '<span class="px-3 py-1.5 text-xs rounded-full font-semibold bg-yellow-100 text-yellow-800"><i data-lucide="clock" class="w-3 h-3 inline mr-1"></i>Pending</span>';
                                            break;
                                        case 'processing':
                                            echo '<span class="px-3 py-1.5 text-xs rounded-full font-semibold bg-orange-100 text-orange-800"><i data-lucide="loader" class="w-3 h-3 inline mr-1 animate-spin"></i>Proses</span>';
                                            break;
                                        case 'sent':
                                            echo '<span class="px-3 py-1.5 text-xs rounded-full font-semibold bg-green-100 text-green-800"><i data-lucide="check" class="w-3 h-3 inline mr-1"></i>Terkirim</span>';
                                            break;
                                        case 'failed':
                                            echo '<span class="px-3 py-1.5 text-xs rounded-full font-semibold bg-red-100 text-red-800" title="' . htmlspecialchars($msg['error_message'] ?? '') . '"><i data-lucide="x" class="w-3 h-3 inline mr-1"></i>Gagal</span>';
                                            break;
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 hidden lg:table-cell">
                                    <div class="text-xs text-gray-500">
                                        <div class="font-medium"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></div>
                                        <?php if ($msg['sent_at']): ?>
                                            <div class="text-green-600 mt-1">Kirim: <?= date('H:i:s', strtotime($msg['sent_at'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <?php if ($msg['status'] === 'failed'): ?>
                                            <a href="<?= BASEURL ?>/admin/retryWaMessage/<?= $msg['id'] ?>"
                                                class="p-2 text-yellow-600 hover:bg-yellow-100 rounded-lg transition" title="Retry">
                                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= BASEURL ?>/admin/hapusWaMessage/<?= $msg['id'] ?>"
                                            class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition" title="Hapus"
                                            onclick="return confirm('Hapus pesan ini?')">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stats by Jenis -->
    <?php if (!empty($stats_by_jenis)): ?>
        <div class="mt-8 bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="bar-chart-3" class="w-5 h-5 text-indigo-600"></i>
                Statistik 7 Hari Terakhir
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($stats_by_jenis as $st): ?>
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4 border border-gray-200">
                        <p class="text-xs text-gray-500 uppercase font-semibold"><?= ucfirst($st['jenis']) ?></p>
                        <p class="text-2xl font-bold text-gray-800 mt-1"><?= $st['total'] ?> <span
                                class="text-sm font-normal text-gray-500">pesan</span></p>
                        <div class="flex gap-3 mt-2 text-xs">
                            <span class="text-green-600 font-medium"><?= $st['sent'] ?> terkirim</span>
                            <span class="text-red-600 font-medium"><?= $st['failed'] ?> gagal</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</main>

<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>