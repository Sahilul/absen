<?php
// File: app/views/psb/login.php
// Form login calon siswa PSB

$pengaturan = $data['pengaturan'] ?? [];
$namaSekolah = getPengaturanAplikasi()['nama_aplikasi'] ?? 'Smart Absensi';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PSB <?= $namaSekolah; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 50%, #166534 100%);
        }
    </style>
</head>

<body class="min-h-screen gradient-bg flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl mb-4 shadow-lg">
                    <i data-lucide="log-in" class="w-8 h-8 text-white"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Login PSB</h1>
                <p class="text-gray-500 mt-1">Masuk untuk melanjutkan pendaftaran</p>
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

            <!-- Form -->
            <form action="<?= BASEURL; ?>/psb/prosesLogin" method="POST" class="space-y-5">
                <!-- NISN -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">NISN</label>
                    <div class="relative">
                        <i data-lucide="id-card"
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                        <input type="text" name="nisn" required pattern="[0-9]{10}" maxlength="10"
                            class="w-full pl-12 pr-4 py-3 rounded-xl border-2 border-gray-200 focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all outline-none"
                            placeholder="Masukkan NISN Anda">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <i data-lucide="lock"
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                        <input type="password" name="password" required id="password"
                            class="w-full pl-12 pr-12 py-3 rounded-xl border-2 border-gray-200 focus:border-green-500 focus:ring-4 focus:ring-green-100 transition-all outline-none"
                            placeholder="Masukkan password">
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i data-lucide="eye" class="w-5 h-5" id="password-icon"></i>
                        </button>
                    </div>
                </div>

                <!-- Forgot Password -->
                <div class="text-right">
                    <a href="<?= BASEURL; ?>/psb/lupaPassword" class="text-sm text-green-600 hover:underline">
                        Lupa password?
                    </a>
                </div>

                <!-- Submit -->
                <button type="submit"
                    class="w-full py-3 bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-green-500/30 transition-all duration-300">
                    Login
                </button>
            </form>

            <!-- Divider -->
            <div class="flex items-center my-6">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="px-4 text-sm text-gray-400">atau</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <!-- Links -->
            <div class="text-center space-y-3">
                <p class="text-gray-600">
                    Belum punya akun?
                    <a href="<?= BASEURL; ?>/psb/register" class="text-green-600 font-semibold hover:underline">Daftar
                        Sekarang</a>
                </p>
                <a href="<?= BASEURL; ?>/psb"
                    class="inline-flex items-center gap-1 text-gray-500 hover:text-gray-700 text-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Kembali ke halaman utama
                </a>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-white/70 text-sm mt-6">
            &copy; <?= date('Y'); ?> <?= $namaSekolah; ?>
        </p>
    </div>

    <script>
        lucide.createIcons();

        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('password-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
</body>

</html>