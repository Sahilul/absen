<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Pendaftaran</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .gradient-hero {
            background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);
        }
    </style>
</head>

<body class="bg-gray-50">

    <!-- Header -->
    <header class="gradient-hero text-white py-10 px-4">
        <div class="max-w-xl mx-auto text-center">
            <a href="<?= BASEURL; ?>/psb" class="inline-flex items-center gap-2 text-white/80 hover:text-white mb-4">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali
            </a>
            <h1 class="text-2xl font-bold">Cek Status Pendaftaran</h1>
            <p class="text-blue-100">Masukkan nomor pendaftaran untuk melihat status</p>
        </div>
    </header>

    <main class="max-w-xl mx-auto px-4 py-10">
        <!-- Search Form -->
        <form method="GET" action="" class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Pendaftaran</label>
            <div class="flex gap-3">
                <input type="text" name="no" value="<?= htmlspecialchars($_GET['no'] ?? ''); ?>"
                    placeholder="Contoh: SMP2024-0001" required
                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 font-mono">
                <button type="submit"
                    class="bg-sky-500 hover:bg-sky-600 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition-colors">
                    <i data-lucide="search" class="w-5 h-5"></i>
                    Cari
                </button>
            </div>
        </form>

        <!-- Result -->
        <?php if (isset($_GET['no'])): ?>
            <?php if ($data['pendaftar']):
                $p = $data['pendaftar'];
                $statusInfo = [
                    'pending' => ['color' => 'amber', 'icon' => 'clock', 'text' => 'Menunggu Verifikasi'],
                    'verifikasi' => ['color' => 'blue', 'icon' => 'check-circle', 'text' => 'Terverifikasi'],
                    'revisi' => ['color' => 'orange', 'icon' => 'alert-circle', 'text' => 'Perlu Revisi'],
                    'diterima' => ['color' => 'green', 'icon' => 'check-circle-2', 'text' => 'Diterima'],
                    'ditolak' => ['color' => 'red', 'icon' => 'x-circle', 'text' => 'Tidak Diterima'],
                    'daftar_ulang' => ['color' => 'indigo', 'icon' => 'refresh-cw', 'text' => 'Daftar Ulang'],
                    'selesai' => ['color' => 'emerald', 'icon' => 'badge-check', 'text' => 'Selesai']
                ];
                $status = $statusInfo[$p['status']] ?? ['color' => 'gray', 'icon' => 'help-circle', 'text' => ucfirst($p['status'])];
                ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <!-- Status Banner -->
                    <div class="bg-<?= $status['color']; ?>-100 border-b border-<?= $status['color']; ?>-200 p-6 text-center">
                        <i data-lucide="<?= $status['icon']; ?>"
                            class="w-12 h-12 mx-auto text-<?= $status['color']; ?>-600 mb-2"></i>
                        <h2 class="text-xl font-bold text-<?= $status['color']; ?>-800"><?= $status['text']; ?></h2>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-400 uppercase">Nomor Pendaftaran</p>
                                <p class="font-mono font-bold text-gray-800"><?= htmlspecialchars($p['no_pendaftaran']); ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase">Tanggal Daftar</p>
                                <p class="font-semibold text-gray-800"><?= date('d/m/Y', strtotime($p['tanggal_daftar'])); ?>
                                </p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs text-gray-400 uppercase">Nama Lengkap</p>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($p['nama_lengkap']); ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase">Lembaga</p>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($p['nama_lembaga']); ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase">Jalur</p>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($p['nama_jalur']); ?></p>
                            </div>
                        </div>

                        <?php if (!empty($p['catatan_admin'])): ?>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs text-gray-400 uppercase mb-1">Catatan dari Panitia</p>
                                <p class="text-gray-700"><?= nl2br(htmlspecialchars($p['catatan_admin'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <a href="<?= BASEURL; ?>/psb/cetakBukti/<?= $p['no_pendaftaran']; ?>" target="_blank"
                            class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-lg font-medium text-center transition-colors">
                            <i data-lucide="printer" class="w-4 h-4 inline mr-2"></i>
                            Cetak Bukti Pendaftaran
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-xl shadow-lg p-10 text-center">
                    <div class="bg-red-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="x-circle" class="w-8 h-8 text-red-500"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Data Tidak Ditemukan</h2>
                    <p class="text-gray-500">Nomor pendaftaran "<?= htmlspecialchars($_GET['no']); ?>" tidak ditemukan dalam
                        sistem.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <script>lucide.createIcons();</script>
</body>

</html>