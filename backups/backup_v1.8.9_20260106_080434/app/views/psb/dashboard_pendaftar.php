<?php
// File: app/views/psb/dashboard_pendaftar.php
$akun = $data['akun'] ?? [];
$pendaftaran = $data['pendaftaran'] ?? [];
$periodeAktif = $data['periode_aktif'] ?? [];
$namaSekolah = getPengaturanAplikasi()['nama_aplikasi'] ?? 'Smart Absensi';

$menuFormulir = [
    ['key' => 'data_diri', 'title' => 'Data Diri', 'icon' => 'user', 'color' => 'green', 'desc' => 'NIK, NISN, Nama, TTL'],
    ['key' => 'alamat', 'title' => 'Alamat', 'icon' => 'map-pin', 'color' => 'emerald', 'desc' => 'Alamat tempat tinggal'],
    ['key' => 'sekolah_asal', 'title' => 'Sekolah Asal', 'icon' => 'building', 'color' => 'violet', 'desc' => 'Asal sekolah sebelumnya'],
    ['key' => 'data_ayah', 'title' => 'Data Ayah', 'icon' => 'user-check', 'color' => 'teal', 'desc' => 'Identitas ayah kandung'],
    ['key' => 'data_ibu', 'title' => 'Data Ibu', 'icon' => 'heart', 'color' => 'pink', 'desc' => 'Identitas ibu kandung'],
    ['key' => 'data_wali', 'title' => 'Data Wali', 'icon' => 'users', 'color' => 'amber', 'desc' => 'Opsional jika ada wali'],
    ['key' => 'dokumen', 'title' => 'Dokumen', 'icon' => 'file-text', 'color' => 'orange', 'desc' => 'Upload berkas pendaftaran'],
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PSB <?= $namaSekolah; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .gradient-primary {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    <header class="gradient-primary text-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 p-2 rounded-xl"><i data-lucide="graduation-cap" class="w-6 h-6"></i></div>
                    <div>
                        <h1 class="font-bold text-lg">PSB <?= $namaSekolah; ?></h1>
                        <p class="text-white/80 text-sm">Dashboard Pendaftar</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <p class="font-semibold"><?= htmlspecialchars($akun['nama_lengkap'] ?? ''); ?></p>
                        <p class="text-white/80 text-sm">NISN: <?= $akun['nisn'] ?? ''; ?></p>
                    </div>
                    <a href="<?= BASEURL; ?>/psb/logout"
                        class="bg-white/20 hover:bg-white/30 p-2 rounded-xl transition-colors" title="Logout">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <?php if (isset($_SESSION['flash'])): ?>
            <div
                class="mb-6 p-4 rounded-xl <?= $_SESSION['flash']['type'] == 'error' ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-green-50 text-green-600 border border-green-200'; ?>">
                <div class="flex items-center gap-2">
                    <i data-lucide="<?= $_SESSION['flash']['type'] == 'error' ? 'alert-circle' : 'check-circle'; ?>"
                        class="w-5 h-5"></i>
                    <span><?= $_SESSION['flash']['message']; ?></span>
                </div>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm p-6 mb-6 border">
            <div class="flex items-center gap-4">
                <div
                    class="gradient-primary w-14 h-14 rounded-full flex items-center justify-center text-white text-xl font-bold">
                    <?= strtoupper(substr($akun['nama_lengkap'] ?? '', 0, 1)); ?>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Selamat Datang,
                        <?= htmlspecialchars($akun['nama_lengkap'] ?? ''); ?>!
                    </h2>
                    <p class="text-gray-500">Kelola pendaftaran Anda di sini</p>
                </div>
            </div>
        </div>

        <?php if (!empty($pendaftaran)): ?>
            <?php foreach ($pendaftaran as $p): ?>
                <div class="mb-8">
                    <div class="bg-white rounded-xl shadow-sm border p-5 mb-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <p class="text-sm text-gray-500">No. Pendaftaran</p>
                                <p class="font-bold text-gray-800 text-lg"><?= $p['no_pendaftaran']; ?></p>
                                <p class="text-gray-600 mt-1"><?= $p['nama_lembaga']; ?> - <?= $p['nama_periode']; ?></p>
                                <p class="text-sm text-gray-500">Jalur: <?= $p['nama_jalur']; ?></p>
                            </div>
                            <div class="flex flex-col items-start sm:items-end gap-2">
                                <?php
                                $statusClass = [
                                    'draft' => 'bg-gray-100 text-gray-600',
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                    'revisi' => 'bg-orange-100 text-orange-700',
                                    'diterima' => 'bg-green-100 text-green-700',
                                    'ditolak' => 'bg-red-100 text-red-700',
                                ];
                                $class = $statusClass[$p['status']] ?? 'bg-gray-100 text-gray-600';
                                ?>
                                <span
                                    class="px-3 py-1 rounded-full text-sm font-semibold <?= $class; ?>"><?= ucfirst($p['status']); ?></span>
                                <div class="flex items-center gap-3">
                                    <a href="<?= BASEURL; ?>/psb/detailPendaftaran/<?= $p['id_pendaftar']; ?>"
                                        class="text-green-500 hover:text-green-700 text-sm flex items-center gap-1">
                                        <i data-lucide="eye" class="w-4 h-4"></i> Lihat Detail
                                    </a>
                                    <?php if (in_array($p['status'], ['draft', 'revisi'])): ?>
                                        <a href="<?= BASEURL; ?>/psb/hapusPendaftaran/<?= $p['id_pendaftar']; ?>"
                                            onclick="return confirm('Yakin ingin membatalkan?')"
                                            class="text-red-500 hover:text-red-700 text-sm flex items-center gap-1">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i> Batalkan
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($p['status'] == 'revisi' && !empty($p['catatan_admin'])): ?>
                            <div class="mt-4 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                                <p class="text-sm font-medium text-orange-800">Catatan Admin:</p>
                                <p class="text-sm text-orange-700"><?= htmlspecialchars($p['catatan_admin']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (in_array($p['status'], ['draft', 'pending', 'revisi'])): ?>
                        <div class="mb-4">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <i data-lucide="edit-3" class="w-5 h-5 text-green-500"></i> Isi Data Pendaftaran
                            </h3>
                            <p class="text-sm text-gray-500 mb-4">Klik bagian yang ingin Anda isi.</p>
                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                <?php foreach ($menuFormulir as $menu): ?>
                                    <a href="<?= BASEURL; ?>/psb/isiFormulir/<?= $p['id_pendaftar']; ?>/<?= $menu['key']; ?>"
                                        class="bg-white rounded-xl shadow-sm border p-4 hover:shadow-md transition-all group">
                                        <div class="flex items-start gap-3">
                                            <div class="bg-<?= $menu['color']; ?>-100 p-2.5 rounded-lg">
                                                <i data-lucide="<?= $menu['icon']; ?>"
                                                    class="w-5 h-5 text-<?= $menu['color']; ?>-600"></i>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-gray-800"><?= $menu['title']; ?></h4>
                                                <p class="text-xs text-gray-500 mt-1"><?= $menu['desc']; ?></p>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php if (in_array($p['status'], ['draft', 'revisi'])): ?>
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200 p-5">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                    <div>
                                        <h4 class="font-semibold text-green-800">
                                            <?= $p['status'] == 'revisi' ? 'Sudah diperbaiki?' : 'Sudah lengkap?'; ?>
                                        </h4>
                                        <p class="text-sm text-green-600">
                                            <?= $p['status'] == 'revisi' ? 'Pastikan data sudah diperbaiki.' : 'Pastikan semua data sudah benar.'; ?>
                                        </p>
                                    </div>
                                    <form action="<?= BASEURL; ?>/psb/kirimPendaftaran/<?= $p['id_pendaftar']; ?>" method="get">
                                        <button type="submit"
                                            class="inline-flex items-center gap-2 px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors font-medium cursor-pointer">
                                            <i data-lucide="send" class="w-4 h-4"></i>
                                            <?= $p['status'] == 'revisi' ? 'Kirim Ulang' : 'Kirim Pendaftaran'; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php
        // Jika sudah ada pendaftaran aktif, tidak tampilkan periode lain
        $hasActiveRegistration = !empty($pendaftaran);
        $availablePeriodes = $hasActiveRegistration ? [] : $periodeAktif;
        ?>

        <?php if (!empty($availablePeriodes)): ?>
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-5 h-5 text-green-500"></i> Daftar di Periode Aktif
                </h3>
                <div class="space-y-6">
                    <?php foreach ($availablePeriodes as $periode): ?>
                        <div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="bg-green-100 p-2 rounded-lg"><i data-lucide="building"
                                        class="w-5 h-5 text-green-600"></i></div>
                                <div>
                                    <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($periode['nama_lembaga']); ?>
                                    </h4>
                                    <p class="text-sm text-gray-500"><?= $periode['jenjang']; ?></p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($periode['nama_periode']); ?></p>
                            <p class="text-xs text-gray-500 mb-4">
                                <i data-lucide="calendar" class="w-3 h-3 inline"></i>
                                <?= date('d M', strtotime($periode['tanggal_buka'])); ?> -
                                <?= date('d M Y', strtotime($periode['tanggal_tutup'])); ?>
                            </p>

                            <!-- Jalur dan Kuota -->
                            <?php if (!empty($periode['jalur'])): ?>
                                <div class="mb-4">
                                    <p class="text-xs font-medium text-gray-500 mb-2">Jalur Pendaftaran:</p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <?php foreach ($periode['jalur'] as $jalur):
                                            $kuota = $jalur['kuota'] ?? 0;
                                            if ($kuota <= 0)
                                                continue;
                                            $terisi = $jalur['terisi'] ?? 0;
                                            $sisa = max(0, $kuota - $terisi);
                                            ?>
                                            <div class="bg-gray-50 rounded-lg p-2 border">
                                                <div class="flex items-center justify-between">
                                                    <span
                                                        class="text-xs font-medium text-gray-700"><?= htmlspecialchars($jalur['nama_jalur']); ?></span>
                                                    <?php if ($sisa > 0): ?>
                                                        <span class="bg-green-100 text-green-600 px-1.5 py-0.5 rounded text-xs">Buka</span>
                                                    <?php else: ?>
                                                        <span class="bg-red-100 text-red-600 px-1.5 py-0.5 rounded text-xs">Penuh</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1">Kuota: <?= $sisa; ?> / <?= $kuota; ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <a href="<?= BASEURL; ?>/psb/pilihJalur/<?= $periode['id_periode']; ?>"
                                class="block w-full text-center py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg font-medium hover:shadow-lg transition-all">
                                Daftar Sekarang
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif (empty($pendaftaran)): ?>
            <div class="bg-white rounded-xl shadow-sm border p-8 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="calendar-x" class="w-10 h-10 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Belum Ada Periode Aktif</h3>
                <p class="text-gray-500">Silakan cek kembali nanti.</p>
            </div>
        <?php endif; ?>
    </main>

    <script>lucide.createIcons();</script>
</body>

</html>