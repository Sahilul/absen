<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['judul'] ?? 'Link Kadaluarsa' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body
    class="min-h-screen bg-gradient-to-br from-orange-400 via-red-400 to-pink-500 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 text-center">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                </path>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2"><?= $data['judul'] ?? 'Link Kadaluarsa' ?></h1>

        <p class="text-gray-600 mb-6">
            <?= $data['message'] ?? 'Link ini sudah melewati batas waktu yang ditentukan dan tidak dapat digunakan lagi.' ?>
        </p>

        <p class="text-sm text-gray-500">
            Silakan hubungi petugas untuk mendapatkan link baru.
        </p>
    </div>
</body>

</html>