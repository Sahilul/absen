<style>
    /* Explicit border styles for form elements */
    .wa-input {
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        width: 100%;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .wa-input:focus {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        outline: none;
    }
    .wa-select {
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        width: 100%;
        background-color: white;
    }
    .wa-checkbox {
        border: 1px solid #d1d5db !important;
        border-radius: 0.25rem;
    }
    .wa-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .wa-table th {
        border-bottom: 2px solid #e5e7eb;
    }
    .wa-table td {
        border-bottom: 1px solid #f3f4f6;
    }
    dialog::backdrop {
        background: rgba(0,0,0,0.5);
    }
</style>

<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-7xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="<?= BASEURL; ?>/admin/dashboard" class="text-gray-500 hover:text-indigo-600">Dashboard</a></li>
            <li><span class="text-gray-400">/</span></li>
            <li><span class="text-gray-700 font-medium">Pengaturan WA Gateway</span></li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
            <div class="p-2 bg-indigo-100 rounded-lg">
                <i data-lucide="smartphone" class="w-6 h-6 text-indigo-600"></i>
            </div>
            Pengaturan WA Gateway
        </h1>
        <p class="text-gray-600 mt-2">Kelola multiple akun WhatsApp Gateway dengan sistem rotasi untuk menghindari pemblokiran.</p>
    </div>

    <!-- Flash Message -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="mb-6 p-4 rounded-lg border <?= $_SESSION['flash']['type'] === 'success' ? 'bg-green-50 text-green-800 border-green-300' : 'bg-red-50 text-red-800 border-red-300' ?>">
            <div class="flex items-center gap-2">
                <i data-lucide="<?= $_SESSION['flash']['type'] === 'success' ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5"></i>
                <span><?= $_SESSION['flash']['message']; ?></span>
            </div>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="wa-card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Akun</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $data['stats']['total'] ?? 0 ?></p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="users" class="w-6 h-6 text-indigo-600"></i>
                </div>
            </div>
        </div>
        <div class="wa-card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Akun Aktif</p>
                    <p class="text-2xl font-bold text-green-600"><?= $data['stats']['active'] ?? 0 ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="wa-card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Terkirim Hari Ini</p>
                    <p class="text-2xl font-bold text-blue-600"><?= $data['stats']['total_sent_today'] ?? 0 ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="send" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="wa-card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Limit</p>
                    <p class="text-2xl font-bold text-purple-600"><?= $data['stats']['total_limit'] ?? 0 ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="gauge" class="w-6 h-6 text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Rotation Settings -->
    <div class="wa-card mb-8">
        <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <i data-lucide="refresh-cw" class="w-5 h-5 text-indigo-600"></i>
                Mode Pengiriman
            </h2>
        </div>
        <form action="<?= BASEURL; ?>/admin/prosesWaGateway" method="POST" class="p-5">
            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="rotation_enabled" value="1" <?= $data['rotation_enabled'] ? 'checked' : '' ?>
                            class="wa-checkbox w-5 h-5 text-indigo-600 rounded">
                        <span class="font-medium text-gray-700">Aktifkan Rotasi Multi Akun</span>
                    </label>
                </div>

                <div class="ml-8 mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mode Rotasi</label>
                    <select name="rotation_mode" class="wa-select max-w-md">
                        <option value="round_robin" <?= ($data['rotation_mode'] ?? '') === 'round_robin' ? 'selected' : '' ?>>
                            Round Robin - Bergantian berurutan
                        </option>
                        <option value="random" <?= ($data['rotation_mode'] ?? '') === 'random' ? 'selected' : '' ?>>
                            Random - Acak pilih akun
                        </option>
                        <option value="load_balance" <?= ($data['rotation_mode'] ?? '') === 'load_balance' ? 'selected' : '' ?>>
                            Load Balance - Pilih beban terendah
                        </option>
                    </select>
                </div>

                <div class="ml-8 mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor WA Admin (untuk notifikasi blokir)</label>
                    <input type="text" name="admin_wa_number" value="<?= htmlspecialchars($data['admin_wa_number'] ?? '') ?>" 
                        class="wa-input max-w-md" placeholder="08xxxxxxxxxx">
                    <p class="text-xs text-gray-500 mt-1">
                        <i data-lucide="info" class="w-3 h-3 inline"></i>
                        Akan menerima notifikasi jika ada akun WA yang terdeteksi terblokir
                    </p>
                </div>

                <div class="flex gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2 font-medium">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Simpan Pengaturan
                    </button>
                    <a href="<?= BASEURL; ?>/admin/resetWaCounter"
                        class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 font-medium border border-gray-200"
                        onclick="return confirm('Yakin reset counter harian semua akun?')">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        Reset Counter
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Test Gateway -->
    <div class="wa-card mb-8">
        <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <i data-lucide="send" class="w-5 h-5 text-indigo-600"></i>
                Test Koneksi Gateway
            </h2>
        </div>
        <form action="<?= BASEURL; ?>/admin/processTestWaGateway" method="POST" class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Akun Pengirim</label>
                    <select name="account_id" class="wa-select">
                        <option value="auto">Otomatiss (Sesuai Rotasi)</option>
                        <?php if (!empty($data['accounts'])): ?>
                            <optgroup label="Pilih Akun Spesifik">
                                <?php foreach ($data['accounts'] as $acc): ?>
                                    <option value="<?= $acc['id'] ?>">
                                        <?= htmlspecialchars($acc['nama']) ?> (<?= $acc['provider'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        Pilih "Otomatis" untuk mengetes logika rotasi sistem
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Tujuan</label>
                    <input type="text" name="target" required class="wa-input" placeholder="08xxxxxxxxxx" value="<?= $data['admin_wa_number'] ?? '' ?>">
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pesan Test</label>
                <textarea name="message" rows="2" class="wa-input" placeholder="Ketik pesan test di sini...">Test Koneksi WA Gateway dari SuperApp</textarea>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2 font-medium">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Kirim Pesan Test
                </button>
            </div>
        </form>
    </div>

    <!-- Accounts List -->
    <div class="wa-card">
        <div class="p-5 border-b border-gray-200 flex flex-wrap justify-between items-center gap-3">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <i data-lucide="smartphone" class="w-5 h-5 text-indigo-600"></i>
                Daftar Akun WA
            </h2>
            <button type="button" onclick="document.getElementById('modalTambah').showModal()"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2 font-medium">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Tambah Akun
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full wa-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Provider</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Limit</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Terkirim</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['accounts'])): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                        <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
                                    </div>
                                    <p class="text-lg font-medium text-gray-600">Belum ada akun WA Gateway</p>
                                    <button type="button" onclick="document.getElementById('modalTambah').showModal()"
                                        class="text-indigo-600 hover:text-indigo-800 font-medium">+ Tambah akun pertama</button>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['accounts'] as $account): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900"><?= htmlspecialchars($account['nama']) ?></div>
                                    <div class="text-sm text-gray-500 truncate max-w-xs"><?= $account['api_url'] ?: '-' ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                        <?= ucfirst($account['provider']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700 font-medium">
                                    <?= $account['daily_limit'] == 0 ? '∞ Unlimited' : $account['daily_limit'] . '/hari' ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="font-bold text-gray-900"><?= $account['today_sent'] ?></span>
                                        <?php if ($account['daily_limit'] > 0): ?>
                                            <div class="w-24 h-2.5 bg-gray-200 rounded-full overflow-hidden border border-gray-300">
                                                <?php $pct = min(100, ($account['today_sent'] / $account['daily_limit']) * 100); ?>
                                                <div class="h-full transition-all <?= $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-yellow-500' : 'bg-green-500') ?>"
                                                    style="width: <?= $pct ?>%"></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($account['is_active']): ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                            Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-300">
                                            <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                                            Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="<?= BASEURL ?>/admin/toggleWaAkun/<?= $account['id'] ?>"
                                            class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors border border-transparent hover:border-indigo-200"
                                            title="Toggle Status">
                                            <i data-lucide="power" class="w-4 h-4"></i>
                                        </a>
                                        <button type="button" onclick="editAkun(<?= htmlspecialchars(json_encode($account)) ?>)"
                                            class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors border border-transparent hover:border-blue-200"
                                            title="Edit">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>
                                        <a href="<?= BASEURL ?>/admin/hapusWaAkun/<?= $account['id'] ?>"
                                            onclick="return confirm('Yakin hapus akun ini?')"
                                            class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-200"
                                            title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Akun -->
<dialog id="modalTambah" class="rounded-xl p-0 w-full max-w-lg border-0 shadow-2xl">
    <div class="bg-white rounded-xl">
        <div class="p-5 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t-xl">
            <h3 class="text-lg font-bold text-gray-900">Tambah Akun WA</h3>
            <button type="button" onclick="document.getElementById('modalTambah').close()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded-lg">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="<?= BASEURL; ?>/admin/tambahWaAkun" method="POST" class="p-5 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Akun <span class="text-red-500">*</span></label>
                <input type="text" name="nama" required class="wa-input" placeholder="Misal: WA Utama">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Provider Gateway</label>
                    <select name="provider" id="add_provider" onchange="toggleAddAuthFields()" class="wa-select">
                        <?php foreach ($data['providers'] as $key => $provider): ?>
                            <option value="<?= $key ?>" 
                                data-auth="<?= $provider['auth_type'] ?>" 
                                data-url="<?= $provider['url'] ?>">
                                <?= $provider['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">URL API</label>
                    <input type="text" name="api_url" id="add_api_url" class="wa-input" placeholder="https://api.fonnte.com/send">
                </div>
            </div>
            
            <!-- Token Auth Fields -->
            <div id="add_token_auth" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Token API</label>
                    <input type="password" name="token" class="wa-input" placeholder="Masukkan token dari dashboard provider">
                </div>
            </div>
            
            <!-- Basic Auth Fields (Go-WA) -->
            <div id="add_basic_auth" class="space-y-4 hidden">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                        <input type="text" name="username" class="wa-input" placeholder="Username">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <input type="password" name="password" class="wa-input" placeholder="Password">
                    </div>
                </div>
                <p class="text-xs text-indigo-600">
                    <i data-lucide="info" class="w-3 h-3 inline"></i>
                    Go-WhatsApp: Jalankan dengan <code class="bg-indigo-100 px-1 rounded">--basic-auth="user:pass"</code>
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Limit Harian</label>
                <input type="number" name="daily_limit" value="100" min="0" class="wa-input">
                <p class="text-xs text-gray-500 mt-1">0 = unlimited (tidak terbatas)</p>
            </div>
            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="wa-checkbox w-5 h-5 text-indigo-600 rounded">
                    <span class="text-sm font-medium text-gray-700">Aktifkan akun ini</span>
                </label>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 mt-4">
                <button type="button" onclick="document.getElementById('modalTambah').close()"
                    class="px-5 py-2.5 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 font-medium border border-gray-200">Batal</button>
                <button type="submit"
                    class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">Simpan</button>
            </div>
        </form>
    </div>
</dialog>

<!-- Modal Edit Akun -->
<dialog id="modalEdit" class="rounded-xl p-0 w-full max-w-lg border-0 shadow-2xl">
    <div class="bg-white rounded-xl">
        <div class="p-5 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t-xl">
            <h3 class="text-lg font-bold text-gray-900">Edit Akun WA</h3>
            <button type="button" onclick="document.getElementById('modalEdit').close()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded-lg">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="formEdit" action="" method="POST" class="p-5 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Akun <span class="text-red-500">*</span></label>
                <input type="text" name="nama" id="edit_nama" required class="wa-input">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Provider Gateway</label>
                    <select name="provider" id="edit_provider" onchange="toggleEditAuthFields()" class="wa-select">
                        <?php foreach ($data['providers'] as $key => $provider): ?>
                            <option value="<?= $key ?>" 
                                data-auth="<?= $provider['auth_type'] ?>" 
                                data-url="<?= $provider['url'] ?>">
                                <?= $provider['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">URL API</label>
                    <input type="text" name="api_url" id="edit_api_url" class="wa-input">
                </div>
            </div>
            
            <!-- Token Auth Fields -->
            <div id="edit_token_auth" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Token API</label>
                    <input type="password" name="token" id="edit_token" class="wa-input" placeholder="Kosongkan jika tidak diubah">
                </div>
            </div>
            
            <!-- Basic Auth Fields -->
            <div id="edit_basic_auth" class="space-y-4 hidden">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                        <input type="text" name="username" id="edit_username" class="wa-input">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <input type="password" name="password" id="edit_password" class="wa-input" placeholder="Kosongkan jika tidak diubah">
                    </div>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Limit Harian</label>
                <input type="number" name="daily_limit" id="edit_daily_limit" min="0" class="wa-input">
            </div>
            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="wa-checkbox w-5 h-5 text-indigo-600 rounded">
                    <span class="text-sm font-medium text-gray-700">Aktifkan akun ini</span>
                </label>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 mt-4">
                <button type="button" onclick="document.getElementById('modalEdit').close()"
                    class="px-5 py-2.5 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 font-medium border border-gray-200">Batal</button>
                <button type="submit"
                    class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">Simpan</button>
            </div>
        </form>
    </div>
</dialog>

<script>
// Toggle auth fields untuk form Tambah
function toggleAddAuthFields() {
    const select = document.getElementById('add_provider');
    const selected = select.options[select.selectedIndex];
    const authType = selected.dataset.auth;
    const defaultUrl = selected.dataset.url;
    
    // Update URL
    document.getElementById('add_api_url').value = defaultUrl;
    
    // Toggle fields
    const tokenAuth = document.getElementById('add_token_auth');
    const basicAuth = document.getElementById('add_basic_auth');
    
    if (authType === 'basic') {
        tokenAuth.classList.add('hidden');
        basicAuth.classList.remove('hidden');
    } else {
        tokenAuth.classList.remove('hidden');
        basicAuth.classList.add('hidden');
    }
}

// Toggle auth fields untuk form Edit
function toggleEditAuthFields() {
    const select = document.getElementById('edit_provider');
    const selected = select.options[select.selectedIndex];
    const authType = selected.dataset.auth;
    
    const tokenAuth = document.getElementById('edit_token_auth');
    const basicAuth = document.getElementById('edit_basic_auth');
    
    if (authType === 'basic') {
        tokenAuth.classList.add('hidden');
        basicAuth.classList.remove('hidden');
    } else {
        tokenAuth.classList.remove('hidden');
        basicAuth.classList.add('hidden');
    }
}

function editAkun(account) {
    document.getElementById('formEdit').action = '<?= BASEURL ?>/admin/editWaAkun/' + account.id;
    document.getElementById('edit_nama').value = account.nama || '';
    document.getElementById('edit_provider').value = account.provider || 'fonnte';
    document.getElementById('edit_api_url').value = account.api_url || '';
    document.getElementById('edit_token').value = '';
    document.getElementById('edit_username').value = account.username || '';
    document.getElementById('edit_password').value = '';
    document.getElementById('edit_daily_limit').value = account.daily_limit || 100;
    document.getElementById('edit_is_active').checked = account.is_active == 1;
    
    // Toggle auth fields based on provider
    toggleEditAuthFields();
    
    document.getElementById('modalEdit').showModal();
    
    // Reinit icons in modal
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Initialize add form with default provider
    toggleAddAuthFields();
});
</script>