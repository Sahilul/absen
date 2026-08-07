<?php
// File: app/views/admin/monitoring_dokumen.php

$siswaList = $data['siswa'] ?? [];
$kelasList = $data['kelas_list'] ?? [];
$filters = $data['filters'] ?? [];
$totalDokumen = $data['total_dokumen'] ?? 0;

// Calculate stats
$totalSiswa = count($siswaList);
$lengkap = 0;
$belumLengkap = 0;

foreach ($siswaList as $s) {
    if ($s['jumlah_upload'] >= $totalDokumen) {
        $lengkap++;
    } else {
        $belumLengkap++;
    }
}
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto content-wrapper p-4 md:p-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl md:text-2xl font-bold text-gray-800">Monitoring Dokumen</h2>
            <p class="text-gray-600 mt-1 text-sm">Pantau kelengkapan berkas siswa</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center">
                <div class="bg-indigo-100 p-2.5 rounded-lg mr-3">
                    <i data-lucide="users" class="w-5 h-5 text-indigo-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Siswa</p>
                    <p class="text-xl font-bold text-gray-900"><?= $totalSiswa; ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center">
                <div class="bg-green-100 p-2.5 rounded-lg mr-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Dokumen Lengkap</p>
                    <p class="text-xl font-bold text-green-600"><?= $lengkap; ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center">
                <div class="bg-orange-100 p-2.5 rounded-lg mr-3">
                    <i data-lucide="clock" class="w-5 h-5 text-orange-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Belum Lengkap</p>
                    <p class="text-xl font-bold text-orange-600"><?= $belumLengkap; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Table Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Filter Section -->
        <div class="px-4 md:px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <form action="" method="GET">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <h3 class="text-base font-semibold text-gray-800">Daftar Kelengkapan</h3>
                        <?php if (!empty($filters['id_kelas']) || !empty($filters['search'])): ?>
                            <a href="<?= BASEURL; ?>/admin/monitoringDokumen"
                                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                                <i data-lucide="refresh-cw" class="w-4 h-4 mr-1"></i> Reset
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Filter Inputs -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                        <div class="lg:col-span-4">
                            <label for="id_kelas" class="block text-xs font-medium text-gray-600 mb-1">Kelas</label>
                            <select name="id_kelas" id="id_kelas"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-- Semua Kelas --</option>
                                <?php foreach ($kelasList as $kelas): ?>
                                    <option value="<?= $kelas['id_kelas']; ?>" <?= ($filters['id_kelas'] == $kelas['id_kelas']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($kelas['nama_kelas']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="lg:col-span-4">
                            <label for="search" class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                            <div class="relative">
                                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-gray-400"></i>
                                <input type="text" name="search" id="search"
                                    class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Nama / NISN..."
                                    value="<?= htmlspecialchars($filters['search'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="lg:col-span-2 flex items-end">
                            <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors text-sm flex items-center justify-center gap-1">
                                <i data-lucide="filter" class="w-4 h-4"></i> Filter
                            </button>
                        </div>
                        <div class="lg:col-span-2 flex items-end">
                            <a href="<?= BASEURL; ?>/admin/cetakRekapDokumen?<?= http_build_query($filters); ?>"
                                class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors text-sm flex items-center justify-center gap-1">
                                <i data-lucide="file-text" class="w-4 h-4"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table (Desktop) -->
        <?php if (!empty($siswaList)): ?>
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-12">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Siswa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kelas</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-1/4">Progress
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($siswaList as $index => $s):
                            $uploaded = $s['jumlah_upload'];
                            $percent = ($totalDokumen > 0) ? round(($uploaded / $totalDokumen) * 100) : 0;
                            $isComplete = ($uploaded >= $totalDokumen);
                            ?>
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-500"><?= $index + 1; ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($s['foto'])): ?>
                                            <img src="<?= BASEURL; ?>/img/siswa/<?= $s['foto']; ?>" alt=""
                                                class="w-9 h-9 rounded-full object-cover">
                                        <?php else: ?>
                                            <div
                                                class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm">
                                                <?= strtoupper(substr($s['nama_siswa'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">
                                                <?= htmlspecialchars($s['nama_siswa']); ?></p>
                                            <p class="text-xs text-gray-500"><?= htmlspecialchars($s['nisn']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                        <?= htmlspecialchars($s['nama_kelas']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs text-gray-500"><?= $uploaded; ?>/<?= $totalDokumen; ?></span>
                                        <span class="text-xs font-semibold text-gray-700"><?= $percent; ?>%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="<?= $isComplete ? 'bg-green-500' : 'bg-indigo-500'; ?> h-1.5 rounded-full transition-all"
                                            style="width: <?= $percent; ?>%"></div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if ($isComplete): ?>
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            <i data-lucide="check" class="w-3 h-3 mr-1"></i> Lengkap
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                                            <i data-lucide="clock" class="w-3 h-3 mr-1"></i> Belum
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button onclick="openDocModal(<?= $s['id_siswa']; ?>, '<?= htmlspecialchars(addslashes($s['nama_siswa'])); ?>')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-medium rounded-lg transition-colors">
                                        <i data-lucide="folder-open" class="w-3.5 h-3.5"></i> Lihat
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Cards (Mobile) -->
            <div class="md:hidden divide-y divide-gray-100">
                <?php foreach ($siswaList as $index => $s):
                    $uploaded = $s['jumlah_upload'];
                    $percent = ($totalDokumen > 0) ? round(($uploaded / $totalDokumen) * 100) : 0;
                    $isComplete = ($uploaded >= $totalDokumen);
                    ?>
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <?php if (!empty($s['foto'])): ?>
                                    <img src="<?= BASEURL; ?>/img/siswa/<?= $s['foto']; ?>" alt=""
                                        class="w-10 h-10 rounded-full object-cover">
                                <?php else: ?>
                                    <div
                                        class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                        <?= strtoupper(substr($s['nama_siswa'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($s['nama_siswa']); ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($s['nisn']); ?> •
                                        <?= htmlspecialchars($s['nama_kelas']); ?></p>
                                </div>
                            </div>
                            <?php if ($isComplete): ?>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    <i data-lucide="check" class="w-3 h-3 mr-1"></i> Lengkap
                                </span>
                            <?php else: ?>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                                    <i data-lucide="clock" class="w-3 h-3 mr-1"></i> Belum
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-gray-500"><?= $uploaded; ?>/<?= $totalDokumen; ?> dokumen</span>
                                <span class="text-xs font-semibold text-gray-700"><?= $percent; ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="<?= $isComplete ? 'bg-green-500' : 'bg-indigo-500'; ?> h-2 rounded-full transition-all"
                                    style="width: <?= $percent; ?>%"></div>
                            </div>
                        </div>

                        <button onclick="openDocModal(<?= $s['id_siswa']; ?>, '<?= htmlspecialchars(addslashes($s['nama_siswa'])); ?>')"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium rounded-lg transition-colors">
                            <i data-lucide="folder-open" class="w-4 h-4"></i> Lihat Dokumen
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-12 px-4">
                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Data</h3>
                <p class="text-gray-500 mb-6 text-sm">Tidak ada data siswa ditemukan yang sesuai filter.</p>
                <?php if (!empty($filters['id_kelas']) || !empty($filters['search'])): ?>
                    <a href="<?= BASEURL; ?>/admin/monitoringDokumen"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                        <i data-lucide="rotate-ccw" class="w-4 h-4 mr-2"></i> Reset Filter
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Document Modal -->
<div id="docModal"
    class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-[9999]">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col relative">
        <!-- Modal Header -->
        <div
            class="px-4 md:px-6 py-4 border-b flex items-center justify-between bg-gradient-to-r from-indigo-50 to-blue-50 rounded-t-2xl">
            <h4 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i data-lucide="folder-open" class="w-5 h-5 text-indigo-600"></i>
                <span id="modalTitle">Dokumen Siswa</span>
            </h4>
            <button onclick="closeDocModal()" class="p-2 hover:bg-white/50 rounded-lg text-gray-500 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Modal Content -->
        <div id="modalContent" class="flex-1 overflow-y-auto">
            <div class="flex items-center justify-center h-64">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    let currentSiswaId = null;

    async function openDocModal(idSiswa, namaSiswa) {
        currentSiswaId = idSiswa;
        const modal = document.getElementById('docModal');
        const content = document.getElementById('modalContent');
        const title = document.getElementById('modalTitle');

        title.textContent = namaSiswa || 'Dokumen Siswa';
        modal.classList.remove('hidden');
        content.innerHTML = `
            <div class="flex flex-col items-center justify-center h-64 text-gray-500">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mb-3"></div>
                <p>Memuat dokumen...</p>
            </div>
        `;

        try {
            const response = await fetch(`<?= BASEURL; ?>/admin/getDokumenSiswaPartial/${idSiswa}`);
            if (!response.ok) throw new Error('Gagal memuat');

            const html = await response.text();
            content.innerHTML = html;

            // Execute inline scripts
            content.querySelectorAll('script').forEach(oldScript => {
                const newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });

            // Re-init icons
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (error) {
            content.innerHTML = `
                <div class="flex flex-col items-center justify-center h-64 text-red-500">
                    <i data-lucide="alert-circle" class="w-10 h-10 mb-2"></i>
                    <p>Gagal memuat dokumen</p>
                </div>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    async function refreshDocumentModal(idSiswa) {
        const content = document.getElementById('modalContent');

        try {
            const response = await fetch(`<?= BASEURL; ?>/admin/getDokumenSiswaPartial/${idSiswa}`);
            if (!response.ok) throw new Error('Gagal memuat');

            const html = await response.text();
            content.innerHTML = html;

            content.querySelectorAll('script').forEach(oldScript => {
                const newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });

            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (error) {
            console.error('Refresh error:', error);
        }
    }

    function closeDocModal() {
        document.getElementById('docModal').classList.add('hidden');
    }

    // Close on Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeDocModal();
    });

    // Close on backdrop click
    document.getElementById('docModal').addEventListener('click', function (e) {
        if (e.target === this) closeDocModal();
    });
</script>