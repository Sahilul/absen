<?php
/**
 * API Siswa Controller
 * File: api/controllers/SiswaController.php
 */

namespace Api;

class SiswaController
{
    private $db;
    private $idSiswa;

    public function __construct()
    {
        $this->db = new \Database();
    }

    public function handleRequest($method, $action, $param = null)
    {
        // Require siswa role
        $payload = \Auth::requireRole(['siswa']);
        $this->idSiswa = $payload['id_ref'];

        switch ($action) {
            case 'dashboard':
                if ($method === 'GET') {
                    $this->dashboard();
                }
                break;

            case 'absensi':
                if ($method === 'GET') {
                    $this->getAbsensi($param);
                }
                break;

            case 'rekap':
                if ($method === 'GET') {
                    $this->getRekapAbsensi();
                }
                break;

            case 'pembayaran':
                if ($method === 'GET') {
                    $this->getPembayaran();
                }
                break;

            case 'profil':
                if ($method === 'GET') {
                    $this->getProfil();
                } elseif ($method === 'PUT') {
                    $this->updateProfil();
                }
                break;

            default:
                \Response::notFound('Action not found');
        }
    }

    /**
     * GET /api/siswa/dashboard
     */
    private function dashboard()
    {
        // Get siswa info
        $this->db->query("SELECT s.*, k.nama_kelas 
                          FROM siswa s
                          LEFT JOIN kelas k ON s.id_kelas = k.id
                          WHERE s.id = :id");
        $this->db->bind(':id', $this->idSiswa);
        $siswa = $this->db->single();

        if (!$siswa) {
            \Response::notFound('Data siswa tidak ditemukan');
        }

        // Get absensi summary this month
        $this->db->query("SELECT 
                            SUM(CASE WHEN status = 'H' THEN 1 ELSE 0 END) as hadir,
                            SUM(CASE WHEN status = 'I' THEN 1 ELSE 0 END) as izin,
                            SUM(CASE WHEN status = 'S' THEN 1 ELSE 0 END) as sakit,
                            SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as alpa
                          FROM absensi a
                          JOIN jurnal j ON a.id_jurnal = j.id
                          WHERE a.id_siswa = :id_siswa
                          AND MONTH(j.tanggal) = MONTH(CURDATE())
                          AND YEAR(j.tanggal) = YEAR(CURDATE())");
        $this->db->bind(':id_siswa', $this->idSiswa);
        $absensiMonth = $this->db->single();

        // Get today's schedule (absensi)
        $this->db->query("SELECT j.*, m.nama_mapel, g.nama as nama_guru,
                          COALESCE(a.status, '-') as status_absensi
                          FROM jurnal j
                          LEFT JOIN mapel m ON j.id_mapel = m.id
                          LEFT JOIN guru g ON j.id_guru = g.id
                          LEFT JOIN absensi a ON j.id = a.id_jurnal AND a.id_siswa = :id_siswa
                          WHERE j.id_kelas = :id_kelas AND DATE(j.tanggal) = CURDATE()
                          ORDER BY j.jam_ke");
        $this->db->bind(':id_siswa', $this->idSiswa);
        $this->db->bind(':id_kelas', $siswa['id_kelas']);
        $todaySchedule = $this->db->resultSet();

        \Response::success([
            'siswa' => [
                'nama' => $siswa['nama'],
                'nisn' => $siswa['nisn'] ?? '',
                'kelas' => $siswa['nama_kelas'],
                'foto' => $siswa['foto'] ?? null
            ],
            'absensi_bulan_ini' => [
                'hadir' => (int) ($absensiMonth['hadir'] ?? 0),
                'izin' => (int) ($absensiMonth['izin'] ?? 0),
                'sakit' => (int) ($absensiMonth['sakit'] ?? 0),
                'alpa' => (int) ($absensiMonth['alpa'] ?? 0)
            ],
            'jadwal_hari_ini' => $todaySchedule
        ]);
    }

    /**
     * GET /api/siswa/absensi
     * GET /api/siswa/absensi/{bulan} - format: 2026-01
     */
    private function getAbsensi($bulan = null)
    {
        $bulan = $bulan ?? date('Y-m');
        list($tahun, $bln) = explode('-', $bulan);

        $this->db->query("SELECT j.tanggal, j.jam_ke, m.nama_mapel, g.nama as nama_guru,
                          a.status, a.keterangan
                          FROM absensi a
                          JOIN jurnal j ON a.id_jurnal = j.id
                          LEFT JOIN mapel m ON j.id_mapel = m.id
                          LEFT JOIN guru g ON j.id_guru = g.id
                          WHERE a.id_siswa = :id_siswa
                          AND MONTH(j.tanggal) = :bulan
                          AND YEAR(j.tanggal) = :tahun
                          ORDER BY j.tanggal DESC, j.jam_ke");
        $this->db->bind(':id_siswa', $this->idSiswa);
        $this->db->bind(':bulan', $bln);
        $this->db->bind(':tahun', $tahun);
        $absensi = $this->db->resultSet();

        // Summary
        $this->db->query("SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN a.status = 'H' THEN 1 ELSE 0 END) as hadir,
                            SUM(CASE WHEN a.status = 'I' THEN 1 ELSE 0 END) as izin,
                            SUM(CASE WHEN a.status = 'S' THEN 1 ELSE 0 END) as sakit,
                            SUM(CASE WHEN a.status = 'A' THEN 1 ELSE 0 END) as alpa
                          FROM absensi a
                          JOIN jurnal j ON a.id_jurnal = j.id
                          WHERE a.id_siswa = :id_siswa
                          AND MONTH(j.tanggal) = :bulan
                          AND YEAR(j.tanggal) = :tahun");
        $this->db->bind(':id_siswa', $this->idSiswa);
        $this->db->bind(':bulan', $bln);
        $this->db->bind(':tahun', $tahun);
        $summary = $this->db->single();

        \Response::success([
            'bulan' => $bulan,
            'summary' => [
                'total' => (int) ($summary['total'] ?? 0),
                'hadir' => (int) ($summary['hadir'] ?? 0),
                'izin' => (int) ($summary['izin'] ?? 0),
                'sakit' => (int) ($summary['sakit'] ?? 0),
                'alpa' => (int) ($summary['alpa'] ?? 0)
            ],
            'data' => $absensi
        ]);
    }

    /**
     * GET /api/siswa/rekap
     */
    private function getRekapAbsensi()
    {
        // Get rekap per semester
        $this->db->query("SELECT s.nama_semester, tp.nama_tp,
                            SUM(CASE WHEN a.status = 'H' THEN 1 ELSE 0 END) as hadir,
                            SUM(CASE WHEN a.status = 'I' THEN 1 ELSE 0 END) as izin,
                            SUM(CASE WHEN a.status = 'S' THEN 1 ELSE 0 END) as sakit,
                            SUM(CASE WHEN a.status = 'A' THEN 1 ELSE 0 END) as alpa,
                            COUNT(*) as total
                          FROM absensi a
                          JOIN jurnal j ON a.id_jurnal = j.id
                          JOIN semester s ON j.id_semester = s.id
                          JOIN tahun_pelajaran tp ON j.id_tp = tp.id
                          WHERE a.id_siswa = :id_siswa
                          GROUP BY j.id_semester, j.id_tp
                          ORDER BY tp.nama_tp DESC, s.id DESC");
        $this->db->bind(':id_siswa', $this->idSiswa);
        $rekap = $this->db->resultSet();

        \Response::success($rekap);
    }

    /**
     * GET /api/siswa/pembayaran
     */
    private function getPembayaran()
    {
        // Get tagihan
        $this->db->query("SELECT t.*, jp.nama_pembayaran
                          FROM tagihan t
                          JOIN jenis_pembayaran jp ON t.id_jenis = jp.id
                          WHERE t.id_siswa = :id_siswa
                          ORDER BY t.created_at DESC");
        $this->db->bind(':id_siswa', $this->idSiswa);
        $tagihan = $this->db->resultSet();

        // Get riwayat pembayaran
        $this->db->query("SELECT p.*, jp.nama_pembayaran
                          FROM pembayaran p
                          JOIN jenis_pembayaran jp ON p.id_jenis = jp.id
                          WHERE p.id_siswa = :id_siswa
                          ORDER BY p.tanggal DESC
                          LIMIT 20");
        $this->db->bind(':id_siswa', $this->idSiswa);
        $riwayat = $this->db->resultSet();

        \Response::success([
            'tagihan' => $tagihan,
            'riwayat' => $riwayat
        ]);
    }

    /**
     * GET /api/siswa/profil
     */
    private function getProfil()
    {
        $this->db->query("SELECT s.*, k.nama_kelas 
                          FROM siswa s
                          LEFT JOIN kelas k ON s.id_kelas = k.id
                          WHERE s.id = :id");
        $this->db->bind(':id', $this->idSiswa);
        $siswa = $this->db->single();

        if (!$siswa) {
            \Response::notFound('Data siswa tidak ditemukan');
        }

        // Remove sensitive data
        unset($siswa['password']);

        \Response::success($siswa);
    }

    /**
     * PUT /api/siswa/profil
     */
    private function updateProfil()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        // Only allow updating certain fields
        $allowedFields = ['no_hp', 'alamat', 'no_hp_ayah', 'no_hp_ibu', 'no_hp_wali'];
        $updates = [];
        $binds = [];

        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updates[] = "{$field} = :{$field}";
                $binds[":{$field}"] = $input[$field];
            }
        }

        if (empty($updates)) {
            \Response::validationError(['fields' => 'No valid fields to update']);
        }

        $sql = "UPDATE siswa SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $this->idSiswa);

        foreach ($binds as $key => $value) {
            $this->db->bind($key, $value);
        }

        if ($this->db->execute()) {
            \Response::success(null, 'Profil berhasil diupdate');
        } else {
            \Response::error('Gagal update profil');
        }
    }
}
