<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .gradient-hero {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden text-center">
            <!-- Success Icon -->
            <div class="gradient-hero py-10">
                <div class="bg-white/20 w-24 h-24 rounded-full flex items-center justify-center mx-auto">
                    <i data-lucide="check-circle" class="w-16 h-16 text-white"></i>
                </div>
            </div>

            <div class="p-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Pendaftaran Berhasil!</h1>
                <p class="text-gray-500 mb-6">Terima kasih, <?= htmlspecialchars($data['result']['nama']); ?>.</p>

                <!-- Nomor Pendaftaran -->
                <div class="bg-gray-100 rounded-xl p-6 mb-6">
                    <p class="text-sm text-gray-500 mb-1">Nomor Pendaftaran Anda</p>
                    <p class="text-3xl font-bold text-gray-800 font-mono">
                        <?= htmlspecialchars($data['result']['no_pendaftaran']); ?></p>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-left mb-6">
                    <p class="text-amber-800 text-sm flex items-start gap-2">
                        <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                        <span><strong>Penting!</strong> Simpan nomor pendaftaran ini untuk mengecek status pendaftaran
                            Anda.</span>
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <a href="<?= BASEURL; ?>/psb/cetakBukti/<?= $data['result']['no_pendaftaran']; ?>" target="_blank"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 rounded-lg font-medium flex items-center justify-center gap-2 transition-colors">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Cetak Bukti
                    </a>
                    <a href="<?= BASEURL; ?>/psb/cekStatus?no=<?= $data['result']['no_pendaftaran']; ?>"
                        class="bg-sky-500 hover:bg-sky-600 text-white py-3 rounded-lg font-medium flex items-center justify-center gap-2 transition-colors">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        Cek Status
                    </a>
                </div>

                <a href="<?= BASEURL; ?>/psb" class="block mt-6 text-gray-500 hover:text-gray-700">
                    &larr; Kembali ke Beranda PSB
                </a>
            </div>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>

</html>