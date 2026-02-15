<?php
/**
 * API Guru Controller
 * File: api/controllers/GuruController.php
 */

namespace Api;

class GuruController
{
    private $db;

    public function __construct()
    {
        $this->db = new \Database();
    }

    public function handleRequest($method, $action, $param = null)
    {
        // Require guru or walikelas role
        $payload = \Auth::requireRole(['guru', 'walikelas', 'admin']);
        $this->idGuru = $payload['id_ref'];

        switch ($action) {
            case 'dashboard':
                if ($method === 'GET') {
                    $this->dashboard();
                }
                break;

            case 'jurnal':
                if ($method === 'GET') {
                    $this->getJurnal($param);
                } elseif ($method === 'POST') {
                    $this->createJurnal();
                } elseif ($method === 'PUT') {
                    $this->updateJurnal($param);
                }
                break;

            case 'absensi':
                if ($method === 'GET') {
                    $this->getAbsensi($param);
                } elseif ($method === 'POST') {
                    $this->saveAbsensi();
                }
                break;

            case 'jadwal':
                if ($method === 'GET') {
                    $this->getJadwal();
                }
                break;

            case 'kelas':
                if ($method === 'GET') {
                    $this->getKelas();
                }
                break;

            case 'mapel':
                if ($method === 'GET') {
                    $this->getMapel();
                }
                break;

            default:
                \Response::notFound('Action not found');
        }
    }

    /**
     * GET /api/guru/dashboard
     */
    private function dashboard()
    {
        // Get active tahun pelajaran
        $this->db->query("SELECT * FROM tahun_pelajaran WHERE is_active = 1 LIMIT 1");
        $tp = $this->db->single();
        $idTp = $tp['id'] ?? 0;

        // Get active semester
        $this->db->query("SELECT * FROM semester WHERE is_active = 1 LIMIT 1");
        $semester = $this->db->single();
        $idSemester = $semester['id'] ?? 0;

        // Count jurnal today
        $this->db->query("SELECT COUNT(*) as total FROM jurnal 
                          WHERE id_guru = :id_guru AND DATE(tanggal) = CURDATE()");
        $this->db->bind(':id_guru', $this->idGuru);
        $jurnalToday = $this->db->single()['total'] ?? 0;

        // Count jurnal this month
        $this->db->query("SELECT COUNT(*) as total FROM jurnal 
                          WHERE id_guru = :id_guru 
                          AND MONTH(tanggal) = MONTH(CURDATE()) 
                          AND YEAR(tanggal) = YEAR(CURDATE())");
        $this->db->bind(':id_guru', $this->idGuru);
        $jurnalMonth = $this->db->single()['total'] ?? 0;

        // Get recent jurnal
        $this->db->query("SELECT j.*, k.nama_kelas, m.nama_mapel 
                          FROM jurnal j
                          LEFT JOIN kelas k ON j.id_kelas = k.id
                          LEFT JOIN mapel m ON j.id_mapel = m.id
                          WHERE j.id_guru = :id_guru
                          ORDER BY j.tanggal DESC, j.jam_mulai DESC
                          LIMIT 5");
        $this->db->bind(':id_guru', $this->idGuru);
        $recentJurnal = $this->db->resultSet();

        // Get guru info
        $this->db->query("SELECT * FROM guru WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $this->idGuru);
        $guru = $this->db->single();

        \Response::success([
            'guru' => [
                'nama' => $guru['nama'] ?? '',
                'nip' => $guru['nip'] ?? ''
            ],
            'tahun_pelajaran' => $tp['nama_tp'] ?? '',
            'semester' => $semester['nama_semester'] ?? '',
            'stats' => [
                'jurnal_hari_ini' => (int) $jurnalToday,
                'jurnal_bulan_ini' => (int) $jurnalMonth
            ],
            'recent_jurnal' => $recentJurnal
        ]);
    }

    /**
     * GET /api/guru/jurnal
     * GET /api/guru/jurnal/{id}
     */
    private function getJurnal($id = null)
    {
        if ($id) {
            // Get single jurnal
            $this->db->query("SELECT j.*, k.nama_kelas, m.nama_mapel 
                              FROM jurnal j
                              LEFT JOIN kelas k ON j.id_kelas = k.id
                              LEFT JOIN mapel m ON j.id_mapel = m.id
                              WHERE j.id = :id AND j.id_guru = :id_guru
                              LIMIT 1");
            $this->db->bind(':id', $id);
            $this->db->bind(':id_guru', $this->idGuru);
            $jurnal = $this->db->single();

            if (!$jurnal) {
                \Response::notFound('Jurnal tidak ditemukan');
            }

            \Response::success($jurnal);
        } else {
            // Get list with pagination
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 20;
            $offset = ($page - 1) * $limit;
            $tanggal = $_GET['tanggal'] ?? null;

            $where = "WHERE j.id_guru = :id_guru";
            if ($tanggal) {
                $where .= " AND DATE(j.tanggal) = :tanggal";
            }

            $this->db->query("SELECT j.*, k.nama_kelas, m.nama_mapel 
                              FROM jurnal j
                              LEFT JOIN kelas k ON j.id_kelas = k.id
                              LEFT JOIN mapel m ON j.id_mapel = m.id
                              {$where}
                              ORDER BY j.tanggal DESC, j.jam_mulai DESC
                              LIMIT {$limit} OFFSET {$offset}");
            $this->db->bind(':id_guru', $this->idGuru);
            if ($tanggal) {
                $this->db->bind(':tanggal', $tanggal);
            }
            $jurnals = $this->db->resultSet();

            // Get total count
            $this->db->query("SELECT COUNT(*) as total FROM jurnal j {$where}");
            $this->db->bind(':id_guru', $this->idGuru);
            if ($tanggal) {
                $this->db->bind(':tanggal', $tanggal);
            }
            $total = $this->db->single()['total'] ?? 0;

            \Response::success([
                'data' => $jurnals,
                'pagination' => [
                    'page' => (int) $page,
                    'limit' => (int) $limit,
                    'total' => (int) $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
        }
    }

    /**
     * POST /api/guru/jurnal
     */
    private function createJurnal()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        // Validation
        $required = ['id_kelas', 'id_mapel', 'tanggal', 'jam_ke', 'materi'];
        $errors = [];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        if (!empty($errors)) {
            \Response::validationError($errors);
        }

        // Get active TP and Semester
        $this->db->query("SELECT id FROM tahun_pelajaran WHERE is_active = 1 LIMIT 1");
        $idTp = $this->db->single()['id'] ?? 0;

        $this->db->query("SELECT id FROM semester WHERE is_active = 1 LIMIT 1");
        $idSemester = $this->db->single()['id'] ?? 0;

        // Insert jurnal
        $this->db->query("INSERT INTO jurnal (id_guru, id_kelas, id_mapel, id_tp, id_semester, tanggal, jam_ke, jam_mulai, jam_selesai, materi, keterangan, created_at)
                          VALUES (:id_guru, :id_kelas, :id_mapel, :id_tp, :id_semester, :tanggal, :jam_ke, :jam_mulai, :jam_selesai, :materi, :keterangan, NOW())");

        $this->db->bind(':id_guru', $this->idGuru);
        $this->db->bind(':id_kelas', $input['id_kelas']);
        $this->db->bind(':id_mapel', $input['id_mapel']);
        $this->db->bind(':id_tp', $idTp);
        $this->db->bind(':id_semester', $idSemester);
        $this->db->bind(':tanggal', $input['tanggal']);
        $this->db->bind(':jam_ke', $input['jam_ke']);
        $this->db->bind(':jam_mulai', $input['jam_mulai'] ?? null);
        $this->db->bind(':jam_selesai', $input['jam_selesai'] ?? null);
        $this->db->bind(':materi', $input['materi']);
        $this->db->bind(':keterangan', $input['keterangan'] ?? '');

        if ($this->db->execute()) {
            $jurnalId = $this->db->lastInsertId();
            \Response::success(['id' => $jurnalId], 'Jurnal berhasil disimpan');
        } else {
            \Response::error('Gagal menyimpan jurnal');
        }
    }

    /**
     * PUT /api/guru/jurnal/{id}
     */
    private function updateJurnal($id)
    {
        $input = json_decode(file_get_contents('php://input'), true);

        // Check ownership
        $this->db->query("SELECT id FROM jurnal WHERE id = :id AND id_guru = :id_guru");
        $this->db->bind(':id', $id);
        $this->db->bind(':id_guru', $this->idGuru);
        if (!$this->db->single()) {
            \Response::notFound('Jurnal tidak ditemukan');
        }

        // Update
        $this->db->query("UPDATE jurnal SET 
                          id_kelas = :id_kelas,
                          id_mapel = :id_mapel,
                          tanggal = :tanggal,
                          jam_ke = :jam_ke,
                          jam_mulai = :jam_mulai,
                          jam_selesai = :jam_selesai,
                          materi = :materi,
                          keterangan = :keterangan,
                          updated_at = NOW()
                          WHERE id = :id");

        $this->db->bind(':id', $id);
        $this->db->bind(':id_kelas', $input['id_kelas'] ?? null);
        $this->db->bind(':id_mapel', $input['id_mapel'] ?? null);
        $this->db->bind(':tanggal', $input['tanggal'] ?? null);
        $this->db->bind(':jam_ke', $input['jam_ke'] ?? null);
        $this->db->bind(':jam_mulai', $input['jam_mulai'] ?? null);
        $this->db->bind(':jam_selesai', $input['jam_selesai'] ?? null);
        $this->db->bind(':materi', $input['materi'] ?? null);
        $this->db->bind(':keterangan', $input['keterangan'] ?? '');

        if ($this->db->execute()) {
            \Response::success(null, 'Jurnal berhasil diupdate');
        } else {
            \Response::error('Gagal update jurnal');
        }
    }

    /**
     * GET /api/guru/kelas
     */
    private function getKelas()
    {
        $this->db->query("SELECT id, nama_kelas FROM kelas WHERE is_active = 1 ORDER BY nama_kelas");
        $kelas = $this->db->resultSet();
        \Response::success($kelas);
    }

    /**
     * GET /api/guru/mapel
     */
    private function getMapel()
    {
        $this->db->query("SELECT id, nama_mapel FROM mapel ORDER BY nama_mapel");
        $mapel = $this->db->resultSet();
        \Response::success($mapel);
    }

    /**
     * POST /api/guru/absensi
     */
    private function saveAbsensi()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $idJurnal = $input['id_jurnal'] ?? null;
        $absensi = $input['absensi'] ?? [];

        if (!$idJurnal || empty($absensi)) {
            \Response::validationError([
                'id_jurnal' => 'ID Jurnal required',
                'absensi' => 'Absensi data required'
            ]);
        }

        // Verify jurnal ownership
        $this->db->query("SELECT * FROM jurnal WHERE id = :id AND id_guru = :id_guru");
        $this->db->bind(':id', $idJurnal);
        $this->db->bind(':id_guru', $this->idGuru);
        $jurnal = $this->db->single();

        if (!$jurnal) {
            \Response::notFound('Jurnal tidak ditemukan');
        }

        // Delete existing absensi for this jurnal
        $this->db->query("DELETE FROM absensi WHERE id_jurnal = :id_jurnal");
        $this->db->bind(':id_jurnal', $idJurnal);
        $this->db->execute();

        // Insert new absensi
        foreach ($absensi as $item) {
            $this->db->query("INSERT INTO absensi (id_jurnal, id_siswa, status, keterangan, created_at)
                              VALUES (:id_jurnal, :id_siswa, :status, :keterangan, NOW())");
            $this->db->bind(':id_jurnal', $idJurnal);
            $this->db->bind(':id_siswa', $item['id_siswa']);
            $this->db->bind(':status', $item['status']); // H, I, S, A
            $this->db->bind(':keterangan', $item['keterangan'] ?? '');
            $this->db->execute();
        }

        \Response::success(null, 'Absensi berhasil disimpan');
    }

    /**
     * GET /api/guru/absensi/{id_jurnal}
     */
    private function getAbsensi($idJurnal)
    {
        if (!$idJurnal) {
            \Response::validationError(['id_jurnal' => 'ID Jurnal required']);
        }

        // Get jurnal
        $this->db->query("SELECT j.*, k.nama_kelas, m.nama_mapel 
                          FROM jurnal j
                          LEFT JOIN kelas k ON j.id_kelas = k.id
                          LEFT JOIN mapel m ON j.id_mapel = m.id
                          WHERE j.id = :id AND j.id_guru = :id_guru");
        $this->db->bind(':id', $idJurnal);
        $this->db->bind(':id_guru', $this->idGuru);
        $jurnal = $this->db->single();

        if (!$jurnal) {
            \Response::notFound('Jurnal tidak ditemukan');
        }

        // Get students in class
        $this->db->query("SELECT s.id, s.nama, s.nisn, 
                          COALESCE(a.status, 'H') as status,
                          a.keterangan
                          FROM siswa s
                          LEFT JOIN absensi a ON s.id = a.id_siswa AND a.id_jurnal = :id_jurnal
                          WHERE s.id_kelas = :id_kelas AND s.is_active = 1
                          ORDER BY s.nama");
        $this->db->bind(':id_jurnal', $idJurnal);
        $this->db->bind(':id_kelas', $jurnal['id_kelas']);
        $siswa = $this->db->resultSet();

        \Response::success([
            'jurnal' => $jurnal,
            'siswa' => $siswa
        ]);
    }

    /**
     * GET /api/guru/jadwal
     */
    private function getJadwal()
    {
        $hari = $_GET['hari'] ?? date('N'); // 1-7

        $this->db->query("SELECT j.*, k.nama_kelas, m.nama_mapel
                          FROM jadwal j
                          LEFT JOIN kelas k ON j.id_kelas = k.id
                          LEFT JOIN mapel m ON j.id_mapel = m.id
                          WHERE j.id_guru = :id_guru AND j.hari = :hari
                          ORDER BY j.jam_ke");
        $this->db->bind(':id_guru', $this->idGuru);
        $this->db->bind(':hari', $hari);
        $jadwal = $this->db->resultSet();

        \Response::success($jadwal);
    }
}
