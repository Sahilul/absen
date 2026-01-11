<?php
// Proses data rekap untuk ditampilkan dengan safety check
$rekap = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];

if (isset($data['rekap_absensi']) && is_array($data['rekap_absensi'])) {
    foreach ($data['rekap_absensi'] as $row) {
        if (isset($row['status_kehadiran']) && isset($row['total'])) {
            $rekap[$row['status_kehadiran']] = (int) $row['total'];
        }
    }
}

$total_pertemuan = array_sum($rekap);
$persentase_hadir = ($total_pertemuan > 0) ? round(($rekap['H'] / $total_pertemuan) * 100, 1) : 0;

// Status kehadiran
$status_kehadiran = 'poor';
if ($persentase_hadir >= 90) {
    $status_kehadiran = 'excellent';
} elseif ($persentase_hadir >= 75) {
    $status_kehadiran = 'good';
} elseif ($persentase_hadir >= 60) {
    $status_kehadiran = 'fair';
}
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-gray-50 to-blue-50 p-4 sm:p-6">

    <!-- Welcome Section -->
    <div class="mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">
                        Halo, <?= explode(' ', $_SESSION['nama_lengkap'])[0]; ?>! 👋
                    </h1>
                    <p class="text-sm sm:text-base text-gray-600">
                        <?= date('d F Y'); ?> • <?= $_SESSION['nama_semester_aktif']; ?>
                    </p>
                </div>
                <div class="hidden sm:block">
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <i data-lucide="sparkles" class="w-8 h-8 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid: 3 Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        <!-- Card 1: Kehadiran -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                            <i data-lucide="calendar-check" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">Kehadiran</h3>
                            <p class="text-blue-100 text-sm">Semester Ini</p>
                        </div>
                    </div>
                </div>
                <div class="text-center py-4">
                    <div class="text-5xl font-bold text-white mb-2"><?= $persentase_hadir ?>%</div>
                    <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur px-4 py-1.5 rounded-full">
                        <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                        <span class="text-white text-sm font-medium">
                            <?php if ($persentase_hadir >= 90): ?>
                                Excellent
                            <?php elseif ($persentase_hadir >= 75): ?>
                                Baik
                            <?php else: ?>
                                Perlu Peningkatan
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <div class="grid grid-cols-4 gap-3 mb-4">
                    <div class="text-center">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
                            <i data-lucide="check" class="w-5 h-5 text-green-600"></i>
                        </div>
                        <div class="text-xl font-bold text-gray-900"><?= $rekap['H'] ?></div>
                        <div class="text-xs text-gray-500">Hadir</div>
                    </div>
                    <div class="text-center">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
                            <i data-lucide="thermometer" class="w-5 h-5 text-yellow-600"></i>
                        </div>
                        <div class="text-xl font-bold text-gray-900"><?= $rekap['S'] ?></div>
                        <div class="text-xs text-gray-500">Sakit</div>
                    </div>
                    <div class="text-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
                            <i data-lucide="mail" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <div class="text-xl font-bold text-gray-900"><?= $rekap['I'] ?></div>
                        <div class="text-xs text-gray-500">Izin</div>
                    </div>
                    <div class="text-center">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
                            <i data-lucide="x" class="w-5 h-5 text-red-600"></i>
                        </div>
                        <div class="text-xl font-bold text-gray-900"><?= $rekap['A'] ?></div>
                        <div class="text-xs text-gray-500">Alpha</div>
                    </div>
                </div>

                <div class="space-y-2">
                    <a href="<?= BASEURL; ?>/siswa/absensiHarian"
                        class="block w-full px-4 py-2.5 bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 border border-blue-200 text-blue-700 rounded-xl text-sm font-medium transition-all text-center">
                        <i data-lucide="list" class="w-4 h-4 inline mr-2"></i>
                        Lihat Detail Harian
                    </a>
                    <a href="<?= BASEURL; ?>/siswa/rekapAbsensi"
                        class="block w-full px-4 py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-xl text-sm font-medium transition-all text-center">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 inline mr-2"></i>
                        Rekap Per Mapel
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 2: Nilai -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                            <i data-lucide="graduation-cap" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">Nilai</h3>
                            <p class="text-green-100 text-sm">Prestasi Akademik</p>
                        </div>
                    </div>
                </div>
                <div class="text-center py-4">
                    <div class="text-5xl font-bold text-white mb-2">
                        <?= number_format($data['rata_rata_nilai'] ?? 0, 1) ?></div>
                    <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur px-4 py-1.5 rounded-full">
                        <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                        <span class="text-white text-sm font-medium">Rata-rata Nilai</span>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-xl p-4 mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-green-700 font-medium">Mata Pelajaran Dinilai</span>
                        <span class="text-2xl font-bold text-green-600"><?= $data['jumlah_mapel_dinilai'] ?? 0 ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-green-600">Status Akademik</span>
                        <span class="text-xs font-semibold text-green-700">
                            <?php if (($data['rata_rata_nilai'] ?? 0) >= 80): ?>
                                🌟 Excellent
                            <?php elseif (($data['rata_rata_nilai'] ?? 0) >= 70): ?>
                                👍 Baik
                            <?php elseif (($data['rata_rata_nilai'] ?? 0) > 0): ?>
                                💪 Cukup
                            <?php else: ?>
                                📚 Belum Ada Nilai
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <div class="space-y-2">
                    <a href="<?= BASEURL; ?>/performaSiswa/index"
                        class="block w-full px-4 py-2.5 bg-gradient-to-r from-green-50 to-emerald-50 hover:from-green-100 hover:to-emerald-100 border border-green-200 text-green-700 rounded-xl text-sm font-medium transition-all text-center">
                        <i data-lucide="file-text" class="w-4 h-4 inline mr-2"></i>
                        Lihat Semua Nilai
                    </a>
                    <a href="<?= BASEURL; ?>/performaSiswa/rapor"
                        class="block w-full px-4 py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-xl text-sm font-medium transition-all text-center">
                        <i data-lucide="award" class="w-4 h-4 inline mr-2"></i>
                        Rapor Semester
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 3: Pembayaran -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-amber-600 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                            <i data-lucide="wallet" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">Pembayaran</h3>
                            <p class="text-orange-100 text-sm">Status Keuangan</p>
                        </div>
                    </div>
                </div>
                <div class="text-center py-4">
                    <div class="text-xl font-bold text-white mb-1">Rp
                        <?= number_format($data['total_belum_bayar'] ?? 0, 0, ',', '.') ?></div>
                    <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur px-4 py-1.5 rounded-full">
                        <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                        <span class="text-white text-sm font-medium">Sisa Pembayaran</span>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <div class="space-y-3 mb-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <span class="text-sm text-gray-600">Total Tagihan</span>
                        <span class="text-sm font-bold text-gray-900">Rp
                            <?= number_format($data['total_tagihan'] ?? 0, 0, ',', '.') ?></span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-xl">
                        <span class="text-sm text-green-700">Terbayar</span>
                        <span class="text-sm font-bold text-green-600">Rp
                            <?= number_format($data['total_terbayar'] ?? 0, 0, ',', '.') ?></span>
                    </div>
                    <?php if (($data['jumlah_belum_lunas'] ?? 0) > 0): ?>
                        <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-center">
                            <div class="text-red-700 text-sm font-semibold">⚠️ <?= $data['jumlah_belum_lunas'] ?> Tagihan
                                Belum Lunas</div>
                        </div>
                    <?php else: ?>
                        <div class="bg-green-50 border border-green-200 rounded-xl p-3 text-center">
                            <div class="text-green-700 text-sm font-semibold">✅ Semua Tagihan Lunas</div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="space-y-2">
                    <a href="<?= BASEURL; ?>/siswa/pembayaran"
                        class="block w-full px-4 py-2.5 bg-gradient-to-r from-orange-50 to-amber-50 hover:from-orange-100 hover:to-amber-100 border border-orange-200 text-orange-700 rounded-xl text-sm font-medium transition-all text-center">
                        <i data-lucide="receipt" class="w-4 h-4 inline mr-2"></i>
                        Lihat Tagihan
                    </a>
                    <a href="<?= BASEURL; ?>/siswa/riwayatPembayaran"
                        class="block w-full px-4 py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-xl text-sm font-medium transition-all text-center">
                        <i data-lucide="history" class="w-4 h-4 inline mr-2"></i>
                        Riwayat Pembayaran
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="calendar" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Total Pertemuan</div>
                    <div class="text-xl font-bold text-gray-900"><?= $total_pertemuan ?></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="book-open" class="w-5 h-5 text-green-600"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Mapel Dinilai</div>
                    <div class="text-xl font-bold text-gray-900"><?= $data['jumlah_mapel_dinilai'] ?? 0 ?></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="file-text" class="w-5 h-5 text-orange-600"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Total Tagihan</div>
                    <div class="text-xl font-bold text-gray-900"><?= $data['jumlah_tagihan'] ?? 0 ?></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-5 h-5 text-purple-600"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Tagihan Lunas</div>
                    <div class="text-xl font-bold text-gray-900"><?= $data['jumlah_lunas'] ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Motivasi Card -->
    <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-2xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <h3 class="text-xl font-bold mb-2">💪 Tetap Semangat!</h3>
                <p class="text-white/90 text-sm leading-relaxed">
                    <?php if ($persentase_hadir >= 90 && ($data['rata_rata_nilai'] ?? 0) >= 80): ?>
                        Prestasi luar biasa! Kamu konsisten hadir dan nilai akademik sangat baik. Pertahankan!
                    <?php elseif ($persentase_hadir >= 75): ?>
                        Kamu sudah di jalur yang benar. Tingkatkan lagi kehadiranmu untuk hasil yang lebih optimal.
                    <?php else: ?>
                        Setiap hari adalah kesempatan baru untuk menjadi lebih baik. Ayo tingkatkan kehadiranmu!
                    <?php endif; ?>
                </p>
            </div>
            <div class="hidden sm:block ml-6">
                <div class="w-20 h-20 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center">
                    <span class="text-4xl">🎯</span>
                </div>
            </div>
        </div>
    </div>

</main>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>

<style>
    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Data untuk Chart.js dengan safety check
        const rekapData = {
            hadir: parseInt(<?= $rekap['H']; ?>) || 0,
            izin: parseInt(<?= $rekap['I']; ?>) || 0,
            sakit: parseInt(<?= $rekap['S']; ?>) || 0,
            alfa: parseInt(<?= $rekap['A']; ?>) || 0
        };

        // Debug log untuk troubleshooting
        console.log('Rekap Data:', rekapData);

        const ctxPie = document.getElementById('absensiPieChart');
        if (ctxPie && typeof Chart !== 'undefined') {
            // Check if there's any data to show
            const totalData = rekapData.hadir + rekapData.izin + rekapData.sakit + rekapData.alfa;

            if (totalData === 0) {
                // Show empty state for chart
                ctxPie.parentElement.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-64 text-secondary-500">
                        <i data-lucide="pie-chart" class="w-16 h-16 mb-4"></i>
                        <p class="text-lg font-medium">Belum ada data absensi</p>
                        <p class="text-sm">Grafik akan muncul setelah ada data</p>
                    </div>
                `;
                // Re-initialize Lucide icons for the new content
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            } else {
                new Chart(ctxPie, {
                    type: 'doughnut',
                    data: {
                        labels: ['Hadir', 'Izin', 'Sakit', 'Alpha'],
                        datasets: [{
                            label: 'Jumlah',
                            data: [rekapData.hadir, rekapData.izin, rekapData.sakit, rekapData.alfa],
                            backgroundColor: [
                                'rgba(34, 197, 94, 0.8)', // green
                                'rgba(59, 130, 246, 0.8)', // blue
                                'rgba(245, 158, 11, 0.8)',  // yellow
                                'rgba(239, 68, 68, 0.8)'   // red
                            ],
                            borderColor: [
                                'rgba(34, 197, 94, 1)',
                                'rgba(59, 130, 246, 1)',
                                'rgba(245, 158, 11, 1)',
                                'rgba(239, 68, 68, 1)'
                            ],
                            borderWidth: 2,
                            hoverBackgroundColor: [
                                'rgba(34, 197, 94, 0.9)',
                                'rgba(59, 130, 246, 0.9)',
                                'rgba(245, 158, 11, 0.9)',
                                'rgba(239, 68, 68, 0.9)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    font: {
                                        size: 12,
                                        family: 'Plus Jakarta Sans'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                borderColor: '#fff',
                                borderWidth: 1,
                                cornerRadius: 8,
                                displayColors: true,
                                callbacks: {
                                    label: function (context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? Math.round((context.raw / total) * 100) : 0;
                                        return context.label + ': ' + context.raw + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true,
                            duration: 1500,
                            easing: 'easeInOutQuart'
                        },
                        cutout: '50%'
                    }
                });
            }
        } else {
            console.warn('Chart.js not found or canvas element missing');
        }

        // Animasi counter HANYA untuk elemen statistik
        function animateCounters() {
            const counters = document.querySelectorAll('[data-counter="true"]');
            counters.forEach(counter => {
                const targetText = counter.textContent.trim();
                const target = parseInt(targetText) || 0;

                // Skip jika target 0 atau tidak valid
                if (target === 0 || isNaN(target)) {
                    return;
                }

                // Simpan nilai original
                counter.setAttribute('data-original', target);

                let current = 0;
                const increment = Math.ceil(target / 15); // Lebih smooth
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.textContent = target;
                        clearInterval(timer);
                    } else {
                        counter.textContent = current;
                    }
                }, 40);
            });
        }

        // Mulai animasi setelah halaman load
        setTimeout(animateCounters, 500);

        // Inisialisasi Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Popup Notifikasi Tagihan
        <?php if (!empty($data['tagihan_belum_lunas'])): ?>
            setTimeout(function () {
                document.getElementById('tagihanModal').classList.remove('hidden');
            }, 1000);
        <?php endif; ?>
    });

    function closeTagihanModal() {
        document.getElementById('tagihanModal').classList.add('hidden');
    }
</script>

<!-- Modal Notifikasi Tagihan -->
<?php if (!empty($data['tagihan_belum_lunas'])): ?>
    <div id="tagihanModal"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full animate-fade-in">
            <div class="bg-gradient-to-r from-orange-500 to-red-600 p-6 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <i data-lucide="alert-circle" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Pemberitahuan Tagihan</h3>
                            <p class="text-white/80 text-sm">Anda memiliki tagihan yang belum lunas</p>
                        </div>
                    </div>
                    <button onclick="closeTagihanModal()" class="text-white/80 hover:text-white transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">Berikut adalah daftar tagihan yang perlu segera dibayar:</p>
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    <?php foreach ($data['tagihan_belum_lunas'] as $tagihan): ?>
                        <?php
                        $nominal = isset($tagihan['nominal']) ? (int) $tagihan['nominal'] : 0;
                        $diskon = isset($tagihan['diskon']) ? (int) $tagihan['diskon'] : 0;
                        $terbayar = isset($tagihan['total_terbayar']) ? (int) $tagihan['total_terbayar'] : 0;
                        $total_harus_bayar = $nominal - $diskon;
                        $sisa = $total_harus_bayar - $terbayar;
                        ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($tagihan['nama']); ?></p>
                                <div class="text-xs text-gray-500">
                                    Nom: Rp<?= number_format($nominal, 0, ',', '.'); ?>
                                    <?php if ($diskon > 0): ?>
                                        <span class="text-green-600 ml-1"> (Disc:
                                            Rp<?= number_format($diskon, 0, ',', '.'); ?>)</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm font-medium text-red-600 mt-1">Sisa:
                                    Rp<?= number_format($sisa, 0, ',', '.'); ?></p>
                            </div>
                            <div class="text-right">
                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Belum
                                    Lunas</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-6 flex gap-3">
                    <button onclick="closeTagihanModal()"
                        class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold transition-colors">
                        Tutup
                    </button>
                    <a href="<?= BASEURL; ?>/siswa/pembayaran"
                        class="flex-1 px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg font-semibold text-center transition-colors">
                        Lihat Detail
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>
<?php endif; ?>