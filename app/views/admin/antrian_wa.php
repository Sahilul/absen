<?php
// Admin - Antrian Pesan WhatsApp 
// Extract data from controller
$stats = $data['stats'] ?? [];
$stats_by_jenis = $data['stats_by_jenis'] ?? [];
$messages = $data['messages'] ?? [];
$filter_status = $data['filter_status'] ?? 'all';
$pagination = $data['pagination'] ?? ['current' => 1, 'total_pages' => 1, 'total' => 0];
$notif_enabled = $data['notif_enabled'] ?? true;
$notif_mode = $data['notif_mode'] ?? 'personal';
$queue_enabled = $data['queue_enabled'] ?? true;
?>

<!-- Page Content -->
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-gray-50 to-gray-100 p-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
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

    <!-- Mode & Settings Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <!-- Mode Antrian -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between gap-4 p-5">
                <div class="flex items-center gap-4">
                    <div
                        class="<?= $queue_enabled ? 'bg-gradient-to-br from-blue-500 to-blue-600' : 'bg-gradient-to-br from-orange-500 to-orange-600' ?> p-3 rounded-xl shadow-lg">
                        <i data-lucide="<?= $queue_enabled ? 'layers' : 'zap' ?>" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Mode Pengiriman</h3>
                        <p class="text-sm text-gray-500">
                            <?php if ($queue_enabled): ?>
                                <span class="text-blue-600 font-semibold">Antrian (Queue)</span> - via Cron
                            <?php else: ?>
                                <span class="text-orange-600 font-semibold">Langsung (Direct)</span> - realtime
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <form action="<?= BASEURL ?>/admin/toggleQueueMode" method="POST" class="inline">
                    <button type="submit"
                        onclick="return confirm('<?= $queue_enabled ? 'Ubah ke mode LANGSUNG? WA dikirim langsung tanpa antrian.' : 'Ubah ke mode ANTRIAN? WA masuk antrian, dikirim via cron.' ?>')"
                        class="px-4 py-2 <?= $queue_enabled ? 'bg-orange-600 hover:bg-orange-700' : 'bg-blue-600 hover:bg-blue-700' ?> text-white rounded-lg text-sm font-medium flex items-center gap-2 transition shadow-md whitespace-nowrap">
                        <i data-lucide="<?= $queue_enabled ? 'zap' : 'layers' ?>" class="w-4 h-4"></i>
                        <?= $queue_enabled ? 'Langsung' : 'Antrian' ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Notifikasi Absensi Status -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between gap-4 p-5">
                <div class="flex items-center gap-4">
                    <div
                        class="<?= $notif_enabled ? 'bg-gradient-to-br from-green-500 to-green-600' : 'bg-gradient-to-br from-gray-400 to-gray-500' ?> p-3 rounded-xl shadow-lg">
                        <i data-lucide="bell" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Notifikasi Absensi</h3>
                        <p class="text-sm text-gray-500">
                            <?php if ($notif_enabled): ?>
                                <span class="text-green-600 font-semibold">Aktif</span> • <?= ucfirst($notif_mode) ?>
                            <?php else: ?>
                                <span class="text-gray-500 font-semibold">Nonaktif</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <a href="<?= BASEURL ?>/admin/pengaturanNotifikasiAbsensi"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium flex items-center gap-2 transition shadow-md whitespace-nowrap">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    Setting
                </a>
            </div>
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
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex gap-4 flex-1">
                <div class="bg-white p-3 rounded-xl shadow-sm h-fit">
                    <i data-lucide="info" class="w-6 h-6 text-indigo-600"></i>
                </div>
                <div>
                    <p class="font-bold text-indigo-800">Cara Kerja Sistem Queue</p>
                    <p class="text-sm text-indigo-700 mt-1">
                        Pesan masuk antrian → Cron job kirim 5 pesan/menit dengan jeda 10 detik → Mencegah blokir WA
                    </p>
                    <p
                        class="text-xs text-indigo-600 mt-3 font-mono bg-white/50 px-3 py-2 rounded-lg inline-block break-all">
                        <?= BASEURL ?>/public/cron_wa_processor.php?token=wa_queue_secret_2026
                    </p>
                </div>
            </div>
            <div class="flex-shrink-0">
                <button onclick="openCronGuide()"
                    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                    Panduan Setup Cron
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Panduan Cron -->
    <div id="cronGuideModal"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden relative">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i data-lucide="clock" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold">Panduan Setup Cron Job</h2>
                            <p class="text-indigo-100 text-sm">Untuk menjalankan antrian WA otomatis</p>
                        </div>
                    </div>
                    <button onclick="closeCronGuide()" class="p-2 hover:bg-white/20 rounded-lg transition">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[60vh]">
                <!-- URL Cron -->
                <div class="bg-gray-50 rounded-xl p-4 mb-6">
                    <p class="text-sm font-semibold text-gray-700 mb-2">🔗 URL Cron Anda:</p>
                    <div class="flex gap-2">
                        <input type="text" id="cronUrl" readonly
                            value="<?= BASEURL ?>/public/cron_wa_processor.php?token=wa_queue_secret_2026"
                            class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-mono text-gray-700">
                        <button onclick="copyCronUrl()"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-1">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                            Copy
                        </button>
                    </div>
                </div>

                <!-- Tabs -->
                <div x-data="{ tab: 'cpanel' }" class="space-y-4">
                    <div class="flex gap-2 border-b border-gray-200">
                        <button @click="tab = 'cpanel'"
                            :class="tab === 'cpanel' ? 'border-indigo-600 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500'"
                            class="px-4 py-3 text-sm font-medium border-b-2 transition flex items-center gap-2">
                            <img src="https://cpanel.net/favicon.ico" class="w-4 h-4"
                                onerror="this.style.display='none'">
                            cPanel
                        </button>
                        <button @click="tab = 'aapanel'"
                            :class="tab === 'aapanel' ? 'border-indigo-600 text-indigo-600 bg-indigo-50' : 'border-transparent text-gray-500'"
                            class="px-4 py-3 text-sm font-medium border-b-2 transition flex items-center gap-2">
                            <span class="text-green-600 font-bold">aa</span>
                            aaPanel
                        </button>
                    </div>

                    <!-- cPanel Guide -->
                    <div x-show="tab === 'cpanel'" class="space-y-4">
                        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
                            <h3 class="font-bold text-orange-800 flex items-center gap-2 mb-3">
                                <span
                                    class="bg-orange-200 text-orange-700 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                Login ke cPanel
                            </h3>
                            <p class="text-sm text-orange-700">Masuk ke cPanel hosting Anda, lalu cari menu
                                <strong>"Cron Jobs"</strong> di bagian Advanced.
                            </p>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                            <h3 class="font-bold text-blue-800 flex items-center gap-2 mb-3">
                                <span
                                    class="bg-blue-200 text-blue-700 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                                Add New Cron Job
                            </h3>
                            <p class="text-sm text-blue-700 mb-3">Setting interval: <strong>Every Minute</strong></p>
                            <div class="grid grid-cols-5 gap-2 text-center text-xs mb-3">
                                <div class="bg-white p-2 rounded border">
                                    <p class="font-bold">*</p>
                                    <p class="text-gray-500">Minute</p>
                                </div>
                                <div class="bg-white p-2 rounded border">
                                    <p class="font-bold">*</p>
                                    <p class="text-gray-500">Hour</p>
                                </div>
                                <div class="bg-white p-2 rounded border">
                                    <p class="font-bold">*</p>
                                    <p class="text-gray-500">Day</p>
                                </div>
                                <div class="bg-white p-2 rounded border">
                                    <p class="font-bold">*</p>
                                    <p class="text-gray-500">Month</p>
                                </div>
                                <div class="bg-white p-2 rounded border">
                                    <p class="font-bold">*</p>
                                    <p class="text-gray-500">Weekday</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                            <h3 class="font-bold text-green-800 flex items-center gap-2 mb-3">
                                <span
                                    class="bg-green-200 text-green-700 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                                Command
                            </h3>
                            <p class="text-sm text-green-700 mb-2">Paste command berikut:</p>
                            <div id="cpanelWget"
                                class="bg-gray-900 text-green-400 p-3 rounded-lg text-xs font-mono overflow-x-auto">wget
                                -q -O /dev/null "<?= BASEURL ?>/public/cron_wa_processor.php?token=wa_queue_secret_2026"
                            </div>
                            <p class="text-xs text-green-600 mt-2">Atau gunakan curl:</p>
                            <div id="cpanelCurl"
                                class="bg-gray-900 text-green-400 p-3 rounded-lg text-xs font-mono overflow-x-auto mt-1">
                                curl -s "<?= BASEURL ?>/public/cron_wa_processor.php?token=wa_queue_secret_2026" >
                                /dev/null 2>&1</div>
                        </div>
                    </div>

                    <!-- aaPanel Guide -->
                    <div x-show="tab === 'aapanel'" class="space-y-4">
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                            <h3 class="font-bold text-green-800 flex items-center gap-2 mb-3">
                                <span
                                    class="bg-green-200 text-green-700 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                Login ke aaPanel
                            </h3>
                            <p class="text-sm text-green-700">Masuk ke aaPanel, lalu klik menu <strong>"Cron"</strong>
                                di sidebar kiri.</p>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                            <h3 class="font-bold text-blue-800 flex items-center gap-2 mb-3">
                                <span
                                    class="bg-blue-200 text-blue-700 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                                Add Task
                            </h3>
                            <div class="space-y-2 text-sm text-blue-700">
                                <p><strong>Type:</strong> Shell Script</p>
                                <p><strong>Name:</strong> WA Queue Processor</p>
                                <p><strong>Period:</strong> Per Minute (N Minutes = 1)</p>
                            </div>
                        </div>

                        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
                            <h3 class="font-bold text-purple-800 flex items-center gap-2 mb-3">
                                <span
                                    class="bg-purple-200 text-purple-700 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                                Script Content
                            </h3>
                            <p class="text-sm text-purple-700 mb-2">Paste script berikut:</p>
                            <div id="aapanelScript"
                                class="bg-gray-900 text-purple-400 p-3 rounded-lg text-xs font-mono overflow-x-auto">
                                wget -q -O /dev/null
                                "<?= BASEURL ?>/public/cron_wa_processor.php?token=wa_queue_secret_2026"</div>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                            <h3 class="font-bold text-yellow-800 flex items-center gap-2 mb-3">
                                <span
                                    class="bg-yellow-200 text-yellow-700 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">4</span>
                                Save & Test
                            </h3>
                            <p class="text-sm text-yellow-700">Klik <strong>Add Task</strong>, lalu klik tombol
                                <strong>Execute</strong> (icon play) untuk test manual.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Warning -->
                <div class="mt-6 bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="flex gap-3">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"></i>
                        <div class="text-sm text-red-700">
                            <p class="font-bold">⚠️ Penting!</p>
                            <ul class="mt-1 list-disc list-inside space-y-1">
                                <li>Ganti <code class="bg-red-100 px-1 rounded">wa_queue_secret_2026</code> dengan token
                                    rahasia Anda sendiri</li>
                                <li>Edit token di file: <code
                                        class="bg-red-100 px-1 rounded">public/cron_wa_processor.php</code> baris 15
                                </li>
                                <li>Jangan share URL cron ini kepada siapapun</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Token Generator -->
                <div class="mt-4 bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="flex-1">
                            <p class="font-bold text-indigo-800 text-sm flex items-center gap-2">
                                <i data-lucide="key" class="w-4 h-4"></i>
                                Generate Token Baru
                            </p>
                            <p class="text-xs text-indigo-600 mt-1">Klik generate, lalu paste ke file
                                cron_wa_processor.php baris 15</p>
                        </div>
                        <button onclick="generateToken()"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium flex items-center gap-2 transition whitespace-nowrap">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                            Generate Token
                        </button>
                    </div>
                    <div id="tokenResult" class="hidden mt-3">
                        <div class="flex gap-2">
                            <input type="text" id="generatedToken" readonly
                                class="flex-1 px-3 py-2 bg-white border border-indigo-300 rounded-lg text-sm font-mono text-indigo-800 font-bold">
                            <button onclick="copyToken()"
                                class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                                <i data-lucide="copy" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <p class="text-xs text-indigo-500 mt-2">
                            📋 Copy token di atas, lalu edit file <code
                                class="bg-indigo-100 px-1 rounded">public/cron_wa_processor.php</code> dan ganti nilai
                            <code class="bg-indigo-100 px-1 rounded">$secretToken</code> dengan token baru.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end">
                <button onclick="closeCronGuide()"
                    class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-sm font-medium transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function openCronGuide() {
            document.getElementById('cronGuideModal').classList.remove('hidden');
            document.getElementById('cronGuideModal').classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeCronGuide() {
            document.getElementById('cronGuideModal').classList.add('hidden');
            document.getElementById('cronGuideModal').classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        function copyCronUrl() {
            const url = document.getElementById('cronUrl');
            url.select();
            document.execCommand('copy');
            alert('URL berhasil disalin!');
        }

        function generateToken() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let result = '';
            for (let i = 0; i < 32; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            const token = 'wa_queue_' + result;
            const baseUrl = '<?= BASEURL ?>/public/cron_wa_processor.php?token=' + token;

            // Update input token
            document.getElementById('generatedToken').value = token;
            document.getElementById('tokenResult').classList.remove('hidden');

            // Update URL Cron input (top)
            document.getElementById('cronUrl').value = baseUrl;

            // Update commands
            const wgetCmd = 'wget -q -O /dev/null "' + baseUrl + '"';
            const curlCmd = 'curl -s "' + baseUrl + '" > /dev/null 2>&1';

            document.getElementById('cpanelWget').textContent = wgetCmd;
            document.getElementById('cpanelCurl').textContent = curlCmd;
            document.getElementById('aapanelScript').textContent = wgetCmd;

            // Auto-save to DB with Feedback
            const saveBtn = document.querySelector('button[onclick="generateToken()"]');
            const originalText = saveBtn.innerHTML; // Use innerHTML to keep icon
            saveBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Menyimpan...';
            saveBtn.disabled = true;

            // Use relative path to avoid Mixed Content issues if BASEURL is http but site is https
            fetch('<?= BASEURL ?>/admin/updateCronToken', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token: token })
            })
                .then(res => res.json())
                .then(data => {
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;

                    if (data.success) {
                        alert('✅ Token berhasil digenerate dan DISIMPAN ke database! Silakan copy URL cron baru.');
                        // Update text instruksi
                        document.querySelector('#tokenResult p').innerHTML = '✅ Token tersimpan otomatis! Anda TIDAK PERLU mengedit file php secara manual. Cukup copy URL di atas ke panel cron.';
                        document.querySelector('#tokenResult p').className = 'text-xs text-green-600 mt-2 font-bold';
                    } else {
                        alert('⚠️ Token ter-generate tapi GAGAL disimpan ke database: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(err => {
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                    console.error('Error saving token:', err);
                    alert('❌ Error Koneksi: Gagal menyimpan token. Cek Console browser. Kemungkinan Mixed Content (HTTP vs HTTPS) atau masalah jaringan.');
                });
        }

        function copyToken() {
            const tokenInput = document.getElementById('generatedToken');
            tokenInput.select();
            document.execCommand('copy');
            alert('Token berhasil disalin! Token juga sudah otomatis tersimpan di database.');
        }

        // Close modal on escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeCronGuide();
        });

        // Close modal on backdrop click
        document.getElementById('cronGuideModal')?.addEventListener('click', function (e) {
            if (e.target === this) closeCronGuide();
        });
    </script>

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
        <!-- Bulk Actions Bar -->
        <form id="bulkForm" action="<?= BASEURL ?>/admin/bulkDeleteWaMessages" method="POST">
            <div id="bulkActions"
                class="hidden bg-gradient-to-r from-red-50 to-red-100 border-b border-red-200 px-6 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-red-700"><span id="selectedCount">0</span> pesan
                        dipilih</span>
                </div>
                <button type="submit" onclick="return confirm('Yakin hapus semua pesan yang dipilih?')"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium flex items-center gap-2 transition">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Hapus Terpilih
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-4 py-4 text-center">
                                <input type="checkbox" id="selectAll"
                                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    onchange="toggleSelectAll(this)">
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                No.
                                WA</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Jenis</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">
                                Pesan</th>
                            <th
                                class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">
                                Waktu</th>
                            <th
                                class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($messages)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
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
                                    <td class="px-4 py-4 text-center">
                                        <input type="checkbox" name="ids[]" value="<?= $msg['id'] ?>" form="bulkForm"
                                            class="msg-checkbox w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            onchange="updateSelectedCount()">
                                    </td>
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
                                            <div class="font-medium"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>
                                            </div>
                                            <?php if ($msg['sent_at']): ?>
                                                <div class="text-green-600 mt-1">Kirim:
                                                    <?= date('H:i:s', strtotime($msg['sent_at'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" onclick='showDetail(<?= json_encode($msg) ?>)'
                                                class="p-2 text-indigo-600 hover:bg-indigo-100 rounded-lg transition"
                                                title="Lihat Detail">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </button>
                                            <?php if ($msg['status'] === 'failed'): ?>
                                                <a href="<?= BASEURL ?>/admin/retryWaMessage/<?= $msg['id'] ?>"
                                                    class="p-2 text-yellow-600 hover:bg-yellow-100 rounded-lg transition"
                                                    title="Retry">
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
        </form>
        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <div
                class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-sm text-gray-600">
                    Menampilkan halaman <strong><?= $pagination['current'] ?></strong> dari
                    <strong><?= $pagination['total_pages'] ?></strong>
                    (<?= $pagination['total'] ?> pesan)
                </p>
                <div class="flex items-center gap-2">
                    <?php if ($pagination['current'] > 1): ?>
                        <a href="?status=<?= $filter_status ?>&page=<?= $pagination['current'] - 1 ?>"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition flex items-center gap-1">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i> Prev
                        </a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $pagination['current'] - 2);
                    $end = min($pagination['total_pages'], $pagination['current'] + 2);
                    for ($i = $start; $i <= $end; $i++):
                        ?>
                        <a href="?status=<?= $filter_status ?>&page=<?= $i ?>"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition <?= $i == $pagination['current'] ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-100' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pagination['current'] < $pagination['total_pages']): ?>
                        <a href="?status=<?= $filter_status ?>&page=<?= $pagination['current'] + 1 ?>"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition flex items-center gap-1">
                            Next <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
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

<!-- Detail Message Modal -->
<div id="detailModal"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i data-lucide="message-circle" class="w-6 h-6"></i>
                    </div>
                    <h2 class="text-xl font-bold">Detail Pesan</h2>
                </div>
                <button onclick="closeDetailModal()" class="p-2 hover:bg-white/20 rounded-lg transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto max-h-[60vh]">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Nomor WA</p>
                        <p id="detailNoWa" class="font-mono text-sm font-bold text-gray-800"></p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Jenis</p>
                        <p id="detailJenis" class="text-sm font-bold text-gray-800"></p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Status</p>
                        <p id="detailStatus" class="text-sm font-bold"></p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Waktu Dibuat</p>
                        <p id="detailCreatedAt" class="text-sm text-gray-800"></p>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Pesan</p>
                    <pre id="detailPesan"
                        class="text-sm text-gray-800 whitespace-pre-wrap bg-white p-4 rounded-lg border border-gray-200 max-h-60 overflow-y-auto"></pre>
                </div>
                <div id="detailErrorContainer" class="hidden bg-red-50 rounded-xl p-4 border border-red-200">
                    <p class="text-xs text-red-600 uppercase font-semibold mb-1">Error Message</p>
                    <p id="detailError" class="text-sm text-red-700"></p>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button onclick="closeDetailModal()"
                class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-sm font-medium transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    // Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Detail Modal Functions
    function showDetail(msg) {
        document.getElementById('detailNoWa').textContent = msg.no_wa || '-';
        document.getElementById('detailJenis').textContent = msg.jenis || '-';
        document.getElementById('detailPesan').textContent = msg.pesan || '-';
        document.getElementById('detailCreatedAt').textContent = msg.created_at || '-';

        // Status with color
        const statusEl = document.getElementById('detailStatus');
        const status = msg.status || 'unknown';
        const statusColors = {
            'pending': 'text-yellow-600',
            'processing': 'text-orange-600',
            'sent': 'text-green-600',
            'failed': 'text-red-600'
        };
        statusEl.className = 'text-sm font-bold ' + (statusColors[status] || 'text-gray-600');
        statusEl.textContent = status.charAt(0).toUpperCase() + status.slice(1);

        // Error Message
        const errorContainer = document.getElementById('detailErrorContainer');
        const errorText = document.getElementById('detailError');

        if (msg.error_message && msg.status === 'failed') {
            errorText.textContent = msg.error_message;
            errorContainer.classList.remove('hidden');
        } else {
            errorContainer.classList.add('hidden');
        }

        document.getElementById('detailModal').classList.remove('hidden');
        document.getElementById('detailModal').classList.add('flex');
    }
    statusEl.textContent = status.charAt(0).toUpperCase() + status.slice(1);

    // Error message
    const errorContainer = document.getElementById('detailErrorContainer');
    const errorEl = document.getElementById('detailError');
    if (msg.error_message) {
        errorContainer.classList.remove('hidden');
        errorEl.textContent = msg.error_message;
    } else {
        errorContainer.classList.add('hidden');
    }

    document.getElementById('detailModal').classList.remove('hidden');
    document.getElementById('detailModal').classList.add('flex');
    document.body.style.overflow = 'hidden';

    // Re-init lucide icons in modal
    if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
        document.getElementById('detailModal').classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    // Bulk Select Functions
    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.msg-checkbox');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.msg-checkbox:checked');
        const count = checkboxes.length;
        document.getElementById('selectedCount').textContent = count;

        const bulkActions = document.getElementById('bulkActions');
        if (count > 0) {
            bulkActions.classList.remove('hidden');
        } else {
            bulkActions.classList.add('hidden');
        }

        // Update select all checkbox
        const allCheckboxes = document.querySelectorAll('.msg-checkbox');
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.checked = count === allCheckboxes.length && count > 0;
            selectAll.indeterminate = count > 0 && count < allCheckboxes.length;
        }
    }

    // Close modal on escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDetailModal();
    });

    // Close modal on backdrop click
    document.getElementById('detailModal')?.addEventListener('click', function (e) {
        if (e.target === this) closeDetailModal();
    });
</script>