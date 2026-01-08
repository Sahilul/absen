<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['judul'] ?? 'Error' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body
    class="min-h-screen bg-gradient-to-br from-gray-500 via-gray-600 to-gray-700 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 text-center">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">Terjadi Kesalahan</h1>

        <p class="text-gray-600 mb-6">
            <?= htmlspecialchars($data['message'] ?? 'Link tidak valid atau terjadi kesalahan sistem.') ?>
        </p>

        <p class="text-sm text-gray-500">
            Silakan hubungi petugas untuk bantuan.
        </p>
    </div>
</body>

</html>