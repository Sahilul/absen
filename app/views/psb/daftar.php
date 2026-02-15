<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pendaftaran - <?= htmlspecialchars($data['periode']['nama_lembaga']); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#f0f9ff', 100: '#e0f2fe', 500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1' },
                        secondary: { 50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1', 400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155', 800: '#1e293b' },
                        danger: { 500: '#ef4444', 600: '#dc2626' }
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .gradient-hero {
            background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);
        }
    </style>
</head>

<body class="font-sans bg-secondary-50 min-h-screen">

    <!-- Header -->
    <header class="gradient-hero text-white py-8 px-4">
        <div class="max-w-3xl mx-auto">
            <a href="<?= BASEURL; ?>/psb" class="inline-flex items-center gap-2 text-white/80 hover:text-white mb-4">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali
            </a>
            <h1 class="text-2xl font-bold">Formulir Pendaftaran</h1>
            <p class="text-blue-100"><?= htmlspecialchars($data['periode']['nama_lembaga']); ?> -
                <?= htmlspecialchars($data['periode']['nama_periode']); ?></p>
        </div>
    </header>

    <!-- Flash Message -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="max-w-3xl mx-auto px-4 mt-4">
            <div
                class="bg-<?= $_SESSION['flash']['type'] == 'error' ? 'red' : 'green'; ?>-100 border border-<?= $_SESSION['flash']['type'] == 'error' ? 'red' : 'green'; ?>-300 text-<?= $_SESSION['flash']['type'] == 'error' ? 'red' : 'green'; ?>-700 px-4 py-3 rounded-lg">
                <?= $_SESSION['flash']['message']; ?>
            </div>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

    <main class="max-w-3xl mx-auto px-4 py-8">
        <form action="<?= BASEURL; ?>/psb/prosesDaftar" method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="id_periode" value="<?= $data['periode']['id_periode']; ?>">

            <!-- Pilih Jalur -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-secondary-800 mb-4 flex items-center gap-2">
                    <i data-lucide="git-branch" class="w-5 h-5 text-purple-500"></i>
                    Pilih Jalur Pendaftaran <span class="text-danger-500">*</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php foreach ($data['jalur'] as $j): ?>
                        <label
                            class="flex items-center gap-3 p-4 border border-secondary-200 rounded-lg cursor-pointer hover:border-primary-500 hover:bg-primary-50 transition-colors has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50">
                            <input type="radio" name="id_jalur" value="<?= $j['id_jalur']; ?>" required
                                class="w-5 h-5 text-primary-600">
                            <div>
                                <p class="font-semibold text-secondary-800"><?= htmlspecialchars($j['nama_jalur']); ?></p>
                                <p class="text-xs text-secondary-500"><?= htmlspecialchars($j['deskripsi'] ?? ''); ?></p>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Data Pribadi -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-secondary-800 mb-4 flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5 text-primary-500"></i>
                    Data Pribadi
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Nama Lengkap <span
                                class="text-danger-500">*</span></label>
                        <input type="text" name="nama_lengkap" required placeholder="Sesuai akta kelahiran"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">NISN</label>
                        <input type="text" name="nisn" placeholder="10 digit" maxlength="10"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">NIK</label>
                        <input type="text" name="nik" placeholder="16 digit" maxlength="16"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Jenis Kelamin <span
                                class="text-danger-500">*</span></label>
                        <select name="jenis_kelamin" required
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Pilih --</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Agama</label>
                        <select name="agama"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Pilih --</option>
                            <option value="Islam">Islam</option>
                            <option value="Kristen">Kristen</option>
                            <option value="Katolik">Katolik</option>
                            <option value="Hindu">Hindu</option>
                            <option value="Buddha">Buddha</option>
                            <option value="Konghucu">Konghucu</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" placeholder="Kota kelahiran"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
            </div>

            <!-- Data Kontak -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-secondary-800 mb-4 flex items-center gap-2">
                    <i data-lucide="phone" class="w-5 h-5 text-green-500"></i>
                    Data Kontak
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Alamat Lengkap</label>
                        <textarea name="alamat" rows="2" placeholder="Alamat tempat tinggal saat ini"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">No. HP / WhatsApp</label>
                        <input type="tel" name="no_hp" placeholder="08xxxxxxxxxx"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Email</label>
                        <input type="email" name="email" placeholder="email@example.com"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
            </div>

            <!-- Data Orang Tua -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-secondary-800 mb-4 flex items-center gap-2">
                    <i data-lucide="users" class="w-5 h-5 text-orange-500"></i>
                    Data Orang Tua
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Nama Ayah</label>
                        <input type="text" name="nama_ayah" placeholder="Nama lengkap"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Pekerjaan Ayah</label>
                        <input type="text" name="pekerjaan_ayah" placeholder="Pekerjaan"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Nama Ibu</label>
                        <input type="text" name="nama_ibu" placeholder="Nama lengkap"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Pekerjaan Ibu</label>
                        <input type="text" name="pekerjaan_ibu" placeholder="Pekerjaan"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
            </div>

            <!-- Asal Sekolah -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-secondary-800 mb-4 flex items-center gap-2">
                    <i data-lucide="school" class="w-5 h-5 text-purple-500"></i>
                    Asal Sekolah
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Nama Sekolah Asal</label>
                        <input type="text" name="asal_sekolah" placeholder="Nama sekolah sebelumnya"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">NPSN Sekolah</label>
                        <input type="text" name="npsn_asal" placeholder="8 digit" maxlength="8"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Tahun Lulus</label>
                        <input type="number" name="tahun_lulus" min="2000" max="2030" placeholder="<?= date('Y'); ?>"
                            class="w-full px-4 py-3 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
            </div>

            <!-- Foto (Opsional) -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-secondary-800 mb-4 flex items-center gap-2">
                    <i data-lucide="camera" class="w-5 h-5 text-blue-500"></i>
                    Pas Foto (Opsional)
                </h2>
                <input type="file" name="foto" accept="image/*"
                    class="w-full px-4 py-3 border border-secondary-300 rounded-lg">
                <p class="text-xs text-secondary-400 mt-1">Format: JPG, PNG</p>
            </div>

            <!-- Submit -->
            <div class="flex justify-between items-center">
                <a href="<?= BASEURL; ?>/psb" class="text-secondary-500 hover:text-secondary-700">
                    &larr; Batal
                </a>
                <button type="submit"
                    class="bg-primary-500 hover:bg-primary-600 text-white px-8 py-3 rounded-lg font-semibold flex items-center gap-2 transition-colors">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    Kirim Pendaftaran
                </button>
            </div>
        </form>
    </main>

    <script>lucide.createIcons();</script>
</body>

</html>