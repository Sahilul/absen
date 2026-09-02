<?php
// File: app/views/admin/detail_izin.php
$izin = $data['izin'] ?? [];
$jenisLabel = ['I' => 'Izin', 'S' => 'Sakit', 'D' => 'Dispensasi'];
$jenisColor = ['I' => 'blue', 'S' => 'yellow', 'D' => 'purple'];
$statusColor = ['aktif' => 'green', 'selesai' => 'gray', 'dibatalkan' => 'red'];
$jc = $jenisColor[$izin['jenis_izin']] ?? 'gray';
$sc = $statusColor[$izin['status']] ?? 'gray';

$tglMulai = strtotime($izin['tanggal_mulai']);
$tglSelesai = strtotime($izin['tanggal_selesai']);
$durasi = (int) ceil(($tglSelesai - $tglMulai) / 86400) + 1;
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 sm:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-3">
            <a href="<?= BASEURL; ?>/admin/izinSiswa"
               class="p-2 rounded-xl text-secondary-500 hover:text-primary-600 hover:bg-white/50 transition-all">
                <i data-lucide="arrow-left" class="w-6 h-6"></i>
            </a>
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-secondary-800">Detail Izin Siswa</h2>
                <p class="text-secondary-600 mt-1 text-sm">Informasi lengkap izin siswa</p>
            </div>
        </div>
    </div>

    <div class="max-w-2xl space-y-4">
        <!-- Status Banner -->
        <div class="bg-<?= $sc ?>-50 border border-<?= $sc ?>-200 rounded-xl p-4 flex items-center gap-3">
            <div class="bg-<?= $sc ?>-100 p-2 rounded-lg">
                <i data-lucide="<?= $izin['status'] === 'aktif' ? 'clock' : ($izin['status'] === 'selesai' ? 'check-circle' : 'x-circle') ?>"
                   class="w-5 h-5 text-<?= $sc ?>-600"></i>
            </div>
            <div>
                <p class="font-semibold text-<?= $sc ?>-800">Status: <?= ucfirst($izin['status']) ?></p>
                <p class="text-xs text-<?= $sc ?>-600">Dibuat <?= date('d M Y H:i', strtotime($izin['created_at'])) ?></p>
            </div>
        </div>

        <!-- Info Card -->
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="divide-y divide-gray-100">
                <div class="px-5 py-4 flex justify-between items-start">
                    <div class="text-sm text-secondary-500">Siswa</div>
                    <div class="text-right">
                        <div class="font-medium text-secondary-800"><?= htmlspecialchars($izin['nama_siswa']) ?></div>
                        <div class="text-xs text-secondary-500">NISN: <?= htmlspecialchars($izin['nisn'] ?? '-') ?></div>
                    </div>
                </div>
                <div class="px-5 py-4 flex justify-between items-center">
                    <div class="text-sm text-secondary-500">Kelas</div>
                    <div class="font-medium text-secondary-800"><?= htmlspecialchars($izin['nama_kelas']) ?></div>
                </div>
                <div class="px-5 py-4 flex justify-between items-center">
                    <div class="text-sm text-secondary-500">Jenis</div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-<?= $jc ?>-100 text-<?= $jc ?>-700">
                        <?= $jenisLabel[$izin['jenis_izin']] ?? $izin['jenis_izin'] ?>
                    </span>
                </div>
                <div class="px-5 py-4 flex justify-between items-start">
                    <div class="text-sm text-secondary-500">Tanggal</div>
                    <div class="text-right">
                        <div class="font-medium text-secondary-800"><?= date('d M Y', $tglMulai) ?> — <?= date('d M Y', $tglSelesai) ?></div>
                        <div class="text-xs text-secondary-500"><?= $durasi ?> hari</div>
                    </div>
                </div>
                <div class="px-5 py-4">
                    <div class="text-sm text-secondary-500 mb-1">Keterangan</div>
                    <p class="text-secondary-800"><?= nl2br(htmlspecialchars($izin['keterangan'] ?? '-')) ?></p>
                </div>
                <div class="px-5 py-4 flex justify-between items-center">
                    <div class="text-sm text-secondary-500">Diinput Oleh</div>
                    <div class="font-medium text-secondary-800"><?= htmlspecialchars($izin['nama_guru'] ?? '-') ?></div>
                </div>
                <?php if (!empty($izin['updated_at']) && $izin['updated_at'] !== $izin['created_at']): ?>
                    <div class="px-5 py-4 flex justify-between items-center">
                        <div class="text-sm text-secondary-500">Terakhir Diubah</div>
                        <div class="text-xs text-secondary-500"><?= date('d M Y H:i', strtotime($izin['updated_at'])) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
            <a href="<?= BASEURL; ?>/admin/izinSiswa" class="px-5 py-2.5 text-sm font-medium text-secondary-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors inline-flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </a>
            <?php if ($izin['status'] === 'aktif'): ?>
                <form method="POST" action="<?= BASEURL; ?>/admin/adminBatalkanIzin"
                      onsubmit="return confirm('Yakin ingin membatalkan izin ini?');">
                    <input type="hidden" name="id_izin" value="<?= $izin['id_izin'] ?>">
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors inline-flex items-center gap-2">
                        <i data-lucide="x-circle" class="w-4 h-4"></i> Batalkan Izin
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
