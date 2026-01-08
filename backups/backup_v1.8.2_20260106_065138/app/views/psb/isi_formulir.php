<?php
// File: app/views/psb/isi_formulir.php
// Form per bagian (menu-based)

$pendaftar = $data['pendaftar'] ?? [];
$section = $data['section'] ?? 'data_diri';
$dokumen = $data['dokumen'] ?? [];
$namaSekolah = getPengaturanAplikasi()['nama_aplikasi'] ?? 'Smart Absensi';
$p = $pendaftar;

$sectionInfo = [
    'data_diri' => ['title' => 'Data Diri', 'icon' => 'user', 'color' => 'sky'],
    'alamat' => ['title' => 'Alamat', 'icon' => 'map-pin', 'color' => 'emerald'],
    'sekolah_asal' => ['title' => 'Sekolah Asal', 'icon' => 'building', 'color' => 'violet'],
    'data_ayah' => ['title' => 'Data Ayah', 'icon' => 'user-check', 'color' => 'blue'],
    'data_ibu' => ['title' => 'Data Ibu', 'icon' => 'heart', 'color' => 'pink'],
    'data_wali' => ['title' => 'Data Wali', 'icon' => 'users', 'color' => 'amber'],
    'dokumen' => ['title' => 'Dokumen', 'icon' => 'file-text', 'color' => 'orange'],
];
$info = $sectionInfo[$section];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $info['title']; ?> - PSB <?= $namaSekolah; ?></title>
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
                    <a href="<?= BASEURL; ?>/psb/dashboardPendaftar"
                        class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    </a>
                    <div>
                        <p class="text-white/70 text-sm">No. Pendaftaran</p>
                        <p class="font-bold"><?= $pendaftar['no_pendaftaran'] ?? '-'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-6">
        <?php if (isset($_SESSION['flash'])): ?>
            <div
                class="mb-6 p-4 rounded-xl <?= $_SESSION['flash']['type'] == 'error' ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-green-50 text-green-600 border border-green-200'; ?>">
                <?= $_SESSION['flash']['message']; ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border">
            <div class="p-5 border-b flex items-center gap-3">
                <div class="bg-<?= $info['color']; ?>-100 p-2 rounded-lg">
                    <i data-lucide="<?= $info['icon']; ?>" class="w-5 h-5 text-<?= $info['color']; ?>-600"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800"><?= $info['title']; ?></h2>
                    <p class="text-gray-500 text-sm">Lengkapi data berikut</p>
                </div>
            </div>

            <?php if ($section == 'dokumen'): ?>
                <!-- Dokumen uses AJAX upload, no form needed -->
                <div class="p-6">
                    <?php include __DIR__ . '/form_sections/dokumen.php'; ?>

                    <div class="flex items-center justify-between mt-8 pt-6 border-t">
                        <a href="<?= BASEURL; ?>/psb/dashboardPendaftar"
                            class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            Kembali
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <form action="<?= BASEURL; ?>/psb/simpanIsiFormulir/<?= $pendaftar['id_pendaftar']; ?>" method="POST"
                    enctype="multipart/form-data" class="p-6">
                    <input type="hidden" name="section" value="<?= $section; ?>">

                    <?php if ($section == 'data_diri'): ?>
                        <?php include __DIR__ . '/form_sections/data_diri.php'; ?>
                    <?php elseif ($section == 'alamat'): ?>
                        <?php include __DIR__ . '/form_sections/alamat.php'; ?>
                    <?php elseif ($section == 'sekolah_asal'): ?>
                        <?php include __DIR__ . '/form_sections/sekolah_asal.php'; ?>
                    <?php elseif ($section == 'data_ayah'): ?>
                        <?php include __DIR__ . '/form_sections/data_ayah.php'; ?>
                    <?php elseif ($section == 'data_ibu'): ?>
                        <?php include __DIR__ . '/form_sections/data_ibu.php'; ?>
                    <?php elseif ($section == 'data_wali'): ?>
                        <?php include __DIR__ . '/form_sections/data_wali.php'; ?>
                    <?php endif; ?>

                    <div class="flex items-center justify-between mt-8 pt-6 border-t">
                        <a href="<?= BASEURL; ?>/psb/dashboardPendaftar"
                            class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            Kembali
                        </a>
                        <button type="submit"
                            class="px-6 py-2.5 bg-<?= $info['color']; ?>-500 hover:bg-<?= $info['color']; ?>-600 text-white rounded-lg font-medium flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <script>lucide.createIcons();</script>
</body>

</html>