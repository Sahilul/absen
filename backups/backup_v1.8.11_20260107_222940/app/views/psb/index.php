<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['pengaturan']['judul_halaman'] ?? 'Penerimaan Siswa Baru'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f8f9fa;
        }

        .hero-bg {
            background: linear-gradient(135deg, #166534 0%, #15803d 100%);
        }

        .btn-primary {
            background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #166534 0%, #15803d 100%);
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .card {
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-4px);
        }
    </style>
</head>

<body>

    <!-- Hero -->
    <div class="hero-bg text-white py-8 px-4">
        <div class="max-w-6xl mx-auto">
            <!-- Nav -->
            <nav class="flex justify-between items-center mb-12">
                <div class="flex items-center gap-2">
                    <i data-lucide="graduation-cap" class="w-8 h-8"></i>
                    <span class="font-bold text-xl">PSB Online</span>
                </div>
                <div class="flex gap-3">
                    <?php if (!empty($data['pengaturan']['brosur_gambar'])): ?>
                        <button onclick="openBrosur()" class="px-4 py-2 btn-outline rounded-lg font-medium">
                            <i data-lucide="image" class="w-4 h-4 inline"></i> Brosur
                        </button>
                    <?php endif; ?>
                    <a href="<?= BASEURL; ?>/psb/login" class="px-4 py-2 btn-outline rounded-lg font-medium">Login</a>
                    <a href="<?= BASEURL; ?>/psb/register"
                        class="px-5 py-2 bg-white text-green-700 rounded-lg font-bold shadow-lg hover:bg-green-50">Daftar</a>
                </div>
            </nav>

            <!-- Hero Content -->
            <div class="text-center py-16">
                <div class="inline-block bg-white/20 px-4 py-2 rounded-full text-sm font-medium mb-6">
                    <i data-lucide="sparkles" class="w-4 h-4 inline"></i> Pendaftaran Dibuka
                </div>
                <h1 class="text-5xl font-extrabold mb-4" style="color: white; text-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                    <?= htmlspecialchars($data['pengaturan']['judul_halaman'] ?? 'Penerimaan Siswa Baru'); ?>
                </h1>
                <p class="text-xl mb-8 max-w-2xl mx-auto" style="color: rgba(255,255,255,0.95);">
                    <?= htmlspecialchars($data['pengaturan']['deskripsi'] ?? 'Selamat datang di halaman pendaftaran siswa baru'); ?>
                </p>
                <div class="flex gap-4 justify-center flex-wrap">
                    <a href="<?= BASEURL; ?>/psb/register"
                        class="px-8 py-4 btn-primary rounded-xl font-bold text-lg shadow-xl inline-flex items-center gap-2">
                        <i data-lucide="user-plus" class="w-5 h-5"></i> Daftar Sekarang
                    </a>
                    <a href="#jalur"
                        class="px-8 py-4 btn-outline rounded-xl font-semibold text-lg inline-flex items-center gap-2">
                        <i data-lucide="list" class="w-5 h-5"></i> Lihat Jalur
                    </a>
                </div>
            </div>
        </div>
    </div>

    <main class="max-w-6xl mx-auto px-4 py-12">

        <!-- Pendaftaran per Lembaga -->
        <?php if (!empty($data['periode_by_lembaga'])): ?>
            <section id="jalur" class="mb-12">
                <h2 class="text-3xl font-bold text-center mb-2 text-gray-800">Pendaftaran Dibuka</h2>
                <p class="text-center text-gray-600 mb-8">Pilih lembaga dan jalur sesuai kriteria Anda</p>

                <div class="space-y-8">
                    <?php foreach ($data['periode_by_lembaga'] as $lembaga): ?>
                        <div class="bg-white rounded-2xl shadow-lg border overflow-hidden">
                            <!-- Header Lembaga -->
                            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="bg-white/20 p-2 rounded-lg">
                                        <i data-lucide="building-2" class="w-6 h-6 text-white"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-white">
                                            <?= htmlspecialchars($lembaga['nama_lembaga']); ?>
                                        </h3>
                                        <span
                                            class="text-green-100 text-sm"><?= htmlspecialchars($lembaga['jenjang']); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Periode dalam lembaga -->
                            <div class="p-6 space-y-6">
                                <?php foreach ($lembaga['periode'] as $periode): ?>
                                    <div class="border border-gray-200 rounded-xl p-5 bg-gray-50/50">
                                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                                            <div>
                                                <h4 class="font-bold text-gray-800 text-lg">
                                                    <?= htmlspecialchars($periode['nama_periode']); ?>
                                                </h4>
                                                <div class="flex items-center gap-2 text-sm text-gray-500 mt-1">
                                                    <i data-lucide="calendar-days" class="w-4 h-4"></i>
                                                    <span><?= date('d M', strtotime($periode['tanggal_buka'])); ?> -
                                                        <?= date('d M Y', strtotime($periode['tanggal_tutup'])); ?></span>
                                                </div>
                                            </div>
                                            <a href="<?= BASEURL; ?>/psb/register"
                                                class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold text-sm inline-flex items-center gap-2 transition-colors">
                                                <i data-lucide="user-plus" class="w-4 h-4"></i> Daftar
                                            </a>
                                        </div>

                                        <!-- Jalur dalam periode -->
                                        <?php if (!empty($periode['jalur'])): ?>
                                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 mt-4">
                                                <?php foreach ($periode['jalur'] as $jalur):
                                                    $kuota = $jalur['kuota'] ?? 0;
                                                    // Skip jalur with 0 quota
                                                    if ($kuota <= 0)
                                                        continue;
                                                    $terisi = $jalur['terisi'] ?? 0;
                                                    $sisa = max(0, $kuota - $terisi);
                                                    $persen = $kuota > 0 ? min(100, ($terisi / $kuota) * 100) : 0;
                                                    ?>
                                                    <div class="bg-white rounded-lg border p-4">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <span
                                                                class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($jalur['nama_jalur']); ?></span>
                                                            <?php if ($sisa > 0): ?>
                                                                <span
                                                                    class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs font-bold">Buka</span>
                                                            <?php else: ?>
                                                                <span
                                                                    class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs font-bold">Penuh</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="mb-2">
                                                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                                                <span>Kuota</span>
                                                                <span class="font-medium text-gray-700"><?= $sisa; ?> /
                                                                    <?= $kuota; ?></span>
                                                            </div>
                                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                                <div class="bg-green-500 h-1.5 rounded-full"
                                                                    style="width: <?= $persen; ?>%"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-gray-500 text-sm italic">Belum ada jalur yang dibuka untuk periode ini.</p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php else: ?>
            <section class="mb-12">
                <div class="bg-white rounded-xl shadow-md p-10 text-center">
                    <i data-lucide="calendar-x" class="w-16 h-16 text-gray-400 mx-auto mb-4"></i>
                    <h3 class="font-bold text-gray-800 mb-2">Belum Ada Periode Aktif</h3>
                    <p class="text-gray-500">Saat ini belum ada periode pendaftaran yang dibuka.</p>
                </div>
            </section>
        <?php endif; ?>

        <!-- About -->
        <?php if (!empty($data['pengaturan']['tentang_sekolah']) || !empty($data['pengaturan']['keunggulan'])): ?>
            <section class="mb-12">
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="grid md:grid-cols-2">
                        <div class="p-8">
                            <h2 class="text-2xl font-bold mb-4 text-gray-800 flex items-center gap-2">
                                <i data-lucide="building" class="w-6 h-6 text-green-600"></i> Tentang Sekolah
                            </h2>
                            <?php if (!empty($data['pengaturan']['tentang_sekolah'])): ?>
                                <p class="text-gray-600 mb-4">
                                    <?= nl2br(htmlspecialchars($data['pengaturan']['tentang_sekolah'])); ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($data['pengaturan']['visi_misi'])): ?>
                                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                    <h4 class="font-semibold text-green-800 mb-2 flex items-center gap-2">
                                        <i data-lucide="target" class="w-4 h-4"></i> Visi & Misi
                                    </h4>
                                    <p class="text-green-700 text-sm">
                                        <?= nl2br(htmlspecialchars($data['pengaturan']['visi_misi'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($data['pengaturan']['keunggulan'])): ?>
                            <div class="bg-gradient-to-br from-green-600 to-green-700 p-8 text-white">
                                <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                                    <i data-lucide="award" class="w-5 h-5"></i> Keunggulan Kami
                                </h3>
                                <div class="space-y-3">
                                    <?php foreach (explode("\n", $data['pengaturan']['keunggulan']) as $item): ?>
                                        <?php if (trim($item)): ?>
                                            <div class="flex gap-3 bg-white/10 rounded-lg p-3">
                                                <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
                                                <span><?= htmlspecialchars(trim($item)); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>


        <!-- Alur & Syarat -->
        <div class="grid lg:grid-cols-2 gap-6 mb-12">
            <?php if (!empty($data['pengaturan']['alur_pendaftaran'])): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-xl font-bold mb-5 text-gray-800 flex items-center gap-2">
                        <i data-lucide="route" class="w-5 h-5 text-green-600"></i> Alur Pendaftaran
                    </h3>
                    <div class="space-y-3">
                        <?php $i = 1;
                        foreach (explode("\n", $data['pengaturan']['alur_pendaftaran']) as $line): ?>
                            <?php if (trim($line)): ?>
                                <div class="flex gap-3">
                                    <div
                                        class="bg-green-600 text-white w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0">
                                        <?= $i++; ?>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3 flex-1">
                                        <p class="text-gray-700"><?= htmlspecialchars(trim($line)); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($data['pengaturan']['syarat_pendaftaran'])): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-xl font-bold mb-5 text-gray-800 flex items-center gap-2">
                        <i data-lucide="clipboard-list" class="w-5 h-5 text-green-600"></i> Syarat Pendaftaran
                    </h3>
                    <div class="space-y-2">
                        <?php foreach (explode("\n", $data['pengaturan']['syarat_pendaftaran']) as $line): ?>
                            <?php if (trim($line)): ?>
                                <div class="flex gap-3 p-3 bg-green-50 rounded-lg border border-green-100">
                                    <i data-lucide="check-circle-2" class="w-5 h-5 text-green-600 flex-shrink-0"></i>
                                    <span class="text-gray-700"><?= htmlspecialchars(trim($line)); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Kontak -->
        <?php if (!empty($data['pengaturan']['kontak_info'])): ?>
            <section class="mb-12">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-xl font-bold mb-4 text-gray-800 flex items-center gap-2">
                        <i data-lucide="phone" class="w-5 h-5 text-green-600"></i> Informasi Kontak
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-700 whitespace-pre-line">
                            <?= htmlspecialchars($data['pengaturan']['kontak_info']); ?>
                        </p>
                    </div>
                </div>
            </section>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-300 py-8">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <?php
            $versionFile = dirname(dirname(dirname(__DIR__))) . '/version.json';
            $versionData = file_exists($versionFile) ? json_decode(file_get_contents($versionFile), true) : [];
            $appVersion = $versionData['version'] ?? '1.0.0';

            // Get dynamic app name
            $appSettings = getPengaturanAplikasi();
            $appName = $appSettings['nama_aplikasi'] ?? 'Smart Absensi';
            ?>
            <div class="flex items-center justify-center gap-2 mb-3">
                <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                <span
                    class="font-semibold text-white"><?= htmlspecialchars($data['pengaturan']['judul_halaman'] ?? 'PSB Online'); ?></span>
            </div>
            <p class="text-sm">&copy; <?= date('Y'); ?> - Powered by <?= htmlspecialchars($appName); ?>
                v<?= $appVersion; ?></p>
        </div>
    </footer>

    <!-- Brosur Modal -->
    <?php if (!empty($data['pengaturan']['brosur_gambar'])): ?>
        <div id="brosur-modal" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4"
            onclick="closeBrosur()">
            <div class="relative max-w-4xl">
                <button onclick="closeBrosur()"
                    class="absolute -top-10 right-0 text-white hover:text-gray-300 flex items-center gap-2">
                    <span>Tutup</span> <i data-lucide="x" class="w-6 h-6"></i>
                </button>
                <img src="<?= BASEURL; ?>/public/uploads/psb/brosur/<?= htmlspecialchars($data['pengaturan']['brosur_gambar']); ?>"
                    alt="Brosur" class="max-w-full max-h-[85vh] rounded-xl shadow-2xl" onclick="event.stopPropagation()">
            </div>
        </div>
    <?php endif; ?>

    <script>
        lucide.createIcons();
        function openBrosur() {
            document.getElementById('brosur-modal').classList.remove('hidden');
            document.getElementById('brosur-modal').classList.add('flex');
        }
        function closeBrosur() {
            document.getElementById('brosur-modal').classList.add('hidden');
            document.getElementById('brosur-modal').classList.remove('flex');
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeBrosur(); closePopup(); } });
    </script>

    <!-- Popup Gambar Promosi -->
    <?php if (!empty($data['pengaturan']['popup_aktif']) && !empty($data['pengaturan']['popup_gambar'])): ?>
        <div id="popup-modal" class="fixed inset-0 bg-black/70 z-[100] flex items-center justify-center p-4"
            onclick="closePopup()">
            <div class="relative max-w-lg w-full animate-bounce-in" onclick="event.stopPropagation()">
                <!-- Close Button -->
                <button onclick="closePopup()"
                    class="absolute -top-3 -right-3 bg-white text-gray-700 hover:bg-gray-100 w-10 h-10 rounded-full shadow-lg flex items-center justify-center z-10 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>

                <!-- Popup Content -->
                <?php if (!empty($data['pengaturan']['popup_link'])): ?>
                    <a href="<?= htmlspecialchars($data['pengaturan']['popup_link']); ?>" target="_blank" rel="noopener">
                        <img src="<?= BASEURL; ?>/public/uploads/psb/popup/<?= htmlspecialchars($data['pengaturan']['popup_gambar']); ?>"
                            alt="<?= htmlspecialchars($data['pengaturan']['popup_judul'] ?? 'Info'); ?>"
                            class="w-full rounded-xl shadow-2xl cursor-pointer hover:opacity-95 transition-opacity">
                    </a>
                <?php else: ?>
                    <img src="<?= BASEURL; ?>/public/uploads/psb/popup/<?= htmlspecialchars($data['pengaturan']['popup_gambar']); ?>"
                        alt="<?= htmlspecialchars($data['pengaturan']['popup_judul'] ?? 'Info'); ?>"
                        class="w-full rounded-xl shadow-2xl">
                <?php endif; ?>

                <!-- Title if exists -->
                <?php if (!empty($data['pengaturan']['popup_judul'])): ?>
                    <div class="bg-white rounded-b-xl p-4 -mt-2">
                        <h3 class="font-bold text-gray-800 text-center">
                            <?= htmlspecialchars($data['pengaturan']['popup_judul']); ?>
                        </h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <style>
            @keyframes bounceIn {
                0% {
                    transform: scale(0.3);
                    opacity: 0;
                }

                50% {
                    transform: scale(1.05);
                }

                70% {
                    transform: scale(0.9);
                }

                100% {
                    transform: scale(1);
                    opacity: 1;
                }
            }

            .animate-bounce-in {
                animation: bounceIn 0.5s ease-out;
            }
        </style>

        <script>
            // Re-init lucide for popup icons
            setTimeout(() => lucide.createIcons(), 100);

            const popupFreq = '<?= $data['pengaturan']['popup_frequency'] ?? 'once_session'; ?>';
            const popupModal = document.getElementById('popup-modal');

            function closePopup() {
                if (popupModal) {
                    popupModal.style.display = 'none';

                    // Save closed state based on frequency settings
                    if (popupFreq === 'once_session') {
                        sessionStorage.setItem('psb_popup_closed', 'true');
                    } else if (popupFreq === 'once_day') {
                        const now = new Date().getTime();
                        localStorage.setItem('psb_popup_last_shown', now);
                    }
                    // 'always' doesn't save anything
                }
            }

            // Check if popup should be shown
            function checkPopupDisplay() {
                if (popupModal) {
                    if (popupFreq === 'once_session') {
                        if (sessionStorage.getItem('psb_popup_closed') === 'true') {
                            popupModal.style.display = 'none';
                        }
                    } else if (popupFreq === 'once_day') {
                        const lastShown = localStorage.getItem('psb_popup_last_shown');
                        if (lastShown) {
                            const now = new Date().getTime();
                            const oneDay = 24 * 60 * 60 * 1000;
                            // Check if less than 24 hours
                            if (now - parseInt(lastShown) < oneDay) {
                                popupModal.style.display = 'none';
                            }
                        }
                    }
                    // 'always' always shows (default display: flex in CSS or via PHP rendered HTML)
                }
            }

            // Run check on load
            checkPopupDisplay();
        </script>
    <?php endif; ?>
</body>

</html>