<?php

class Pembayaran_model
{
    private $db;

    public function __construct()
    {
        require_once APPROOT . '/app/core/Database.php';
        $this->db = new Database;
    }

    // =============================
    // TAGIHAN (class-scoped or global derived)
    // =============================

    public function getTagihanKelas($id_kelas, $id_tp, $id_semester = null)
    {
        $sql = "SELECT * FROM pembayaran_tagihan 
                WHERE id_kelas = :id_kelas AND id_tp = :id_tp";
        if ($id_semester) {
            $sql .= " AND (id_semester = :id_semester OR id_semester IS NULL)";
        }
        $sql .= " ORDER BY created_at DESC";
        $this->db->query($sql);
        $this->db->bind('id_kelas', $id_kelas);
        $this->db->bind('id_tp', $id_tp);
        if ($id_semester)
            $this->db->bind('id_semester', $id_semester);
        return $this->db->resultSet();
    }

    public function getTagihanById($id)
    {
        $this->db->query("SELECT * FROM pembayaran_tagihan WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function createTagihanKelas($data)
    {
        // data: nama, kategori_id?, id_tp, id_semester?, id_kelas, tipe, nominal_default, jatuh_tempo, created_by_user, created_by_role, ref_global_id?
        $this->db->query("INSERT INTO pembayaran_tagihan (nama, kategori_id, is_global, ref_global_id, id_tp, id_semester, id_kelas, tipe, nominal_default, jatuh_tempo, created_by_user, created_by_role)
                          VALUES (:nama, :kategori_id, 0, :ref_global_id, :id_tp, :id_semester, :id_kelas, :tipe, :nominal_default, :jatuh_tempo, :created_by_user, :created_by_role)");
        $this->db->bind('nama', $data['nama']);
        $this->db->bind('kategori_id', $data['kategori_id'] ?? null);
        $this->db->bind('ref_global_id', $data['ref_global_id'] ?? null);
        $this->db->bind('id_tp', $data['id_tp']);
        $this->db->bind('id_semester', $data['id_semester'] ?? null);
        $this->db->bind('id_kelas', $data['id_kelas']);
        $this->db->bind('tipe', $data['tipe'] ?? 'sekali');
        $this->db->bind('nominal_default', $data['nominal_default'] ?? 0);
        $this->db->bind('jatuh_tempo', $data['jatuh_tempo'] ?? null);
        $this->db->bind('created_by_user', $data['created_by_user'] ?? null);
        $this->db->bind('created_by_role', $data['created_by_role'] ?? 'wali_kelas');
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    public function updateTagihanKelas($id, $data)
    {
        // Update tagihan kelas - hanya update field yang dikirim
        $sql = "UPDATE pembayaran_tagihan SET ";
        $updates = [];

        if (isset($data['nama'])) {
            $updates[] = "nama = :nama";
        }
        if (isset($data['nominal_default'])) {
            $updates[] = "nominal_default = :nominal_default";
        }
        if (isset($data['jatuh_tempo'])) {
            $updates[] = "jatuh_tempo = :jatuh_tempo";
        }
        if (isset($data['tipe'])) {
            $updates[] = "tipe = :tipe";
        }

        if (empty($updates))
            return false;

        $sql .= implode(', ', $updates) . " WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind('id', $id);

        if (isset($data['nama'])) {
            $this->db->bind('nama', $data['nama']);
        }
        if (isset($data['nominal_default'])) {
            $this->db->bind('nominal_default', $data['nominal_default']);
        }
        if (isset($data['jatuh_tempo'])) {
            $this->db->bind('jatuh_tempo', $data['jatuh_tempo']);
        }
        if (isset($data['tipe'])) {
            $this->db->bind('tipe', $data['tipe']);
        }

        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    public function deleteTagihanKelas($id)
    {
        // First delete all related transactions
        $this->db->query("DELETE FROM pembayaran_transaksi WHERE tagihan_id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();

        // Then delete all siswa mappings
        $this->db->query("DELETE FROM pembayaran_tagihan_siswa WHERE tagihan_id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();

        // Finally delete the tagihan itself
        $this->db->query("DELETE FROM pembayaran_tagihan WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();

        return $this->db->rowCount() > 0;
    }

    public function deriveTagihanFromGlobal($id_tagihan_global, $overrides)
    {
        // Copy fields from global to class-scoped
        $this->db->query("SELECT * FROM pembayaran_tagihan WHERE id = :id AND is_global = 1");
        $this->db->bind('id', $id_tagihan_global);
        $global = $this->db->single();
        if (!$global)
            return false;

        $data = [
            'nama' => $global['nama'],
            'kategori_id' => $global['kategori_id'],
            'ref_global_id' => $global['id'],
            'id_tp' => $overrides['id_tp'],
            'id_semester' => $overrides['id_semester'] ?? null,
            'id_kelas' => $overrides['id_kelas'],
            'tipe' => $global['tipe'],
            'nominal_default' => $overrides['nominal_default'] ?? $global['nominal_default'],
            'jatuh_tempo' => $overrides['jatuh_tempo'] ?? $global['jatuh_tempo'],
            'created_by_user' => $overrides['created_by_user'] ?? null,
            'created_by_role' => 'wali_kelas',
        ];
        return $this->createTagihanKelas($data);
    }

    // =============================
    // PER-SISWA MAPPING & STATUS
    // =============================

    public function getTagihanSiswaList($tagihan_id)
    {
        $this->db->query("SELECT tgs.*, s.nama_siswa 
                          FROM pembayaran_tagihan_siswa tgs
                          JOIN siswa s ON s.id_siswa = tgs.id_siswa
                          WHERE tgs.tagihan_id = :tid
                          ORDER BY s.nama_siswa ASC");
        $this->db->bind('tid', $tagihan_id);
        return $this->db->resultSet();
    }

    public function ensureTagihanSiswa($tagihan_id, $id_siswa, $nominal_default = 0, $jatuh_tempo = null, $periode_bulan = null, $periode_tahun = null)
    {
        // Normalize period to 0 if null
        $pb = ($periode_bulan === null) ? 0 : (int) $periode_bulan;
        $pt = ($periode_tahun === null) ? 0 : (int) $periode_tahun;

        // If not exists, insert default row
        $this->db->query("SELECT id FROM pembayaran_tagihan_siswa WHERE tagihan_id = :tid AND id_siswa = :sid AND periode_bulan = :bln AND periode_tahun = :thn LIMIT 1");
        $this->db->bind('tid', $tagihan_id);
        $this->db->bind('sid', $id_siswa);
        $this->db->bind('bln', $pb);
        $this->db->bind('thn', $pt);
        $row = $this->db->single();
        if ($row)
            return $row['id'];

        $this->db->query("INSERT INTO pembayaran_tagihan_siswa (tagihan_id, id_siswa, nominal, diskon, total_terbayar, status, jatuh_tempo, periode_bulan, periode_tahun)
                          VALUES (:tid, :sid, :nom, 0, 0, 'belum', :jt, :bln, :thn)");
        $this->db->bind('tid', $tagihan_id);
        $this->db->bind('sid', $id_siswa);
        $this->db->bind('nom', $nominal_default);
        $this->db->bind('jt', $jatuh_tempo);
        $this->db->bind('bln', $pb);
        $this->db->bind('thn', $pt);
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    public function updateDiskonSiswa($tagihan_id, $id_siswa, $diskon)
    {
        $this->db->query("UPDATE pembayaran_tagihan_siswa SET diskon = :diskon WHERE tagihan_id = :tid AND id_siswa = :sid");
        $this->db->bind('diskon', $diskon);
        $this->db->bind('tid', $tagihan_id);
        $this->db->bind('sid', $id_siswa);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Get payment status for a specific student and tagihan
     * @param int $tagihan_id
     * @param int $id_siswa
     * @return array|null Payment record with total_terbayar
     */
    public function getPembayaranSiswa($tagihan_id, $id_siswa)
    {
        $this->db->query("SELECT * FROM pembayaran_tagihan_siswa 
                          WHERE tagihan_id = :tid AND id_siswa = :sid 
                          LIMIT 1");
        $this->db->bind('tid', $tagihan_id);
        $this->db->bind('sid', $id_siswa);
        return $this->db->single();
    }

    // =============================
    // TRANSAKSI
    // =============================

    public function createTransaksi($tagihan_id, $id_siswa, $jumlah, $metode = null, $keterangan = null, $bukti_path = null, $user_input_id = null)
    {
        // Ensure mapping exists to know nominal & diskon
        $tagihan = $this->getTagihanById($tagihan_id);
        if (!$tagihan)
            return false;

        // Create transaksi
        $this->db->query("INSERT INTO pembayaran_transaksi (tagihan_id, id_siswa, jumlah, metode, keterangan, bukti_path, user_input_id)
                          VALUES (:tid, :sid, :jml, :metode, :ket, :bukti, :uid)");
        $this->db->bind('tid', $tagihan_id);
        $this->db->bind('sid', $id_siswa);
        $this->db->bind('jml', $jumlah);
        $this->db->bind('metode', $metode);
        $this->db->bind('ket', $keterangan);
        $this->db->bind('bukti', $bukti_path);
        $this->db->bind('uid', $user_input_id);
        $this->db->execute();

        // Upsert tagihan_siswa
        $mapId = $this->ensureTagihanSiswa($tagihan_id, $id_siswa, $tagihan['nominal_default'], $tagihan['jatuh_tempo']);

        // Update akumulasi
        $this->db->query("UPDATE pembayaran_tagihan_siswa 
                          SET total_terbayar = total_terbayar + :jml
                          WHERE id = :id");
        $this->db->bind('jml', $jumlah);
        $this->db->bind('id', $mapId);
        $this->db->execute();

        // Refresh and set status
        $this->db->query("SELECT nominal, diskon, total_terbayar FROM pembayaran_tagihan_siswa WHERE id = :id");
        $this->db->bind('id', $mapId);
        $st = $this->db->single();
        $target = max(0, ((int) $st['nominal']) - ((int) $st['diskon']));
        $status = 'belum';
        if ((int) $st['total_terbayar'] <= 0) {
            $status = 'belum';
        } else if ((int) $st['total_terbayar'] < $target) {
            $status = 'sebagian';
        } else {
            $status = 'lunas';
        }
        $this->db->query("UPDATE pembayaran_tagihan_siswa SET status = :status WHERE id = :id");
        $this->db->bind('status', $status);
        $this->db->bind('id', $mapId);
        $this->db->execute();

        return true;
    }

    public function getRiwayat($id_kelas, $id_tp, $limit = 100)
    {
        $this->db->query("SELECT trx.*, s.nama_siswa, t.nama AS nama_tagihan,
                             COALESCE(g.nama_guru, u.nama_lengkap, 'Sistem') AS petugas_input
                          FROM pembayaran_transaksi trx
                          JOIN pembayaran_tagihan t ON t.id = trx.tagihan_id
                          JOIN siswa s ON s.id_siswa = trx.id_siswa
                          LEFT JOIN users u ON u.id = trx.user_input_id
                          LEFT JOIN guru g ON g.id_guru = u.id_ref AND u.role IN ('guru', 'wali_kelas')
                          WHERE t.id_kelas = :id_kelas AND t.id_tp = :id_tp
                          ORDER BY trx.tanggal DESC
                          LIMIT :lim");
        $this->db->bind('id_kelas', $id_kelas);
        $this->db->bind('id_tp', $id_tp);
        // For LIMIT binding, ensure integer binding
        $this->db->bind('lim', (int) $limit);
        return $this->db->resultSet();
    }

    /**
     * Get all riwayat across all classes (for admin)
     */
    public function getRiwayatAll($id_tp, $limit = 500)
    {
        $this->db->query("SELECT trx.*, s.nama_siswa, t.nama AS nama_tagihan, k.nama_kelas,
                             COALESCE(g.nama_guru, u.nama_lengkap, 'Sistem') AS petugas_input
                          FROM pembayaran_transaksi trx
                          JOIN pembayaran_tagihan t ON t.id = trx.tagihan_id
                          JOIN siswa s ON s.id_siswa = trx.id_siswa
                          JOIN kelas k ON k.id_kelas = t.id_kelas
                          LEFT JOIN users u ON u.id = trx.user_input_id
                          LEFT JOIN guru g ON g.id_guru = u.id_ref AND u.role IN ('guru', 'wali_kelas')
                          WHERE t.id_tp = :id_tp
                          ORDER BY trx.tanggal DESC, trx.id DESC
                          LIMIT :lim");
        $this->db->bind('id_tp', $id_tp);
        $this->db->bind('lim', (int) $limit);
        return $this->db->resultSet();
    }

    /**
     * Get all transactions for a specific student and tagihan
     */
    public function getTransaksiSiswa($tagihan_id, $id_siswa)
    {
        $this->db->query("SELECT t.*, COALESCE(g.nama_guru, u.nama_lengkap, 'Sistem') AS petugas_input 
                          FROM pembayaran_transaksi t
                          LEFT JOIN users u ON u.id_user = t.user_input_id
                          LEFT JOIN guru g ON g.id_guru = u.id_ref AND u.role IN ('guru', 'wali_kelas')
                          WHERE t.tagihan_id = :tid AND t.id_siswa = :sid 
                          ORDER BY t.tanggal DESC, t.id DESC");
        $this->db->bind('tid', $tagihan_id);
        $this->db->bind('sid', $id_siswa);
        return $this->db->resultSet();
    }

    /**
     * Get transaction by ID
     */
    public function getTransaksiById($id)
    {
        $this->db->query("SELECT * FROM pembayaran_transaksi WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    /**
     * Delete transaction and recalculate total_terbayar
     */
    public function deleteTransaksi($id)
    {
        // Get transaksi info first
        $transaksi = $this->getTransaksiById($id);
        if (!$transaksi)
            return false;

        $tagihan_id = $transaksi['tagihan_id'];
        $id_siswa = $transaksi['id_siswa'];
        $jumlah = $transaksi['jumlah'];

        // Delete transaction
        $this->db->query("DELETE FROM pembayaran_transaksi WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();

        // Update total_terbayar
        $this->db->query("UPDATE pembayaran_tagihan_siswa 
                          SET total_terbayar = GREATEST(0, total_terbayar - :jml)
                          WHERE tagihan_id = :tid AND id_siswa = :sid");
        $this->db->bind('jml', $jumlah);
        $this->db->bind('tid', $tagihan_id);
        $this->db->bind('sid', $id_siswa);
        $this->db->execute();

        // Recalculate status
        $this->db->query("SELECT id, nominal, diskon, total_terbayar 
                          FROM pembayaran_tagihan_siswa 
                          WHERE tagihan_id = :tid AND id_siswa = :sid");
        $this->db->bind('tid', $tagihan_id);
        $this->db->bind('sid', $id_siswa);
        $st = $this->db->single();

        if ($st) {
            $target = max(0, ((int) $st['nominal']) - ((int) $st['diskon']));
            $status = 'belum';
            if ((int) $st['total_terbayar'] <= 0) {
                $status = 'belum';
            } else if ((int) $st['total_terbayar'] < $target) {
                $status = 'sebagian';
            } else {
                $status = 'lunas';
            }

            $this->db->query("UPDATE pembayaran_tagihan_siswa SET status = :status WHERE id = :id");
            $this->db->bind('status', $status);
            $this->db->bind('id', $st['id']);
            $this->db->execute();
        }

        return true;
    }

}
