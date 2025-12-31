<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Guru</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/umd/lucide.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Manajemen Data Guru</h2>
                <p class="text-gray-600 mt-1">Kelola data dan akun guru</p>
            </div>
            <a href="<?= BASEURL; ?>/admin/tambahGuru" 
               class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg flex items-center transition-colors duration-200 shadow-sm">
                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>
                Tambah Guru
            </a>
        </div>

        <!-- Tempat untuk menampilkan pesan flash -->
        <?php Flasher::flash(); ?>

        <!-- Stats Card -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="flex items-center">
                    <div class="bg-indigo-100 p-2 rounded-lg mr-3">
                        <i data-lucide="users" class="w-5 h-5 text-indigo-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Guru</p>
                        <p class="text-xl font-semibold text-gray-900"><?= count($data['guru']); ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="flex items-center">
                    <div class="bg-green-100 p-2 rounded-lg mr-3">
                        <i data-lucide="shield-check" class="w-5 h-5 text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Akun Aktif</p>
                        <p class="text-xl font-semibold text-gray-900"><?= count(array_filter($data['guru'], function($g) { return !empty($g['password_plain']); })); ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="flex items-center">
                    <div class="bg-orange-100 p-2 rounded-lg mr-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-orange-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Belum Ada Password</p>
                        <p class="text-xl font-semibold text-gray-900"><?= count(array_filter($data['guru'], function($g) { return empty($g['password_plain']); })); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                    <h3 class="text-lg font-semibold text-gray-800">Daftar Guru</h3>
                    <div class="flex items-center space-x-3">
                        <div class="text-sm text-gray-500">
                            Menampilkan <?= count($data['guru']); ?> guru
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Content -->
            <?php if (!empty($data['guru'])): ?>
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                NIK / Username
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama Guru
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status Akun
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($data['guru'] as $index => $guru) : ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                        <span class="text-xs font-semibold text-indigo-600"><?= $index + 1; ?></span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($guru['nik']); ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?= 'ID-' . str_pad($guru['id_guru'], 4, '0', STR_PAD_LEFT); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                                        <i data-lucide="graduation-cap" class="w-4 h-4 text-gray-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($guru['nama_guru']); ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Guru
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($guru['email'])): ?>
                                    <div class="flex items-center">
                                        <i data-lucide="mail" class="w-4 h-4 text-gray-400 mr-2"></i>
                                        <div>
                                            <div class="text-sm text-gray-900"><?= htmlspecialchars($guru['email']); ?></div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mt-1">
                                                <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                                Siap OAuth
                                            </span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="flex items-center">
                                        <i data-lucide="mail" class="w-4 h-4 text-gray-300 mr-2"></i>
                                        <div>
                                            <span class="text-xs text-gray-400 italic">Belum ada email</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 mt-1">
                                                <i data-lucide="alert-circle" class="w-3 h-3 mr-1"></i>
                                                Perlu diisi
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($guru['password_plain'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                        Akun Aktif
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Password: <span class="font-mono bg-gray-100 px-1 rounded"><?= htmlspecialchars($guru['password_plain']); ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        <i data-lucide="alert-circle" class="w-3 h-3 mr-1"></i>
                                        Belum Ada Password
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-3">
                                    <a href="<?= BASEURL; ?>/admin/editGuru/<?= $guru['id_guru']; ?>" 
                                       class="text-indigo-600 hover:text-indigo-800 transition-colors duration-150"
                                       title="Edit data guru">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </a>
                                    
                                    <?php if (!empty($guru['email'])): ?>
                                    <button onclick="generatePasswordGmail(<?= $guru['id_guru']; ?>, '<?= htmlspecialchars($guru['nama_guru'], ENT_QUOTES); ?>', '<?= htmlspecialchars($guru['email'], ENT_QUOTES); ?>')"
                                       class="text-green-600 hover:text-green-800 transition-colors duration-150"
                                       title="Generate Password Gmail">
                                        <i data-lucide="key" class="w-4 h-4"></i>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <a href="<?= BASEURL; ?>/admin/hapusGuru/<?= $guru['id_guru']; ?>" 
                                       class="text-red-600 hover:text-red-800 transition-colors duration-150"
                                       title="Hapus data guru"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus data guru <?= htmlspecialchars($guru['nama_guru']); ?>?\n\nSemua data terkait akan ikut terhapus!')">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="max-w-sm mx-auto">
                    <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="users-x" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Data Guru</h3>
                    <p class="text-gray-500 mb-6">Mulai dengan menambahkan data guru pertama untuk sistem.</p>
                    <a href="<?= BASEURL; ?>/admin/tambahGuru" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>
                        Tambah Guru
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer Info -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>💡 <strong>Tips:</strong> Pastikan setiap guru memiliki email dan password untuk dapat login via Google atau username</p>
        </div>
    </main>

    <!-- Modal Generate Password Gmail -->
    <div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Generate Password Gmail</h3>
                <button onclick="closePasswordModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <div class="mb-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-blue-800">
                        <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                        Password akan di-generate secara otomatis. Copy password dan reset manual di Google Workspace Admin Console.
                    </p>
                </div>
                
                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 uppercase">Nama Guru</label>
                        <p id="modalNamaGuru" class="text-sm font-medium text-gray-900"></p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 uppercase">Email</label>
                        <p id="modalEmail" class="text-sm font-medium text-gray-900"></p>
                    </div>
                    <div id="passwordResult" class="hidden">
                        <label class="text-xs font-semibold text-gray-600 uppercase">Password Baru</label>
                        <div class="flex items-center gap-2 mt-1">
                            <input type="text" id="generatedPassword" readonly 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm bg-gray-50"
                                   value="">
                            <button onclick="copyPassword()" 
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-2">
                                <i data-lucide="copy" class="w-4 h-4"></i>
                                Copy
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            ✓ Password tersimpan di database lokal<br>
                            ⚠️ Jangan lupa reset manual di <a href="https://admin.google.com" target="_blank" class="text-blue-600 hover:underline">Google Workspace Admin Console</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button onclick="closePasswordModal()" 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Tutup
                </button>
                <button id="btnGenerate" onclick="executeGenerate()" 
                        class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center gap-2">
                    <i data-lucide="key" class="w-4 h-4"></i>
                    Generate Password
                </button>
            </div>
        </div>
    </div>

    <script>
    let currentGuruId = null;

    function generatePasswordGmail(idGuru, namaGuru, email) {
        currentGuruId = idGuru;
        document.getElementById('modalNamaGuru').textContent = namaGuru;
        document.getElementById('modalEmail').textContent = email;
        document.getElementById('passwordResult').classList.add('hidden');
        document.getElementById('btnGenerate').disabled = false;
        document.getElementById('passwordModal').classList.remove('hidden');
        document.getElementById('passwordModal').classList.add('flex');
        
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
    }

    function closePasswordModal() {
        document.getElementById('passwordModal').classList.add('hidden');
        document.getElementById('passwordModal').classList.remove('flex');
        currentGuruId = null;
    }

    function executeGenerate() {
        if (!currentGuruId) return;

        const btn = document.getElementById('btnGenerate');
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin mr-2"></i>Generating...';
        
        fetch('<?= BASEURL; ?>/admin/generatePasswordGmail/' + currentGuruId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('generatedPassword').value = data.password;
                document.getElementById('passwordResult').classList.remove('hidden');
                btn.classList.add('hidden');
                
                // Show success message
                alert('✓ Password berhasil di-generate!\n\n' + 
                      'Password: ' + data.password + '\n\n' +
                      'Silakan copy password dan reset manual di Google Workspace Admin Console.');
            } else {
                alert('✗ Error: ' + data.message);
                btn.disabled = false;
            }
            
            if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat generate password');
            btn.disabled = false;
        });
    }

    function copyPassword() {
        const input = document.getElementById('generatedPassword');
        input.select();
        document.execCommand('copy');
        
        // Visual feedback
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> Copied!';
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        }, 2000);
        
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
    }

    // Auto refresh dan inisialisasi
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
    </script>
</body>
</html>