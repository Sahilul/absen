<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-4 md:p-6">
    
    <!-- Header -->
    <div class="mb-6 md:mb-8">
        <div class="glass-effect rounded-xl md:rounded-2xl p-4 md:p-6 border border-white/20 shadow-xl">
            <div class="flex flex-col gap-4">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-secondary-800 mb-2">Pembayaran</h2>
                    <p class="text-sm md:text-base text-secondary-600">
                        Semester: <span class="font-semibold text-primary-600"><?= $_SESSION['nama_semester_aktif']; ?></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6 mb-6 md:mb-8">
        <div class="glass-effect rounded-lg md:rounded-xl p-4 md:p-6 border border-white/20 shadow-lg">
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between mb-2">
                    <div class="gradient-primary p-2 md:p-3 rounded-lg shadow-md">
                        <i data-lucide="file-text" class="w-4 h-4 md:w-5 md:h-5 text-white"></i>
                    </div>
                </div>
                <div class="text-xs md:text-sm text-secondary-500 font-medium">Total Tagihan</div>
                <div class="text-xl md:text-3xl font-bold text-secondary-800">
                    <?= $data['jumlah_tagihan']; ?>
                </div>
            </div>
        </div>

        <div class="glass-effect rounded-lg md:rounded-xl p-4 md:p-6 border border-white/20 shadow-lg">
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between mb-2">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-2 md:p-3 rounded-lg shadow-md">
                        <i data-lucide="wallet" class="w-4 h-4 md:w-5 md:h-5 text-white"></i>
                    </div>
                </div>
                <div class="text-xs md:text-sm text-secondary-500 font-medium">Total Nominal</div>
                <div class="text-base md:text-xl lg:text-2xl font-bold text-blue-600">
                    Rp <?= number_format($data['total_tagihan'], 0, ',', '.'); ?>
                </div>
            </div>
        </div>

        <div class="glass-effect rounded-lg md:rounded-xl p-4 md:p-6 border border-white/20 shadow-lg">
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between mb-2">
                    <div class="gradient-success p-2 md:p-3 rounded-lg shadow-md">
                        <i data-lucide="check-circle" class="w-4 h-4 md:w-5 md:h-5 text-white"></i>
                    </div>
                </div>
                <div class="text-xs md:text-sm text-secondary-500 font-medium">Sudah Dibayar</div>
                <div class="text-base md:text-xl lg:text-2xl font-bold text-success-600">
                    Rp <?= number_format($data['total_terbayar'], 0, ',', '.'); ?>
                </div>
            </div>
        </div>

        <div class="glass-effect rounded-lg md:rounded-xl p-4 md:p-6 border border-white/20 shadow-lg">
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between mb-2">
                    <div class="gradient-danger p-2 md:p-3 rounded-lg shadow-md">
                        <i data-lucide="alert-circle" class="w-4 h-4 md:w-5 md:h-5 text-white"></i>
                    </div>
                </div>
                <div class="text-xs md:text-sm text-secondary-500 font-medium">Belum Dibayar</div>
                <div class="text-base md:text-xl lg:text-2xl font-bold text-danger-600">
                    Rp <?= number_format($data['total_belum_bayar'], 0, ',', '.'); ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($data['tagihan_siswa'])) : ?>
        <!-- Empty State -->
        <div class="glass-effect rounded-xl p-8 md:p-12 border border-white/20 shadow-lg text-center">
            <div class="w-16 h-16 md:w-24 md:h-24 bg-secondary-100 rounded-full flex items-center justify-center mx-auto mb-4 md:mb-6">
                <i data-lucide="wallet" class="w-8 h-8 md:w-12 md:h-12 text-secondary-400"></i>
            </div>
            <h3 class="text-xl md:text-2xl font-bold text-secondary-800 mb-2 md:mb-3">Belum Ada Tagihan</h3>
            <p class="text-sm md:text-base text-secondary-600 mb-4 md:mb-6">Belum ada tagihan pembayaran untuk semester ini.</p>
            <a href="<?= BASEURL; ?>/siswa/dashboard" class="btn-primary inline-flex items-center gap-2 text-sm md:text-base">
                <i data-lucide="home" class="w-4 h-4"></i>
                Kembali ke Dashboard
            </a>
        </div>
    <?php else : ?>
        
        <!-- Filter Controls -->
        <div class="glass-effect rounded-xl p-4 md:p-6 border border-white/20 shadow-lg mb-4 md:mb-6">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-secondary-400 w-4 h-4"></i>
                        <input type="text" id="searchTagihan" placeholder="Cari tagihan..." 
                               class="input-modern pl-10 pr-4 py-2 text-sm w-full">
                    </div>
                    <select id="statusFilter" class="input-modern text-sm">
                        <option value="">Semua Status</option>
                        <option value="lunas">Lunas</option>
                        <option value="belum">Belum Lunas</option>
                    </select>
                </div>
                <div class="flex items-center justify-between text-xs md:text-sm">
                    <span class="text-secondary-600">
                        <span class="font-semibold text-success-600"><?= $data['jumlah_lunas']; ?></span> Lunas | 
                        <span class="font-semibold text-danger-600"><?= $data['jumlah_belum_lunas']; ?></span> Belum Lunas
                    </span>
                </div>
            </div>
        </div>

        <!-- Daftar Tagihan - Card View for Mobile -->
        <div class="block lg:hidden space-y-3 mb-6" id="mobileTagihanList">
            <?php foreach ($data['tagihan_siswa'] as $index => $tagihan) : ?>
                <?php
                    $nominal = isset($tagihan['nominal']) ? (int)$tagihan['nominal'] : 0;
                    $diskon = isset($tagihan['diskon']) ? (int)$tagihan['diskon'] : 0;
                    $terbayar = isset($tagihan['total_terbayar']) ? (int)$tagihan['total_terbayar'] : 0;
                    $harus_bayar = $nominal - $diskon;
                    $sisa = $harus_bayar - $terbayar;
                    $is_lunas = $terbayar >= $harus_bayar;
                    $progress = $harus_bayar > 0 ? round(($terbayar / $harus_bayar) * 100) : 0;
                ?>
                <div class="glass-effect rounded-xl p-4 border border-white/20 shadow-lg animate-fade-in"
                     style="animation-delay: <?= $index * 50; ?>ms"
                     data-row
                     data-tagihan="<?= strtolower($tagihan['nama']); ?>"
                     data-status="<?= $is_lunas ? 'lunas' : 'belum'; ?>">
                    
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-start gap-3 flex-1">
                            <div class="w-10 h-10 gradient-primary rounded-lg flex items-center justify-center flex-shrink-0">
                                <i data-lucide="file-text" class="w-5 h-5 text-white"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-secondary-800 mb-1 line-clamp-2">
                                    <?= htmlspecialchars($tagihan['nama']); ?>
                                </h4>
                                <span class="<?= $is_lunas ? 'status-lunas' : 'status-belum'; ?> px-2 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1">
                                    <i data-lucide="<?= $is_lunas ? 'check-circle' : 'alert-circle'; ?>" class="w-3 h-3"></i>
                                    <?= $is_lunas ? 'Lunas' : 'Belum Lunas'; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Info Grid -->
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="bg-blue-50 rounded-lg p-3">
                            <div class="text-xs text-secondary-600 mb-1">Harus Bayar</div>
                            <div class="text-sm font-bold text-blue-600">
                                Rp <?= number_format($harus_bayar, 0, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="bg-success-50 rounded-lg p-3">
                            <div class="text-xs text-secondary-600 mb-1">Terbayar</div>
                            <div class="text-sm font-bold text-success-600">
                                Rp <?= number_format($terbayar, 0, ',', '.'); ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($sisa > 0): ?>
                    <div class="bg-danger-50 border border-danger-200 rounded-lg p-3 mb-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-danger-600 font-medium">Sisa Pembayaran</span>
                            <span class="text-sm font-bold text-danger-600">
                                Rp <?= number_format($sisa, 0, ',', '.'); ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Progress Bar -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-secondary-600">Progress</span>
                            <span class="font-semibold text-secondary-800"><?= $progress; ?>%</span>
                        </div>
                        <div class="progress-bar h-2 rounded-full overflow-hidden">
                            <div class="progress-fill <?= $is_lunas ? 'gradient-success' : 'gradient-primary'; ?> h-full rounded-full transition-all duration-500" 
                                 style="width: <?= $progress; ?>%"></div>
                        </div>
                    </div>

                    <?php if ($diskon > 0): ?>
                    <div class="mt-3 pt-3 border-t border-secondary-200">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-secondary-600">Nominal Awal</span>
                            <span class="text-secondary-500">Rp <?= number_format($nominal, 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs mt-1">
                            <span class="text-secondary-600">Diskon</span>
                            <span class="text-yellow-600 font-semibold">- Rp <?= number_format($diskon, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Button Riwayat -->
                    <div class="mt-3 pt-3 border-t border-secondary-200">
                        <button onclick="showRiwayat(<?= $tagihan['id']; ?>, '<?= htmlspecialchars(addslashes($tagihan['nama'])); ?>')"
                                class="w-full btn-secondary text-xs py-2 flex items-center justify-center gap-2">
                            <i data-lucide="history" class="w-3 h-3"></i>
                            Lihat Riwayat Pembayaran
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Table View for Desktop -->
        <div class="hidden lg:block glass-effect rounded-xl border border-white/20 shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-secondary-50 to-secondary-100 px-6 py-4 border-b border-white/20">
                <h3 class="text-lg font-semibold text-secondary-800">Daftar Tagihan</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-secondary-200" id="tagihanTable">
                    <thead class="bg-secondary-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-secondary-600 uppercase tracking-wider">
                                Nama Tagihan
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-secondary-600 uppercase tracking-wider">
                                Nominal
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-secondary-600 uppercase tracking-wider">
                                Diskon
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-secondary-600 uppercase tracking-wider">
                                Harus Bayar
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-secondary-600 uppercase tracking-wider">
                                Terbayar
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-secondary-600 uppercase tracking-wider">
                                Sisa
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-secondary-600 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-secondary-100" id="tableBody">
                        <?php foreach ($data['tagihan_siswa'] as $index => $tagihan) : ?>
                            <?php
                                $nominal = isset($tagihan['nominal']) ? (int)$tagihan['nominal'] : 0;
                                $diskon = isset($tagihan['diskon']) ? (int)$tagihan['diskon'] : 0;
                                $terbayar = isset($tagihan['total_terbayar']) ? (int)$tagihan['total_terbayar'] : 0;
                                $harus_bayar = $nominal - $diskon;
                                $sisa = $harus_bayar - $terbayar;
                                $is_lunas = $terbayar >= $harus_bayar;
                            ?>
                            <tr class="hover:bg-secondary-50 transition-colors duration-200 animate-fade-in" 
                                style="animation-delay: <?= $index * 50; ?>ms" 
                                data-row 
                                data-tagihan="<?= strtolower($tagihan['nama']); ?>"
                                data-status="<?= $is_lunas ? 'lunas' : 'belum'; ?>">
                                
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 gradient-primary rounded-lg flex items-center justify-center mr-4">
                                            <i data-lucide="file-text" class="w-5 h-5 text-white"></i>
                                        </div>
                                        <div class="text-sm font-semibold text-secondary-800">
                                            <?= htmlspecialchars($tagihan['nama']); ?>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-semibold text-secondary-800">
                                        Rp <?= number_format($nominal, 0, ',', '.'); ?>
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4 text-center">
                                    <?php if ($diskon > 0): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                            Rp <?= number_format($diskon, 0, ',', '.'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-sm text-secondary-400">-</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-bold text-blue-600">
                                        Rp <?= number_format($harus_bayar, 0, ',', '.'); ?>
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-semibold text-success-600">
                                        Rp <?= number_format($terbayar, 0, ',', '.'); ?>
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4 text-center">
                                    <?php if ($sisa > 0): ?>
                                        <span class="text-sm font-bold text-danger-600">
                                            Rp <?= number_format($sisa, 0, ',', '.'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-sm text-secondary-400">-</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="<?= $is_lunas ? 'status-lunas' : 'status-belum'; ?> px-3 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1">
                                            <i data-lucide="<?= $is_lunas ? 'check-circle' : 'alert-circle'; ?>" class="w-3 h-3"></i>
                                            <?= $is_lunas ? 'Lunas' : 'Belum Lunas'; ?>
                                        </span>
                                        <button onclick="showRiwayat(<?= $tagihan['id']; ?>, '<?= htmlspecialchars(addslashes($tagihan['nama'])); ?>')" 
                                                class="p-2 hover:bg-primary-50 rounded-lg transition-colors group"
                                                title="Lihat Riwayat">
                                            <i data-lucide="history" class="w-4 h-4 text-primary-600 group-hover:text-primary-700"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Riwayat Transaksi -->
        <?php if (!empty($data['riwayat_transaksi'])): ?>
        <!-- Hidden data untuk JavaScript -->
        <script>
        const riwayatData = <?= json_encode($data['riwayat_transaksi']); ?>;
        </script>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Modal Riwayat Pembayaran -->
    <div id="modalRiwayat" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden animate-modal-in">
            <!-- Header -->
            <div class="bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <i data-lucide="history" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Riwayat Pembayaran</h3>
                        <p class="text-xs text-white/80" id="modalTagihanNama"></p>
                    </div>
                </div>
                <button onclick="closeModal()" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5 text-white"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <div id="modalRiwayatContent" class="space-y-3">
                    <!-- Akan diisi dengan JavaScript -->
                </div>
                
                <!-- Empty State -->
                <div id="modalEmptyState" class="hidden text-center py-12">
                    <div class="w-16 h-16 bg-secondary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="inbox" class="w-8 h-8 text-secondary-400"></i>
                    </div>
                    <p class="text-secondary-600">Belum ada riwayat pembayaran untuk tagihan ini</p>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    
    document.getElementById('searchTagihan')?.addEventListener('input', filterItems);
    document.getElementById('statusFilter')?.addEventListener('change', filterItems);
    
    function initializeFilters() {
        filterItems();
    }
    
    function filterItems() {
        const searchTerm = document.getElementById('searchTagihan')?.value.toLowerCase() || '';
        const statusFilter = document.getElementById('statusFilter')?.value || '';
        
        // Filter mobile cards
        const mobileCards = document.querySelectorAll('#mobileTagihanList [data-row]');
        mobileCards.forEach(card => {
            const tagihan = card.dataset.tagihan || '';
            const status = card.dataset.status || '';
            
            const searchMatch = !searchTerm || tagihan.includes(searchTerm);
            const statusMatch = !statusFilter || status === statusFilter;
            
            card.style.display = searchMatch && statusMatch ? '' : 'none';
        });
        
        // Filter desktop table rows
        const tableRows = document.querySelectorAll('#tableBody tr[data-row]');
        tableRows.forEach(row => {
            const tagihan = row.dataset.tagihan || '';
            const status = row.dataset.status || '';
            
            const searchMatch = !searchTerm || tagihan.includes(searchTerm);
            const statusMatch = !statusFilter || status === statusFilter;
            
            row.style.display = searchMatch && statusMatch ? '' : 'none';
        });
    }
    
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

// Show modal riwayat
function showRiwayat(tagihanId, namaTagihan) {
    const modal = document.getElementById('modalRiwayat');
    const modalNama = document.getElementById('modalTagihanNama');
    const modalContent = document.getElementById('modalRiwayatContent');
    const emptyState = document.getElementById('modalEmptyState');
    
    // Set nama tagihan
    modalNama.textContent = namaTagihan;
    
    // Filter riwayat by tagihan_id
    const riwayat = typeof riwayatData !== 'undefined' 
        ? riwayatData.filter(r => r.tagihan_id == tagihanId)
        : [];
    
    // Clear content
    modalContent.innerHTML = '';
    
    if (riwayat.length === 0) {
        emptyState.classList.remove('hidden');
        modalContent.classList.add('hidden');
    } else {
        emptyState.classList.add('hidden');
        modalContent.classList.remove('hidden');
        
        // Build riwayat items
        riwayat.forEach((item, index) => {
            const date = new Date(item.created_at);
            const formattedDate = date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });
            const formattedTime = date.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            }) + ' WIB';
            
            const itemHtml = `
                <div class="bg-secondary-50 rounded-xl p-4 border border-secondary-200 hover:border-primary-300 transition-all animate-fade-in" style="animation-delay: ${index * 50}ms">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 bg-success-100 rounded-lg flex items-center justify-center">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-success-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-secondary-800">Pembayaran Berhasil</div>
                                    <div class="text-xs text-secondary-500">${formattedDate}</div>
                                </div>
                            </div>
                            <div class="ml-10 space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-secondary-600">Waktu</span>
                                    <span class="text-secondary-800 font-medium">${formattedTime}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-secondary-600">Jumlah</span>
                                    <span class="text-lg font-bold text-success-600">Rp ${parseInt(item.jumlah).toLocaleString('id-ID')}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            modalContent.innerHTML += itemHtml;
        });
        
        // Reinitialize Lucide icons for new content
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
    
    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Reinitialize Lucide icons
    setTimeout(() => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }, 100);
}

// Close modal
function closeModal() {
    const modal = document.getElementById('modalRiwayat');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

// Close modal when clicking outside
document.getElementById('modalRiwayat')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
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

@keyframes modal-in {
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
    animation: fade-in 0.3s ease-out forwards;
}

.animate-modal-in {
    animation: modal-in 0.3s ease-out;
}

.status-lunas {
    background: #d1fae5;
    color: #065f46;
}

.status-belum {
    background: #fee2e2;
    color: #991b1b;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.progress-bar {
    background: #e5e7eb;
}

@media print {
    .glass-effect:nth-child(1) button {
        display: none !important;
    }
    
    .bg-gradient-to-br {
        background: white !important;
    }
}
</style>
