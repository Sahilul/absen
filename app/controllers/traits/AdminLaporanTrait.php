<?php
// File: app/controllers/traits/AdminLaporanTrait.php
// v1.25.0 - Hasil pemecahan AdminController.php (domain: Riwayat jurnal, laporan/rekap, cetak, review RPP).
// Perilaku identik dengan method asli; hanya dipindahkan agar mudah dipelihara.

trait AdminLaporanTrait
{
function riwayatPerMapel()
    {
        $this->data['judul'] = 'Riwayat Jurnal & Statistik';
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;
        // Filter dari GET parameters
        $filter_guru = $_GET['guru'] ?? null;
        $filter_mapel = $_GET['mapel'] ?? null;
        $filter_kelas = $_GET['kelas'] ?? null;
        // Data untuk dropdown filter
        $this->data['daftar_guru'] = $this->model('Guru_model')->getAllGuru();
        $this->data['daftar_mapel'] = $this->model('Mapel_model')->getAllMapel();
        $this->data['daftar_kelas'] = $this->model('Kelas_model')->getKelasByTP($_SESSION['id_tp_aktif'] ?? 0);
        // Data riwayat jurnal dengan statistik untuk admin
        $this->data['jurnal_per_mapel'] = $this->getAllJurnalPerMapelAdmin($id_semester_aktif, $filter_guru, $filter_mapel, $filter_kelas);
        // Data filter yang dipilih
        $this->data['filter'] = [
            'guru' => $filter_guru,
            'mapel' => $filter_mapel,
            'kelas' => $filter_kelas
        ];
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/riwayat_per_mapel_with_stats', $this->data);
        $this->view('templates/footer', $this->data);
    }

function cetakMapelAdmin($combo_id)
    {
        // Format combo_id: "guru_id-mapel_id" 
        $parts = explode('-', $combo_id);
        if (count($parts) < 2) {
            echo "<div style='padding:20px;color:#ef4444;'>Error: Format combo_id tidak valid. Harus berupa 'guru_id-mapel_id'</div>";
            return;
        }
        $id_guru = $parts[0];
        $id_mapel = $parts[1];
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;
        // Ambil data untuk laporan
        $meta = $this->getMetaLaporanAdmin($id_guru, $id_mapel, $id_semester);
        $rekap_siswa = $this->getRekapSiswaAdmin($id_guru, $id_mapel, $id_semester);
        $rekap_pertemuan = $this->getRekapPertemuanAdmin($id_guru, $id_mapel, $id_semester);
        $this->data = [
            'meta' => $meta,
            'rekap_siswa' => $rekap_siswa,
            'rekap_pertemuan' => $rekap_pertemuan,
            'total_siswa' => count($rekap_siswa),
            'id_mapel' => $combo_id
        ];
        // Render view
        $wantPdf = isset($_GET['pdf']) && $_GET['pdf'] == 1;
        $renderView = function ($view, $data) {
            extract($data);
            ob_start();
            require __DIR__ . "/../views/$view.php";
            return ob_get_clean();
        };
        $html = $renderView('admin/cetak_mapel', $this->data);
        if ($wantPdf) {
            // Setup Dompdf
            $dompdfPath = __DIR__ . '/../core/dompdf/autoload.inc.php';
            if (!file_exists($dompdfPath)) {
                header('Content-Type: text/html; charset=utf-8');
                echo "<div style='padding:20px;font-family:Arial,sans-serif;'>Library Dompdf tidak ditemukan di core/dompdf/</div>";
                echo $html;
                return;
            }
            require_once $dompdfPath;
            try {
                $dompdf = new \Dompdf\Dompdf([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'defaultFont' => 'Arial'
                ]);
                $dompdf->loadHtml($html, 'UTF-8');
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $mapel_name = $meta['nama_mapel'] ?? 'Mapel';
                $guru_name = $meta['nama_guru'] ?? 'Guru';
                $filename = 'Laporan_' . preg_replace('/\s+/', '_', $mapel_name) . '_' . preg_replace('/\s+/', '_', $guru_name) . '_' . date('Y-m-d') . '.pdf';
                $dompdf->stream($filename, ['Attachment' => true]);
                return;
            } catch (Exception $e) {
                header('Content-Type: text/html; charset=utf-8');
                echo "<div style='padding:20px;color:#ef4444;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                echo $html;
                return;
            }
        }
        // Tampilkan halaman cetak HTML
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

function cetakRincianAbsenAdmin($combo_id)
    {
        // Format combo_id: "guru_id-mapel_id"
        $parts = explode('-', $combo_id);
        if (count($parts) < 2) {
            echo "<div style='padding:20px;color:#ef4444;'>Error: Format combo_id tidak valid. Harus berupa 'guru_id-mapel_id'</div>";
            return;
        }
        $id_guru = $parts[0];
        $id_mapel = $parts[1];
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;
        // Parameter filter
        $periode = $_GET['periode'] ?? 'semester';
        $tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
        $tanggal_akhir = $_GET['tanggal_akhir'] ?? '';
        // Ambil data
        $this->data['mapel_info'] = $this->getMapelInfoAdmin($id_semester, $id_mapel, $id_guru);
        $this->data['rincian_data'] = $this->getRincianAbsenAdmin($id_semester, $id_mapel, $id_guru, $periode, $tanggal_mulai, $tanggal_akhir);
        $this->data['filter_info'] = [
            'periode' => $periode,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_akhir' => $tanggal_akhir,
            'tanggal_cetak' => date('d F Y')
        ];
        // Render view
        $wantPdf = isset($_GET['pdf']) && $_GET['pdf'] == 1;
        $renderView = function ($view, $data) {
            extract($data);
            ob_start();
            require __DIR__ . "/../views/$view.php";
            return ob_get_clean();
        };
        $html = $renderView('admin/cetak_rincian_absen', $this->data);
        if ($wantPdf) {
            // Setup Dompdf
            $dompdfPath = __DIR__ . '/../core/dompdf/autoload.inc.php';
            if (!file_exists($dompdfPath)) {
                header('Content-Type: text/html; charset=utf-8');
                echo "<div style='padding:20px;font-family:Arial,sans-serif;'>Library Dompdf tidak ditemukan di core/dompdf/</div>";
                echo $html;
                return;
            }
            require_once $dompdfPath;
            try {
                $dompdf = new \Dompdf\Dompdf([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'defaultFont' => 'Arial'
                ]);
                $dompdf->loadHtml($html, 'UTF-8');
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                $mapel_name = $this->data['mapel_info']['nama_mapel'] ?? 'Mapel';
                $guru_name = $this->data['mapel_info']['nama_guru'] ?? 'Guru';
                $filename = 'Rincian_Absen_' . preg_replace('/\s+/', '_', $mapel_name) . '_' . preg_replace('/\s+/', '_', $guru_name) . '_' . date('Y-m-d') . '.pdf';
                $dompdf->stream($filename, ['Attachment' => true]);
                return;
            } catch (Exception $e) {
                header('Content-Type: text/html; charset=utf-8');
                echo "<div style='padding:20px;color:#ef4444;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                echo $html;
                return;
            }
        }
        // Tampilkan halaman cetak HTML
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

function getAllJurnalPerMapelAdmin($id_semester, $filter_guru = null, $filter_mapel = null, $filter_kelas = null)
    {
        $db = new Database();
        try {
            // Build WHERE clause
            $whereClause = "p.id_semester = :id_semester";
            $params = ['id_semester' => $id_semester];
            if ($filter_guru) {
                $whereClause .= " AND p.id_guru = :id_guru";
                $params['id_guru'] = $filter_guru;
            }
            if ($filter_mapel) {
                $whereClause .= " AND p.id_mapel = :id_mapel";
                $params['id_mapel'] = $filter_mapel;
            }
            if ($filter_kelas) {
                $whereClause .= " AND p.id_kelas = :id_kelas";
                $params['id_kelas'] = $filter_kelas;
            }
            // Query statistik absensi per mapel-guru kombinasi
            $sql = "SELECT 
                        p.id_penugasan,
                        g.id_guru,
                        g.nama_guru,
                        m.id_mapel,
                        m.nama_mapel,
                        k.nama_kelas,
                        COUNT(DISTINCT j.id_jurnal) as total_pertemuan,
                        COUNT(DISTINCT siswa.id_siswa) as total_siswa,
                        SUM(CASE WHEN a.status_kehadiran = 'H' THEN 1 ELSE 0 END) as total_hadir,
                        SUM(CASE WHEN a.status_kehadiran = 'I' THEN 1 ELSE 0 END) as total_izin,
                        SUM(CASE WHEN a.status_kehadiran = 'S' THEN 1 ELSE 0 END) as total_sakit,
                        SUM(CASE WHEN a.status_kehadiran = 'A' OR a.status_kehadiran IS NULL THEN 1 ELSE 0 END) as total_alpha,
                        COUNT(a.id_absensi) as total_absensi_records
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    JOIN guru g ON p.id_guru = g.id_guru
                    LEFT JOIN jurnal j ON p.id_penugasan = j.id_penugasan
                    LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal
                    LEFT JOIN siswa ON k.id_kelas = siswa.id_kelas
                    WHERE {$whereClause}
                    GROUP BY p.id_penugasan, g.id_guru, m.id_mapel, k.id_kelas
                    HAVING total_pertemuan > 0
                    ORDER BY g.nama_guru, m.nama_mapel, k.nama_kelas";
            $db->query($sql);
            foreach ($params as $key => $value) {
                $db->bind($key, $value);
            }
            $statistik_results = $db->resultSet();
            // Transform data untuk view (mirip struktur guru)
            $jurnal_per_mapel = [];
            foreach ($statistik_results as $stat) {
                $persentase_kehadiran = $stat['total_absensi_records'] > 0 ?
                    round(($stat['total_hadir'] / $stat['total_absensi_records']) * 100, 1) : 0;
                $chart_data = [
                    'hadir' => (int) $stat['total_hadir'],
                    'izin' => (int) $stat['total_izin'],
                    'sakit' => (int) $stat['total_sakit'],
                    'alpha' => (int) $stat['total_alpha']
                ];
                $jurnal_per_mapel[] = [
                    'id_mapel_untuk_link' => $stat['id_guru'] . '-' . $stat['id_mapel'], // Format combo untuk link
                    'id_guru' => $stat['id_guru'],
                    'nama_guru' => $stat['nama_guru'],
                    'id_mapel' => $stat['id_mapel'],
                    'nama_mapel' => $stat['nama_mapel'],
                    'nama_kelas' => $stat['nama_kelas'],
                    'statistik' => [
                        'total_pertemuan' => $stat['total_pertemuan'],
                        'total_siswa' => $stat['total_siswa'],
                        'total_hadir' => $stat['total_hadir'],
                        'total_izin' => $stat['total_izin'],
                        'total_sakit' => $stat['total_sakit'],
                        'total_alpha' => $stat['total_alpha'],
                        'total_absensi_records' => $stat['total_absensi_records'],
                        'persentase_kehadiran' => $persentase_kehadiran
                    ],
                    'chart_data' => $chart_data,
                    'pertemuan' => [] // Bisa diisi jika diperlukan detail pertemuan
                ];
            }
            return $jurnal_per_mapel;
        } catch (Exception $e) {
            error_log("Error in getAllJurnalPerMapelAdmin: " . $e->getMessage());
            return [];
        }
    }

function getDaftarMapelAdmin($id_semester)
    {
        $db = new Database();
        try {
            $sql = "SELECT DISTINCT 
                        CONCAT(p.id_guru, '-', m.id_mapel) as combo_id,
                        m.id_mapel, 
                        m.nama_mapel, 
                        k.nama_kelas,
                        g.nama_guru,
                        g.id_guru
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    JOIN guru g ON p.id_guru = g.id_guru
                    WHERE p.id_semester = :id_semester
                    ORDER BY g.nama_guru, m.nama_mapel, k.nama_kelas";
            $db->query($sql);
            $db->bind('id_semester', $id_semester);
            return $db->resultSet();
        } catch (Exception $e) {
            error_log("Error in getDaftarMapelAdmin: " . $e->getMessage());
            return [];
        }
    }

function getMapelInfoAdmin($id_semester, $id_mapel, $id_guru)
    {
        $db = new Database();
        try {
            // FIXED: JOIN semester dengan tp untuk mendapatkan nama_tp
            $sql = "SELECT m.nama_mapel, k.nama_kelas, g.nama_guru, tp.nama_tp, smt.semester
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    JOIN guru g ON p.id_guru = g.id_guru
                    JOIN semester smt ON p.id_semester = smt.id_semester
                    JOIN tp ON smt.id_tp = tp.id_tp
                    WHERE p.id_semester = :id_semester AND m.id_mapel = :id_mapel";
            // Jika id_guru tidak kosong, tambahkan filter guru
            if (!empty($id_guru)) {
                $sql .= " AND p.id_guru = :id_guru";
            }
            $sql .= " LIMIT 1";
            $db->query($sql);
            $db->bind('id_semester', $id_semester);
            $db->bind('id_mapel', $id_mapel);
            if (!empty($id_guru)) {
                $db->bind('id_guru', $id_guru);
            }
            return $db->single() ?: [];
        } catch (Exception $e) {
            error_log("Error in getMapelInfoAdmin: " . $e->getMessage());
            return [];
        }
    }

function getRincianAbsenAdmin($id_semester, $id_mapel, $id_guru, $periode, $tanggal_mulai, $tanggal_akhir)
    {
        $db = new Database();
        try {
            // Build WHERE clause berdasarkan periode
            $whereClause = "p.id_semester = :id_semester AND m.id_mapel = :id_mapel AND p.id_guru = :id_guru";
            $params = [
                'id_semester' => $id_semester,
                'id_mapel' => $id_mapel,
                'id_guru' => $id_guru
            ];
            // Tambahkan filter periode
            switch ($periode) {
                case 'hari_ini':
                    $whereClause .= " AND DATE(j.tanggal) = CURDATE()";
                    break;
                case 'minggu_ini':
                    $whereClause .= " AND YEARWEEK(j.tanggal, 1) = YEARWEEK(CURDATE(), 1)";
                    break;
                case 'bulan_ini':
                    $whereClause .= " AND YEAR(j.tanggal) = YEAR(CURDATE()) AND MONTH(j.tanggal) = MONTH(CURDATE())";
                    break;
                case 'custom':
                    if ($tanggal_mulai && $tanggal_akhir) {
                        $whereClause .= " AND j.tanggal BETWEEN :tanggal_mulai AND :tanggal_akhir";
                        $params['tanggal_mulai'] = $tanggal_mulai;
                        $params['tanggal_akhir'] = $tanggal_akhir;
                    }
                    break;
            }
            // Query sama seperti method guru
            $sql = "SELECT 
                        s.id_siswa,
                        s.nama_siswa,
                        s.nisn,
                        j.id_jurnal,
                        j.tanggal,
                        j.pertemuan_ke,
                        j.topik_materi,
                        COALESCE(a.status_kehadiran, 'A') as status_kehadiran,
                        a.waktu_absen,
                        a.keterangan
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    JOIN jurnal j ON p.id_penugasan = j.id_penugasan
                    JOIN siswa s ON s.id_kelas = k.id_kelas
                    LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal AND s.id_siswa = a.id_siswa
                    WHERE $whereClause
                    ORDER BY s.nama_siswa ASC, j.tanggal ASC, j.pertemuan_ke ASC";
            $db->query($sql);
            foreach ($params as $key => $value) {
                $db->bind($key, $value);
            }
            $result = $db->resultSet();
            // Structure data sama seperti method guru
            $structured_data = [];
            $pertemuan_list = [];
            foreach ($result as $row) {
                $id_siswa = $row['id_siswa'];
                $id_jurnal = $row['id_jurnal'];
                if (!isset($structured_data[$id_siswa])) {
                    $structured_data[$id_siswa] = [
                        'id_siswa' => $id_siswa,
                        'nama_siswa' => $row['nama_siswa'],
                        'nisn' => $row['nisn'],
                        'pertemuan' => [],
                        'total_hadir' => 0,
                        'total_izin' => 0,
                        'total_sakit' => 0,
                        'total_alpha' => 0
                    ];
                }
                $structured_data[$id_siswa]['pertemuan'][$id_jurnal] = [
                    'tanggal' => $row['tanggal'],
                    'pertemuan_ke' => $row['pertemuan_ke'],
                    'topik_materi' => $row['topik_materi'],
                    'status' => $row['status_kehadiran'],
                    'waktu_absen' => $row['waktu_absen'],
                    'keterangan' => $row['keterangan']
                ];
                switch ($row['status_kehadiran']) {
                    case 'H':
                        $structured_data[$id_siswa]['total_hadir']++;
                        break;
                    case 'I':
                        $structured_data[$id_siswa]['total_izin']++;
                        break;
                    case 'S':
                        $structured_data[$id_siswa]['total_sakit']++;
                        break;
                    default:
                        $structured_data[$id_siswa]['total_alpha']++;
                        break;
                }
                if (!isset($pertemuan_list[$id_jurnal])) {
                    $pertemuan_list[$id_jurnal] = [
                        'tanggal' => $row['tanggal'],
                        'pertemuan_ke' => $row['pertemuan_ke'],
                        'topik_materi' => $row['topik_materi']
                    ];
                }
            }
            uasort($pertemuan_list, function ($a, $b) {
                return strtotime($a['tanggal']) - strtotime($b['tanggal']);
            });
            return [
                'siswa_data' => array_values($structured_data),
                'pertemuan_headers' => array_values($pertemuan_list)
            ];
        } catch (Exception $e) {
            error_log("Error in getRincianAbsenAdmin: " . $e->getMessage());
            return [
                'siswa_data' => [],
                'pertemuan_headers' => []
            ];
        }
    }

function getMetaLaporanAdmin($id_guru, $id_mapel, $id_semester)
    {
        $db = new Database();
        try {
            // FIXED: JOIN semester dengan tp
            $sql = "SELECT m.nama_mapel, k.nama_kelas, g.nama_guru, tp.nama_tp, smt.semester
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    JOIN guru g ON p.id_guru = g.id_guru
                    JOIN semester smt ON p.id_semester = smt.id_semester
                    JOIN tp ON smt.id_tp = tp.id_tp
                    WHERE p.id_guru = :id_guru AND p.id_semester = :id_semester AND m.id_mapel = :id_mapel
                    LIMIT 1";
            $db->query($sql);
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);
            $db->bind('id_mapel', $id_mapel);
            $result = $db->single();
            if ($result) {
                $result['tanggal'] = date('d F Y');
                $result['tp'] = $result['nama_tp'] ?? '';
            }
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error in getMetaLaporanAdmin: " . $e->getMessage());
            return [];
        }
    }

function getRekapSiswaAdmin($id_guru, $id_mapel, $id_semester)
    {
        $db = new Database();
        try {
            $sql = "SELECT 
                        s.nama_siswa,
                        SUM(CASE WHEN a.status_kehadiran = 'H' THEN 1 ELSE 0 END) as hadir,
                        SUM(CASE WHEN a.status_kehadiran = 'I' THEN 1 ELSE 0 END) as izin,
                        SUM(CASE WHEN a.status_kehadiran = 'S' THEN 1 ELSE 0 END) as sakit,
                        SUM(CASE WHEN a.status_kehadiran = 'A' OR a.status_kehadiran IS NULL THEN 1 ELSE 0 END) as alpha,
                        COUNT(j.id_jurnal) as total
                    FROM penugasan p
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    JOIN siswa s ON k.id_kelas = s.id_kelas
                    LEFT JOIN jurnal j ON p.id_penugasan = j.id_penugasan
                    LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal AND s.id_siswa = a.id_siswa
                    WHERE p.id_guru = :id_guru AND p.id_semester = :id_semester AND p.id_mapel = :id_mapel
                    GROUP BY s.id_siswa, s.nama_siswa
                    ORDER BY s.nama_siswa";
            $db->query($sql);
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);
            $db->bind('id_mapel', $id_mapel);
            return $db->resultSet();
        } catch (Exception $e) {
            error_log("Error in getRekapSiswaAdmin: " . $e->getMessage());
            return [];
        }
    }

function getRekapPertemuanAdmin($id_guru, $id_mapel, $id_semester)
    {
        $db = new Database();
        try {
            $sql = "SELECT 
                        j.pertemuan_ke,
                        j.tanggal,
                        j.topik_materi,
                        SUM(CASE WHEN a.status_kehadiran = 'H' THEN 1 ELSE 0 END) as hadir,
                        SUM(CASE WHEN a.status_kehadiran = 'I' THEN 1 ELSE 0 END) as izin,
                        SUM(CASE WHEN a.status_kehadiran = 'S' THEN 1 ELSE 0 END) as sakit,
                        SUM(CASE WHEN a.status_kehadiran = 'A' OR a.status_kehadiran IS NULL THEN 1 ELSE 0 END) as alpha
                    FROM penugasan p
                    JOIN jurnal j ON p.id_penugasan = j.id_penugasan
                    LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal
                    WHERE p.id_guru = :id_guru AND p.id_semester = :id_semester AND p.id_mapel = :id_mapel
                    GROUP BY j.id_jurnal, j.pertemuan_ke, j.tanggal
                    ORDER BY j.tanggal, j.pertemuan_ke";
            $db->query($sql);
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);
            $db->bind('id_mapel', $id_mapel);
            return $db->resultSet();
        } catch (Exception $e) {
            error_log("Error in getRekapPertemuanAdmin: " . $e->getMessage());
            return [];
        }
    }

function getDaftarMapelGuru($id_guru, $id_semester)
    {
        $db = new Database();
        try {
            $sql = "SELECT DISTINCT m.id_mapel, m.nama_mapel, k.nama_kelas
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel  
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    WHERE p.id_guru = :id_guru AND p.id_semester = :id_semester
                    ORDER BY m.nama_mapel, k.nama_kelas";
            $db->query($sql);
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);
            return $db->resultSet();
        } catch (Exception $e) {
            error_log("Error in getDaftarMapelGuru: " . $e->getMessage());
            return [];
        }
    }

function getMapelInfo($id_guru, $id_semester, $id_mapel)
    {
        $db = new Database();
        try {
            // FIXED: Gunakan nama tabel yang benar
            $sql = "SELECT m.nama_mapel, k.nama_kelas, g.nama_guru, tp.nama_tp, smt.semester
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas  
                    JOIN guru g ON p.id_guru = g.id_guru
                    JOIN semester smt ON p.id_semester = smt.id_semester
                    JOIN tp ON smt.id_tp = tp.id_tp
                    WHERE p.id_guru = :id_guru AND p.id_semester = :id_semester AND m.id_mapel = :id_mapel
                    LIMIT 1";
            $db->query($sql);
            $db->bind('id_guru', $id_guru);
            $db->bind('id_semester', $id_semester);
            $db->bind('id_mapel', $id_mapel);
            return $db->single() ?: [];
        } catch (Exception $e) {
            error_log("Error in getMapelInfo: " . $e->getMessage());
            return [];
        }
    }

function getRincianAbsenPerPertemuan($id_guru, $id_semester, $id_mapel, $periode, $tanggal_mulai, $tanggal_akhir)
    {
        $db = new Database();
        try {
            // Build WHERE clause berdasarkan periode
            $whereClause = "p.id_guru = :id_guru AND p.id_semester = :id_semester AND m.id_mapel = :id_mapel";
            $params = [
                'id_guru' => $id_guru,
                'id_semester' => $id_semester,
                'id_mapel' => $id_mapel
            ];
            switch ($periode) {
                case 'hari_ini':
                    $whereClause .= " AND DATE(j.tanggal) = CURDATE()";
                    break;
                case 'minggu_ini':
                    $whereClause .= " AND YEARWEEK(j.tanggal, 1) = YEARWEEK(CURDATE(), 1)";
                    break;
                case 'bulan_ini':
                    $whereClause .= " AND YEAR(j.tanggal) = YEAR(CURDATE()) AND MONTH(j.tanggal) = MONTH(CURDATE())";
                    break;
                case 'custom':
                    if ($tanggal_mulai && $tanggal_akhir) {
                        $whereClause .= " AND j.tanggal BETWEEN :tanggal_mulai AND :tanggal_akhir";
                        $params['tanggal_mulai'] = $tanggal_mulai;
                        $params['tanggal_akhir'] = $tanggal_akhir;
                    }
                    break;
                default: // semester - tidak ada filter tambahan
                    break;
            }
            // Query utama - ambil data absen per siswa per pertemuan
            $sql = "SELECT 
                        s.id_siswa,
                        s.nama_siswa,
                        s.nisn,
                        j.id_jurnal,
                        j.tanggal,
                        j.pertemuan_ke,
                        j.topik_materi,
                        COALESCE(a.status_kehadiran, 'A') as status_kehadiran,
                        a.waktu_absen,
                        a.keterangan
                    FROM penugasan p
                    JOIN mapel m ON p.id_mapel = m.id_mapel
                    JOIN kelas k ON p.id_kelas = k.id_kelas
                    JOIN jurnal j ON p.id_penugasan = j.id_penugasan
                    JOIN siswa s ON s.id_kelas = k.id_kelas
                    LEFT JOIN absensi a ON j.id_jurnal = a.id_jurnal AND s.id_siswa = a.id_siswa
                    WHERE $whereClause
                    ORDER BY s.nama_siswa ASC, j.tanggal ASC, j.pertemuan_ke ASC";
            $db->query($sql);
            foreach ($params as $key => $value) {
                $db->bind($key, $value);
            }
            $result = $db->resultSet();
            // Restructure data: group by siswa, dengan detail per pertemuan
            $structured_data = [];
            $pertemuan_list = [];
            foreach ($result as $row) {
                $id_siswa = $row['id_siswa'];
                $id_jurnal = $row['id_jurnal'];
                // Simpan info siswa
                if (!isset($structured_data[$id_siswa])) {
                    $structured_data[$id_siswa] = [
                        'id_siswa' => $id_siswa,
                        'nama_siswa' => $row['nama_siswa'],
                        'nisn' => $row['nisn'],
                        'pertemuan' => [],
                        'total_hadir' => 0,
                        'total_izin' => 0,
                        'total_sakit' => 0,
                        'total_alpha' => 0
                    ];
                }
                // Simpan detail pertemuan
                $structured_data[$id_siswa]['pertemuan'][$id_jurnal] = [
                    'tanggal' => $row['tanggal'],
                    'pertemuan_ke' => $row['pertemuan_ke'],
                    'topik_materi' => $row['topik_materi'],
                    'status' => $row['status_kehadiran'],
                    'waktu_absen' => $row['waktu_absen'],
                    'keterangan' => $row['keterangan']
                ];
                // Hitung total per status
                switch ($row['status_kehadiran']) {
                    case 'H':
                        $structured_data[$id_siswa]['total_hadir']++;
                        break;
                    case 'I':
                        $structured_data[$id_siswa]['total_izin']++;
                        break;
                    case 'S':
                        $structured_data[$id_siswa]['total_sakit']++;
                        break;
                    default:
                        $structured_data[$id_siswa]['total_alpha']++;
                        break;
                }
                // Simpan daftar pertemuan untuk header tabel
                if (!isset($pertemuan_list[$id_jurnal])) {
                    $pertemuan_list[$id_jurnal] = [
                        'tanggal' => $row['tanggal'],
                        'pertemuan_ke' => $row['pertemuan_ke'],
                        'topik_materi' => $row['topik_materi']
                    ];
                }
            }
            // Sort pertemuan by tanggal
            uasort($pertemuan_list, function ($a, $b) {
                return strtotime($a['tanggal']) - strtotime($b['tanggal']);
            });
            return [
                'siswa_data' => array_values($structured_data),
                'pertemuan_headers' => array_values($pertemuan_list)
            ];
        } catch (Exception $e) {
            error_log("Error in getRincianAbsenPerPertemuan: " . $e->getMessage());
            return [
                'siswa_data' => [],
                'pertemuan_headers' => []
            ];
        }
    }

function rincianAbsen($id_mapel = null)
    {
        $this->data['judul'] = 'Rincian Absen per Pertemuan';
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;
        // Parameter filter dari GET
        $periode = $_GET['periode'] ?? 'semester';
        $tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
        $tanggal_akhir = $_GET['tanggal_akhir'] ?? '';
        $id_mapel_filter = $_GET['id_mapel'] ?? $id_mapel;
        $this->data['filter'] = [
            'periode' => $periode,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_akhir' => $tanggal_akhir,
            'id_mapel' => $id_mapel_filter
        ];
        // Ambil daftar mapel yang diajar guru
        $this->data['daftar_mapel'] = $this->getDaftarMapelGuru($id_guru, $id_semester);
        // Jika ada mapel yang dipilih, ambil rincian absen
        $this->data['rincian_data'] = [];
        $this->data['mapel_info'] = null;
        if ($id_mapel_filter) {
            $this->data['mapel_info'] = $this->getMapelInfo($id_guru, $id_semester, $id_mapel_filter);
            $this->data['rincian_data'] = $this->getRincianAbsenPerPertemuan($id_guru, $id_semester, $id_mapel_filter, $periode, $tanggal_mulai, $tanggal_akhir);
        }
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_guru', $this->data);
        $this->view('guru/rincian_absen_filter', $this->data);
        $this->view('templates/footer', $this->data);
    }

function cetakRincianAbsen($id_mapel)
    {
        $id_guru = $_SESSION['id_ref'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;
        // Parameter filter
        $periode = $_GET['periode'] ?? 'semester';
        $tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
        $tanggal_akhir = $_GET['tanggal_akhir'] ?? '';
        // Ambil data
        $this->data['mapel_info'] = $this->getMapelInfo($id_guru, $id_semester, $id_mapel);
        $this->data['rincian_data'] = $this->getRincianAbsenPerPertemuan($id_guru, $id_semester, $id_mapel, $periode, $tanggal_mulai, $tanggal_akhir);
        $this->data['filter_info'] = [
            'periode' => $periode,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_akhir' => $tanggal_akhir,
            'tanggal_cetak' => date('d F Y')
        ];
        // Render view
        $wantPdf = isset($_GET['pdf']) && $_GET['pdf'] == 1;
        $renderView = function ($view, $data) {
            extract($data);
            ob_start();
            require __DIR__ . "/../views/$view.php";
            return ob_get_clean();
        };
        $html = $renderView('guru/cetak_rincian_absen', $this->data);
        if ($wantPdf) {
            // Setup Dompdf (sama seperti method cetakMapel)
            $dompdfPath = __DIR__ . '/../core/dompdf/autoload.inc.php';
            if (!file_exists($dompdfPath)) {
                header('Content-Type: text/html; charset=utf-8');
                echo "<div style='padding:20px;font-family:Arial,sans-serif;'>Library Dompdf tidak ditemukan di core/dompdf/</div>";
                echo $html;
                return;
            }
            require_once $dompdfPath;
            try {
                $dompdf = new \Dompdf\Dompdf([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'defaultFont' => 'Arial'
                ]);
                $dompdf->loadHtml($html, 'UTF-8');
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                $mapel_name = $this->data['mapel_info']['nama_mapel'] ?? 'Mapel';
                $filename = 'Rincian_Absen_' . preg_replace('/\s+/', '_', $mapel_name) . '_' . date('Y-m-d') . '.pdf';
                $dompdf->stream($filename, ['Attachment' => true]);
                return;
            } catch (Exception $e) {
                header('Content-Type: text/html; charset=utf-8');
                echo "<div style='padding:20px;color:#ef4444;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                echo $html;
                return;
            }
        }
        // Tampilkan halaman cetak HTML
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

function detailRiwayatAdmin($id_guru, $id_mapel)
    {
        $this->data['judul'] = 'Detail Riwayat Jurnal Admin';
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? null;
        $this->data['detail_jurnal'] = [];
        $this->data['detail_absensi_siswa'] = [];
        $this->data['nama_mapel'] = 'Mapel Tidak Ditemukan';
        $this->data['nama_guru'] = 'Guru Tidak Ditemukan';
        if ($id_guru && $id_semester_aktif && $id_mapel) {
            // Ambil detail jurnal menggunakan method yang sudah ada di model
            $this->data['detail_jurnal'] =
                $this->model('Jurnal_model')->getDetailRiwayatByMapel($id_guru, $id_semester_aktif, $id_mapel);
            // Ambil detail absensi per siswa
            $this->data['detail_absensi_siswa'] =
                $this->model('Jurnal_model')->getDetailAbsensiPerMapel($id_guru, $id_semester_aktif, $id_mapel);
            // Set nama guru dan mapel dari data yang diambil
            if (!empty($this->data['detail_jurnal'])) {
                $this->data['nama_mapel'] = $this->data['detail_jurnal'][0]['nama_mapel'] ?? 'Mapel';
                $this->data['nama_guru'] = $this->data['detail_jurnal'][0]['nama_guru'] ?? 'Guru';
            } else {
                // Fallback: ambil nama dari master data
                $guruInfo = $this->model('Guru_model')->getGuruById($id_guru);
                $mapelInfo = $this->model('Mapel_model')->getMapelById($id_mapel);
                if (!empty($guruInfo['nama_guru'])) {
                    $this->data['nama_guru'] = $guruInfo['nama_guru'];
                }
                if (!empty($mapelInfo['nama_mapel'])) {
                    $this->data['nama_mapel'] = $mapelInfo['nama_mapel'];
                }
            }
        }
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/detail_riwayat_admin', $this->data);
        $this->view('templates/footer', $this->data);
    }

function _getGuruListSafe()
    {
        $m = $this->model('Guru_model');
        if (method_exists($m, 'getAll'))
            return $m->getAll();
        if (method_exists($m, 'getAllGuru'))
            return $m->getAllGuru();
        if (method_exists($m, 'getGuru'))
            return $m->getGuru();
        if (method_exists($m, 'getAllData'))
            return $m->getAllData();
        if (method_exists($m, 'all'))
            return $m->all();
        // fallback super aman (silakan sesuaikan nama tabel kolom)
        if (property_exists($m, 'db')) {
            $m->db->query("SELECT id_guru, nama_guru FROM guru ORDER BY nama_guru ASC");
            return $m->db->resultSet();
        }
        return [];
    }

function listRPPReview()
    {
        $rppModel = $this->model('RPP_model');

        // Get filter from query string
        $status = $_GET['status'] ?? null;
        $id_semester = $_SESSION['id_semester_aktif'] ?? null;
        $id_tp = $_SESSION['id_tp_aktif'] ?? null;

        // Get all RPP with filter
        $list_rpp = $rppModel->getAllRPP($id_tp, $id_semester, $status);

        // Count by status for tabs
        $count_submitted = count(array_filter($list_rpp, fn($r) => $r['status'] === 'submitted'));
        $count_approved = count(array_filter($list_rpp, fn($r) => $r['status'] === 'approved'));
        $count_revision = count(array_filter($list_rpp, fn($r) => $r['status'] === 'revision'));
        $count_draft = count(array_filter($list_rpp, fn($r) => $r['status'] === 'draft'));

        $this->data['judul'] = 'Review RPP';
        $this->data['list_rpp'] = $list_rpp;
        $this->data['current_status'] = $status;
        $this->data['count_submitted'] = $count_submitted;
        $this->data['count_approved'] = $count_approved;
        $this->data['count_revision'] = $count_revision;
        $this->data['count_draft'] = $count_draft;

        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/list_rpp_review', $this->data);
        $this->view('templates/footer', $this->data);
    }

function detailRPPReview($id_rpp = null)
    {
        if (!$id_rpp) {
            Flasher::setFlash('ID RPP tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/listRPPReview');
            exit;
        }
        $rppModel = $this->model('RPP_model');
        $rpp = $rppModel->getRPPById($id_rpp);
        if (!$rpp) {
            Flasher::setFlash('RPP tidak ditemukan!', 'danger');
            header('Location: ' . BASEURL . '/admin/listRPPReview');
            exit;
        }

        // Load template sections and fields for dynamic display
        $templateModel = $this->model('RPPTemplate_model');
        $sectionsList = $templateModel->getAllSections(true);
        $sections = [];

        foreach ($sectionsList as $section) {
            $section['fields'] = $templateModel->getFieldsBySection($section['id_section'], true);
            $sections[] = $section;
        }

        $this->data['judul'] = 'Detail RPP';
        $this->data['rpp'] = $rpp;
        $this->data['sections'] = $sections;
        $this->view('templates/header', $this->data);
        $this->view('templates/sidebar_admin', $this->data);
        $this->view('admin/detail_rpp_review', $this->data);
        $this->view('templates/footer', $this->data);
    }

function approveRPP($id_rpp = null)
    {
        if (!$id_rpp) {
            Flasher::setFlash('ID RPP tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/listRPPReview');
            exit;
        }
        $rppModel = $this->model('RPP_model');
        $rppModel->approveRPP($id_rpp, $_SESSION['user_id']);
        Flasher::setFlash('RPP berhasil diapprove!', 'success');
        header('Location: ' . BASEURL . '/admin/listRPPReview');
        exit;
    }

function revisionRPP($id_rpp = null)
    {
        if (!$id_rpp) {
            Flasher::setFlash('ID RPP tidak valid!', 'danger');
            header('Location: ' . BASEURL . '/admin/listRPPReview');
            exit;
        }
        $catatan = $_POST['catatan'] ?? '';
        $rppModel = $this->model('RPP_model');
        $rppModel->revisionRPP($id_rpp, $catatan, $_SESSION['user_id']);
        Flasher::setFlash('RPP dikembalikan untuk revisi.', 'warning');
        header('Location: ' . BASEURL . '/admin/listRPPReview');
        exit;
    }

function _getMapelListSafe()
    {
        $m = $this->model('Mapel_model');
        if (method_exists($m, 'getAll'))
            return $m->getAll();
        if (method_exists($m, 'getAllMapel'))
            return $m->getAllMapel();
        if (method_exists($m, 'getMapel'))
            return $m->getMapel();
        if (method_exists($m, 'getAllData'))
            return $m->getAllData();
        if (method_exists($m, 'all'))
            return $m->all();
        // fallback super aman (silakan sesuaikan)
        if (property_exists($m, 'db')) {
            $m->db->query("SELECT id_mapel, nama_mapel FROM mapel ORDER BY nama_mapel ASC");
            return $m->db->resultSet();
        }
        return [];
    }

function cetakLaporanRekap()
    {
        // Ambil parameter dari GET
        $id_kelas = $_GET['id_kelas'] ?? null;
        $id_mapel = $_GET['id_mapel'] ?? null;
        $periode = $_GET['periode'] ?? 'semester';
        $tanggal_mulai = $_GET['tanggal_mulai'] ?? null;
        $tanggal_akhir = $_GET['tanggal_akhir'] ?? null;
        $mode = $_GET['mode'] ?? 'rekap';
        $isPdfMode = isset($_GET['pdf']) && $_GET['pdf'] == '1';
        $id_semester_aktif = $_SESSION['id_semester_aktif'] ?? 0;
        // Validasi input minimal
        if (empty($id_kelas)) {
            echo "<div style='padding:20px;color:#ef4444;'>Error: Kelas harus dipilih</div>";
            return;
        }
        // Ambil info kelas
        $kelasModel = $this->model('Kelas_model');
        $this->data['kelas_info'] = $kelasModel->getKelasById($id_kelas);
        // Ambil info mapel jika dipilih
        $this->data['mapel_info'] = null;
        $this->data['guru_info'] = null;
        if (!empty($id_mapel)) {
            $mapelModel = $this->model('Mapel_model');
            $this->data['mapel_info'] = $mapelModel->getMapelById($id_mapel);
            // Ambil guru yang mengajar mapel di kelas ini
            $penugasanModel = $this->model('Penugasan_model');
            $guru_pengampu = $penugasanModel->getGuruByMapelKelas($id_mapel, $id_kelas, $id_semester_aktif);
            if (!empty($guru_pengampu)) {
                $this->data['guru_info'] = $guru_pengampu;
            }
        }
        // Ambil info semester dan TP
        $semesterModel = $this->model('TahunPelajaran_model');
        $this->data['semester_info'] = $semesterModel->getSemesterById($id_semester_aktif);
        $this->data['tp_info'] = null;
        if (isset($this->data['semester_info']['id_tp'])) {
            $this->data['tp_info'] = $semesterModel->getTahunPelajaranById($this->data['semester_info']['id_tp']);
        }
        // Setup filter info untuk template
        $this->data['filter_info'] = [
            'periode' => $periode,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_akhir' => $tanggal_akhir,
            'tanggal_cetak' => date('d F Y')
        ];
        // Ambil data rekap absensi
        $filter = [
            'id_kelas' => $id_kelas,
            'id_mapel' => $id_mapel,
            'periode' => $periode,
            'tgl_mulai' => $tanggal_mulai,
            'tgl_selesai' => $tanggal_akhir,
            'id_semester' => $id_semester_aktif
        ];
        $laporanModel = $this->model('Laporan_model');
        $this->data['rekap_absensi'] = $laporanModel->getRekapAbsensiPerKelas($filter);
        // Jika mode rincian dan ada mapel spesifik, ambil data rincian
        if ($mode === 'rincian' && !empty($id_mapel) && !empty($this->data['guru_info']['id_guru'])) {
            $this->data['rincian_data'] = $this->getRincianAbsenAdmin(
                $id_semester_aktif,
                $id_mapel,
                $this->data['guru_info']['id_guru'],
                $periode,
                $tanggal_mulai,
                $tanggal_akhir
            );
        }
        // Render view dengan template cetak
        $renderView = function ($view, $data) {
            extract($data);
            ob_start();
            require __DIR__ . "/../views/$view.php";
            return ob_get_clean();
        };
        // Gunakan template cetak admin
        $html = $renderView('admin/cetak_laporan_rekap', $this->data);
        if ($isPdfMode) {
            // Setup Dompdf untuk PDF
            $dompdfPath = __DIR__ . '/../core/dompdf/autoload.inc.php';
            if (!file_exists($dompdfPath)) {
                header('Content-Type: text/html; charset=utf-8');
                echo "<div style='padding:20px;font-family:Arial,sans-serif;'>Library Dompdf tidak ditemukan di core/dompdf/</div>";
                echo $html;
                return;
            }
            require_once $dompdfPath;
            try {
                $dompdf = new \Dompdf\Dompdf([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'defaultFont' => 'Arial'
                ]);
                $dompdf->loadHtml($html, 'UTF-8');
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $kelas_name = $this->data['kelas_info']['nama_kelas'] ?? 'Kelas';
                $mapel_name = $this->data['mapel_info']['nama_mapel'] ?? 'Semua_Mapel';
                $filename = 'Laporan_Kehadiran_' . preg_replace('/\s+/', '_', $kelas_name) . '_' . preg_replace('/\s+/', '_', $mapel_name) . '_' . date('Y-m-d') . '.pdf';
                $dompdf->stream($filename, ['Attachment' => true]);
                return;
            } catch (Exception $e) {
                header('Content-Type: text/html; charset=utf-8');
                echo "<div style='padding:20px;color:#ef4444;'>Error PDF: " . htmlspecialchars($e->getMessage()) . "</div>";
                echo $html;
                return;
            }
        }
        // Tampilkan halaman cetak HTML
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }
}
