<?php
// File: app/views/psb/lupa_password.php
// Form lupa password dengan verifikasi WA

$namaSekolah = getPengaturanAplikasi()['nama_aplikasi'] ?? 'Smart Absensi';
$step = $data['step'] ?? 1;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - PSB <?= $namaSekolah; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 50%, #7c3aed 100%);
        }
    </style>
</head>

<body class="min-h-screen gradient-bg flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl mb-4 shadow-lg">
                    <i data-lucide="key" class="w-8 h-8 text-white"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Lupa Password</h1>
                <p class="text-gray-500 mt-1">
                    <?php if ($step == 1): ?>
                        Masukkan nomor WhatsApp yang terdaftar
                    <?php elseif ($step == 2): ?>
                        Masukkan kode verifikasi
                    <?php else: ?>
                        Buat password baru
                    <?php endif; ?>
                </p>
            </div>

            <!-- Progress -->
            <div class="flex items-center justify-center gap-2 mb-6">
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="w-3 h-3 rounded-full <?= $i <= $step ? 'bg-sky-500' : 'bg-gray-200'; ?>"></div>
                <?php endfor; ?>
            </div>

            <!-- Flash Message -->
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

            <?php if ($step == 1): ?>
                <!-- Step 1: Input No WA -->
                <form action="<?= BASEURL; ?>/psb/prosesLupaPassword" method="POST" class="space-y-5">
                    <input type="hidden" name="step" value="1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor WhatsApp</label>
                        <div class="relative">
                            <i data-lucide="phone"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                            <input type="tel" name="no_wa" required
                                class="w-full pl-12 pr-4 py-3 rounded-xl border-2 border-gray-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-100 transition-all outline-none"
                                placeholder="Contoh: 08123456789">
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Kode verifikasi akan dikirim ke WhatsApp Anda</p>
                    </div>
                    <button type="submit"
                        class="w-full py-3 bg-gradient-to-r from-sky-500 to-blue-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all">
                        Kirim Kode
                    </button>
                </form>

            <?php elseif ($step == 2): ?>
                <!-- Step 2: Input Token -->
                <form action="<?= BASEURL; ?>/psb/prosesLupaPassword" method="POST" class="space-y-5">
                    <input type="hidden" name="step" value="2">
                    <input type="hidden" name="nisn" value="<?= $data['nisn'] ?? ''; ?>">

                    <div class="bg-sky-50 border border-sky-200 rounded-xl p-4 text-center">
                        <i data-lucide="message-circle" class="w-8 h-8 text-sky-500 mx-auto mb-2"></i>
                        <p class="text-sky-700 text-sm">Kode verifikasi telah dikirim ke WhatsApp Anda</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kode Verifikasi (6 digit)</label>
                        <input type="text" name="token" required pattern="[0-9]{6}" maxlength="6"
                            class="w-full py-4 text-center text-2xl font-bold tracking-widest rounded-xl border-2 border-gray-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-100 transition-all outline-none"
                            placeholder="______">
                    </div>
                    <button type="submit"
                        class="w-full py-3 bg-gradient-to-r from-sky-500 to-blue-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all">
                        Verifikasi
                    </button>
                </form>

            <?php else: ?>
                <!-- Step 3: New Password -->
                <form action="<?= BASEURL; ?>/psb/prosesLupaPassword" method="POST" class="space-y-5">
                    <input type="hidden" name="step" value="3">
                    <input type="hidden" name="nisn" value="<?= $data['nisn'] ?? ''; ?>">
                    <input type="hidden" name="token" value="<?= $data['token'] ?? ''; ?>">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                        <div class="relative">
                            <i data-lucide="lock"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                            <input type="password" name="password" required minlength="6" id="password"
                                class="w-full pl-12 pr-4 py-3 rounded-xl border-2 border-gray-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-100 transition-all outline-none"
                                placeholder="Minimal 6 karakter">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                        <div class="relative">
                            <i data-lucide="lock"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                            <input type="password" name="password_confirm" required minlength="6"
                                class="w-full pl-12 pr-4 py-3 rounded-xl border-2 border-gray-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-100 transition-all outline-none"
                                placeholder="Ulangi password baru">
                        </div>
                    </div>
                    <button type="submit"
                        class="w-full py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all">
                        Simpan Password Baru
                    </button>
                </form>
            <?php endif; ?>

            <!-- Back Link -->
            <div class="text-center mt-6">
                <a href="<?= BASEURL; ?>/psb/login"
                    class="inline-flex items-center gap-1 text-gray-500 hover:text-gray-700 text-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Kembali ke Login
                </a>
            </div>
        </div>

        <p class="text-center text-white/70 text-sm mt-6">
            &copy; <?= date('Y'); ?> <?= $namaSekolah; ?>
        </p>
    </div>

    <script>lucide.createIcons();</script>
</body>

</html>