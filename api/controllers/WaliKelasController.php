<?php
/**
 * API Wali Kelas Controller
 * File: api/controllers/WaliKelasController.php
 */

namespace Api;

class WaliKelasController
{
    private $db;
    private $idGuru;
    private $idKelas;

    public function __construct()
    {
        $this->db = new \Database();
    }

    public function handleRequest($method, $action, $param = null)
    {
        // Require walikelas or guru role
        $payload = \Auth::requireRole(['walikelas', 'guru', 'admin']);
        $this->idGuru = $payload['id_ref'];

        // Get wali kelas assignment
        $this->db->query("SELECT wk.*, k.nama_kelas 
                          FROM wali_kelas wk 
                          JOIN kelas k ON wk.id_kelas = k.id 
                          WHERE wk.id_guru = :id_guru 
                          AND wk.id_tp = (SELECT id FROM tahun_pelajaran WHERE is_active = 1 LIMIT 1)
                          LIMIT 1");
        $this->db->bind(':id_guru', $this->idGuru);
        $wali = $this->db->single();

        if (!$wali && $payload['role'] !== 'admin') {
            \Response::error('Anda tidak terdaftar sebagai wali kelas', 403);
        }

        $this->idKelas = $wali['id_kelas'] ?? null;
        $this->namaKelas = $wali['nama_kelas'] ?? '';

        switch ($action) {
            case 'dashboard':
                if ($method === 'GET') {
                    $this->dashboard();
                }
                break;

            case 'siswa':
                if ($method === 'GET') {
                    $this->getDaftarSiswa($param);
                }
                break;

            case 'absensi':
                if ($method === 'GET') {
                    $this->getMonitoringAbsensi($param);
                }
                break;

            case 'nilai':
                if ($method === 'GET') {
                    $this->getMonitoringNilai();
                }
                break;

            case 'pembayaran':
                if ($method === 'GET') {
                    $this->getPembayaran();
                }
                break;

            default:
                \Response::notFound('Action not found');
        }
    }

    /**
     * GET /api/waliKelas/dashboard
     */
    private function dashboard()
    {
        // Get guru info
        $this->db->query("SELECT * FROM guru WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $this->idGuru);
        $guru = $this->db->single();

        // Count siswa in class
        $this->db->query("SELECT COUNT(*) as total FROM siswa WHERE id_kelas = :id_kelas AND is_active = 1");
        $this->db->bind(':id_kelas', $this->idKelas);
        $totalSiswa = $this->db->single()['total'] ?? 0;

        // Count by gender
        $this->db->query("SELECT 
                            SUM(CASE WHEN jenis_kelamin = 'L' THEN 1 ELSE 0 END) as laki,
                            SUM(CASE WHEN jenis_kelamin = 'P' THEN 1 ELSE 0 END) as perempuan
                          FROM siswa WHERE id_kelas = :id_kelas AND is_active = 1");
        $this->db->bind(':id_kelas', $this->idKelas);
        $genderCount = $this->db->single();

        // Get absensi today summary
        $this->db->query("SELECT 
                            SUM(CASE WHEN a.status = 'H' THEN 1 ELSE 0 END) as hadir,
                            SUM(CASE WHEN a.status = 'I' THEN 1 ELSE 0 END) as izin,
                            SUM(CASE WHEN a.status = 'S' THEN 1 ELSE 0 END) as sakit,
                            SUM(CASE WHEN a.status = 'A' THEN 1 ELSE 0 END) as alpa
                          FROM absensi a
                          JOIN jurnal j ON a.id_jurnal = j.id
                          JOIN siswa s ON a.id_siswa = s.id
                          WHERE s.id_kelas = :id_kelas AND DATE(j.tanggal) = CURDATE()");
        $this->db->bind(':id_kelas', $this->idKelas);
        $absensiToday = $this->db->single();

        // Get siswa with most alpha this month
        $this->db->query("SELECT s.nama, COUNT(*) as total_alpha
                          FROM absensi a
                          JOIN jurnal j ON a.id_jurnal = j.id
                          JOIN siswa s ON a.id_siswa = s.id
                          WHERE s.id_kelas = :id_kelas 
                          AND a.status = 'A'
                          AND MONTH(j.tanggal) = MONTH(CURDATE())
                          AND YEAR(j.tanggal) = YEAR(CURDATE())
                          GROUP BY a.id_siswa
                          ORDER BY total_alpha DESC
                          LIMIT 5");
        $this->db->bind(':id_kelas', $this->idKelas);
        $topAlpha = $this->db->resultSet();

        \Response::success([
            'guru' => [
                'nama' => $guru['nama'] ?? '',
                'nip' => $guru['nip'] ?? ''
            ],
            'kelas' => $this->namaKelas,
            'siswa' => [
                'total' => (int) $totalSiswa,
                'laki' => (int) ($genderCount['laki'] ?? 0),
                'perempuan' => (int) ($genderCount['perempuan'] ?? 0)
            ],
            'absensi_hari_ini' => [
                'hadir' => (int) ($absensiToday['hadir'] ?? 0),
                'izin' => (int) ($absensiToday['izin'] ?? 0),
                'sakit' => (int) ($absensiToday['sakit'] ?? 0),
                'alpa' => (int) ($absensiToday['alpa'] ?? 0)
            ],
            'siswa_sering_alpha' => $topAlpha
        ]);
    }

    /**
     * GET /api/waliKelas/siswa
     * GET /api/waliKelas/siswa/{id}
     */
    private function getDaftarSiswa($id = null)
    {
        if ($id) {
            // Get single siswa
            $this->db->query("SELECT * FROM siswa WHERE id = :id AND id_kelas = :id_kelas");
            $this->db->bind(':id', $id);
            $this->db->bind(':id_kelas', $this->idKelas);
            $siswa = $this->db->single();

            if (!$siswa) {
                \Response::notFound('Siswa tidak ditemukan');
            }

            unset($siswa['password']);
            \Response::success($siswa);
        } else {
            // Get all siswa in class
            $this->db->query("SELECT id, nama, nisn, jenis_kelamin, foto 
                              FROM siswa 
                              WHERE id_kelas = :id_kelas AND is_active = 1
                              ORDER BY nama");
            $this->db->bind(':id_kelas', $this->idKelas);
            $siswa = $this->db->resultSet();

            \Response::success([
                'kelas' => $this->namaKelas,
                'total' => count($siswa),
                'data' => $siswa
            ]);
        }
    }

    /**
     * GET /api/waliKelas/absensi
     * GET /api/waliKelas/absensi/{bulan} - format: 2026-01
     */
    private function getMonitoringAbsensi($bulan = null)
    {
        $bulan = $bulan ?? date('Y-m');
        list($tahun, $bln) = explode('-', $bulan);

        // Get all siswa with absensi summary
        $this->db->query("SELECT s.id, s.nama, s.nisn,
                            SUM(CASE WHEN a.status = 'H' THEN 1 ELSE 0 END) as hadir,
                            SUM(CASE WHEN a.status = 'I' THEN 1 ELSE 0 END) as izin,
                            SUM(CASE WHEN a.status = 'S' THEN 1 ELSE 0 END) as sakit,
                            SUM(CASE WHEN a.status = 'A' THEN 1 ELSE 0 END) as alpa,
                            COUNT(a.id) as total
                          FROM siswa s
                          LEFT JOIN absensi a ON s.id = a.id_siswa
                          LEFT JOIN jurnal j ON a.id_jurnal = j.id 
                              AND MONTH(j.tanggal) = :bulan 
                              AND YEAR(j.tanggal) = :tahun
                          WHERE s.id_kelas = :id_kelas AND s.is_active = 1
                          GROUP BY s.id
                          ORDER BY s.nama");
        $this->db->bind(':id_kelas', $this->idKelas);
        $this->db->bind(':bulan', $bln);
        $this->db->bind(':tahun', $tahun);
        $data = $this->db->resultSet();

        // Total summary
        $summary = [
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0
        ];

        foreach ($data as $row) {
            $summary['hadir'] += (int) $row['hadir'];
            $summary['izin'] += (int) $row['izin'];
            $summary['sakit'] += (int) $row['sakit'];
            $summary['alpa'] += (int) $row['alpa'];
        }

        \Response::success([
            'kelas' => $this->namaKelas,
            'bulan' => $bulan,
            'summary' => $summary,
            'data' => $data
        ]);
    }

    /**
     * GET /api/waliKelas/nilai
     */
    private function getMonitoringNilai()
    {
        // Get active semester
        $this->db->query("SELECT id FROM semester WHERE is_active = 1 LIMIT 1");
        $idSemester = $this->db->single()['id'] ?? 0;

        // Get nilai per mapel
        $this->db->query("SELECT m.nama_mapel, 
                            AVG(n.nilai) as rata_rata,
                            MIN(n.nilai) as nilai_min,
                            MAX(n.nilai) as nilai_max
                          FROM nilai n
                          JOIN mapel m ON n.id_mapel = m.id
                          JOIN siswa s ON n.id_siswa = s.id
                          WHERE s.id_kelas = :id_kelas AND n.id_semester = :id_semester
                          GROUP BY n.id_mapel
                          ORDER BY m.nama_mapel");
        $this->db->bind(':id_kelas', $this->idKelas);
        $this->db->bind(':id_semester', $idSemester);
        $nilaiMapel = $this->db->resultSet();

        \Response::success([
            'kelas' => $this->namaKelas,
            'data' => $nilaiMapel
        ]);
    }

    /**
     * GET /api/waliKelas/pembayaran
     */
    private function getPembayaran()
    {
        // Get tagihan summary per siswa
        $this->db->query("SELECT s.id, s.nama, s.nisn,
                            COALESCE(SUM(t.nominal), 0) as total_tagihan,
                            COALESCE(SUM(p.nominal), 0) as total_bayar,
                            COALESCE(SUM(t.nominal), 0) - COALESCE(SUM(p.nominal), 0) as sisa
                          FROM siswa s
                          LEFT JOIN tagihan t ON s.id = t.id_siswa
                          LEFT JOIN pembayaran p ON s.id = p.id_siswa
                          WHERE s.id_kelas = :id_kelas AND s.is_active = 1
                          GROUP BY s.id
                          ORDER BY s.nama");
        $this->db->bind(':id_kelas', $this->idKelas);
        $data = $this->db->resultSet();

        // Summary
        $totalTagihan = 0;
        $totalBayar = 0;
        foreach ($data as $row) {
            $totalTagihan += (int) $row['total_tagihan'];
            $totalBayar += (int) $row['total_bayar'];
        }

        \Response::success([
            'kelas' => $this->namaKelas,
            'summary' => [
                'total_tagihan' => $totalTagihan,
                'total_bayar' => $totalBayar,
                'sisa' => $totalTagihan - $totalBayar
            ],
            'data' => $data
        ]);
    }
}
