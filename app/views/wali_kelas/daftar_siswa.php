<?php
// File: app/views/wali_kelas/daftar_siswa.php
?>

<div class="container-fluid">
    <?php $fc = $data['fieldConfig'] ?? []; ?>
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-secondary-800 mb-2">
                    <i data-lucide="users-round" class="inline-block w-8 h-8 mr-2 text-primary-600"></i>
                    Daftar Siswa Kelas
                </h1>
                <p class="text-secondary-600">Kelola dan monitor siswa di kelas Anda</p>
            </div>
        </div>
    </div>

    <!-- Info Kelas -->
    <div class="glass-effect rounded-2xl p-6 mb-6 border border-white/20">
        <div class="flex items-center gap-4">
            <div class="gradient-primary p-4 rounded-xl">
                <i data-lucide="school" class="w-8 h-8 text-white"></i>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-secondary-800">
                    Kelas <?= htmlspecialchars($data['wali_kelas_info']['nama_kelas'] ?? '-'); ?>
                </h3>
                <p class="text-secondary-600">
                    Tahun Pelajaran: <?= htmlspecialchars($data['wali_kelas_info']['nama_tp'] ?? '-'); ?>
                </p>
            </div>
            <div class="ml-auto text-right">
                <p class="text-sm text-secondary-500">Total Siswa</p>
                <p class="text-3xl font-bold text-primary-600">
                    <?= count($data['siswa_list'] ?? []); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Statistik Gender -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <?php
        $totalSiswa = count($data['siswa_list'] ?? []);
        $lakiLaki = 0;
        $perempuan = 0;

        foreach ($data['siswa_list'] ?? [] as $siswa) {
            if ($siswa['jenis_kelamin'] == 'L') {
                $lakiLaki++;
            } else {
                $perempuan++;
            }
        }
        ?>

        <!-- Total Siswa -->
        <div class="glass-effect rounded-2xl p-6 border border-white/20 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-secondary-500 mb-1">Total Siswa</p>
                    <h3 class="text-3xl font-bold text-secondary-800"><?= $totalSiswa; ?></h3>
                    <p class="text-xs text-secondary-400 mt-2">Siswa Aktif</p>
                </div>
                <div class="gradient-primary p-4 rounded-xl">
                    <i data-lucide="users" class="w-8 h-8 text-white"></i>
                </div>
            </div>
        </div>

        <!-- Laki-laki -->
        <div class="glass-effect rounded-2xl p-6 border border-white/20 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-secondary-500 mb-1">Laki-laki</p>
                    <h3 class="text-3xl font-bold text-blue-600"><?= $lakiLaki; ?></h3>
                    <p class="text-xs text-secondary-400 mt-2">
                        <?= $totalSiswa > 0 ? round(($lakiLaki / $totalSiswa) * 100, 1) : 0; ?>% dari total
                    </p>
                </div>
                <div class="bg-gradient-to-br from-blue-400 to-blue-600 p-4 rounded-xl">
                    <i data-lucide="user" class="w-8 h-8 text-white"></i>
                </div>
            </div>
        </div>

        <!-- Perempuan -->
        <div class="glass-effect rounded-2xl p-6 border border-white/20 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-secondary-500 mb-1">Perempuan</p>
                    <h3 class="text-3xl font-bold text-pink-600"><?= $perempuan; ?></h3>
                    <p class="text-xs text-secondary-400 mt-2">
                        <?= $totalSiswa > 0 ? round(($perempuan / $totalSiswa) * 100, 1) : 0; ?>% dari total
                    </p>
                </div>
                <div class="bg-gradient-to-br from-pink-400 to-pink-600 p-4 rounded-xl">
                    <i data-lucide="user" class="w-8 h-8 text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Siswa -->
    <div class="glass-effect rounded-2xl border border-white/20 overflow-hidden">
        <!-- Header Tabel -->
        <div class="bg-gradient-to-r from-primary-500 to-primary-600 px-4 sm:px-6 py-4">
            <h2 class="text-lg sm:text-xl font-bold text-white flex items-center">
                <i data-lucide="list" class="w-5 h-5 mr-2"></i>
                Daftar Siswa
            </h2>
        </div>

        <!-- Search & Filter -->
        <div class="p-3 sm:p-4 bg-white/50 border-b border-white/20">
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                <div class="flex-1">
                    <input type="text" id="searchSiswa" placeholder="Cari nama atau NISN siswa..."
                        class="w-full px-3 sm:px-4 py-2 rounded-lg border border-secondary-200 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                </div>
                <button
                    class="w-full sm:w-auto px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    <span class="sm:hidden">Cari</span>
                </button>
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-secondary-200">
                <thead class="bg-secondary-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                            No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                            NISN</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                            Nama Siswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                            Jenis Kelamin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="px-6 py-3 text-center text-xs font-medium text-secondary-500 uppercase tracking-wider">
                            Kontak Ortu</th>
                        <th
                            class="px-6 py-3 text-center text-xs font-medium text-secondary-500 uppercase tracking-wider">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-secondary-200" id="tableSiswa">
                    <?php if (!empty($data['siswa_list'])): ?>
                        <?php $no = 1;
                        foreach ($data['siswa_list'] as $siswa): ?>
                            <tr class="hover:bg-secondary-50 transition-colors siswa-row">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900"><?= $no++; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900 siswa-nisn">
                                    <?= htmlspecialchars($siswa['nisn']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap siswa-nama">
                                    <div class="flex items-center">
                                        <div
                                            class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-bold mr-3">
                                            <?= strtoupper(substr($siswa['nama_siswa'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-secondary-900">
                                                <?= htmlspecialchars($siswa['nama_siswa']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900">
                                    <?php if ($siswa['jenis_kelamin'] == 'L'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <i data-lucide="user" class="w-3 h-3 inline"></i> Laki-laki
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-pink-100 text-pink-800">
                                            <i data-lucide="user" class="w-3 h-3 inline"></i> Perempuan
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($siswa['status_siswa'] == 'aktif'): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <?= ucfirst($siswa['status_siswa']); ?>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex gap-2 justify-center">
                                        <a href="<?= BASEURL; ?>/waliKelas/dokumenSiswa/<?= $siswa['id_siswa']; ?>"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg transition-colors">
                                            <i data-lucide="folder-open" class="w-3 h-3"></i> Dokumen
                                        </a>
                                        <button
                                            onclick="downloadSKSA(<?= $siswa['id_siswa']; ?>, '<?= htmlspecialchars($siswa['nama_siswa'], ENT_QUOTES); ?>')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded-lg transition-colors">
                                            <i data-lucide="download" class="w-3 h-3"></i> SKSA
                                        </button>
                                        <button onclick="showDetailSiswa(<?= htmlspecialchars(json_encode($siswa)); ?>)"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition-colors">
                                            <i data-lucide="eye" class="w-3 h-3"></i> Detail
                                        </button>
                                        <a href="<?= BASEURL; ?>/waliKelas/editSiswa/<?= $siswa['id_siswa']; ?>"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-semibold rounded-lg transition-colors">
                                            <i data-lucide="edit" class="w-3 h-3"></i> Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-secondary-500">
                                <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 text-secondary-300"></i>
                                <p>Belum ada siswa di kelas ini</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden divide-y divide-secondary-200" id="mobileCardList">
            <?php if (!empty($data['siswa_list'])): ?>
                <?php foreach ($data['siswa_list'] as $index => $siswa): ?>
                    <div class="p-4 bg-white hover:bg-secondary-50 transition-colors siswa-card"
                        data-nama="<?= htmlspecialchars($siswa['nama_siswa']); ?>"
                        data-nisn="<?= htmlspecialchars($siswa['nisn']); ?>">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-bold flex-shrink-0">
                                <?= strtoupper(substr($siswa['nama_siswa'], 0, 1)); ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-secondary-900 truncate">
                                    <?= htmlspecialchars($siswa['nama_siswa']); ?>
                                </h3>
                                <p class="text-xs text-secondary-500 mt-0.5">
                                    NISN: <?= htmlspecialchars($siswa['nisn']); ?>
                                </p>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <?php if ($siswa['jenis_kelamin'] == 'L'): ?>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Laki-laki
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-pink-100 text-pink-800">
                                            Perempuan
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($siswa['status_siswa'] == 'aktif'): ?>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <?= ucfirst($siswa['status_siswa']); ?>
                                        </span>
                                    <?php endif; ?>

                                    <!-- Status HP Ortu -->
                                    <?php if (!empty($siswa['ayah_no_hp'])): ?>
                                        <span
                                            class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                                            <i data-lucide="phone" class="w-3 h-3 inline mr-0.5"></i> Ayah
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800 border border-red-200 opacity-60">
                                            <i data-lucide="phone-off" class="w-3 h-3 inline mr-0.5"></i> Ayah
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($siswa['ibu_no_hp'])): ?>
                                        <span
                                            class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                                            <i data-lucide="phone" class="w-3 h-3 inline mr-0.5"></i> Ibu
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800 border border-red-200 opacity-60">
                                            <i data-lucide="phone-off" class="w-3 h-3 inline mr-0.5"></i> Ibu
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex flex-wrap gap-2 mt-3">
                                    <a href="<?= BASEURL; ?>/waliKelas/dokumenSiswa/<?= $siswa['id_siswa']; ?>"
                                        class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg transition-colors">
                                        <i data-lucide="folder-open" class="w-3 h-3"></i> Dokumen
                                    </a>
                                    <button
                                        onclick="downloadSKSA(<?= $siswa['id_siswa']; ?>, '<?= htmlspecialchars($siswa['nama_siswa'], ENT_QUOTES); ?>')"
                                        class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded-lg transition-colors">
                                        <i data-lucide="download" class="w-3 h-3"></i> SKSA
                                    </button>
                                    <button onclick="showDetailSiswa(<?= htmlspecialchars(json_encode($siswa)); ?>)"
                                        class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg transition-colors">
                                        <i data-lucide="eye" class="w-3 h-3"></i> Detail
                                    </button>
                                    <a href="<?= BASEURL; ?>/waliKelas/editSiswa/<?= $siswa['id_siswa']; ?>"
                                        class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-semibold rounded-lg transition-colors">
                                        <i data-lucide="edit" class="w-3 h-3"></i> Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-8 text-center text-secondary-500">
                    <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 text-secondary-300"></i>
                    <p>Belum ada siswa di kelas ini</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="px-4 sm:px-6 py-4 bg-white/50 border-t border-white/20">
            <p class="text-sm text-secondary-600">
                Menampilkan <span class="font-semibold" id="countSiswa"><?= count($data['siswa_list'] ?? []); ?></span>
                siswa
            </p>
        </div>
    </div>
</div>

<!-- Modal Detail Siswa -->
<div id="modalDetailSiswa"
    class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div
            class="bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-4 flex items-center justify-between rounded-t-2xl sticky top-0 z-10">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i data-lucide="user-circle" class="w-6 h-6"></i>
                Detail Siswa
            </h3>
            <button onclick="closeDetailModal()" class="text-white hover:bg-white/20 rounded-lg p-1 transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-6">
            <!-- Avatar & Nama -->
            <div class="text-center pb-4 border-b border-secondary-200">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-3xl font-bold mx-auto mb-3"
                    id="detailAvatar">
                    A
                </div>
                <h4 class="text-xl font-bold text-secondary-900 mb-1" id="detailNama">-</h4>
                <p class="text-secondary-500" id="detailNisn">NISN: -</p>
            </div>

            <!-- Data Pribadi -->
            <div>
                <h5
                    class="font-bold text-sm text-secondary-800 flex items-center gap-2 mb-3 pb-2 border-b-2 border-indigo-200">
                    <i data-lucide="user-circle" class="w-4 h-4 text-indigo-600"></i>
                    Data Pribadi
                </h5>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                        <p class="text-xs text-gray-600 mb-1">Jenis Kelamin</p>
                        <p class="font-semibold text-gray-900" id="detailJK">-</p>
                    </div>
                    <?php if ($fc['tempat_lahir'] ?? true): ?>
                    <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                        <p class="text-xs text-gray-600 mb-1">Tempat Lahir</p>
                        <p class="font-semibold text-gray-900" id="detailTempatLahir">-</p>
                    </div>
                    <?php endif; ?>
                    <?php if ($fc['tgl_lahir'] ?? true): ?>
                    <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                        <p class="text-xs text-gray-600 mb-1">Tanggal Lahir</p>
                        <p class="font-semibold text-gray-900" id="detailTglLahir">-</p>
                    </div>
                    <?php endif; ?>
                    <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                        <p class="text-xs text-gray-600 mb-1">Status</p>
                        <p class="font-semibold text-gray-900" id="detailStatus">-</p>
                    </div>
                </div>
            </div>

            <!-- Kontak -->
            <?php if (($fc['no_wa'] ?? true) || ($fc['email'] ?? true)): ?>
            <div>
                <h5
                    class="font-bold text-sm text-secondary-800 flex items-center gap-2 mb-3 pb-2 border-b-2 border-indigo-200">
                    <i data-lucide="phone" class="w-4 h-4 text-indigo-600"></i>
                    Informasi Kontak
                </h5>
                <div class="grid grid-cols-2 gap-3">
                    <?php if ($fc['no_wa'] ?? true): ?>
                    <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                        <p class="text-xs text-gray-600 mb-1">No. WhatsApp</p>
                        <p class="font-semibold text-gray-900" id="detailNoWa">-</p>
                    </div>
                    <?php endif; ?>
                    <?php if ($fc['email'] ?? true): ?>
                    <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                        <p class="text-xs text-gray-600 mb-1">Email</p>
                        <p class="font-semibold text-gray-900 break-all" id="detailEmail">-</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Alamat Lengkap -->
            <?php if ($fc['alamat'] ?? true): ?>
            <div>
                <h5
                    class="font-bold text-sm text-secondary-800 flex items-center gap-2 mb-3 pb-2 border-b-2 border-green-200">
                    <i data-lucide="map-pin" class="w-4 h-4 text-green-600"></i>
                    Alamat Lengkap
                </h5>
                <div class="space-y-3">
                    <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                        <p class="text-xs text-gray-600 mb-1">Alamat</p>
                        <p class="font-semibold text-gray-900" id="detailAlamat">-</p>
                    </div>
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
                        <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                            <p class="text-xs text-gray-600 mb-1">Kelurahan/Desa</p>
                            <p class="font-semibold text-gray-900" id="detailKelurahan">-</p>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                            <p class="text-xs text-gray-600 mb-1">Kecamatan</p>
                            <p class="font-semibold text-gray-900" id="detailKecamatan">-</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                            <p class="text-xs text-gray-600 mb-1">Kabupaten/Kota</p>
                            <p class="font-semibold text-gray-900" id="detailKabupaten">-</p>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                            <p class="text-xs text-gray-600 mb-1">Provinsi</p>
                            <p class="font-semibold text-gray-900" id="detailProvinsi">-</p>
                        </div>
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
            <?php if ($fc['ayah_nama'] ?? true): ?>
            <div>
                <h5
                    class="font-bold text-sm text-secondary-800 flex items-center gap-2 mb-3 pb-2 border-b-2 border-blue-200">
                    <i data-lucide="user" class="w-4 h-4 text-blue-600"></i>
                    Data Ayah
                </h5>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg">
                            <p class="text-xs text-blue-700 mb-1">Nama Ayah</p>
                            <p class="font-semibold text-blue-900" id="detailAyah">-</p>
                        </div>
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
            <?php if ($fc['ibu_nama'] ?? true): ?>
            <div>
                <h5
                    class="font-bold text-sm text-secondary-800 flex items-center gap-2 mb-3 pb-2 border-b-2 border-pink-200">
                    <i data-lucide="heart" class="w-4 h-4 text-pink-600"></i>
                    Data Ibu
                </h5>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-pink-50 border border-pink-200 p-3 rounded-lg">
                            <p class="text-xs text-pink-700 mb-1">Nama Ibu</p>
                            <p class="font-semibold text-pink-900" id="detailIbu">-</p>
                        </div>
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
            <?php if ($fc['wali_nama'] ?? true): ?>
            <div>
                <h5
                    class="font-bold text-sm text-secondary-800 flex items-center gap-2 mb-3 pb-2 border-b-2 border-amber-200">
                    <i data-lucide="users" class="w-4 h-4 text-amber-600"></i>
                    Data Wali (Opsional)
                </h5>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg">
                            <p class="text-xs text-amber-700 mb-1">Nama Wali</p>
                            <p class="font-semibold text-amber-900" id="detailWali">-</p>
                        </div>
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
                <h5
                    class="font-bold text-sm text-secondary-800 flex items-center gap-2 mb-3 pb-2 border-b-2 border-violet-200">
                    <i data-lucide="lock" class="w-4 h-4 text-violet-600"></i>
                    Akun Login
                </h5>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-violet-50 border border-violet-200 p-3 rounded-lg">
                        <p class="text-xs text-violet-700 mb-1">Username</p>
                        <p class="font-semibold text-violet-900" id="detailUsername">-</p>
                    </div>
                    <div class="bg-violet-50 border border-violet-200 p-3 rounded-lg">
                        <p class="text-xs text-violet-700 mb-1">Password</p>
                        <p class="font-mono font-semibold text-violet-900" id="detailPassword">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 bg-secondary-50 border-t border-secondary-200 flex justify-end gap-2 rounded-b-2xl">
            <button onclick="closeDetailModal()"
                class="px-4 py-2 bg-secondary-200 hover:bg-secondary-300 text-secondary-800 rounded-lg transition-colors font-semibold">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Script Search -->
<script>
    let passwordVisible = false;
    let currentPasswordPlain = '';

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchSiswa');
        const tableRows = document.querySelectorAll('.siswa-row');
        const cardItems = document.querySelectorAll('.siswa-card');

        searchInput.addEventListener('keyup', function () {
            const searchTerm = this.value.toLowerCase();
            let visibleCount = 0;

            // Search in desktop table
            tableRows.forEach(row => {
                const nama = row.querySelector('.siswa-nama').textContent.toLowerCase();
                const nisn = row.querySelector('.siswa-nisn').textContent.toLowerCase();

                if (nama.includes(searchTerm) || nisn.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Search in mobile cards
            cardItems.forEach(card => {
                const nama = card.dataset.nama.toLowerCase();
                const nisn = card.dataset.nisn.toLowerCase();

                if (nama.includes(searchTerm) || nisn.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });

            document.getElementById('countSiswa').textContent = visibleCount || cardItems.length;
        });

        // Reinitialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    function showDetailSiswa(siswa) {
        // Helper to safely set text content
        const setText = (id, text) => {
            const el = document.getElementById(id);
            if (el) el.textContent = text;
        };

        // Update avatar
        const initial = siswa.nama_siswa ? siswa.nama_siswa.charAt(0).toUpperCase() : 'A';
        setText('detailAvatar', initial);

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

        const status = siswa.status_siswa ? siswa.status_siswa.charAt(0).toUpperCase() + siswa.status_siswa.slice(1) : 'Aktif';
        setText('detailStatus', status);

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
        document.getElementById('detailUsername').textContent = siswa.nisn || '-';
        document.getElementById('detailPassword').textContent = siswa.password_plain || '-';

        // Show modal
        document.getElementById('modalDetailSiswa').classList.remove('hidden');
        document.getElementById('modalDetailSiswa').classList.add('flex');

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

    // Download SKSA dengan notifikasi
    function downloadSKSA(idSiswa, namaSiswa) {
        // Download di window yang sama (tidak buka tab baru)
        const url = '<?= BASEURL; ?>/waliKelas/cetakSKSA/' + idSiswa;
        window.location.href = url;

        // Tampilkan popup sukses setelah delay
        setTimeout(() => {
            showNotification(
                'SKSA Berhasil Dicetak!',
                'Surat Keterangan Siswa Aktif untuk ' + namaSiswa + ' sedang didownload.',
                'success'
            );
        }, 500);
    }

    // Fungsi untuk menampilkan notifikasi popup
    function showNotification(title, message, type = 'success') {
        const icon = type === 'success' ? 'check-circle' : 'alert-circle';
        const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';

        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-2xl z-[9999] transform transition-all duration-300 max-w-md`;
        notification.style.animation = 'slideIn 0.3s ease-out';

        notification.innerHTML = `
        <div class="flex items-start gap-3">
            <i data-lucide="${icon}" class="w-6 h-6 flex-shrink-0"></i>
            <div class="flex-1">
                <h4 class="font-bold text-lg mb-1">${title}</h4>
                <p class="text-sm opacity-90">${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
    `;

        document.body.appendChild(notification);

        // Reinitialize icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-in';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    // Add animations
    const style = document.createElement('style');
    style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
    document.head.appendChild(style);

    // Close modal with ESC key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeDetailModal();
        }
    });
</script>