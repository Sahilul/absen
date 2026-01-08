<?php
$tamu = $data['tamu'] ?? [];
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Detail Tamu</h2>
            <p class="text-gray-600 mt-1"><?= htmlspecialchars($tamu['nama_tamu']) ?></p>
        </div>
        <a href="<?= BASEURL ?>/bukuTamu"
            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-gray-700 flex items-center gap-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Foto -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Foto Kehadiran</h3>
            <?php if (!empty($tamu['foto_url'])): ?>
                <img src="<?= $tamu['foto_url'] ?>" alt="Foto tamu" class="w-full rounded-xl">
                <a href="<?= $tamu['foto_url'] ?>" target="_blank"
                    class="block text-center text-indigo-600 text-sm mt-3 hover:underline">
                    <i data-lucide="external-link" class="w-4 h-4 inline"></i> Buka di tab baru
                </a>
            <?php else: ?>
                <div class="w-full h-48 bg-gray-100 rounded-xl flex items-center justify-center text-gray-400">
                    <i data-lucide="image-off" class="w-12 h-12"></i>
                </div>
                <p class="text-center text-gray-500 text-sm mt-3">Tidak ada foto</p>
            <?php endif; ?>
        </div>

        <!-- Data Tamu -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Informasi Tamu</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-gray-500 uppercase">Nama Lengkap</label>
                    <p class="font-medium text-gray-900"><?= htmlspecialchars($tamu['nama_tamu']) ?></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase">Asal Instansi</label>
                    <p class="font-medium text-gray-900"><?= htmlspecialchars($tamu['instansi'] ?? '-') ?></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase">No. HP</label>
                    <p class="font-medium text-gray-900"><?= htmlspecialchars($tamu['no_hp']) ?></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase">Email</label>
                    <p class="font-medium text-gray-900"><?= htmlspecialchars($tamu['email'] ?? '-') ?></p>
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs text-gray-500 uppercase">Keperluan</label>
                    <p class="font-medium text-gray-900"><?= nl2br(htmlspecialchars($tamu['keperluan'])) ?></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase">Bertemu Dengan</label>
                    <p class="font-medium text-gray-900"><?= htmlspecialchars($tamu['bertemu_dengan'] ?? '-') ?></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase">Lembaga</label>
                    <p class="font-medium text-gray-900"><?= htmlspecialchars($tamu['nama_lembaga']) ?></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase">Waktu Datang</label>
                    <p class="font-medium text-gray-900"><?= date('d M Y, H:i', strtotime($tamu['waktu_datang'])) ?></p>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase">Waktu Pulang</label>
                    <p class="font-medium text-gray-900">
                        <?= $tamu['waktu_pulang'] ? date('d M Y, H:i', strtotime($tamu['waktu_pulang'])) : '<span class="text-orange-600">Belum pulang</span>' ?>
                    </p>
                </div>
                <?php if (!empty($tamu['catatan'])): ?>
                    <div class="sm:col-span-2">
                        <label class="text-xs text-gray-500 uppercase">Catatan</label>
                        <p class="font-medium text-gray-900 bg-yellow-50 p-3 rounded-lg mt-1">
                            <?= nl2br(htmlspecialchars($tamu['catatan'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
</script>