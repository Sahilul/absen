<?php $fc = $data['fieldConfig'] ?? []; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Siswa</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/umd/lucide.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50">
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 space-y-4 sm:space-y-0">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Manajemen Data Siswa</h2>
                <p class="text-gray-600 mt-1">Kelola data dan akun siswa</p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                <a href="<?= BASEURL; ?>/admin/importPsb"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center transition-colors duration-200 shadow-sm">
                    <i data-lucide="download-cloud" class="w-4 h-4 mr-2"></i>
                    Import dari PSB
                </a>
                <a href="<?= BASEURL; ?>/admin/importSiswa"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center transition-colors duration-200 shadow-sm">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-2"></i>
                    Import Excel
                </a>
                <a href="<?= BASEURL; ?>/admin/tambahSiswa"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg flex items-center justify-center transition-colors duration-200 shadow-sm">
                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>
                    Tambah Siswa
                </a>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="flex items-center">
                    <div class="bg-indigo-100 p-2 rounded-lg mr-3">
                        <i data-lucide="users" class="w-5 h-5 text-indigo-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Siswa</p>
                        <p class="text-xl font-semibold text-gray-900"><?= count($data['siswa']); ?></p>
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
                        <p class="text-xl font-semibold text-gray-900">
                            <?= count(array_filter($data['siswa'], function ($s) {
                                return !empty($s['password_plain']);
                            })); ?>
                        </p>
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
                        <p class="text-xl font-semibold text-gray-900">
                            <?= count(array_filter($data['siswa'], function ($s) {
                                return empty($s['password_plain']);
                            })); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (class_exists('Flasher')) {
            Flasher::flash();
        } ?>

        <!-- Table Card -->
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-col space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                        <h3 class="text-lg font-semibold text-gray-800">Daftar Siswa</h3>
                        <div class="flex items-center space-x-3">
                            <div class="text-sm text-gray-500">
                                Menampilkan <span id="filtered-count"><?= count($data['siswa']); ?></span> siswa
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="exportToExcel()"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-md text-sm font-medium transition-colors flex items-center">
                                    <i data-lucide="download" class="w-4 h-4 mr-1"></i>
                                    Export Excel
                                </button>
                                <button onclick="cetakKartuLogin()"
                                    class="bg-green-100 hover:bg-green-200 text-green-700 px-3 py-1 rounded-md text-sm font-medium transition-colors flex items-center">
                                    <i data-lucide="id-card" class="w-4 h-4 mr-1"></i>
                                    Cetak Kartu Login
                                </button>
                                <button id="bulkDeleteBtn" onclick="openBulkDeleteModal()"
                                    class="hidden bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1 rounded-md text-sm font-medium transition-colors flex items-center">
                                    <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i>
                                    Hapus Terpilih (<span id="selectedCount">0</span>)
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Search Section -->
                    <div class="border-b border-gray-200 pb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                        <div class="flex gap-2">
                            <div class="flex-1 relative">
                                <i data-lucide="search"
                                    class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="text" id="search-input" placeholder="Ketik NISN atau nama siswa..."
                                    oninput="applyFilters()"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <button onclick="clearSearch()"
                                class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-lg transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Filter Section -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter Kelas</label>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <select id="filter-kelas"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    onchange="applyFilters()">
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php if (!empty($data['kelas_list'])): ?>
                                        <?php foreach ($data['kelas_list'] as $kls): ?>
                                            <option value="<?= htmlspecialchars($kls['nama_kelas']); ?>">
                                                <?= htmlspecialchars($kls['nama_kelas']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <button onclick="clearFilter()"
                                class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-lg transition-colors">
                                <i data-lucide="filter-x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Reset All Button -->
                    <div class="pt-2 border-t border-gray-200">
                        <button onclick="resetAll()"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors flex items-center justify-center">
                            <i data-lucide="rotate-ccw" class="w-4 h-4 mr-2"></i>
                            Reset Semua
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table Content -->
            <?php if (!empty($data['siswa'])): ?>
                <!-- Desktop Table (hidden on mobile) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200" id="siswa-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)"
                                        class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    NISN
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Siswa
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kelas Terakhir
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jenis Kelamin
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status Akun
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kontak Ortu
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($data['siswa'] as $index => $siswa): ?>
                                <tr class="hover:bg-gray-50 transition-colors duration-150 siswa-row"
                                    data-id="<?= (int) $siswa['id_siswa']; ?>"
                                    data-nisn="<?= htmlspecialchars(strtolower($siswa['nisn'])); ?>"
                                    data-nama="<?= htmlspecialchars(strtolower($siswa['nama_siswa'])); ?>"
                                    data-kelas="<?= htmlspecialchars(strtolower($siswa['nama_kelas'] ?? '-')); ?>">
                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                        <input type="checkbox"
                                            class="siswa-checkbox w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            value="<?= (int) $siswa['id_siswa']; ?>"
                                            data-nama="<?= htmlspecialchars($siswa['nama_siswa']); ?>"
                                            onchange="updateBulkDeleteButton()">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                                <span class="text-xs font-semibold text-indigo-600"><?= $index + 1; ?></span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($siswa['nisn'] ?? ''); ?>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    <?= 'ID-' . str_pad($siswa['id_siswa'], 4, '0', STR_PAD_LEFT); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div data-foto-siswa="<?= (int) $siswa['id_siswa']; ?>"
                                                onclick='openFotoModal(<?= json_encode(["id_siswa" => $siswa["id_siswa"], "nisn" => $siswa["nisn"] ?? "", "nama_siswa" => $siswa["nama_siswa"], "foto" => $siswa["foto"] ?? "", "nama_kelas" => $siswa["nama_kelas"] ?? ""], JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
                                                class="relative flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center mr-3 overflow-hidden bg-gray-100 border border-gray-200 shadow-sm cursor-pointer group"
                                                title="Klik untuk ubah foto">
                                                <?php if (!empty($siswa['foto'])): ?>
                                                    <img src="<?= BASEURL; ?>/foto/siswa/<?= (int) $siswa['id_siswa']; ?>"
                                                        alt="<?= htmlspecialchars($siswa['nama_siswa']); ?>"
                                                        class="w-full h-full object-cover"
                                                        onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                                    <span class="fm-fallback text-xs font-bold text-gray-600 hidden"><?= strtoupper(substr($siswa['nama_siswa'], 0, 1)); ?></span>
                                                <?php else: ?>
                                                    <i data-lucide="user" class="w-4 h-4 text-gray-500 fm-fallback"></i>
                                                <?php endif; ?>
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-full">
                                                    <i data-lucide="camera" class="w-4 h-4 text-white"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($siswa['nama_siswa']); ?>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    Siswa
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i data-lucide="school" class="w-3 h-3 mr-1"></i>
                                            <?= !empty($siswa['nama_kelas']) ? htmlspecialchars($siswa['nama_kelas']) : '-'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <?php if ($siswa['jenis_kelamin'] === 'L'): ?>
                                                <div class="bg-blue-100 p-1.5 rounded-lg mr-2">
                                                    <i data-lucide="user" class="w-3 h-3 text-blue-600"></i>
                                                </div>
                                                <span class="text-sm text-gray-900">Laki-laki</span>
                                            <?php elseif ($siswa['jenis_kelamin'] === 'P'): ?>
                                                <div class="bg-pink-100 p-1.5 rounded-lg mr-2">
                                                    <i data-lucide="user" class="w-3 h-3 text-pink-600"></i>
                                                </div>
                                                <span class="text-sm text-gray-900">Perempuan</span>
                                            <?php else: ?>
                                                <span class="text-sm text-gray-500">-</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (!empty($siswa['password_plain'])): ?>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                                Akun Aktif
                                            </span>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Password: <span id="pw-t-<?= $siswa['id_siswa']; ?>"
                                                    class="font-mono bg-gray-100 px-1 rounded"><?= htmlspecialchars($siswa['password_plain']); ?></span>
                                                <button type="button"
                                                    onclick="revealPassword(<?= (int) $siswa['id_siswa']; ?>, 'siswa', 'pw-t-<?= $siswa['id_siswa']; ?>', this)"
                                                    class="ml-1 text-indigo-600 hover:text-indigo-800 align-middle" title="Lihat password">
                                                    <i data-lucide="eye" class="w-3 h-3 inline"></i>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <i data-lucide="alert-circle" class="w-3 h-3 mr-1"></i>
                                                Belum Ada Password
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- Ayah -->
                                            <div class="flex flex-col items-center group relative cursor-help">
                                                <span class="text-[10px] text-gray-500 mb-0.5">Ayah</span>
                                                <?php if (!empty($siswa['ayah_no_hp'])): ?>
                                                    <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                    <!-- Tooltip -->
                                                    <div class="absolute bottom-full mb-2 hidden group-hover:block z-10 w-max px-2 py-1 bg-gray-800 text-white text-xs rounded shadow-lg">
                                                        <?= htmlspecialchars($siswa['ayah_no_hp']); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="w-6 h-6 rounded-full bg-red-50 flex items-center justify-center text-red-500">
                                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <!-- Ibu -->
                                            <div class="flex flex-col items-center group relative cursor-help">
                                                <span class="text-[10px] text-gray-500 mb-0.5">Ibu</span>
                                                <?php if (!empty($siswa['ibu_no_hp'])): ?>
                                                    <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                    <!-- Tooltip -->
                                                    <div class="absolute bottom-full mb-2 hidden group-hover:block z-10 w-max px-2 py-1 bg-gray-800 text-white text-xs rounded shadow-lg">
                                                        <?= htmlspecialchars($siswa['ibu_no_hp']); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="w-6 h-6 rounded-full bg-red-50 flex items-center justify-center text-red-500">
                                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <button onclick="showDetailSiswa(<?= htmlspecialchars(json_encode($siswa)); ?>)"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition-colors"
                                                title="Lihat detail siswa">
                                                <i data-lucide="eye" class="w-3 h-3"></i>
                                            </button>
                                            <a href="<?= BASEURL; ?>/admin/dokumenSiswa/<?= $siswa['id_siswa']; ?>"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg transition-colors"
                                                title="Kelola berkas siswa">
                                                <i data-lucide="folder-open" class="w-3 h-3"></i>
                                            </a>
                                            <a href="<?= BASEURL; ?>/admin/editSiswa/<?= $siswa['id_siswa']; ?>"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-semibold rounded-lg transition-colors"
                                                title="Edit data siswa">
                                                <i data-lucide="edit-2" class="w-3 h-3"></i>
                                            </a>
                                            <button type="button"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold rounded-lg transition-colors"
                                                title="Hapus / Nonaktifkan siswa"
                                                onclick="openDeleteModal(<?= (int) $siswa['id_siswa']; ?>, '<?= htmlspecialchars($siswa['nama_siswa']); ?>')">
                                                <i data-lucide="trash-2" class="w-3 h-3"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards (hidden on desktop) -->
                <div class="md:hidden divide-y divide-gray-100" id="siswa-cards">
                    <?php foreach ($data['siswa'] as $index => $siswa): ?>
                        <div class="p-4 mobile-card" data-nisn="<?= htmlspecialchars(strtolower($siswa['nisn'])); ?>"
                            data-nama="<?= htmlspecialchars(strtolower($siswa['nama_siswa'])); ?>"
                            data-kelas="<?= htmlspecialchars(strtolower($siswa['nama_kelas'] ?? '-')); ?>">
                            <!-- Header: Name & Status -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div data-foto-siswa="<?= (int) $siswa['id_siswa']; ?>"
                                        onclick='openFotoModal(<?= json_encode(["id_siswa" => $siswa["id_siswa"], "nisn" => $siswa["nisn"] ?? "", "nama_siswa" => $siswa["nama_siswa"], "foto" => $siswa["foto"] ?? "", "nama_kelas" => $siswa["nama_kelas"] ?? ""], JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
                                        class="relative w-10 h-10 rounded-full flex items-center justify-center overflow-hidden bg-indigo-100 text-indigo-600 font-bold text-sm shadow-sm cursor-pointer group"
                                        title="Klik untuk ubah foto">
                                        <?php if (!empty($siswa['foto'])): ?>
                                            <img src="<?= BASEURL; ?>/foto/siswa/<?= (int) $siswa['id_siswa']; ?>"
                                                alt="<?= htmlspecialchars($siswa['nama_siswa']); ?>"
                                                class="w-full h-full object-cover"
                                                onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                            <span class="fm-fallback hidden"><?= strtoupper(substr($siswa['nama_siswa'], 0, 1)); ?></span>
                                        <?php else: ?>
                                            <span class="fm-fallback"><?= strtoupper(substr($siswa['nama_siswa'], 0, 1)); ?></span>
                                        <?php endif; ?>
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-full">
                                            <i data-lucide="camera" class="w-4 h-4 text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 text-sm">
                                            <?= htmlspecialchars($siswa['nama_siswa']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($siswa['nisn']); ?></p>
                                    </div>
                                </div>
                                <?php if (!empty($siswa['password_plain'])): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        <i data-lucide="check" class="w-3 h-3 mr-1"></i>Aktif
                                    </span>
                                <?php else: ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                                        <i data-lucide="alert-circle" class="w-3 h-3 mr-1"></i>Belum
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Info Tags -->
                            <div class="flex flex-wrap gap-2 mb-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs bg-blue-50 text-blue-700">
                                    <i data-lucide="school" class="w-3 h-3 mr-1"></i>
                                    <?= !empty($siswa['nama_kelas']) ? htmlspecialchars($siswa['nama_kelas']) : '-'; ?>
                                </span>
                                <?php if ($siswa['jenis_kelamin'] === 'L'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs bg-blue-50 text-blue-600">
                                        <i data-lucide="user" class="w-3 h-3 mr-1"></i>L
                                    </span>
                                <?php elseif ($siswa['jenis_kelamin'] === 'P'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs bg-pink-50 text-pink-600">
                                        <i data-lucide="user" class="w-3 h-3 mr-1"></i>P
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($siswa['password_plain'])): ?>
                                    <span id="pw-c-<?= $siswa['id_siswa']; ?>"
                                        class="inline-flex items-center px-2 py-1 rounded-md text-xs bg-gray-100 text-gray-600 font-mono">
                                        <?= htmlspecialchars($siswa['password_plain']); ?>
                                    </span>
                                    <button type="button"
                                        onclick="revealPassword(<?= (int) $siswa['id_siswa']; ?>, 'siswa', 'pw-c-<?= $siswa['id_siswa']; ?>', this)"
                                        class="text-indigo-600 hover:text-indigo-800" title="Lihat password">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>
                                <?php endif; ?>

                                <!-- Status HP Ortu -->
                                <?php if (!empty($siswa['ayah_no_hp'])): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs bg-green-50 text-green-700 border border-green-200">
                                        <i data-lucide="phone" class="w-3 h-3 mr-1"></i>Ayah
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs bg-red-50 text-red-700 border border-red-200 opacity-60">
                                        <i data-lucide="phone-off" class="w-3 h-3 mr-1"></i>Ayah
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($siswa['ibu_no_hp'])): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs bg-green-50 text-green-700 border border-green-200">
                                        <i data-lucide="phone" class="w-3 h-3 mr-1"></i>Ibu
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs bg-red-50 text-red-700 border border-red-200 opacity-60">
                                        <i data-lucide="phone-off" class="w-3 h-3 mr-1"></i>Ibu
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Action Buttons -->
                            <div class="grid grid-cols-4 gap-2">
                                <button onclick="showDetailSiswa(<?= htmlspecialchars(json_encode($siswa)); ?>)"
                                    class="flex items-center justify-center px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-lg">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                                <a href="<?= BASEURL; ?>/admin/dokumenSiswa/<?= $siswa['id_siswa']; ?>"
                                    class="flex items-center justify-center px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded-lg">
                                    <i data-lucide="folder-open" class="w-4 h-4"></i>
                                </a>
                                <a href="<?= BASEURL; ?>/admin/editSiswa/<?= $siswa['id_siswa']; ?>"
                                    class="flex items-center justify-center px-3 py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-medium rounded-lg">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </a>
                                <button type="button"
                                    onclick="openDeleteModal(<?= (int) $siswa['id_siswa']; ?>, '<?= htmlspecialchars(addslashes($siswa['nama_siswa'])); ?>')"
                                    class="flex items-center justify-center px-3 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- v1.26.0 - Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span>Tampilkan</span>
                        <select id="per-page-select" onchange="changePerPage(this.value)"
                            class="px-2 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>per halaman</span>
                        <span class="text-gray-400">|</span>
                        <span id="page-info" class="text-gray-500"></span>
                    </div>
                    <div class="flex items-center gap-1" id="pagination-controls"></div>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="max-w-sm mx-auto">
                        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i data-lucide="users-x" class="w-8 h-8 text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Data Siswa</h3>
                        <p class="text-gray-500 mb-6">Mulai dengan menambahkan data siswa atau import dari Excel.</p>
                        <div class="flex flex-col sm:flex-row justify-center space-y-2 sm:space-y-0 sm:space-x-3">
                            <a href="<?= BASEURL; ?>/admin/importSiswa"
                                class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                                <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-2"></i>
                                Import Excel
                            </a>
                            <a href="<?= BASEURL; ?>/admin/tambahSiswa"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>
                                Tambah Manual
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer Info -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p class="flex items-center justify-center">
                <i data-lucide="lightbulb" class="w-4 h-4 mr-2"></i>
                <strong>Tips:</strong> Gunakan fitur Import Excel untuk menambahkan banyak siswa sekaligus, atau tambah
                manual untuk data individual
            </p>
        </div>
    </main>


    <script>
        // Global variables
        let allRows = [];
        let allCards = [];
        let totalRows = 0;

        // v1.26.0 - Pagination
        let filteredRows = [];
        let filteredCards = [];
        let currentPage = 1;
        let perPage = parseInt(localStorage.getItem('siswa_per_page') || '25', 10);
        if (![25, 50, 100].includes(perPage)) perPage = 25;

        // Auto refresh dan inisialisasi
        document.addEventListener('DOMContentLoaded', function () {
            // Inisialisasi Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Get all table rows
            const tableBody = document.querySelector('#siswa-table tbody');
            if (tableBody) {
                allRows = Array.from(tableBody.querySelectorAll('tr'));
                totalRows = allRows.length;
            }

            // Get all mobile cards
            const cardsContainer = document.querySelector('#siswa-cards');
            if (cardsContainer) {
                allCards = Array.from(cardsContainer.querySelectorAll('.mobile-card'));
            }

            // Setup event listeners
            const searchInput = document.getElementById('search-input');
            const filterKelas = document.getElementById('filter-kelas');

            // v1.26.0 - inisialisasi pilihan jumlah data per halaman
            const perPageSelect = document.getElementById('per-page-select');
            if (perPageSelect) perPageSelect.value = String(perPage);

            // Initial apply to set correct count
            applyFilters();
        });

        function applyFilters() {
            const searchInput = document.getElementById('search-input');
            const filterKelas = document.getElementById('filter-kelas');

            const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const selectedKelas = filterKelas ? filterKelas.value.toLowerCase().trim() : '';

            filteredRows = [];
            filteredCards = [];

            // Filter table rows (desktop)
            allRows.forEach(row => {
                const nisn = (row.getAttribute('data-nisn') || '').toLowerCase();
                const nama = (row.getAttribute('data-nama') || '').toLowerCase();
                const kelas = (row.getAttribute('data-kelas') || '').toLowerCase();

                const matchSearch = !searchTerm || nisn.includes(searchTerm) || nama.includes(searchTerm);
                const matchKelas = !selectedKelas || kelas === selectedKelas;

                if (matchSearch && matchKelas) {
                    filteredRows.push(row);
                } else {
                    row.style.display = 'none';
                }
            });

            // Filter mobile cards
            allCards.forEach(card => {
                const nisn = (card.getAttribute('data-nisn') || '').toLowerCase();
                const nama = (card.getAttribute('data-nama') || '').toLowerCase();
                const kelas = (card.getAttribute('data-kelas') || '').toLowerCase();

                const matchSearch = !searchTerm || nisn.includes(searchTerm) || nama.includes(searchTerm);
                const matchKelas = !selectedKelas || kelas === selectedKelas;

                if (matchSearch && matchKelas) {
                    filteredCards.push(card);
                } else {
                    card.style.display = 'none';
                }
            });

            // Update counter
            const countEl = document.getElementById('filtered-count');
            if (countEl) {
                countEl.textContent = filteredRows.length;
            }

            // v1.26.0 - Kembali ke halaman 1 setiap filter berubah, lalu terapkan pagination
            currentPage = 1;
            applyPagination();
        }

        // ==========================================
        // v1.26.0 - PAGINATION (25 / 50 / 100)
        // ==========================================
        function applyPagination() {
            const total = filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(total / perPage));
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const start = (currentPage - 1) * perPage;
            const end = Math.min(start + perPage, total);

            filteredRows.forEach((row, i) => {
                row.style.display = (i >= start && i < end) ? '' : 'none';
            });
            filteredCards.forEach((card, i) => {
                card.style.display = (i >= start && i < end) ? '' : 'none';
            });

            renderPaginationControls(total, totalPages, start, end);

            // Re-render icons
            if (typeof lucide !== 'undefined') {
                setTimeout(() => lucide.createIcons(), 100);
            }
        }

        function renderPaginationControls(total, totalPages, start, end) {
            const info = document.getElementById('page-info');
            if (info) {
                info.textContent = total === 0 ? 'Tidak ada data' : `Menampilkan ${start + 1}\u2013${end} dari ${total}`;
            }

            const controls = document.getElementById('pagination-controls');
            if (!controls) return;
            controls.innerHTML = '';

            if (totalPages <= 1) return;

            const makeBtn = (label, page, disabled, active) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = label;
                btn.className = 'min-w-[32px] px-2 py-1 text-sm rounded-md border transition-colors ' +
                    (active
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : disabled
                            ? 'bg-gray-50 text-gray-300 border-gray-200 cursor-not-allowed'
                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100');
                if (!disabled && !active) btn.onclick = () => goToPage(page);
                return btn;
            };

            controls.appendChild(makeBtn('\u2039', currentPage - 1, currentPage === 1, false));

            // Nomor halaman: pertama, terakhir, dan sekitar halaman aktif
            const pages = [];
            for (let p = 1; p <= totalPages; p++) {
                if (p === 1 || p === totalPages || Math.abs(p - currentPage) <= 1) pages.push(p);
            }
            let prev = 0;
            pages.forEach(p => {
                if (p - prev > 1) {
                    const dots = document.createElement('span');
                    dots.textContent = '\u2026';
                    dots.className = 'px-1 text-gray-400 text-sm';
                    controls.appendChild(dots);
                }
                controls.appendChild(makeBtn(String(p), p, false, p === currentPage));
                prev = p;
            });

            controls.appendChild(makeBtn('\u203A', currentPage + 1, currentPage === totalPages, false));
        }

        function goToPage(page) {
            currentPage = page;
            applyPagination();
            const table = document.getElementById('siswa-table');
            if (table) table.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function changePerPage(val) {
            const n = parseInt(val, 10);
            if (![25, 50, 100].includes(n)) return;
            perPage = n;
            localStorage.setItem('siswa_per_page', String(perPage));
            currentPage = 1;
            applyPagination();
        }

        function clearSearch() {
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.value = '';
                applyFilters();
            }
        }

        function clearFilter() {
            const filterKelas = document.getElementById('filter-kelas');
            if (filterKelas) {
                filterKelas.value = '';
                applyFilters();
            }
        }

        function resetAll() {
            clearSearch();
            clearFilter();
        }

        // Export to Excel function (Server Side via CSV)
        function exportToExcel() {
            const filterKelasEl = document.getElementById('filter-kelas');
            const selectedKelas = filterKelasEl ? (filterKelasEl.value || '').trim() : '';
            
            let url = '<?= BASEURL; ?>/admin/exportSiswaExcel';
            if (selectedKelas) {
                url += '?kelas=' + encodeURIComponent(selectedKelas);
            }
            
            window.location.href = url;
        }
        // Modal konfirmasi hapus / nonaktifkan
        function openDeleteModal(id, nama) {
            const modal = document.getElementById('deleteModal');
            document.getElementById('modalStudentName').textContent = nama;
            modal.dataset.idSiswa = id;
            modal.classList.remove('hidden');
        }
        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
        }
        function confirmDeleteSiswa() {
            const modal = document.getElementById('deleteModal');
            const id = modal.dataset.idSiswa;
            window.location.href = '<?= BASEURL; ?>/admin/hapusSiswa/' + id;
        }
        // Tutup modal jika klik di luar konten
        document.addEventListener('click', function (e) {
            const modal = document.getElementById('deleteModal');
            if (!modal || modal.classList.contains('hidden')) return;
            if (e.target === modal) {
                closeDeleteModal();
            }
        });

        // ==========================================
        // BULK DELETE FUNCTIONS
        // ==========================================

        // Toggle select all checkboxes
        function toggleSelectAll(masterCheckbox) {
            const checkboxes = document.querySelectorAll('.siswa-checkbox');
            checkboxes.forEach(cb => {
                // Only toggle visible checkboxes
                const row = cb.closest('tr');
                if (row && row.style.display !== 'none') {
                    cb.checked = masterCheckbox.checked;
                }
            });
            updateBulkDeleteButton();
        }

        // Update bulk delete button visibility and count
        function updateBulkDeleteButton() {
            const checkboxes = document.querySelectorAll('.siswa-checkbox:checked');
            const count = checkboxes.length;
            const btn = document.getElementById('bulkDeleteBtn');
            const countSpan = document.getElementById('selectedCount');

            if (count > 0) {
                btn.classList.remove('hidden');
                btn.classList.add('flex');
                countSpan.textContent = count;
            } else {
                btn.classList.add('hidden');
                btn.classList.remove('flex');
            }

            // Update select all checkbox state
            const allCheckboxes = document.querySelectorAll('.siswa-checkbox');
            const visibleCheckboxes = Array.from(allCheckboxes).filter(cb => {
                const row = cb.closest('tr');
                return row && row.style.display !== 'none';
            });
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            if (visibleCheckboxes.length > 0 && visibleCheckboxes.every(cb => cb.checked)) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else if (count > 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            }

            // Re-render icons
            if (typeof lucide !== 'undefined') {
                setTimeout(() => lucide.createIcons(), 50);
            }
        }

        // Open bulk delete modal
        function openBulkDeleteModal() {
            const checkboxes = document.querySelectorAll('.siswa-checkbox:checked');
            const count = checkboxes.length;

            if (count === 0) {
                alert('Pilih minimal 1 siswa untuk dihapus.');
                return;
            }

            // Build list of names
            let listHtml = '<ul class="list-disc list-inside space-y-1">';
            checkboxes.forEach(cb => {
                const nama = cb.getAttribute('data-nama') || 'Unknown';
                listHtml += `<li>${nama}</li>`;
            });
            listHtml += '</ul>';

            document.getElementById('bulkDeleteCount').textContent = count;
            document.getElementById('bulkDeleteList').innerHTML = listHtml;

            const modal = document.getElementById('bulkDeleteModal');
            modal.classList.remove('hidden');

            if (typeof lucide !== 'undefined') {
                setTimeout(() => lucide.createIcons(), 50);
            }
        }

        // Close bulk delete modal
        function closeBulkDeleteModal() {
            const modal = document.getElementById('bulkDeleteModal');
            modal.classList.add('hidden');
        }

        // Confirm bulk delete - submit form via POST
        function confirmBulkDelete() {
            const checkboxes = document.querySelectorAll('.siswa-checkbox:checked');
            const ids = Array.from(checkboxes).map(cb => cb.value);

            if (ids.length === 0) {
                alert('Tidak ada siswa yang dipilih.');
                return;
            }

            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= BASEURL; ?>/admin/bulkHapusSiswa';

            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }

        // Close bulk modal on click outside
        document.addEventListener('click', function (e) {
            const modal = document.getElementById('bulkDeleteModal');
            if (!modal || modal.classList.contains('hidden')) return;
            if (e.target === modal) {
                closeBulkDeleteModal();
            }
        });
    </script>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal"
        class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md border border-gray-200">
            <div class="px-6 py-4 border-b flex items-center">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500 mr-2"></i>
                <h4 class="text-lg font-semibold text-gray-800">Konfirmasi Penghapusan</h4>
            </div>
            <div class="px-6 py-5 space-y-4">
                <p class="text-sm text-gray-600 leading-relaxed">
                    Anda memilih siswa: <span class="font-medium text-gray-900" id="modalStudentName"></span>.
                </p>
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-xs text-red-700">
                    Semua data terkait (absensi, jurnal, nilai, performa) akan dihapus permanen bersama siswa.
                </div>
                <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3">
                <button onclick="closeDeleteModal()"
                    class="px-4 py-2 text-sm font-medium rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700">Batal</button>
                <button onclick="confirmDeleteSiswa()"
                    class="px-4 py-2 text-sm font-medium rounded-md bg-red-600 hover:bg-red-700 text-white flex items-center">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Confirmation Modal -->
    <div id="bulkDeleteModal"
        class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-lg border border-gray-200">
            <div class="px-6 py-4 border-b flex items-center">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500 mr-2"></i>
                <h4 class="text-lg font-semibold text-gray-800">Hapus Beberapa Siswa</h4>
            </div>
            <div class="px-6 py-5 space-y-4">
                <p class="text-sm text-gray-600 leading-relaxed">
                    Anda akan menghapus <span class="font-bold text-red-600" id="bulkDeleteCount">0</span> siswa:
                </p>
                <div id="bulkDeleteList"
                    class="max-h-40 overflow-y-auto bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-gray-700">
                    <!-- List akan diisi via JavaScript -->
                </div>
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-xs text-red-700">
                    <strong>Peringatan:</strong> Semua data terkait (absensi, jurnal, nilai, performa) akan dihapus
                    permanen bersama siswa.
                </div>
                <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3">
                <button onclick="closeBulkDeleteModal()"
                    class="px-4 py-2 text-sm font-medium rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700">Batal</button>
                <button onclick="confirmBulkDelete()" id="confirmBulkDeleteBtn"
                    class="px-4 py-2 text-sm font-medium rounded-md bg-red-600 hover:bg-red-700 text-white flex items-center">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Hapus Semua
                </button>
            </div>
        </div>
    </div>

    <!-- Detail Siswa Modal -->
    <div id="modalDetailSiswa"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="sticky top-0 bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4 rounded-t-2xl">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center overflow-hidden" id="detailAvatarContainer">
                        <span class="text-2xl font-bold text-white" id="detailAvatar">A</span>
                        <img id="detailAvatarImg" src="" alt="" class="w-16 h-16 rounded-full object-cover hidden">
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-white" id="detailNama">-</h3>
                        <p class="text-indigo-100 text-sm" id="detailNisn">NISN: -</p>
                    </div>
                    <button onclick="closeDetailModal()"
                        class="text-white hover:bg-white/20 rounded-lg p-2 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-6">
                <!-- Data Pribadi -->
                <div>
                    <h4
                        class="text-sm font-bold text-gray-800 mb-3 pb-2 border-b-2 border-indigo-200 flex items-center gap-2">
                        <i data-lucide="user-circle" class="w-4 h-4 text-indigo-600"></i>
                        Data Pribadi
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                            <p class="text-xs text-gray-600 mb-1">Jenis Kelamin</p>
                            <p class="font-semibold text-gray-900" id="detailJK">-</p>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                            <p class="text-xs text-gray-600 mb-1">Tempat Lahir</p>
                            <p class="font-semibold text-gray-900" id="detailTempatLahir">-</p>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                            <p class="text-xs text-gray-600 mb-1">Tanggal Lahir</p>
                            <p class="font-semibold text-gray-900" id="detailTglLahir">-</p>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                            <p class="text-xs text-gray-600 mb-1">Kelas Terakhir</p>
                            <p class="font-semibold text-gray-900" id="detailKelas">-</p>
                        </div>
                    </div>
                </div>

                <!-- Kontak -->
                <?php if (($fc['no_wa'] ?? true) || ($fc['email'] ?? true)): ?>
                <div>
                    <h4
                        class="text-sm font-bold text-gray-800 mb-3 pb-2 border-b-2 border-indigo-200 flex items-center gap-2">
                        <i data-lucide="phone" class="w-4 h-4 text-indigo-600"></i>
                        Informasi Kontak
                    </h4>
                    <div class="space-y-3">
                        <?php if ($fc['no_wa'] ?? true): ?>
                        <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                            <p class="text-xs text-gray-600 mb-1">No. WhatsApp</p>
                            <p class="font-semibold text-gray-900" id="detailNoWa">-</p>
                        </div>
                        <?php endif; ?>
                        <?php if ($fc['email'] ?? true): ?>
                        <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                            <p class="text-xs text-gray-600 mb-1">Email</p>
                            <p class="font-semibold text-gray-900" id="detailEmail">-</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Alamat Lengkap -->
                <?php if (($fc['alamat'] ?? true) || ($fc['rt'] ?? true) || ($fc['rw'] ?? true) || ($fc['dusun'] ?? true) || ($fc['kelurahan'] ?? true) || ($fc['kecamatan'] ?? true) || ($fc['kabupaten'] ?? true) || ($fc['provinsi'] ?? true) || ($fc['kode_pos'] ?? true)): ?>
                <div>
                    <h4
                        class="text-sm font-bold text-gray-800 mb-3 pb-2 border-b-2 border-green-200 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-green-600"></i>
                        Alamat Lengkap
                    </h4>
                    <div class="space-y-3">
                        <?php if ($fc['alamat'] ?? true): ?>
                        <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                            <p class="text-xs text-gray-600 mb-1">Alamat</p>
                            <p class="font-semibold text-gray-900" id="detailAlamat">-</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <?php if (($fc['rt'] ?? true) || ($fc['rw'] ?? true)): ?>
                            <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                                <p class="text-xs text-gray-600 mb-1">RT / RW</p>
                                <p class="font-semibold text-gray-900" id="detailRtRw">-</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($fc['dusun'] ?? true): ?>
                            <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                                <p class="text-xs text-gray-600 mb-1">Dusun</p>
                                <p class="font-semibold text-gray-900" id="detailDusun">-</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <?php if ($fc['kelurahan'] ?? true): ?>
                            <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                                <p class="text-xs text-gray-600 mb-1">Kelurahan/Desa</p>
                                <p class="font-semibold text-gray-900" id="detailKelurahan">-</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($fc['kecamatan'] ?? true): ?>
                            <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                                <p class="text-xs text-gray-600 mb-1">Kecamatan</p>
                                <p class="font-semibold text-gray-900" id="detailKecamatan">-</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <?php if ($fc['kabupaten'] ?? true): ?>
                            <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                                <p class="text-xs text-gray-600 mb-1">Kabupaten/Kota</p>
                                <p class="font-semibold text-gray-900" id="detailKabupaten">-</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($fc['provinsi'] ?? true): ?>
                            <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                                <p class="text-xs text-gray-600 mb-1">Provinsi</p>
                                <p class="font-semibold text-gray-900" id="detailProvinsi">-</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($fc['kode_pos'] ?? true): ?>
                        <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                            <p class="text-xs text-gray-600 mb-1">Kode Pos</p>
                            <p class="font-semibold text-gray-900" id="detailKodePos">-</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Data Ayah -->
                <!-- Data Ayah -->
                <?php if (($fc['ayah_nama'] ?? true) || ($fc['ayah_nik'] ?? true) || ($fc['ayah_tempat_lahir'] ?? true) || ($fc['ayah_status'] ?? true) || ($fc['ayah_pendidikan'] ?? true) || ($fc['ayah_pekerjaan'] ?? true) || ($fc['ayah_penghasilan'] ?? true) || ($fc['ayah_no_hp'] ?? true)): ?>
                <div>
                    <h4
                        class="text-sm font-bold text-gray-800 mb-3 pb-2 border-b-2 border-blue-200 flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4 text-blue-600"></i>
                        Data Ayah
                    </h4>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <?php if ($fc['ayah_nama'] ?? true): ?>
                            <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg">
                                <p class="text-xs text-blue-700 mb-1">Nama Ayah</p>
                                <p class="font-semibold text-blue-900" id="detailAyah">-</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($fc['ayah_nik'] ?? true): ?>
                            <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg">
                                <p class="text-xs text-blue-700 mb-1">NIK Ayah</p>
                                <p class="font-semibold text-blue-900" id="detailAyahNik">-</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <?php if (($fc['ayah_tempat_lahir'] ?? true) || ($fc['ayah_tanggal_lahir'] ?? true)): ?>
                            <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg">
                                <p class="text-xs text-blue-700 mb-1">Tempat, Tgl Lahir</p>
                                <p class="font-semibold text-blue-900" id="detailAyahTtl">-</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($fc['ayah_status'] ?? true): ?>
                            <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg">
                                <p class="text-xs text-blue-700 mb-1">Status</p>
                                <p class="font-semibold text-blue-900" id="detailAyahStatus">-</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <?php if ($fc['ayah_pendidikan'] ?? true): ?>
                            <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg">
                                <p class="text-xs text-blue-700 mb-1">Pendidikan</p>
                                <p class="font-semibold text-blue-900" id="detailAyahPendidikan">-</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($fc['ayah_pekerjaan'] ?? true): ?>
                            <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg">
                                <p class="text-xs text-blue-700 mb-1">Pekerjaan</p>
                                <p class="font-semibold text-blue-900" id="detailAyahPekerjaan">-</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($fc['ayah_penghasilan'] ?? true): ?>
                            <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg">
                                <p class="text-xs text-blue-700 mb-1">Penghasilan</p>
                                <p class="font-semibold text-blue-900" id="detailAyahPenghasilan">-</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($fc['ayah_no_hp'] ?? true): ?>
                        <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg">
                            <p class="text-xs text-blue-700 mb-1">No. HP Ayah</p>
                            <p class="font-semibold text-blue-900" id="detailAyahHp">-</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Data Ibu -->
                <!-- Data Ibu -->
                <?php if (($fc['ibu_nama'] ?? true) || ($fc['ibu_nik'] ?? true) || ($fc['ibu_tempat_lahir'] ?? true) || ($fc['ibu_status'] ?? true) || ($fc['ibu_pendidikan'] ?? true) || ($fc['ibu_pekerjaan'] ?? true) || ($fc['ibu_penghasilan'] ?? true) || ($fc['ibu_no_hp'] ?? true)): ?>
                <div>
                    <h4
                        class="text-sm font-bold text-gray-800 mb-3 pb-2 border-b-2 border-pink-200 flex items-center gap-2">
                        <i data-lucide="heart" class="w-4 h-4 text-pink-600"></i>
                        Data Ibu
                    </h4>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <?php if ($fc['ibu_nama'] ?? true): ?>
                            <div class="bg-pink-50 border border-pink-200 p-3 rounded-lg">
                                <p class="text-xs text-pink-700 mb-1">Nama Ibu</p>
                                <p class="font-semibold text-pink-900" id="detailIbu">-</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($fc['ibu_nik'] ?? true): ?>
                            <div class="bg-pink-50 border border-pink-200 p-3 rounded-lg">
                                <p class="text-xs text-pink-700 mb-1">NIK Ibu</p>
                                <p class="font-semibold text-pink-900" id="detailIbuNik">-</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <?php if (($fc['ibu_tempat_lahir'] ?? true) || ($fc['ibu_tanggal_lahir'] ?? true)): ?>
                            <div class="bg-pink-50 border border-pink-200 p-3 rounded-lg">
                                <p class="text-xs text-pink-700 mb-1">Tempat, Tgl Lahir</p>
                                <p class="font-semibold text-pink-900" id="detailIbuTtl">-</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($fc['ibu_status'] ?? true): ?>
                            <div class="bg-pink-50 border border-pink-200 p-3 rounded-lg">
                                <p class="text-xs text-pink-700 mb-1">Status</p>
                                <p class="font-semibold text-pink-900" id="detailIbuStatus">-</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <?php if ($fc['ibu_pendidikan'] ?? true): ?>
                            <div class="bg-pink-50 border border-pink-200 p-3 rounded-lg">
                                <p class="text-xs text-pink-700 mb-1">Pendidikan</p>
                                <p class="font-semibold text-pink-900" id="detailIbuPendidikan">-</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($fc['ibu_pekerjaan'] ?? true): ?>
                            <div class="bg-pink-50 border border-pink-200 p-3 rounded-lg">
                                <p class="text-xs text-pink-700 mb-1">Pekerjaan</p>
                                <p class="font-semibold text-pink-900" id="detailIbuPekerjaan">-</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($fc['ibu_penghasilan'] ?? true): ?>
                            <div class="bg-pink-50 border border-pink-200 p-3 rounded-lg">
                                <p class="text-xs text-pink-700 mb-1">Penghasilan</p>
                                <p class="font-semibold text-pink-900" id="detailIbuPenghasilan">-</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($fc['ibu_no_hp'] ?? true): ?>
                        <div class="bg-pink-50 border border-pink-200 p-3 rounded-lg">
                            <p class="text-xs text-pink-700 mb-1">No. HP Ibu</p>
                            <p class="font-semibold text-pink-900" id="detailIbuHp">-</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Data Wali -->
                <!-- Data Wali -->
                <?php if (($fc['wali_nama'] ?? true) || ($fc['wali_hubungan'] ?? true) || ($fc['wali_nik'] ?? true) || ($fc['wali_no_hp'] ?? true) || ($fc['wali_pendidikan'] ?? true) || ($fc['wali_pekerjaan'] ?? true) || ($fc['wali_penghasilan'] ?? true)): ?>
                <div>
                    <h4
                        class="text-sm font-bold text-gray-800 mb-3 pb-2 border-b-2 border-amber-200 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-amber-600"></i>
                        Data Wali (Opsional)
                    </h4>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <?php if ($fc['wali_nama'] ?? true): ?>
                            <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg">
                                <p class="text-xs text-amber-700 mb-1">Nama Wali</p>
                                <p class="font-semibold text-amber-900" id="detailWali">-</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($fc['wali_hubungan'] ?? true): ?>
                            <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg">
                                <p class="text-xs text-amber-700 mb-1">Hubungan</p>
                                <p class="font-semibold text-amber-900" id="detailWaliHubungan">-</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <?php if ($fc['wali_nik'] ?? true): ?>
                            <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg">
                                <p class="text-xs text-amber-700 mb-1">NIK Wali</p>
                                <p class="font-semibold text-amber-900" id="detailWaliNik">-</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($fc['wali_no_hp'] ?? true): ?>
                            <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg">
                                <p class="text-xs text-amber-700 mb-1">No. HP Wali</p>
                                <p class="font-semibold text-amber-900" id="detailWaliHp">-</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <?php if ($fc['wali_pendidikan'] ?? true): ?>
                            <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg">
                                <p class="text-xs text-amber-700 mb-1">Pendidikan</p>
                                <p class="font-semibold text-amber-900" id="detailWaliPendidikan">-</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($fc['wali_pekerjaan'] ?? true): ?>
                            <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg">
                                <p class="text-xs text-amber-700 mb-1">Pekerjaan</p>
                                <p class="font-semibold text-amber-900" id="detailWaliPekerjaan">-</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($fc['wali_penghasilan'] ?? true): ?>
                            <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg">
                                <p class="text-xs text-amber-700 mb-1">Penghasilan</p>
                                <p class="font-semibold text-amber-900" id="detailWaliPenghasilan">-</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Akun Login -->
                <div>
                    <h4
                        class="text-sm font-bold text-gray-800 mb-3 pb-2 border-b-2 border-amber-200 flex items-center gap-2">
                        <i data-lucide="lock" class="w-4 h-4 text-amber-600"></i>
                        Akun Login
                    </h4>
                    <div class="grid grid-cols-1 gap-4">
                        <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg">
                            <p class="text-xs text-amber-700 mb-1">Username</p>
                            <p class="font-semibold text-amber-900" id="detailUsername">-</p>
                        </div>
                        <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg">
                            <p class="text-xs text-amber-700 mb-1">Password</p>
                            <p class="font-mono font-semibold text-amber-900 flex items-center gap-2">
                                <span id="detailPassword">-</span>
                                <button type="button" onclick="revealDetailPassword(this)"
                                    class="text-amber-600 hover:text-amber-800" title="Lihat password">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-2 rounded-b-2xl">
                <button onclick="closeDetailModal()"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-colors font-semibold">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Upload Foto Siswa 3x4 -->
    <div id="modalFotoSiswa"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] items-center justify-center p-4"
        style="align-items: flex-start; padding-top: 3vh;">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[94vh] overflow-y-auto">
            <!-- Header -->
            <div class="sticky top-0 bg-gradient-to-r from-emerald-500 to-emerald-600 px-5 py-3.5 rounded-t-2xl z-10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center overflow-hidden">
                            <img id="fotoModalHeaderImg" src="" alt="" class="w-full h-full object-cover hidden">
                            <span id="fotoModalHeaderInitial" class="text-white font-bold text-base"></span>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-sm leading-tight" id="fotoModalNama"></h3>
                            <p class="text-emerald-100 text-xs" id="fotoModalNisn"></p>
                        </div>
                    </div>
                    <button onclick="closeFotoModal()" class="text-white/80 hover:text-white transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="p-4">
                <!-- STEP 1: Input Selection (Camera or Upload) -->
                <div id="fm-input-step">
                    <!-- Mode Tabs -->
                    <div class="flex gap-2 mb-3">
                        <button type="button" id="fm-btn-camera" onclick="fmSetMode('camera')"
                            class="flex-1 px-3 py-2 rounded-lg text-sm font-medium border transition-colors bg-emerald-600 text-white border-emerald-600 flex items-center justify-center gap-1.5">
                            <i data-lucide="camera" class="w-4 h-4"></i> Kamera
                        </button>
                        <button type="button" id="fm-btn-upload" onclick="fmSetMode('upload')"
                            class="flex-1 px-3 py-2 rounded-lg text-sm font-medium border transition-colors bg-white text-gray-700 border-gray-300 hover:bg-gray-50 flex items-center justify-center gap-1.5">
                            <i data-lucide="upload" class="w-4 h-4"></i> Upload File
                        </button>
                    </div>

                    <!-- Camera View -->
                    <div id="fm-camera-mode">
                        <div class="relative rounded-xl overflow-hidden bg-black aspect-[4/3] flex items-center justify-center">
                            <video id="fm-video" autoplay playsinline
                                class="w-full h-full object-cover" style="display:none; transform: scaleX(-1);"></video>
                            <div id="fm-camera-placeholder"
                                class="w-full h-full flex flex-col items-center justify-center text-gray-400 bg-gray-100 p-4 text-center">
                                <i data-lucide="video" class="w-10 h-10 mb-2 text-gray-400"></i>
                                <p class="text-xs text-gray-500">Klik "Buka Kamera" untuk mengambil foto</p>
                            </div>
                            <!-- 3:4 guide overlay -->
                            <div id="fm-rect-guide" class="absolute inset-0 pointer-events-none hidden flex items-center justify-center">
                                <div style="width:52%; aspect-ratio:3/4; border:2px dashed rgba(255,255,255,0.85); border-radius:8px; box-shadow: 0 0 0 9999px rgba(0,0,0,0.4);"></div>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <button type="button" id="fm-btn-start" onclick="fmStartCamera()"
                                class="flex-1 px-3 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 flex items-center justify-center gap-1.5 font-medium">
                                <i data-lucide="video" class="w-4 h-4"></i> Buka Kamera
                            </button>
                            <button type="button" id="fm-btn-capture" onclick="fmCapture()" style="display:none"
                                class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 flex items-center justify-center gap-1.5 font-medium">
                                <i data-lucide="camera" class="w-4 h-4"></i> Ambil Foto
                            </button>
                            <button type="button" id="fm-btn-switch" onclick="fmSwitchCamera()" style="display:none"
                                class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 flex items-center justify-center"
                                title="Ganti kamera">
                                <i data-lucide="switch-camera" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Upload View -->
                    <div id="fm-upload-mode" style="display:none">
                        <label id="fm-dropzone"
                            class="flex flex-col items-center justify-center w-full py-12 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50 transition-colors">
                            <i data-lucide="image-plus" class="w-12 h-12 text-gray-400 mb-2"></i>
                            <span class="text-sm text-gray-700 font-medium">Pilih atau seret foto ke sini</span>
                            <span class="text-xs text-gray-400 mt-1">JPG, PNG, WebP (maks 5MB)</span>
                            <input type="file" id="fm-file-input" accept="image/jpeg,image/png,image/webp" class="hidden"
                                onchange="fmPreviewFile(this)">
                        </label>
                    </div>
                </div>

                <!-- STEP 2: Cropper/Editor (3:4 Canvas) -->
                <div id="fm-crop-step" style="display:none">
                    <div class="text-center mb-2">
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                            <i data-lucide="crop" class="w-3 h-3"></i> Pas Foto 3×4
                        </span>
                        <p class="text-[11px] text-gray-500 mt-1">Geser foto & atur zoom agar pas di bingkai</p>
                    </div>

                    <!-- Interactive 3:4 Canvas Container -->
                    <div class="flex justify-center">
                        <div class="relative rounded-xl overflow-hidden shadow-lg border-2 border-emerald-500 bg-gray-900"
                            style="width: 240px; height: 320px; touch-action: none;">
                            <canvas id="fm-crop-canvas" width="300" height="400"
                                class="w-full h-full cursor-grab active:cursor-grabbing select-none block"></canvas>
                        </div>
                    </div>

                    <!-- Zoom & Controls -->
                    <div class="flex items-center justify-center gap-2 mt-3">
                        <button type="button" onclick="fmZoomAdjust(-0.15)"
                            class="w-7 h-7 rounded-full bg-gray-200 hover:bg-gray-300 text-gray-700 flex items-center justify-center font-bold text-sm">−</button>
                        <input type="range" id="fm-zoom-slider" min="100" max="300" value="100"
                            class="w-36 accent-emerald-600 cursor-pointer" oninput="fmZoomSliderChange(this.value)">
                        <button type="button" onclick="fmZoomAdjust(0.15)"
                            class="w-7 h-7 rounded-full bg-gray-200 hover:bg-gray-300 text-gray-700 flex items-center justify-center font-bold text-sm">+</button>
                    </div>

                    <div class="flex justify-center mt-2">
                        <button type="button" onclick="fmResetToInput()"
                            class="text-xs text-gray-500 hover:text-emerald-600 flex items-center gap-1 font-medium py-1 px-2 rounded hover:bg-gray-100 transition-colors">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Ambil Ulang / Ganti Foto
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between rounded-b-2xl">
                <button type="button" id="fm-btn-delete" onclick="fmHapusFoto()" style="display:none"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-red-50 hover:bg-red-100 text-red-600 flex items-center gap-1 transition-colors">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus Foto
                </button>
                <div class="flex-1"></div>
                <div class="flex gap-2">
                    <button type="button" onclick="closeFotoModal()"
                        class="px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition-colors">
                        Batal
                    </button>
                    <button type="button" id="fm-btn-save" onclick="fmSimpanFoto()" disabled
                        class="px-4 py-1.5 text-xs font-medium rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white flex items-center gap-1.5 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <i data-lucide="save" class="w-3.5 h-3.5"></i>
                        <span id="fm-save-text">Simpan Foto</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ===== FOTO MODAL with Interactive 3:4 Canvas Cropper =====
        let fmStream = null;
        let fmSiswaId = null;
        let fmHasFoto = false;
        let fmFacingMode = 'user';

        // Canvas Cropper State
        let fmImg = null;
        let fmZoom = 1.0;
        let fmBaseScale = 1.0;
        let fmPosX = 0, fmPosY = 0;
        let fmDragging = false;
        let fmDragStartX = 0, fmDragStartY = 0;
        let fmInitialPosX = 0, fmInitialPosY = 0;

        const FM_CW = 300; // Canvas output width (3:4 ratio)
        const FM_CH = 400; // Canvas output height

        function openFotoModal(siswa) {
            fmSiswaId = siswa.id_siswa;
            fmHasFoto = !!siswa.foto;
            fmResetToInput();

            const initial = siswa.nama_siswa ? siswa.nama_siswa.charAt(0).toUpperCase() : 'A';
            document.getElementById('fotoModalNama').textContent = siswa.nama_siswa || '-';
            document.getElementById('fotoModalNisn').textContent = 'NISN: ' + (siswa.nisn || '-') + (siswa.nama_kelas ? ' · ' + siswa.nama_kelas : '');
            document.getElementById('fotoModalHeaderInitial').textContent = initial;

            const headerImg = document.getElementById('fotoModalHeaderImg');
            const headerInit = document.getElementById('fotoModalHeaderInitial');

            if (fmHasFoto) {
                const url = '<?= BASEURL; ?>/foto/siswa/' + siswa.id_siswa + '?v=' + Date.now();
                headerImg.src = url;
                headerImg.classList.remove('hidden');
                headerInit.classList.add('hidden');
                headerImg.onerror = function() { headerImg.classList.add('hidden'); headerInit.classList.remove('hidden'); };
                document.getElementById('fm-btn-delete').style.display = '';
            } else {
                headerImg.classList.add('hidden');
                headerInit.classList.remove('hidden');
                document.getElementById('fm-btn-delete').style.display = 'none';
            }

            document.getElementById('fm-btn-save').disabled = true;
            fmSetMode('camera');

            const modal = document.getElementById('modalFotoSiswa');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function closeFotoModal() {
            fmStopCamera();
            const modal = document.getElementById('modalFotoSiswa');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            fmSiswaId = null;
            fmImg = null;
            const fi = document.getElementById('fm-file-input');
            if (fi) fi.value = '';
        }

        function fmSetMode(mode) {
            const camBtn = document.getElementById('fm-btn-camera');
            const uplBtn = document.getElementById('fm-btn-upload');
            const camDiv = document.getElementById('fm-camera-mode');
            const uplDiv = document.getElementById('fm-upload-mode');
            if (mode === 'camera') {
                camBtn.className = 'flex-1 px-3 py-2 rounded-lg text-sm font-medium border transition-colors bg-emerald-600 text-white border-emerald-600 flex items-center justify-center gap-1.5';
                uplBtn.className = 'flex-1 px-3 py-2 rounded-lg text-sm font-medium border transition-colors bg-white text-gray-700 border-gray-300 hover:bg-gray-50 flex items-center justify-center gap-1.5';
                camDiv.style.display = '';
                uplDiv.style.display = 'none';
            } else {
                uplBtn.className = 'flex-1 px-3 py-2 rounded-lg text-sm font-medium border transition-colors bg-emerald-600 text-white border-emerald-600 flex items-center justify-center gap-1.5';
                camBtn.className = 'flex-1 px-3 py-2 rounded-lg text-sm font-medium border transition-colors bg-white text-gray-700 border-gray-300 hover:bg-gray-50 flex items-center justify-center gap-1.5';
                camDiv.style.display = 'none';
                uplDiv.style.display = '';
                fmStopCamera();
            }
        }

        async function fmStartCamera() {
            try {
                const video = document.getElementById('fm-video');
                const ph = document.getElementById('fm-camera-placeholder');
                fmStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: fmFacingMode, width: { ideal: 1280 }, height: { ideal: 720 } }
                });
                video.srcObject = fmStream;
                video.style.display = 'block';
                ph.style.display = 'none';
                document.getElementById('fm-btn-start').style.display = 'none';
                document.getElementById('fm-btn-capture').style.display = '';
                document.getElementById('fm-btn-switch').style.display = '';
                document.getElementById('fm-rect-guide').classList.remove('hidden');
            } catch (e) {
                alert('Tidak dapat mengakses kamera. Gunakan mode Upload File.');
                fmSetMode('upload');
            }
        }

        function fmStopCamera() {
            if (fmStream) {
                fmStream.getTracks().forEach(t => t.stop());
                fmStream = null;
            }
            const video = document.getElementById('fm-video');
            if (video) { video.style.display = 'none'; video.srcObject = null; }
            const ph = document.getElementById('fm-camera-placeholder');
            if (ph) ph.style.display = '';
            const bs = document.getElementById('fm-btn-start');
            if (bs) bs.style.display = '';
            const bc = document.getElementById('fm-btn-capture');
            if (bc) bc.style.display = 'none';
            const bsw = document.getElementById('fm-btn-switch');
            if (bsw) bsw.style.display = 'none';
            const rg = document.getElementById('fm-rect-guide');
            if (rg) rg.classList.add('hidden');
        }

        async function fmSwitchCamera() {
            fmFacingMode = fmFacingMode === 'user' ? 'environment' : 'user';
            fmStopCamera();
            await fmStartCamera();
        }

        function fmCapture() {
            const video = document.getElementById('fm-video');
            if (!video || !video.videoWidth) return;

            // Render mirrored frame to offscreen canvas
            const offCanvas = document.createElement('canvas');
            offCanvas.width = video.videoWidth;
            offCanvas.height = video.videoHeight;
            const ctx = offCanvas.getContext('2d');
            ctx.translate(video.videoWidth, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0);

            fmStopCamera();
            const dataUrl = offCanvas.toDataURL('image/jpeg', 0.95);
            fmLoadImageIntoCropper(dataUrl);
        }

        function fmPreviewFile(input) {
            if (input.files && input.files[0]) {
                if (input.files[0].size > 5 * 1024 * 1024) {
                    alert('Ukuran foto maksimal 5MB');
                    input.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    fmLoadImageIntoCropper(e.target.result);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // ===== 3:4 CANVAS CROPPER ENGINE =====
        function fmLoadImageIntoCropper(src) {
            const img = new Image();
            img.onload = function() {
                fmImg = img;
                document.getElementById('fm-input-step').style.display = 'none';
                document.getElementById('fm-crop-step').style.display = '';
                document.getElementById('fm-btn-save').disabled = false;

                // Calculate base scale to fill 300x400
                const scaleX = FM_CW / img.naturalWidth;
                const scaleY = FM_CH / img.naturalHeight;
                fmBaseScale = Math.max(scaleX, scaleY);
                fmZoom = 1.0;
                document.getElementById('fm-zoom-slider').value = 100;

                // Center initial position
                const curW = img.naturalWidth * fmBaseScale;
                const curH = img.naturalHeight * fmBaseScale;
                fmPosX = (FM_CW - curW) / 2;
                fmPosY = (FM_CH - curH) / 2;

                fmClampPos();
                fmDrawCanvas();
                fmInitCanvasEvents();
                if (typeof lucide !== 'undefined') lucide.createIcons();
            };
            img.src = src;
        }

        function fmResetToInput() {
            fmImg = null;
            document.getElementById('fm-input-step').style.display = '';
            document.getElementById('fm-crop-step').style.display = 'none';
            document.getElementById('fm-btn-save').disabled = true;
            const fi = document.getElementById('fm-file-input');
            if (fi) fi.value = '';
            fmStopCamera();
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function fmClampPos() {
            if (!fmImg) return;
            const totalScale = fmBaseScale * fmZoom;
            const curW = fmImg.naturalWidth * totalScale;
            const curH = fmImg.naturalHeight * totalScale;

            const minX = FM_CW - curW;
            const maxX = 0;
            const minY = FM_CH - curH;
            const maxY = 0;

            fmPosX = Math.max(minX, Math.min(maxX, fmPosX));
            fmPosY = Math.max(minY, Math.min(maxY, fmPosY));
        }

        function fmDrawCanvas() {
            if (!fmImg) return;
            const canvas = document.getElementById('fm-crop-canvas');
            const ctx = canvas.getContext('2d');
            const totalScale = fmBaseScale * fmZoom;
            const curW = fmImg.naturalWidth * totalScale;
            const curH = fmImg.naturalHeight * totalScale;

            ctx.fillStyle = '#111827';
            ctx.fillRect(0, 0, FM_CW, FM_CH);
            ctx.drawImage(fmImg, fmPosX, fmPosY, curW, curH);
        }

        function fmZoomSliderChange(val) {
            if (!fmImg) return;
            const oldZoom = fmZoom;
            const newZoom = val / 100;
            if (newZoom < 1.0) return;

            // Zoom centered on canvas center (150, 200)
            const cx = FM_CW / 2;
            const cy = FM_CH / 2;
            const oldScale = fmBaseScale * oldZoom;
            const newScale = fmBaseScale * newZoom;

            const imgX = (cx - fmPosX) / oldScale;
            const imgY = (cy - fmPosY) / oldScale;

            fmZoom = newZoom;
            fmPosX = cx - (imgX * newScale);
            fmPosY = cy - (imgY * newScale);

            fmClampPos();
            fmDrawCanvas();
        }

        function fmZoomAdjust(delta) {
            const slider = document.getElementById('fm-zoom-slider');
            let current = parseFloat(slider.value) / 100;
            let next = Math.max(1.0, Math.min(3.0, current + delta));
            slider.value = Math.round(next * 100);
            fmZoomSliderChange(slider.value);
        }

        let fmCanvasEventsBound = false;
        function fmInitCanvasEvents() {
            const canvas = document.getElementById('fm-crop-canvas');
            if (fmCanvasEventsBound || !canvas) return;
            fmCanvasEventsBound = true;

            function getCanvasScale() {
                const rect = canvas.getBoundingClientRect();
                return {
                    scaleX: FM_CW / rect.width,
                    scaleY: FM_CH / rect.height
                };
            }

            function onPointerDown(e) {
                if (!fmImg) return;
                e.preventDefault();
                fmDragging = true;
                const pt = e.touches ? e.touches[0] : e;
                fmDragStartX = pt.clientX;
                fmDragStartY = pt.clientY;
                fmInitialPosX = fmPosX;
                fmInitialPosY = fmPosY;
            }

            function onPointerMove(e) {
                if (!fmDragging || !fmImg) return;
                e.preventDefault();
                const pt = e.touches ? e.touches[0] : e;
                const s = getCanvasScale();
                const dx = (pt.clientX - fmDragStartX) * s.scaleX;
                const dy = (pt.clientY - fmDragStartY) * s.scaleY;
                fmPosX = fmInitialPosX + dx;
                fmPosY = fmInitialPosY + dy;
                fmClampPos();
                fmDrawCanvas();
            }

            function onPointerUp() {
                fmDragging = false;
            }

            canvas.addEventListener('mousedown', onPointerDown);
            canvas.addEventListener('touchstart', onPointerDown, { passive: false });
            window.addEventListener('mousemove', onPointerMove);
            window.addEventListener('touchmove', onPointerMove, { passive: false });
            window.addEventListener('mouseup', onPointerUp);
            window.addEventListener('touchend', onPointerUp);

            // Mouse wheel to zoom
            canvas.addEventListener('wheel', function(e) {
                e.preventDefault();
                const delta = e.deltaY < 0 ? 0.1 : -0.1;
                fmZoomAdjust(delta);
            }, { passive: false });
        }

        async function fmSimpanFoto() {
            if (!fmSiswaId || !fmImg) return;
            const saveBtn = document.getElementById('fm-btn-save');
            const saveText = document.getElementById('fm-save-text');
            saveBtn.disabled = true;
            saveText.textContent = 'Menyimpan...';

            const canvas = document.getElementById('fm-crop-canvas');
            const base64Data = canvas.toDataURL('image/jpeg', 0.90);

            const formData = new FormData();
            formData.append('id_siswa', fmSiswaId);
            formData.append('foto_base64', base64Data);

            try {
                const res = await fetch('<?= BASEURL; ?>/admin/simpanFotoSiswaAjax', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.success) {
                    fmRefreshAvatars(fmSiswaId, data.foto_url);
                    closeFotoModal();
                } else {
                    alert(data.message || 'Gagal menyimpan foto');
                }
            } catch (e) {
                alert('Terjadi kesalahan jaringan');
            } finally {
                saveBtn.disabled = false;
                saveText.textContent = 'Simpan Foto';
            }
        }

        async function fmHapusFoto() {
            if (!fmSiswaId) return;
            if (!confirm('Hapus foto siswa ini?')) return;

            const delBtn = document.getElementById('fm-btn-delete');
            delBtn.disabled = true;

            const formData = new FormData();
            formData.append('id_siswa', fmSiswaId);

            try {
                const res = await fetch('<?= BASEURL; ?>/admin/hapusFotoSiswaAjax', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.success) {
                    fmRefreshAvatars(fmSiswaId, null);
                    closeFotoModal();
                } else {
                    alert(data.message || 'Gagal menghapus foto');
                }
            } catch (e) {
                alert('Terjadi kesalahan jaringan');
            } finally {
                delBtn.disabled = false;
            }
        }

        function fmRefreshAvatars(idSiswa, fotoUrl) {
            document.querySelectorAll('[data-foto-siswa="' + idSiswa + '"]').forEach(el => {
                const img = el.querySelector('img');
                const fallback = el.querySelector('.fm-fallback');
                if (fotoUrl) {
                    if (img) {
                        img.src = fotoUrl;
                        img.classList.remove('hidden');
                        img.style.display = '';
                    }
                    if (fallback) fallback.classList.add('hidden');
                    el.closest('[data-siswa-row]')?.setAttribute('data-has-foto', '1');
                } else {
                    if (img) { img.classList.add('hidden'); img.src = ''; }
                    if (fallback) fallback.classList.remove('hidden');
                    el.closest('[data-siswa-row]')?.removeAttribute('data-has-foto');
                }
            });
        }

        document.getElementById('modalFotoSiswa')?.addEventListener('click', function(e) {
            if (e.target === this) closeFotoModal();
        });

        // Drag & drop support
        (function() {
            const dz = document.getElementById('fm-dropzone');
            if (!dz) return;
            ['dragenter', 'dragover'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('border-emerald-400', 'bg-emerald-50'); }));
            ['dragleave', 'drop'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.remove('border-emerald-400', 'bg-emerald-50'); }));
            dz.addEventListener('drop', e => {
                const file = e.dataTransfer.files[0];
                if (file && file.type.startsWith('image/')) {
                    const input = document.getElementById('fm-file-input');
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                    fmPreviewFile(input);
                }
            });
        })();
                const res = await fetch('<?= BASEURL; ?>/admin/hapusFotoSiswaAjax', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.success) {
                    fmRefreshAvatars(fmSiswaId, null);
                    closeFotoModal();
                } else {
                    alert(data.message || 'Gagal menghapus foto');
                }
            } catch (e) {
                alert('Terjadi kesalahan jaringan');
            } finally {
                delBtn.disabled = false;
            }
        }

        function fmRefreshAvatars(idSiswa, fotoUrl) {
            document.querySelectorAll('[data-foto-siswa="' + idSiswa + '"]').forEach(el => {
                const img = el.querySelector('img');
                const fallback = el.querySelector('.fm-fallback');
                if (fotoUrl) {
                    if (img) {
                        img.src = fotoUrl;
                        img.classList.remove('hidden');
                        img.style.display = '';
                    }
                    if (fallback) fallback.classList.add('hidden');
                    el.closest('[data-siswa-row]')?.setAttribute('data-has-foto', '1');
                } else {
                    if (img) { img.classList.add('hidden'); img.src = ''; }
                    if (fallback) fallback.classList.remove('hidden');
                    el.closest('[data-siswa-row]')?.removeAttribute('data-has-foto');
                }
            });
        }

        document.getElementById('modalFotoSiswa')?.addEventListener('click', function(e) {
            if (e.target === this) closeFotoModal();
        });

        // Drag & drop support
        (function() {
            const dz = document.getElementById('fm-dropzone');
            if (!dz) return;
            ['dragenter', 'dragover'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('border-emerald-400', 'bg-emerald-50'); }));
            ['dragleave', 'drop'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.remove('border-emerald-400', 'bg-emerald-50'); }));
            dz.addEventListener('drop', e => {
                const file = e.dataTransfer.files[0];
                if (file && file.type.startsWith('image/')) {
                    const input = document.getElementById('fm-file-input');
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                    fmPreviewFile(input);
                }
            });
        })();

        // v1.26.0 - Lihat password on-demand (khusus admin). Klik lagi untuk menyembunyikan.
        function revealPassword(idRef, role, targetId, btn) {
            const el = document.getElementById(targetId);
            if (!el) return;
            if (el.dataset.revealed === '1') {
                el.textContent = '••••••••';
                delete el.dataset.revealed;
                if (btn) btn.title = 'Lihat password';
                return;
            }
            const original = el.textContent;
            el.textContent = '...';
            fetch('<?= BASEURL; ?>/admin/lihatPassword?id_ref=' + encodeURIComponent(idRef) + '&role=' + encodeURIComponent(role), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        el.textContent = d.password;
                        el.dataset.revealed = '1';
                        if (btn) btn.title = 'Sembunyikan password';
                    } else {
                        el.textContent = d.message || 'Gagal memuat';
                    }
                })
                .catch(() => { el.textContent = original; });
        }

        function revealDetailPassword(btn) {
            if (!window.__detailSiswaId) return;
            revealPassword(window.__detailSiswaId, 'siswa', 'detailPassword', btn);
        }

        // Show detail siswa modal
        function showDetailSiswa(siswa) {
            // Helper to safely set text content
            const setText = (id, text) => {
                const el = document.getElementById(id);
                if (el) el.textContent = text;
            };

            // Update avatar
            const avatarSpan = document.getElementById('detailAvatar');
            const avatarImg = document.getElementById('detailAvatarImg');
            const initial = siswa.nama_siswa ? siswa.nama_siswa.charAt(0).toUpperCase() : 'A';
            avatarSpan.textContent = initial;

            if (siswa.foto) {
                avatarImg.onerror = function() {
                    avatarImg.classList.add('hidden');
                    avatarSpan.classList.remove('hidden');
                };
                avatarImg.onload = function() {
                    avatarSpan.classList.add('hidden');
                    avatarImg.classList.remove('hidden');
                };
                avatarImg.src = '<?= BASEURL; ?>/foto/siswa/' + siswa.id_siswa;
            } else {
                avatarSpan.classList.remove('hidden');
                avatarImg.classList.add('hidden');
                avatarImg.src = '';
            }

            // Update nama dan NISN
            setText('detailNama', siswa.nama_siswa || '-');
            setText('detailNisn', 'NISN: ' + (siswa.nisn || '-'));

            // Data Pribadi
            const jk = siswa.jenis_kelamin === 'L' ? 'Laki-laki' : siswa.jenis_kelamin === 'P' ? 'Perempuan' : '-';
            setText('detailJK', jk);
            setText('detailTempatLahir', siswa.tempat_lahir || '-');

            // Format tanggal lahir
            let tglLahir = '-';
            if (siswa.tgl_lahir && siswa.tgl_lahir !== '0000-00-00') {
                const date = new Date(siswa.tgl_lahir);
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                tglLahir = date.toLocaleDateString('id-ID', options);
            }
            setText('detailTglLahir', tglLahir);
            setText('detailKelas', siswa.nama_kelas || '-');

            // Kontak
            setText('detailNoWa', siswa.no_wa || '-');
            setText('detailEmail', siswa.email || '-');

            // Alamat Lengkap
            setText('detailAlamat', siswa.alamat || '-');
            const rtRw = (siswa.rt || siswa.rw) ? `${siswa.rt || '-'} / ${siswa.rw || '-'}` : '-';
            setText('detailRtRw', rtRw);
            setText('detailDusun', siswa.dusun || '-');
            setText('detailKelurahan', siswa.kelurahan || '-');
            setText('detailKecamatan', siswa.kecamatan || '-');
            setText('detailKabupaten', siswa.kabupaten || '-');
            setText('detailProvinsi', siswa.provinsi || '-');
            setText('detailKodePos', siswa.kode_pos || '-');

            // Data Ayah
            setText('detailAyah', siswa.ayah_kandung || '-');
            setText('detailAyahNik', siswa.ayah_nik || '-');
            let ayahTtl = '-';
            if (siswa.ayah_tempat_lahir || siswa.ayah_tanggal_lahir) {
                const tgl = siswa.ayah_tanggal_lahir && siswa.ayah_tanggal_lahir !== '0000-00-00'
                    ? new Date(siswa.ayah_tanggal_lahir).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
                    : '';
                ayahTtl = `${siswa.ayah_tempat_lahir || '-'}${tgl ? ', ' + tgl : ''}`;
            }
            setText('detailAyahTtl', ayahTtl);
            setText('detailAyahStatus', siswa.ayah_status || '-');
            setText('detailAyahPendidikan', siswa.ayah_pendidikan || '-');
            setText('detailAyahPekerjaan', siswa.ayah_pekerjaan || '-');
            setText('detailAyahPenghasilan', siswa.ayah_penghasilan || '-');
            setText('detailAyahHp', siswa.ayah_no_hp || '-');

            // Data Ibu
            setText('detailIbu', siswa.ibu_kandung || '-');
            setText('detailIbuNik', siswa.ibu_nik || '-');
            let ibuTtl = '-';
            if (siswa.ibu_tempat_lahir || siswa.ibu_tanggal_lahir) {
                const tgl = siswa.ibu_tanggal_lahir && siswa.ibu_tanggal_lahir !== '0000-00-00'
                    ? new Date(siswa.ibu_tanggal_lahir).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
                    : '';
                ibuTtl = `${siswa.ibu_tempat_lahir || '-'}${tgl ? ', ' + tgl : ''}`;
            }
            setText('detailIbuTtl', ibuTtl);
            setText('detailIbuStatus', siswa.ibu_status || '-');
            setText('detailIbuPendidikan', siswa.ibu_pendidikan || '-');
            setText('detailIbuPekerjaan', siswa.ibu_pekerjaan || '-');
            setText('detailIbuPenghasilan', siswa.ibu_penghasilan || '-');
            setText('detailIbuHp', siswa.ibu_no_hp || '-');

            // Data Wali
            setText('detailWali', siswa.wali_nama || '-');
            setText('detailWaliHubungan', siswa.wali_hubungan || '-');
            setText('detailWaliNik', siswa.wali_nik || '-');
            setText('detailWaliHp', siswa.wali_no_hp || '-');
            setText('detailWaliPendidikan', siswa.wali_pendidikan || '-');
            setText('detailWaliPekerjaan', siswa.wali_pekerjaan || '-');
            setText('detailWaliPenghasilan', siswa.wali_penghasilan || '-');

            // Akun Login
            setText('detailUsername', siswa.nisn || '-');
            setText('detailPassword', siswa.password_plain || '-');
            window.__detailSiswaId = siswa.id_siswa || null;
            const dp = document.getElementById('detailPassword');
            if (dp) delete dp.dataset.revealed;

            // Show modal
            const modal = document.getElementById('modalDetailSiswa');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            // Reinitialize icons in modal
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        function closeDetailModal() {
            document.getElementById('modalDetailSiswa').classList.add('hidden');
            document.getElementById('modalDetailSiswa').classList.remove('flex');
        }

        // Close modal when clicking outside
        document.getElementById('modalDetailSiswa')?.addEventListener('click', function (e) {
            if (e.target === this) {
                closeDetailModal();
            }
        });

        // Close modal with ESC key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDetailModal();
            }
        });

        // Cetak Kartu Login - Opens print page with filtered students
        function cetakKartuLogin() {
            const kelasFilter = document.getElementById('filter-kelas').value;
            let url = '<?= BASEURL; ?>/admin/cetakKartuLoginSiswa';
            if (kelasFilter) {
                url += '?kelas=' + encodeURIComponent(kelasFilter);
            }
            window.open(url, '_blank');
        }
    </script>
</body>

</html>