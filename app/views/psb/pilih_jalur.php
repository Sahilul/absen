<?php
// File: app/views/psb/pilih_jalur.php
// Pilih jalur pendaftaran

$periode = $data['periode'] ?? [];
$jalur = $data['jalur'] ?? [];
$namaSekolah = getPengaturanAplikasi()['nama_aplikasi'] ?? 'Smart Absensi';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Jalur - PSB <?= $namaSekolah; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .gradient-primary {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    <header class="gradient-primary text-white shadow-lg">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="<?= BASEURL; ?>/psb/dashboardPendaftar" class="p-2 hover:bg-white/20 rounded-lg">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    </a>
                    <div>
                        <h1 class="font-bold text-lg">Pilih Jalur Pendaftaran</h1>
                        <p class="text-white/80 text-sm"><?= htmlspecialchars($periode['nama_lembaga'] ?? ''); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="font-bold text-lg text-gray-800 mb-2"><?= htmlspecialchars($periode['nama_periode'] ?? ''); ?>
            </h2>
            <p class="text-gray-500 text-sm">
                <i data-lucide="calendar" class="w-4 h-4 inline"></i>
                <?= date('d M Y', strtotime($periode['tanggal_buka'])); ?> -
                <?= date('d M Y', strtotime($periode['tanggal_tutup'])); ?>
            </p>
        </div>

        <h3 class="text-lg font-bold text-gray-800 mb-4">Pilih Jalur:</h3>

        <form action="<?= BASEURL; ?>/psb/mulaiPendaftaran" method="POST">
            <input type="hidden" name="id_periode" value="<?= $periode['id_periode']; ?>">

            <div class="grid gap-4 mb-6">
                <?php foreach ($jalur as $j):
                    $kuota = $j['kuota'] ?? 0;
                    $terisi = $j['terisi'] ?? 0;
                    $sisa = max(0, $kuota - $terisi);
                    $isPenuh = $sisa <= 0;
                    ?>
                    <label class="block <?= $isPenuh ? 'opacity-60 cursor-not-allowed' : ''; ?>">
                        <input type="radio" name="id_jalur" value="<?= $j['id_jalur']; ?>" class="peer hidden" required
                            <?= $isPenuh ? 'disabled' : ''; ?>>
                        <div
                            class="bg-white rounded-xl border-2 border-gray-200 p-5 cursor-pointer transition-all <?= $isPenuh ? '' : 'peer-checked:border-sky-500 peer-checked:bg-sky-50 hover:border-sky-300'; ?>">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-sky-100 rounded-lg flex items-center justify-center">
                                    <i data-lucide="route" class="w-6 h-6 text-sky-600"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-bold text-gray-800"><?= htmlspecialchars($j['nama_jalur']); ?></h4>
                                        <?php if ($isPenuh): ?>
                                            <span
                                                class="bg-red-100 text-red-600 px-2 py-0.5 rounded-full text-xs font-medium">Penuh</span>
                                        <?php else: ?>
                                            <span
                                                class="bg-green-100 text-green-600 px-2 py-0.5 rounded-full text-xs font-medium">Tersedia</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-gray-500 text-sm mt-1"><?= htmlspecialchars($j['deskripsi'] ?? '-'); ?>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-2">
                                        <i data-lucide="users" class="w-3 h-3 inline"></i>
                                        Kuota: <?= $sisa; ?> / <?= $kuota; ?> tersedia
                                    </p>
                                </div>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit"
                class="w-full py-3 bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all">
                <i data-lucide="arrow-right" class="w-5 h-5 inline mr-2"></i>
                Lanjutkan Pendaftaran
            </button>
        </form>
    </main>

    <script>lucide.createIcons();</script>
</body>

</html>