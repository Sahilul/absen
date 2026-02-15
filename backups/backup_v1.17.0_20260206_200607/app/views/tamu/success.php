<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['judul'] ?? 'Terima Kasih' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body
    class="min-h-screen bg-gradient-to-br from-green-400 via-emerald-500 to-teal-500 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">Terima Kasih!</h1>

        <p class="text-gray-600 mb-4">
            Data Anda telah tercatat di sistem buku tamu
            <strong><?= htmlspecialchars($data['lembaga'] ?? '') ?></strong>
        </p>

        <div class="bg-gray-50 rounded-xl p-4 mb-6">
            <p class="text-sm text-gray-500">Nama Tamu</p>
            <p class="font-semibold text-gray-800"><?= htmlspecialchars($data['nama_tamu'] ?? 'Tamu') ?></p>
        </div>

        <p class="text-sm text-gray-500">
            Silakan menunggu di ruang tunggu yang telah disediakan.
        </p>
    </div>
</body>

</html>