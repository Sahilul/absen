<?php

// File: app/models/Penugasan_model.php
class Penugasan_model
{
    private $db;

    public function __construct()
    {
        require_once APPROOT . '/app/core/Database.php';
        $this->db = new Database;
    }

    /**
     * Mengambil semua data penugasan untuk semester yang aktif.
     * @param int $id_semester ID semester yang sedang aktif.
     * @return array Daftar penugasan.
     */
    public function getAllPenugasanBySemester($id_semester)
    {
        $query = "SELECT 
                    penugasan.id_penugasan,
                    penugasan.id_guru,
                    penugasan.id_mapel,
                    penugasan.id_kelas,
                    guru.nama_guru, 
                    mapel.nama_mapel, 
                    kelas.nama_kelas
                  FROM penugasan
                  JOIN guru ON penugasan.id_guru = guru.id_guru
                  JOIN mapel ON penugasan.id_mapel = mapel.id_mapel
                  JOIN kelas ON penugasan.id_kelas = kelas.id_kelas
                  WHERE penugasan.id_semester = :id_semester
                  ORDER BY guru.nama_guru, 
                  CASE kelas.jenjang 
                      WHEN 'VII' THEN 7 
                      WHEN 'VIII' THEN 8 
                      WHEN 'IX' THEN 9 
                      WHEN 'X' THEN 10 
                      WHEN 'XI' THEN 11 
                      WHEN 'XII' THEN 12 
                      ELSE 99 
                  END ASC, 
                  kelas.nama_kelas ASC";

        $this->db->query($query);
        $this->db->bind('id_semester', $id_semester);
        return $this->db->resultSet();
    }

    /**
     * Menambahkan data penugasan baru.
     * @param array $data Data dari form (id_guru, id_mapel, id_kelas, id_semester).
     * @return int Jumlah baris yang terpengaruh (1 jika berhasil).
     */
    public function tambahDataPenugasan($data)
    {
        $query = "INSERT INTO penugasan (id_guru, id_mapel, id_kelas, id_semester) 
                  VALUES (:id_guru, :id_mapel, :id_kelas, :id_semester)";

        $this->db->query($query);
        $this->db->bind('id_guru', $data['id_guru']);
        $this->db->bind('id_mapel', $data['id_mapel']);
        $this->db->bind('id_kelas', $data['id_kelas']);
        $this->db->bind('id_semester', $data['id_semester']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Menghapus data penugasan berdasarkan ID.
     * @param int $id ID penugasan.
     * @return int Jumlah baris yang terpengaruh.
     */
    public function hapusDataPenugasan($id)
    {
        $query = "DELETE FROM penugasan WHERE id_penugasan = :id";
        $this->db->query($query);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Mengambil semua penugasan seorang guru di semester tertentu.
     * @param int $id_guru ID guru.
     * @param int $id_semester ID semester.
     * @return array Daftar penugasan guru.
     */
    public function getPenugasanByGuru($id_guru, $id_semester)
    {
        $query = "SELECT 
                    penugasan.id_penugasan,
                    penugasan.id_mapel,
                    penugasan.id_kelas,
                    mapel.nama_mapel, 
                    kelas.nama_kelas
                  FROM penugasan
                  JOIN mapel ON penugasan.id_mapel = mapel.id_mapel
                  JOIN kelas ON penugasan.id_kelas = kelas.id_kelas
                  WHERE penugasan.id_guru = :id_guru AND penugasan.id_semester = :id_semester
                  ORDER BY kelas.nama_kelas, mapel.nama_mapel";

        $this->db->query($query);
        $this->db->bind('id_guru', $id_guru);
        $this->db->bind('id_semester', $id_semester);
        return $this->db->resultSet();
    }

    /**
     * Mengambil detail penugasan berdasarkan ID.
     * @param int $id ID penugasan.
     * @return array Data penugasan lengkap dengan nama guru, mapel, dan kelas.
     */
    public function getPenugasanById($id)
    {
        $this->db->query('SELECT p.*, g.nama_guru, m.nama_mapel, k.nama_kelas,
                         s.semester as nama_semester, s.id_semester, s.id_tp,
                         tp.nama_tp as tahun_pelajaran
                         FROM penugasan p
                         JOIN guru g ON p.id_guru = g.id_guru
                         JOIN mapel m ON p.id_mapel = m.id_mapel  
                         JOIN kelas k ON p.id_kelas = k.id_kelas
                         JOIN semester s ON p.id_semester = s.id_semester
                         JOIN tp ON s.id_tp = tp.id_tp
                         WHERE p.id_penugasan = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    /**
     * Memperbarui data penugasan.
     * @param array $data Data yang diperbarui (termasuk id_penugasan).
     * @return int Jumlah baris yang terpengaruh.
     */
    public function updateDataPenugasan($data)
    {
        $query = "UPDATE penugasan SET 
                  id_guru = :id_guru,
                  id_mapel = :id_mapel,
                  id_kelas = :id_kelas,
                  id_semester = :id_semester
                  WHERE id_penugasan = :id_penugasan";

        $this->db->query($query);
        $this->db->bind('id_guru', $data['id_guru']);
        $this->db->bind('id_mapel', $data['id_mapel']);
        $this->db->bind('id_kelas', $data['id_kelas']);
        $this->db->bind('id_semester', $data['id_semester']);
        $this->db->bind('id_penugasan', $data['id_penugasan']);

        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Mengecek apakah kombinasi penugasan (guru, mapel, kelas, semester) sudah ada — digunakan saat TAMBAH.
     * @param int $id_guru
     * @param int $id_mapel
     * @param int $id_kelas
     * @param int $id_semester
     * @return bool true jika sudah ada (duplikat), false jika belum.
     */
    public function cekDuplikasiPenugasan($id_guru, $id_mapel, $id_kelas, $id_semester)
    {
        $this->db->query('SELECT COUNT(*) as total FROM penugasan 
                         WHERE id_guru = :id_guru 
                         AND id_mapel = :id_mapel 
                         AND id_kelas = :id_kelas 
                         AND id_semester = :id_semester');

        $this->db->bind('id_guru', $id_guru);
        $this->db->bind('id_mapel', $id_mapel);
        $this->db->bind('id_kelas', $id_kelas);
        $this->db->bind('id_semester', $id_semester);

        $result = $this->db->single();
        return $result['total'] > 0;
    }

    /**
     * Mengecek duplikasi penugasan saat EDIT (mengabaikan ID yang sedang diedit).
     * @param int $id_guru
     * @param int $id_mapel
     * @param int $id_kelas
     * @param int $id_semester
     * @param int $id_penugasan ID yang sedang diedit (dikecualikan dari pengecekan).
     * @return bool true jika duplikat ditemukan, false jika aman.
     */
    public function cekDuplikasiPenugasanEdit($id_guru, $id_mapel, $id_kelas, $id_semester, $id_penugasan)
    {
        $this->db->query('SELECT COUNT(*) as total FROM penugasan 
                         WHERE id_guru = :id_guru 
                         AND id_mapel = :id_mapel 
                         AND id_kelas = :id_kelas 
                         AND id_semester = :id_semester
                         AND id_penugasan != :id_penugasan');

        $this->db->bind('id_guru', $id_guru);
        $this->db->bind('id_mapel', $id_mapel);
        $this->db->bind('id_kelas', $id_kelas);
        $this->db->bind('id_semester', $id_semester);
        $this->db->bind('id_penugasan', $id_penugasan);

        $result = $this->db->single();
        return $result['total'] > 0;
    }

    /**
     * Ambil daftar mapel untuk kelas tertentu dalam semester
     */
    public function getMapelByKelas($id_kelas, $id_semester)
    {
        $query = "SELECT 
                    p.id_penugasan,
                    p.id_guru,
                    p.id_mapel,
                    m.nama_mapel,
                    g.nama_guru
                  FROM penugasan p
                  JOIN mapel m ON p.id_mapel = m.id_mapel
                  JOIN guru g ON p.id_guru = g.id_guru
                  WHERE p.id_kelas = :id_kelas 
                    AND p.id_semester = :id_semester
                  ORDER BY m.nama_mapel";

        $this->db->query($query);
        $this->db->bind('id_kelas', $id_kelas);
        $this->db->bind('id_semester', $id_semester);
        return $this->db->resultSet();
    }

    /**
     * Hitung total penugasan di semester tertentu
     * @param int $id_semester
     * @return int
     */
    public function countPenugasanBySemester($id_semester)
    {
        $this->db->query('SELECT COUNT(*) as total FROM penugasan WHERE id_semester = :id_semester');
        $this->db->bind('id_semester', $id_semester);
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    /**
     * Mencari tepat satu kelas ekuivalen pada tahun pelajaran semester tujuan.
     * Untuk copy dalam TP yang sama, kelas sumber dipertahankan. Untuk lintas
     * TP, nama dan jenjang harus sama serta hasilnya tidak boleh ambigu.
     */
    private function resolveKelasTujuan($id_kelas_sumber, $id_semester_tujuan)
    {
        $this->db->query('SELECT
                            k_sumber.id_kelas AS id_kelas_sumber,
                            k_sumber.id_tp AS id_tp_sumber,
                            k_sumber.nama_kelas AS nama_kelas_sumber,
                            k_sumber.jenjang AS jenjang_sumber,
                            sem_tujuan.id_tp AS id_tp_tujuan
                         FROM kelas k_sumber
                         JOIN semester sem_tujuan
                            ON sem_tujuan.id_semester = :id_semester_tujuan
                         WHERE k_sumber.id_kelas = :id_kelas_sumber');
        $this->db->bind('id_semester_tujuan', $id_semester_tujuan);
        $this->db->bind('id_kelas_sumber', $id_kelas_sumber);
        $scope = $this->db->single();

        if (!$scope) {
            return ['id_kelas_list' => [], 'reason' => 'semester atau kelas sumber tidak ditemukan'];
        }

        // Copy dalam TP yang sama: pertahankan kelas sumber.
        if ((int) $scope['id_tp_sumber'] === (int) $scope['id_tp_tujuan']) {
            return ['id_kelas_list' => [(int) $scope['id_kelas_sumber']], 'reason' => null];
        }

        // Lintas TP: petakan berdasarkan jenjang yang sama.
        // Cocokkan by JENJANG saja (bukan nama_kelas), supaya kelas yg berganti
        // penamaan/pemekaran tetap ketemu. Contoh: IX (lama) -> IX A + IX B (baru).
        // Semua kelas tujuan dgn jenjang sama akan menerima copy (mapping 1:banyak).
        $this->db->query('SELECT k_tujuan.id_kelas
                         FROM kelas k_tujuan
                         WHERE k_tujuan.id_tp = :id_tp_tujuan
                           AND k_tujuan.jenjang = :jenjang_sumber
                         ORDER BY k_tujuan.id_kelas');
        $this->db->bind('id_tp_tujuan', (int) $scope['id_tp_tujuan']);
        $this->db->bind('jenjang_sumber', $scope['jenjang_sumber']);
        $matches = $this->db->resultSet();

        if (empty($matches)) {
            return [
                'id_kelas_list' => [],
                'reason' => "kelas tujuan dgn jenjang '{$scope['jenjang_sumber']}' tidak ditemukan di TP tujuan"
            ];
        }

        $ids = array_map(function ($row) {
            return (int) $row['id_kelas'];
        }, $matches);

        return ['id_kelas_list' => $ids, 'reason' => null];
    }

    /**
     * Copy penugasan dari semester sumber ke semester tujuan.
     * Kelas dipetakan ke kelas ekuivalen pada TP tujuan dan duplikat dilewati.
     * @param int $id_semester_sumber
     * @param int $id_semester_tujuan
     * @return array ['copied' => int, 'skipped' => int, 'errors' => array]
     */
    public function copyPenugasanFromSemester($id_semester_sumber, $id_semester_tujuan)
    {
        $result = [
            'copied' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        // Ambil semua penugasan dari semester sumber
        $penugasanSumber = $this->getAllPenugasanBySemester($id_semester_sumber);

        foreach ($penugasanSumber as $tugas) {
            $kelasTujuan = $this->resolveKelasTujuan(
                $tugas['id_kelas'],
                $id_semester_tujuan
            );
            $idKelasTujuanList = $kelasTujuan['id_kelas_list'];

            if (empty($idKelasTujuanList)) {
                $result['errors'][] = "{$tugas['nama_kelas']}: {$kelasTujuan['reason']}";
                continue;
            }

            // Satu penugasan sumber bisa dipetakan ke >1 kelas tujuan
            // (mis. IX lama -> IX A & IX B baru). Copy ke tiap kelas tujuan.
            foreach ($idKelasTujuanList as $idKelasTujuan) {
                // Cek apakah kombinasi sudah ada di semester tujuan
                $isDuplicate = $this->cekDuplikasiPenugasan(
                    $tugas['id_guru'],
                    $tugas['id_mapel'],
                    $idKelasTujuan,
                    $id_semester_tujuan
                );

                if ($isDuplicate) {
                    $result['skipped']++;
                    continue;
                }

                // Insert penugasan baru menggunakan kelas milik TP tujuan
                try {
                    $data = [
                        'id_guru' => $tugas['id_guru'],
                        'id_mapel' => $tugas['id_mapel'],
                        'id_kelas' => $idKelasTujuan,
                        'id_semester' => $id_semester_tujuan
                    ];

                    if ($this->tambahDataPenugasan($data) > 0) {
                        $result['copied']++;
                    } else {
                        $result['errors'][] = "Gagal copy: {$tugas['nama_guru']} - {$tugas['nama_mapel']} - {$tugas['nama_kelas']}";
                    }
                } catch (Exception $e) {
                    $result['errors'][] = $e->getMessage();
                }
            }
        }

        return $result;
    }
}