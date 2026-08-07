<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-secondary-50 to-secondary-100 p-6">
    <?php
    // Cek blokir akses RPP
    $blokirAkses = cekBlokirAksesRPP('all');
    $statsRPP = getStatistikRPPGuru();
    ?>
    
    <?php if ($blokirAkses): ?>
    <!-- Peringatan Akses Dibatasi -->
    <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 animate-fade-in">
        <div class="flex gap-3">
            <div class="shrink-0">
                <div class="p-2 bg-red-100 rounded-lg">
                    <i data-lucide="shield-alert" class="w-5 h-5 text-red-600"></i>
                </div>
            </div>
            <div class="flex-1">
                <h5 class="font-bold text-red-800 mb-1">Akses Fitur Dibatasi</h5>
                <p class="text-sm text-red-700 mb-3"><?= htmlspecialchars($blokirAkses['pesan']) ?></p>
                <div class="flex flex-wrap gap-2 mb-3">
                    <?php if (!empty($blokirAkses['blokir_absensi'])): ?>
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-lg">
                        <i data-lucide="lock" class="w-3 h-3"></i> Absensi
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($blokirAkses['blokir_jurnal'])): ?>
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-lg">
                        <i data-lucide="lock" class="w-3 h-3"></i> Jurnal
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($blokirAkses['blokir_nilai'])): ?>
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-lg">
                        <i data-lucide="lock" class="w-3 h-3"></i> Input Nilai
                    </span>
                    <?php endif; ?>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="<?= BASEURL ?>/guru/rpp" class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors">
                        <i data-lucide="file-plus" class="w-3 h-3"></i>
                        Buat RPP
                    </a>
                    <span class="text-xs text-red-600">
                        RPP Disetujui: <strong><?= (int)($statsRPP['approved'] ?? 0) ?></strong> | 
                        Diajukan: <strong><?= (int)($statsRPP['submitted'] ?? 0) ?></strong> | 
                        Draft: <strong><?= (int)($statsRPP['draft'] ?? 0) ?></strong>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Welcome Header (Simplified) -->
    <div class="mb-8">
        <div class="glass-effect rounded-2xl p-6 border border-white/20 shadow-lg animate-fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-secondary-800 mb-2">
                        Selamat Datang, <?= $_SESSION['nama_lengkap']; ?>!
                    </h1>
                    <p class="text-secondary-600 font-medium">
                        <i data-lucide="calendar-check" class="w-4 h-4 inline mr-2"></i>
                        Sesi Aktif: <span class="gradient-success text-white px-3 py-1 rounded-lg text-sm font-semibold"><?= $_SESSION['nama_semester_aktif']; ?></span>
                    </p>
                    <p class="text-sm text-secondary-500 mt-2">Mari kelola jurnal dan absensi kelas Anda dengan mudah</p>
                </div>
                <div class="hidden md:block">
                    <div class="gradient-success p-4 rounded-2xl shadow-lg">
                        <i data-lucide="book-open-check" class="w-12 h-12 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Dashboard -->
    <div class="mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Penugasan -->
            <div class="glass-effect rounded-xl p-6 border border-white/20 shadow-lg animate-slide-up">
                <div class="flex items-center justify-between mb-4">
                    <div class="gradient-primary p-3 rounded-xl">
                        <i data-lucide="book-open" class="w-6 h-6 text-white"></i>
                    </div>
                    <span class="status-badge bg-primary-100 text-primary-800">Total</span>
                </div>
                <h3 class="text-2xl font-bold text-secondary-800 mb-1"><?= $data['total_penugasan'] ?? 0; ?></h3>
                <p class="text-sm text-secondary-600">Mata Pelajaran Diampu</p>
            </div>

            <!-- Total Hari Mengajar -->
            <div class="glass-effect rounded-xl p-6 border border-white/20 shadow-lg animate-slide-up" style="animation-delay: 0.1s;">
                <div class="flex items-center justify-between mb-4">
                    <div class="gradient-warning p-3 rounded-xl">
                        <i data-lucide="calendar-days" class="w-6 h-6 text-white"></i>
                    </div>
                    <span class="status-badge bg-warning-100 text-warning-800">Hari</span>
                </div>
                <h3 class="text-2xl font-bold text-secondary-800 mb-1"><?= $data['total_hari_mengajar'] ?? 0; ?></h3>
                <p class="text-sm text-secondary-600">Hari Mengajar</p>
            </div>

            <!-- Mengajar Kelas & Mapel -->
            <div class="glass-effect rounded-xl p-6 border border-white/20 shadow-lg animate-slide-up" style="animation-delay: 0.2s;">
                <div class="flex items-center justify-between mb-4">
                    <div class="gradient-success p-3 rounded-xl">
                        <i data-lucide="graduation-cap" class="w-6 h-6 text-white"></i>
                    </div>
                    <span class="status-badge bg-success-100 text-success-800">Mengajar</span>
                </div>
                <div class="space-y-2 max-h-24 overflow-y-auto">
                    <?php if (!empty($data['kelas_mapel_info'])): ?>
                        <?php foreach ($data['kelas_mapel_info'] as $info): ?>
                            <div class="text-sm bg-white/30 px-2 py-1 rounded">
                                <span class="font-semibold text-secondary-800"><?= htmlspecialchars($info['nama_kelas']); ?></span>
                                <span class="text-secondary-600"> - <?= htmlspecialchars($info['nama_mapel']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-sm text-secondary-600">Belum ada penugasan</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    // ===== Helper functions dari jurnal.php =====
    function themeByKelas($nama_kelas) {
        $token = strtoupper(trim(strtok((string)$nama_kelas, ' '))); // VII/VIII/IX/X/XI/XII
        $isLower = in_array($token, ['VII','VIII','IX'], true) || preg_match('/^\s*[7-9]\b/', (string)$nama_kelas);
        return $isLower
            ? ['header' => 'from-emerald-500 to-teal-500', 'badge' => 'badge-smp']
            : ['header' => 'from-indigo-500 to-violet-500', 'badge' => 'badge-sma'];
    }

    function kelasSortKey($kelas) {
        $s = trim((string)$kelas);
        // 1) Romawi
        if (preg_match('/\b(VII|VIII|IX|X|XI|XII)\b/i', $s, $m)) {
            $map = ['VII'=>7,'VIII'=>8,'IX'=>9,'X'=>10,'XI'=>11,'XII'=>12];
            $g = $map[strtoupper($m[1])] ?? 99;
            // buang token di depan utk suffix
            $suffix = trim(preg_replace('/^(VII|VIII|IX|X|XI|XII)\s*/i', '', $s, 1));
            return [$g, $suffix ?: $s];
        }
        // 2) Angka di depan (7-12)
        if (preg_match('/^\s*(\d{1,2})\b/', $s, $m)) {
            $n = (int)$m[1];
            if ($n >= 7 && $n <= 12) {
                $suffix = trim(preg_replace('/^\s*\d{1,2}\s*/', '', $s, 1));
                return [$n, $suffix ?: $s];
            }
        }
        // 3) Tidak terdeteksi → taruh di akhir
        return [99, $s];
    }

    function cmpKelas($a, $b) {
        [$ga,$sa] = kelasSortKey($a);
        [$gb,$sb] = kelasSortKey($b);
        if ($ga !== $gb) return $ga <=> $gb;
        $cmp = strnatcasecmp($sa, $sb);
        if ($cmp !== 0) return $cmp;
        return strnatcasecmp((string)$a, (string)$b);
    }

    // ===== Hitung jumlah pertemuan (1x query) & siapkan data =====
    $jumlahPertemuanByPenugasan = [];
    $maxPertemuanByPenugasan    = [];
    $rppExistsByPenugasan       = []; // Track RPP existence
    $rppIdByPenugasan           = []; // Track RPP ID for download
    $rppApprovedByPenugasan     = []; // Track RPP approval status per penugasan
    $rppStatusByPenugasan       = []; // Track RPP status per penugasan
    $kelasOptions = [];
    $jadwalSorted = $data['jadwal_mengajar'] ?? [];
    
    // Ambil pengaturan wajib RPP dari admin
    $pengaturanWajibRPP = getPengaturanWajibRPP();

    if (!empty($jadwalSorted)) {
        // 1) Ambil id_penugasan & kumpulkan kelas unik
        $ids = [];
        $kelasSetAssoc = [];
        foreach ($jadwalSorted as $row) {
            if (isset($row['id_penugasan'])) $ids[] = (int)$row['id_penugasan'];
            if (!empty($row['nama_kelas']))  $kelasSetAssoc[$row['nama_kelas']] = true;
        }
        // 2) Ambil jumlah pertemuan per id_penugasan
        $ids = array_values(array_unique(array_filter($ids)));
        if (!empty($ids)) {
            require_once APPROOT . '/app/core/Database.php';
            $db = new Database;
            $in = implode(',', $ids);
            $sql = "
                SELECT id_penugasan,
                       COUNT(*) AS total_pertemuan,
                       COALESCE(MAX(pertemuan_ke),0) AS max_pertemuan
                FROM jurnal
                WHERE id_penugasan IN ($in)
                GROUP BY id_penugasan
            ";
            $db->query($sql);
            $rows = $db->resultSet();
            foreach ($rows as $r) {
                $pid = (int)$r['id_penugasan'];
                $jumlahPertemuanByPenugasan[$pid] = (int)$r['total_pertemuan'];
                $maxPertemuanByPenugasan[$pid]    = (int)$r['max_pertemuan'];
            }
            
            // 3) Cek RPP yang sudah ada untuk penugasan ini
            $id_tp = $_SESSION['id_tp_aktif'] ?? null;
            $id_semester = $_SESSION['id_semester_aktif'] ?? null;
            if ($id_tp && $id_semester) {
                $sqlRpp = "
                    SELECT id_rpp, id_penugasan, status
                    FROM rpp
                    WHERE id_penugasan IN ($in)
                    AND id_tp = :id_tp
                    AND id_semester = :id_semester
                ";
                $db->query($sqlRpp);
                $db->bind('id_tp', $id_tp);
                $db->bind('id_semester', $id_semester);
                $rppRows = $db->resultSet();
                foreach ($rppRows as $rr) {
                    $pid = (int)$rr['id_penugasan'];
                    $rppExistsByPenugasan[$pid] = true;
                    $rppIdByPenugasan[$pid] = (int)$rr['id_rpp'];
                    $rppStatusByPenugasan[$pid] = $rr['status'] ?? 'draft';
                    $rppApprovedByPenugasan[$pid] = ($rr['status'] === 'approved');
                }
            }
        }
        // 4) Urutkan daftar kelas (filter) mulai VII→XII
        $kelasOptions = array_keys($kelasSetAssoc);
        usort($kelasOptions, 'cmpKelas');

        // 5) Urutkan kartu berdasarkan nama_kelas (VII→XII), lalu nama mapel
        usort($jadwalSorted, function($A, $B) {
            $ka = $A['nama_kelas'] ?? '';
            $kb = $B['nama_kelas'] ?? '';
            $c = cmpKelas($ka, $kb);
            if ($c !== 0) return $c;
            return strnatcasecmp($A['nama_mapel'] ?? '', $B['nama_mapel'] ?? '');
        });
    }
    ?>

    <!-- Class Selection + Filter -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-secondary-800 flex items-center">
                <i data-lucide="school" class="w-5 h-5 mr-2 text-success-500"></i>
                Kelas Mengajar
            </h3>
            <span class="status-badge bg-primary-100 text-primary-800">
                <?= count($data['jadwal_mengajar'] ?? []); ?> Kelas Tersedia
            </span>
        </div>

        <?php if (empty($jadwalSorted)): ?>
            <!-- Empty State -->
            <div class="glass-effect rounded-2xl p-12 border border-white/20 shadow-lg text-center animate-fade-in">
                <div class="max-w-md mx-auto">
                    <div class="gradient-warning p-4 rounded-2xl inline-flex mb-6">
                        <i data-lucide="calendar-x" class="w-12 h-12 text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-secondary-800 mb-3">Belum Ada Jadwal Mengajar</h3>
                    <p class="text-secondary-600 mb-6">
                        Anda belum memiliki jadwal mengajar untuk semester ini.
                        Silakan hubungi admin untuk mendapatkan penugasan kelas.
                    </p>
                    <div class="space-y-3">
                        <button onclick="window.location.reload()" class="btn-secondary w-full">
                            <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-2"></i>Refresh Halaman
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>

            <!-- FILTER KELAS (otomatis & terurut VII→XII) -->
            <div class="glass-effect rounded-xl p-3 border border-white/20 shadow mb-5">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <label for="filter-kelas" class="text-sm font-medium text-secondary-700 flex items-center gap-2">
                        <i data-lucide="filter" class="w-4 h-4 text-secondary-500"></i>
                        Filter Kelas
                    </label>
                    <div class="relative w-full sm:w-72">
                        <select id="filter-kelas" class="filter-select">
                            <option value="">Semua Kelas</option>
                            <?php foreach ($kelasOptions as $k): ?>
                                <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
                            <i data-lucide="chevron-down" class="w-4 h-4 text-secondary-400"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRID KARTU (sudah terurut) -->
            <div id="cards-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php foreach ($jadwalSorted as $index => $jadwal): ?>
                    <?php
                        $pid   = (int)$jadwal['id_penugasan'];
                        $mapel = $jadwal['nama_mapel'] ?? '-';
                        $kelas = $jadwal['nama_kelas'] ?? '-';
                        $id_mapel = $jadwal['id_mapel'] ?? '';
                        $id_kelas = $jadwal['id_kelas'] ?? '';

                        $theme = themeByKelas($kelas);
                        $total = $jumlahPertemuanByPenugasan[$pid] ?? 0;
                        $kelasAttr = strtolower(trim($kelas));
                        
                        // RPP status
                        $hasRpp = $rppExistsByPenugasan[$pid] ?? false;
                        $rppId = $rppIdByPenugasan[$pid] ?? null;
                        $rppApproved = $rppApprovedByPenugasan[$pid] ?? false;
                        $rppStatus = $rppStatusByPenugasan[$pid] ?? null;
                        
                        // Cek apakah menu harus disembunyikan berdasarkan pengaturan admin dan status RPP
                        $hideJurnal = !empty($pengaturanWajibRPP['wajib_rpp_untuk_jurnal']) && !$rppApproved;
                        $hideAbsen = !empty($pengaturanWajibRPP['wajib_rpp_untuk_absen']) && !$rppApproved;
                        $hideNilai = !empty($pengaturanWajibRPP['wajib_rpp_untuk_nilai']) && !$rppApproved;
                    ?>
                    <div class="class-card rounded-xl border border-white/20 shadow-md overflow-hidden card-hover group animate-slide-up"
                         data-kelas-label="<?= htmlspecialchars($kelas) ?>"
                         data-kelas="<?= htmlspecialchars($kelasAttr) ?>"
                         style="animation-delay: <?= $index * 0.06; ?>s;">
                        <!-- Header: Mapel di tengah atas -->
                        <div class="relative p-4 text-white bg-gradient-to-r <?= $theme['header']; ?>">
                            <!-- Badge jumlah pertemuan (pojok kanan) -->
                            <span class="pertemuan-badge <?= $theme['badge']; ?>" title="Total pertemuan berjalan">
                                <?= $total; ?>x
                            </span>
                            <h3 class="text-center text-lg sm:text-xl font-bold leading-tight">
                                <?= htmlspecialchars($mapel); ?>
                            </h3>
                        </div>

                        <!-- Konten: Kelas besar + info -->
                        <div class="p-4">
                            <div class="text-center">
                                <div class="text-lg sm:text-xl font-semibold text-secondary-800">
                                    <?= htmlspecialchars($kelas); ?>
                                </div>
                                <div class="mt-1 text-sm text-secondary-600 flex items-center justify-center gap-1">
                                    <i data-lucide="calendar-range" class="w-4 h-4 text-secondary-400"></i>
                                    <span><b><?= $total; ?></b> pertemuan</span>
                                </div>
                            </div>

                            <?php if ($hideJurnal): ?>
                            <!-- Tombol Buat Jurnal Terkunci -->
                            <div class="mt-4 p-3 bg-gray-100 rounded-xl text-center">
                                <div class="flex items-center justify-center gap-2 text-gray-500">
                                    <i data-lucide="lock" class="w-4 h-4"></i>
                                    <span class="text-sm font-medium">Buat Jurnal Terkunci</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">RPP harus disetujui terlebih dahulu</p>
                            </div>
                            <?php else: ?>
                            <!-- Tombol besar: Buat Jurnal -->
                            <a href="<?= BASEURL; ?>/guru/tambahJurnal/<?= $pid; ?>"
                               class="btn-primary btn-primary-lg w-full mt-4 inline-flex items-center justify-center gap-2">
                                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                                Buat Jurnal
                            </a>
                            <?php endif; ?>

                            <?php if ($total > 0): // Hanya tampilkan jika ada pertemuan ?>
                            
                            <?php if (!$hideAbsen): ?>
                            <!-- Section: Absen & Jurnal -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex items-center mb-3">
                                    <i data-lucide="clipboard-check" class="w-4 h-4 mr-1.5 text-secondary-400"></i>
                                    <span class="text-xs font-bold text-secondary-600 uppercase tracking-wider">Absen & Jurnal</span>
                                </div>
                                <!-- Desktop: Grid Buttons -->
                                <div class="seg-actions hidden sm:grid grid-cols-3 gap-3">
                                    <!-- Detail -->
                                    <a href="<?= BASEURL; ?>/riwayatJurnal/detail/<?= $pid; ?>" class="seg-btn seg-primary" title="Lihat Detail">
                                        <div class="seg-left">
                                            <span class="seg-icon">
                                                <i data-lucide="eye" class="w-5 h-5"></i>
                                            </span>
                                            <span class="seg-label">Detail</span>
                                        </div>
                                        <i data-lucide="chevron-right" class="w-4 h-4 seg-right"></i>
                                    </a>

                                    <!-- Rincian Absen (langsung ke rincianAbsen dengan parameter) -->
                                    <a href="<?= BASEURL; ?>/guru/rincianAbsen?id_mapel=<?= $id_mapel; ?>&id_kelas=<?= $id_kelas; ?>&periode=semester" class="seg-btn seg-secondary" title="Rincian Absensi">
                                        <div class="seg-left">
                                            <span class="seg-icon">
                                                <i data-lucide="list-checks" class="w-5 h-5"></i>
                                            </span>
                                            <span class="seg-label">Rincian</span>
                                        </div>
                                        <i data-lucide="chevron-right" class="w-4 h-4 seg-right"></i>
                                    </a>

                                    <!-- Download Absen -->
                                    <a href="<?= BASEURL; ?>/riwayatJurnal/downloadAbsensi/<?= $pid; ?>" class="seg-btn seg-dark" title="Download Absen" target="_blank">
                                        <div class="seg-left">
                                            <span class="seg-icon">
                                                <i data-lucide="download" class="w-5 h-5"></i>
                                            </span>
                                            <span class="seg-label">Absen</span>
                                        </div>
                                        <i data-lucide="chevron-right" class="w-4 h-4 seg-right"></i>
                                    </a>

                                    <!-- Download Jurnal -->
                                    <a href="<?= BASEURL; ?>/riwayatJurnal/downloadJurnal/<?= $pid; ?>" class="seg-btn seg-purple" title="Download Jurnal" target="_blank">
                                        <div class="seg-left">
                                            <span class="seg-icon">
                                                <i data-lucide="file-down" class="w-5 h-5"></i>
                                            </span>
                                            <span class="seg-label">Jurnal</span>
                                        </div>
                                        <i data-lucide="chevron-right" class="w-4 h-4 seg-right"></i>
                                    </a>
                                </div>
                                <!-- Mobile: Dropdown -->
                                <div class="sm:hidden relative">
                                    <select onchange="handleMenuSelect(this, <?= $pid; ?>)" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none pr-10">
                                        <option value="">Pilih Menu...</option>
                                        <option value="detail|<?= BASEURL; ?>/riwayatJurnal/detail/<?= $pid; ?>">👁️ Detail</option>
                                        <option value="rincian|<?= BASEURL; ?>/guru/rincianAbsen?id_mapel=<?= $id_mapel; ?>&id_kelas=<?= $id_kelas; ?>&periode=semester">📋 Rincian Absen</option>
                                        <option value="link|<?= BASEURL; ?>/riwayatJurnal/downloadAbsensi/<?= $pid; ?>">📥 Download Absen</option>
                                        <option value="link|<?= BASEURL; ?>/riwayatJurnal/downloadJurnal/<?= $pid; ?>">📄 Download Jurnal</option>
                                    </select>
                                    <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                                </div>
                            </div>
                            <?php endif; // endif !$hideAbsen ?>

                            <?php if (defined('MENU_INPUT_NILAI_ENABLED') && MENU_INPUT_NILAI_ENABLED && !$hideNilai): ?>
                            <!-- Section: Input Nilai -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex items-center mb-3">
                                    <i data-lucide="pencil" class="w-4 h-4 mr-1.5 text-secondary-400"></i>
                                    <span class="text-xs font-bold text-secondary-600 uppercase tracking-wider">Input Nilai</span>
                                </div>
                                <!-- Desktop: Grid Buttons -->
                                <div class="seg-actions hidden sm:grid grid-cols-3 gap-3">
                                    <!-- Harian -->
                                    <a href="<?= BASEURL; ?>/nilai/tugasHarian?id_penugasan=<?= $pid; ?>" class="seg-btn seg-warning" title="Input Nilai Harian">
                                        <div class="seg-left">
                                            <span class="seg-icon">
                                                <i data-lucide="file-edit" class="w-5 h-5"></i>
                                            </span>
                                            <span class="seg-label">Harian</span>
                                        </div>
                                        <i data-lucide="chevron-right" class="w-4 h-4 seg-right"></i>
                                    </a>

                                    <!-- STS -->
                                    <a href="<?= BASEURL; ?>/nilai/tengahSemester?id_penugasan=<?= $pid; ?>" class="seg-btn seg-info" title="Input Nilai Tengah Semester">
                                        <div class="seg-left">
                                            <span class="seg-icon">
                                                <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                                            </span>
                                            <span class="seg-label">STS</span>
                                        </div>
                                        <?php if (!empty($jadwal['jumlah_nilai_sts'])): ?>
                                            <span class="seg-badge"><?= (int)$jadwal['jumlah_nilai_sts']; ?></span>
                                        <?php else: ?>
                                            <i data-lucide="chevron-right" class="w-4 h-4 seg-right"></i>
                                        <?php endif; ?>
                                    </a>

                                    <!-- SAS -->
                                    <a href="<?= BASEURL; ?>/nilai/akhirSemester?id_penugasan=<?= $pid; ?>" class="seg-btn seg-success" title="Input Nilai Akhir Semester">
                                        <div class="seg-left">
                                            <span class="seg-icon">
                                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                                            </span>
                                            <span class="seg-label">SAS</span>
                                        </div>
                                        <?php if (!empty($jadwal['jumlah_nilai_sas'])): ?>
                                            <span class="seg-badge"><?= (int)$jadwal['jumlah_nilai_sas']; ?></span>
                                        <?php else: ?>
                                            <i data-lucide="chevron-right" class="w-4 h-4 seg-right"></i>
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <!-- Mobile: Dropdown -->
                                <div class="sm:hidden relative">
                                    <select onchange="if(this.value) window.location.href=this.value" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none pr-10">
                                        <option value="">Pilih Menu Input Nilai...</option>
                                        <option value="<?= BASEURL; ?>/nilai/tugasHarian?id_penugasan=<?= $pid; ?>">📝 Harian</option>
                                        <option value="<?= BASEURL; ?>/nilai/tengahSemester?id_penugasan=<?= $pid; ?>">📋 STS <?php if (!empty($jadwal['jumlah_nilai_sts'])): ?>(<?= (int)$jadwal['jumlah_nilai_sts']; ?>)<?php endif; ?></option>
                                        <option value="<?= BASEURL; ?>/nilai/akhirSemester?id_penugasan=<?= $pid; ?>">✅ SAS <?php if (!empty($jadwal['jumlah_nilai_sas'])): ?>(<?= (int)$jadwal['jumlah_nilai_sas']; ?>)<?php endif; ?></option>
                                    </select>
                                    <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>

                            <!-- Section: RPP (selalu tampil meskipun tidak ada pertemuan) -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex items-center mb-3">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-1.5 text-secondary-400"></i>
                                    <span class="text-xs font-bold text-secondary-600 uppercase tracking-wider">RPP</span>
                                    <?php if ($hasRpp): ?>
                                        <?php if ($rppApproved): ?>
                                            <span class="ml-2 px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-700 rounded-full">✓ Disetujui</span>
                                        <?php elseif ($rppStatus === 'submitted'): ?>
                                            <span class="ml-2 px-2 py-0.5 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded-full">⏳ Menunggu</span>
                                        <?php elseif ($rppStatus === 'revision'): ?>
                                            <span class="ml-2 px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-700 rounded-full">↩ Revisi</span>
                                        <?php else: ?>
                                            <span class="ml-2 px-2 py-0.5 text-xs font-semibold bg-gray-100 text-gray-600 rounded-full">Draft</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <!-- Desktop: Grid Buttons -->
                                <div class="seg-actions hidden sm:grid grid-cols-2 gap-3">
                                    <?php if ($hasRpp): ?>
                                        <!-- Lihat RPP (sudah dibuat) -->
                                        <a href="<?= BASEURL; ?>/guru/detailRPP/<?= $rppId; ?>" class="seg-btn seg-indigo" title="Lihat RPP">
                                            <div class="seg-left">
                                                <span class="seg-icon">
                                                    <i data-lucide="file-search" class="w-5 h-5"></i>
                                                </span>
                                                <span class="seg-label">Lihat RPP</span>
                                            </div>
                                            <i data-lucide="chevron-right" class="w-4 h-4 seg-right"></i>
                                        </a>

                                        <!-- Download RPP -->
                                        <a href="<?= BASEURL; ?>/guru/downloadRPPPDF/<?= $rppId; ?>" class="seg-btn seg-success" title="Download RPP">
                                            <div class="seg-left">
                                                <span class="seg-icon">
                                                    <i data-lucide="download" class="w-5 h-5"></i>
                                                </span>
                                                <span class="seg-label">Download RPP</span>
                                            </div>
                                            <i data-lucide="chevron-right" class="w-4 h-4 seg-right"></i>
                                        </a>
                                    <?php else: ?>
                                        <!-- Buat RPP (belum dibuat) -->
                                        <a href="<?= BASEURL; ?>/guru/buatRPP/<?= $pid; ?>" class="seg-btn seg-purple" title="Buat RPP">
                                            <div class="seg-left">
                                                <span class="seg-icon">
                                                    <i data-lucide="file-plus" class="w-5 h-5"></i>
                                                </span>
                                                <span class="seg-label">Buat RPP</span>
                                            </div>
                                            <i data-lucide="chevron-right" class="w-4 h-4 seg-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <!-- Mobile: Dropdown -->
                                <div class="sm:hidden relative">
                                    <select onchange="if(this.value) window.location.href=this.value" class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none pr-10">
                                        <option value="">Pilih Menu RPP...</option>
                                        <?php if ($hasRpp): ?>
                                            <option value="<?= BASEURL; ?>/guru/detailRPP/<?= $rppId; ?>">🔍 Lihat RPP</option>
                                            <option value="<?= BASEURL; ?>/guru/downloadRPPPDF/<?= $rppId; ?>">📥 Download RPP</option>
                                        <?php else: ?>
                                            <option value="<?= BASEURL; ?>/guru/buatRPP/<?= $pid; ?>">📝 Buat RPP</option>
                                        <?php endif; ?>
                                    </select>
                                    <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</main>

<style>
/* Modern Action Button Styles with Icon Circles */
.action-btn {
    @apply flex flex-col items-center justify-center p-4 rounded-xl transition-all duration-300;
    @apply bg-white border-2 border-gray-100;
    min-height: 95px;
    text-align: center;
}

.action-btn:hover {
    @apply transform -translate-y-2 shadow-xl border-transparent;
}

/* Badge indikator jumlah nilai */
.action-btn.has-nilai {
    @apply border-success-300;
}

.badge-count {
    position: absolute;
    top: -4px;
    right: -4px;
    min-width: 22px;
    height: 22px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    padding: 0 6px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}

.icon-circle {
    @apply rounded-full p-3 transition-all duration-300;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.action-btn:hover .icon-circle {
    @apply transform scale-110;
}

/* Warning - Orange for Harian */
.action-btn-warning .icon-circle {
    @apply bg-warning-100;
}
.action-btn-warning .icon-circle i {
    color: #f59e0b;
    stroke-width: 2.5;
}
.action-btn-warning span {
    @apply text-warning-700;
}
.action-btn-warning:hover {
    @apply bg-warning-50;
}
.action-btn-warning:hover .icon-circle {
    @apply bg-warning-200 shadow-lg;
}

/* Info - Cyan for STS */
.action-btn-info .icon-circle {
    @apply bg-info-100;
}
.action-btn-info .icon-circle i {
    color: #06b6d4;
    stroke-width: 2.5;
}
.action-btn-info span {
    @apply text-info-700;
}
.action-btn-info:hover {
    @apply bg-info-50;
}
.action-btn-info:hover .icon-circle {
    @apply bg-info-200 shadow-lg;
}

/* Success - Green for SAS */
.action-btn-success .icon-circle {
    @apply bg-success-100;
}
.action-btn-success .icon-circle i {
    color: #10b981;
    stroke-width: 2.5;
}
.action-btn-success span {
    @apply text-success-700;
}
.action-btn-success:hover {
    @apply bg-success-50;
}
.action-btn-success:hover .icon-circle {
    @apply bg-success-200 shadow-lg;
}

/* Primary - Blue for Detail */
.action-btn-primary .icon-circle {
    @apply bg-primary-100;
}
.action-btn-primary .icon-circle i {
    color: #3b82f6;
    stroke-width: 2.5;
}
.action-btn-primary span {
    @apply text-primary-700;
}
.action-btn-primary:hover {
    @apply bg-primary-50;
}
.action-btn-primary:hover .icon-circle {
    @apply bg-primary-200 shadow-lg;
}

/* Secondary - Gray for Absen */
.action-btn-secondary .icon-circle {
    @apply bg-secondary-100;
}
.action-btn-secondary .icon-circle i {
    color: #64748b;
    stroke-width: 2.5;
}
.action-btn-secondary span {
    @apply text-secondary-700;
}
.action-btn-secondary:hover {
    @apply bg-secondary-50;
}
.action-btn-secondary:hover .icon-circle {
    @apply bg-secondary-200 shadow-lg;
}

/* Dark - Dark Gray for Print */
.action-btn-dark .icon-circle {
    @apply bg-gray-100;
}
.action-btn-dark .icon-circle i {
    color: #374151;
    stroke-width: 2.5;
}
.action-btn-dark span {
    @apply text-gray-700;
}
.action-btn-dark:hover {
    @apply bg-gray-50;
}
.action-btn-dark:hover .icon-circle {
    @apply bg-gray-200 shadow-lg;
}
</style>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  lucide.createIcons();

  // Hover lembut kartu
  document.querySelectorAll('.card-hover').forEach(card=>{
    card.addEventListener('mouseenter', ()=> card.style.transform='translateY(-6px) scale(1.015)');
    card.addEventListener('mouseleave', ()=> card.style.transform='translateY(0) scale(1)');
  });

  // === FILTER KELAS ===
  const select = document.getElementById('filter-kelas');
  const cards  = document.querySelectorAll('.class-card');

  function applyFilter() {
    const val = (select?.value || '').toLowerCase().trim();
    cards.forEach(card => {
      const kelas = (card.dataset.kelas || '');
      const show  = !val || kelas === val; // match exact nama_kelas (case-insensitive)
      card.style.display = show ? '' : 'none';
    });
  }

  if (select) {
    // Auto-select jika hanya 1 kelas unik (di luar "Semua Kelas")
    if (select.options.length === 2) select.selectedIndex = 1;
    applyFilter();
    select.addEventListener('change', applyFilter);
  }

  // Animate stats on load
  const stats = document.querySelectorAll('.text-2xl.font-bold.text-secondary-800');
  stats.forEach((stat, index) => {
    setTimeout(() => {
      stat.style.opacity = '0';
      stat.style.transform = 'scale(1.2)';
      stat.style.transition = 'all 0.3s ease';
      
      setTimeout(() => {
        stat.style.opacity = '1';
        stat.style.transform = 'scale(1)';
      }, 100);
    }, index * 200);
  });
});

// Function untuk cetak
function printPenugasan(penugasanId) {
  const url = '<?= BASEURL; ?>/riwayatJurnal/cetak/' + encodeURIComponent(penugasanId);
  const w = window.open(url, '_blank');
  if (!w || w.closed || typeof w.closed === 'undefined') {
    showNotification('Izinkan pop-up untuk menampilkan layar cetak.', 'warning');
    window.location.href = url;
  }
}

// Handle menu select untuk mobile dropdown
function handleMenuSelect(select, penugasanId) {
  const value = select.value;
  if (!value) return;
  
  const [action, url] = value.split('|');
  
  if (action === 'cetak') {
    printPenugasan(url); // url berisi penugasan ID
  } else if (action === 'link') {
    window.open(url, '_blank'); // Open in new tab for downloads
  } else {
    window.location.href = url;
  }
  
  // Reset dropdown
  select.value = '';
}

// Notification system (simplified version)
function showNotification(message, type = 'info') {
  alert(message);
}
</script>

<style>
/* Segmented pill action buttons */
.seg-actions { width: 100%; }
.seg-btn {
    display: flex; align-items: center; justify-content: space-between;
    gap: .75rem; width: 100%; padding: .75rem .9rem;
    border-radius: .9rem; border: 1px solid rgba(0,0,0,.06);
    background: #fff; color: #0f172a; text-decoration: none;
    box-shadow: 0 4px 14px rgba(2,6,23,.06);
    transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
}
.seg-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(2,6,23,.10); filter: brightness(1.02); }
.seg-left { display: inline-flex; align-items: center; gap: .6rem; }
.seg-label { font-size: .9rem; font-weight: 700; letter-spacing: .2px; }
.seg-right { color: rgba(15,23,42,.55); }
.seg-icon { display:inline-flex; align-items:center; justify-content:center; width: 2rem; height: 2rem; border-radius: .65rem; color: #fff; }
.seg-badge { display:inline-flex; align-items:center; justify-content:center; min-width: 1.5rem; height: 1.5rem; padding: 0 .4rem; border-radius: 999px; font-size: .75rem; font-weight: 800; color:#fff; }

/* Variants */
.seg-warning { background: linear-gradient(135deg,#fff7ed,#ffedd5); border-color: rgba(245,158,11,.25); }
.seg-warning .seg-icon { background: linear-gradient(135deg,#f59e0b,#d97706); }
.seg-warning .seg-label { color: #92400e; }

.seg-info { background: linear-gradient(135deg,#ecfeff,#cffafe); border-color: rgba(6,182,212,.25); }
.seg-info .seg-icon { background: linear-gradient(135deg,#06b6d4,#0891b2); }
.seg-info .seg-label { color: #155e75; }
.seg-info .seg-badge { background:#06b6d4; }

.seg-success { background: linear-gradient(135deg,#ecfdf5,#d1fae5); border-color: rgba(34,197,94,.25); }
.seg-success .seg-icon { background: linear-gradient(135deg,#22c55e,#16a34a); }
.seg-success .seg-label { color: #065f46; }
.seg-success .seg-badge { background:#22c55e; }

.seg-primary { background: linear-gradient(135deg,#eff6ff,#dbeafe); border-color: rgba(59,130,246,.25); }
.seg-primary .seg-icon { background: linear-gradient(135deg,#3b82f6,#1d4ed8); }
.seg-primary .seg-label { color:#1e40af; }

.seg-secondary { background: linear-gradient(135deg,#f1f5f9,#e2e8f0); border-color: rgba(100,116,139,.25); }
.seg-secondary .seg-icon { background: linear-gradient(135deg,#64748b,#475569); }
.seg-secondary .seg-label { color:#334155; }

.seg-dark { background: linear-gradient(135deg,#f8fafc,#e2e8f0); border-color: rgba(55,65,81,.25); }
.seg-dark .seg-icon { background: linear-gradient(135deg,#374151,#111827); }
.seg-dark .seg-label { color:#111827; }

.seg-purple { background: linear-gradient(135deg,#faf5ff,#f3e8ff); border-color: rgba(168,85,247,.25); }
.seg-purple .seg-icon { background: linear-gradient(135deg,#a855f7,#9333ea); }
.seg-purple .seg-label { color:#581c87; }

.seg-indigo { background: linear-gradient(135deg,#eef2ff,#e0e7ff); border-color: rgba(99,102,241,.25); }
.seg-indigo .seg-icon { background: linear-gradient(135deg,#6366f1,#4f46e5); }
.seg-indigo .seg-label { color:#312e81; }
/* Glass effect */
.glass-effect {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

/* Kartu & animasi */
.card-hover { transition: transform .18s ease, box-shadow .18s ease; }
.card-hover:hover { box-shadow: 0 8px 26px rgba(2,6,23,.08); }
@keyframes slideUp { from {opacity:0; transform: translateY(10px)} to {opacity:1; transform:none} }
.animate-slide-up{ animation: slideUp .28s ease both; }
@keyframes fadeIn { from {opacity:0;} to {opacity:1;} }
.animate-fade-in{ animation: fadeIn .4s ease both; }

/* Gradients util */
.gradient-primary{ background: linear-gradient(135deg,#6366f1,#22d3ee); }
.gradient-success{ background: linear-gradient(135deg,#10b981,#34d399); }
.gradient-warning{ background: linear-gradient(135deg,#f59e0b,#fbbf24); }

/* Header warna berdasarkan jenjang */
.badge-smp { background: rgba(16,185,129,.18); color:#fff; border:1px solid rgba(255,255,255,.3); }
.badge-sma { background: rgba(99,102,241,.22); color:#fff; border:1px solid rgba(255,255,255,.3); }

/* Badge jumlah pertemuan di header */
.pertemuan-badge{
  position:absolute; right:.75rem; top:.6rem;
  font-size:.75rem; line-height:1;
  padding:.25rem .5rem; border-radius:999px;
}

/* Button Styles */
.btn-primary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  font-weight: 600;
  color: white;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  border: 1px solid rgba(59, 130, 246, 0.3);
  box-shadow: 0 6px 14px rgba(59, 130, 246, 0.15);
  transition: all 0.15s ease;
  white-space: nowrap;
  text-decoration: none;
}

.btn-primary:hover {
  filter: brightness(1.05);
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
}

.btn-secondary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  font-weight: 600;
  color: #4338ca;
  background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
  border: 1px solid rgba(99, 102, 241, 0.25);
  box-shadow: 0 6px 14px rgba(99, 102, 241, 0.12);
  transition: all 0.15s ease;
  white-space: nowrap;
  text-decoration: none;
}

.btn-secondary:hover {
  filter: brightness(0.97);
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(99, 102, 241, 0.18);
  background: linear-gradient(135deg, #c7d2fe, #a5b4fc);
}

/* Tombol 'Buat Jurnal' diperbesar */
.btn-primary-lg{
  font-size: .9375rem; /* 15px */
  padding: .75rem 1rem;
  border-radius: .9rem;
  font-weight: 700;
  box-shadow: 0 8px 20px rgba(59,130,246,.15);
  transition: transform .15s ease, box-shadow .15s ease;
}
.btn-primary-lg:hover{ transform: translateY(-1px); box-shadow: 0 12px 26px rgba(59,130,246,.22); }
.btn-primary-lg:active{ transform: translateY(0); }

/* Action Icons Styles */
.action-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  border-radius: 0.5rem;
  border: none;
  cursor: pointer;
  transition: all 0.15s ease;
  text-decoration: none;
}

.action-icon:hover {
  transform: translateY(-1px) scale(1.05);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.action-icon-primary {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
}

.action-icon-secondary {
  background: linear-gradient(135deg, #6366f1, #4338ca);
  color: white;
}

.action-icon-success {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
}

.action-icon-warning {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
}

.action-icon-info {
  background: linear-gradient(135deg, #06b6d4, #0891b2);
  color: white;
}

.action-icon-dark {
  background: linear-gradient(135deg, #64748b, #475569);
  color: white;
}

/* Select filter */
.filter-select{
  width:100%;
  appearance:none;
  -webkit-appearance:none;
  -moz-appearance:none;
  background:#fff;
  border:1px solid rgb(226,232,240);
  border-radius:.75rem;
  padding:.6rem 2.25rem .6rem .9rem; /* space untuk icon kanan */
  font-size:.925rem;
  color:#0f172a;
  outline:none;
  transition:border-color .18s ease, box-shadow .18s ease;
}
.filter-select:focus{
  border-color:#0ea5e9; box-shadow:0 0 0 4px rgba(14,165,233,.15);
}

/* Status badge */
.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}
</style>