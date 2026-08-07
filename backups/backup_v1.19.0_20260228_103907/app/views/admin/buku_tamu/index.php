<?php
$stats = $data['stats'] ?? [];
$tamu_list = $data['tamu_list'] ?? [];
$lembaga_list = $data['lembaga_list'] ?? [];
$selected_lembaga = $data['selected_lembaga'] ?? '';
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Buku Tamu Digital</h2>
            <p class="text-gray-600 mt-1">Kelola kunjungan tamu</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= BASEURL ?>/bukuTamu/lembaga"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-gray-700 flex items-center gap-2">
                <i data-lucide="building-2" class="w-4 h-4"></i> Lembaga
            </a>
            <a href="<?= BASEURL ?>/bukuTamu/generateLink"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl flex items-center gap-2">
                <i data-lucide="link" class="w-4 h-4"></i> Generate Link
            </a>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="bg-blue-100 p-2 rounded-xl">
                    <i data-lucide="calendar-check" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Hari Ini</p>
                    <p class="text-xl font-bold"><?= $stats['today'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="bg-green-100 p-2 rounded-xl">
                    <i data-lucide="calendar-days" class="w-5 h-5 text-green-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Minggu Ini</p>
                    <p class="text-xl font-bold"><?= $stats['week'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="bg-purple-100 p-2 rounded-xl">
                    <i data-lucide="calendar" class="w-5 h-5 text-purple-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Bulan Ini</p>
                    <p class="text-xl font-bold"><?= $stats['month'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div class="flex items-center gap-3">
                <div class="bg-amber-100 p-2 rounded-xl">
                    <i data-lucide="users" class="w-5 h-5 text-amber-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total</p>
                    <p class="text-xl font-bold"><?= $stats['total'] ?? 0 ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Tamu -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-4 py-3 border-b flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h3 class="font-semibold text-gray-800">Daftar Kunjungan Tamu</h3>
            <div class="flex items-center gap-2">
                <select id="filterLembaga" class="px-3 py-1.5 border rounded-lg text-sm" onchange="filterByLembaga()">
                    <option value="">Semua Lembaga</option>
                    <?php foreach ($lembaga_list as $lb): ?>
                        <option value="<?= $lb['id'] ?>" <?= ($selected_lembaga == $lb['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($lb['nama_lembaga']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <a href="<?= BASEURL ?>/bukuTamu/downloadPDF<?= $selected_lembaga ? '?lembaga=' . $selected_lembaga : '' ?>"
                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm flex items-center gap-1">
                    <i data-lucide="download" class="w-4 h-4"></i> Download PDF
                </a>
            </div>
        </div>

        <?php if (empty($tamu_list)): ?>
            <div class="p-12 text-center text-gray-500">
                <i data-lucide="users-x" class="w-12 h-12 mx-auto mb-4 opacity-50"></i>
                <p>Belum ada data tamu</p>
            </div>
        <?php else: ?>
            <!-- Mobile Card View -->
            <div class="md:hidden divide-y">
                <?php foreach ($tamu_list as $tamu): ?>
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <?php if (!empty($tamu['foto_url'])): ?>
                                <img src="<?= $tamu['foto_url'] ?>" class="w-12 h-12 rounded-full object-cover flex-shrink-0">
                            <?php else: ?>
                                <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="user" class="w-6 h-6 text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="font-semibold text-gray-900"><?= htmlspecialchars($tamu['nama_tamu']) ?>
                                        </div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($tamu['instansi'] ?? '-') ?>
                                        </div>
                                    </div>
                                    <span
                                        class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded text-xs"><?= htmlspecialchars($tamu['nama_lembaga']) ?></span>
                                </div>
                                <div class="text-sm text-gray-600 mt-2 line-clamp-2"><?= htmlspecialchars($tamu['keperluan']) ?>
                                </div>
                                <?php if (!empty($tamu['bertemu_dengan'])): ?>
                                    <div class="text-xs text-gray-500 mt-1">Bertemu:
                                        <?= htmlspecialchars($tamu['bertemu_dengan']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="flex items-center justify-between mt-3">
                                    <div class="text-xs text-gray-500">
                                        <i data-lucide="clock" class="w-3 h-3 inline"></i>
                                        <?= date('d M Y, H:i', strtotime($tamu['waktu_datang'])) ?>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <a href="<?= BASEURL ?>/bukuTamu/detail/<?= $tamu['id'] ?>"
                                            class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="<?= BASEURL ?>/bukuTamu/hapus/<?= $tamu['id'] ?>"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                            onclick="return confirm('Hapus data tamu ini?')">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Desktop Table View -->
            <div id="tableContainer" class="hidden md:block overflow-x-auto">
                <table id="tamuTable" class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Nama / Instansi</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Keperluan</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Lembaga</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Waktu</th>
                            <th class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($tamu_list as $tamu): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($tamu['foto_url'])): ?>
                                            <img src="<?= $tamu['foto_url'] ?>" class="w-10 h-10 rounded-full object-cover">
                                        <?php else: ?>
                                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="font-medium text-gray-900"><?= htmlspecialchars($tamu['nama_tamu']) ?>
                                            </div>
                                            <div class="text-xs text-gray-500"><?= htmlspecialchars($tamu['instansi'] ?? '-') ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-700 truncate max-w-[200px]">
                                        <?= htmlspecialchars($tamu['keperluan']) ?>
                                    </div>
                                    <?php if (!empty($tamu['bertemu_dengan'])): ?>
                                        <div class="text-xs text-gray-500">Bertemu: <?= htmlspecialchars($tamu['bertemu_dengan']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded text-xs"><?= htmlspecialchars($tamu['nama_lembaga']) ?></span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-700"><?= date('d M Y', strtotime($tamu['waktu_datang'])) ?></div>
                                    <div class="text-xs text-gray-500"><?= date('H:i', strtotime($tamu['waktu_datang'])) ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="<?= BASEURL ?>/bukuTamu/detail/<?= $tamu['id'] ?>"
                                            class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Detail">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="<?= BASEURL ?>/bukuTamu/hapus/<?= $tamu['id'] ?>"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Hapus"
                                            onclick="return confirm('Hapus data tamu ini?')">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
    function filterByLembaga() {
        const id = document.getElementById('filterLembaga').value;
        if (id) {
            window.location.href = '<?= BASEURL ?>/bukuTamu?lembaga=' + id;
        } else {
            window.location.href = '<?= BASEURL ?>/bukuTamu';
        }
    }

    document.addEventListener('DOMContentLoaded', () => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
</script>