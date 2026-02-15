<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-6">
    <!-- Header dengan tombol Download PDF -->
    <div class="mb-8">
        <div class="glass-effect rounded-2xl p-6 border border-white/20 shadow-xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h2 class="text-3xl font-bold text-secondary-800 mb-2">Riwayat Pembayaran</h2>
                    <p class="text-secondary-600 text-lg">
                        Semua transaksi pembayaran yang telah dilakukan oleh siswa.
                    </p>
                </div>
                <div>
                    <a href="<?= BASEURL; ?>/siswa/downloadRiwayatPembayaranPDF"
                        class="btn-primary flex items-center justify-center gap-2 px-6 py-3">
                        <i data-lucide="download" class="w-5 h-5"></i>
                        Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden glass-effect rounded-xl p-12 border border-white/20 shadow-lg text-center">
        <div class="w-24 h-24 bg-secondary-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="receipt" class="w-12 h-12 text-secondary-400"></i>
        </div>
        <h3 class="text-2xl font-bold text-secondary-800 mb-3">Belum Ada Riwayat Pembayaran</h3>
        <p class="text-secondary-600 mb-6">Belum ada transaksi pembayaran yang tercatat.</p>
        <a href="<?= BASEURL; ?>/siswa/pembayaran" class="btn-primary inline-flex items-center gap-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Pembayaran
        </a>
    </div>

    <!-- Table View Responsive -->
    <div id="tableView" class="glass-effect rounded-xl border border-white/20 shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-secondary-50 to-secondary-100 px-6 py-4 border-b border-white/20">
            <h3 class="text-lg font-semibold text-secondary-800">Detail Riwayat Pembayaran</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-secondary-200" id="riwayatTable">
                <thead class="bg-secondary-50/50">
                    <tr>
                        <th
                            class="px-4 py-3 sm:px-6 sm:py-4 text-left text-xs font-bold text-secondary-600 uppercase tracking-wider">
                            Tanggal</th>
                        <th
                            class="px-4 py-3 sm:px-6 sm:py-4 text-left text-xs font-bold text-secondary-600 uppercase tracking-wider">
                            Tagihan</th>
                        <th
                            class="px-4 py-3 sm:px-6 sm:py-4 text-left text-xs font-bold text-secondary-600 uppercase tracking-wider">
                            Jumlah</th>
                        <th
                            class="px-4 py-3 sm:px-6 sm:py-4 text-left text-xs font-bold text-secondary-600 uppercase tracking-wider">
                            Metode</th>
                        <th
                            class="px-4 py-3 sm:px-6 sm:py-4 text-left text-xs font-bold text-secondary-600 uppercase tracking-wider">
                            Keterangan</th>
                        <th
                            class="px-4 py-3 sm:px-6 sm:py-4 text-left text-xs font-bold text-secondary-600 uppercase tracking-wider">
                            Penerima</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-secondary-100" id="tableBody">
                    <?php if (!empty($data['riwayat_pembayaran'])): ?>
                        <?php foreach ($data['riwayat_pembayaran'] as $index => $transaksi): ?>
                            <tr class="hover:bg-secondary-50 transition-colors duration-200 animate-fade-in"
                                style="animation-delay: <?= $index * 100; ?>ms">
                                <td class="px-4 py-3 sm:px-6 sm:py-4 text-sm text-secondary-700 whitespace-nowrap">
                                    <?= date('d M Y', strtotime($transaksi['tanggal'])); ?>
                                </td>
                                <td class="px-4 py-3 sm:px-6 sm:py-4 text-sm font-semibold text-secondary-800">
                                    <?= htmlspecialchars($transaksi['nama_tagihan']); ?>
                                </td>
                                <td class="px-4 py-3 sm:px-6 sm:py-4 text-sm text-primary-700 font-bold whitespace-nowrap">
                                    Rp<?= number_format($transaksi['jumlah'], 0, ',', '.'); ?>
                                </td>
                                <td class="px-4 py-3 sm:px-6 sm:py-4 text-sm text-secondary-700">
                                    <?= htmlspecialchars($transaksi['metode']); ?>
                                </td>
                                <td class="px-4 py-3 sm:px-6 sm:py-4 text-sm text-secondary-500">
                                    <?= htmlspecialchars($transaksi['keterangan']); ?>
                                </td>
                                <td class="px-4 py-3 sm:px-6 sm:py-4 text-sm text-secondary-600 font-medium">
                                    <?= htmlspecialchars($transaksi['petugas_input'] ?? 'Sistem'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <script>document.getElementById('emptyState').classList.remove('hidden'); document.getElementById('tableView').classList.add('hidden');</script>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>

<style>
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.3s ease-out forwards;
    }

    @media print {
        .glass-effect {
            box-shadow: none !important;
        }

        .bg-gradient-to-br {
            background: white !important;
        }
    }
</style>