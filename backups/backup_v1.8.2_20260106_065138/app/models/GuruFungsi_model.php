<?php
// File: app/models/GuruFungsi_model.php
// Model untuk mengelola fungsi tambahan guru (bendahara, petugas_psb, dll)

class GuruFungsi_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Get all functions assigned to a guru for specific TP
     */
    public function getFungsiByGuru($id_guru, $id_tp = null)
    {
        if ($id_tp) {
            $this->db->query('SELECT * FROM guru_fungsi WHERE id_guru = :id_guru AND id_tp = :id_tp AND is_active = 1');
            $this->db->bind('id_tp', $id_tp);
        } else {
            $this->db->query('SELECT * FROM guru_fungsi WHERE id_guru = :id_guru AND is_active = 1');
        }
        $this->db->bind('id_guru', $id_guru);
        return $this->db->resultSet();
    }

    /**
     * Check if guru is bendahara for given TP
     */
    public function isBendahara($id_guru, $id_tp)
    {
        $this->db->query('SELECT id FROM guru_fungsi WHERE id_guru = :id_guru AND fungsi = "bendahara" AND id_tp = :id_tp AND is_active = 1');
        $this->db->bind('id_guru', $id_guru);
        $this->db->bind('id_tp', $id_tp);
        return $this->db->single() ? true : false;
    }

    /**
     * Check if guru is petugas PSB for given TP
     */
    public function isPetugasPSB($id_guru, $id_tp)
    {
        $this->db->query('SELECT id FROM guru_fungsi WHERE id_guru = :id_guru AND fungsi = "petugas_psb" AND id_tp = :id_tp AND is_active = 1');
        $this->db->bind('id_guru', $id_guru);
        $this->db->bind('id_tp', $id_tp);
        return $this->db->single() ? true : false;
    }

    /**
     * Check if guru is admin CMS for given TP
     */
    public function isAdminCMS($id_guru, $id_tp)
    {
        $this->db->query('SELECT id FROM guru_fungsi WHERE id_guru = :id_guru AND fungsi = "admin_cms" AND id_tp = :id_tp AND is_active = 1');
        $this->db->bind('id_guru', $id_guru);
        $this->db->bind('id_tp', $id_tp);
        return $this->db->single() ? true : false;
    }

    /**
     * Check if guru is petugas buku tamu for given TP
     */
    public function isPetugasBukuTamu($id_guru, $id_tp)
    {
        $this->db->query('SELECT id FROM guru_fungsi WHERE id_guru = :id_guru AND fungsi = "petugas_buku_tamu" AND id_tp = :id_tp AND is_active = 1');
        $this->db->bind('id_guru', $id_guru);
        $this->db->bind('id_tp', $id_tp);
        return $this->db->single() ? true : false;
    }

    /**
     * Check if guru has specific function
     */
    public function hasFungsi($id_guru, $fungsi, $id_tp)
    {
        $this->db->query('SELECT id FROM guru_fungsi WHERE id_guru = :id_guru AND fungsi = :fungsi AND id_tp = :id_tp AND is_active = 1');
        $this->db->bind('id_guru', $id_guru);
        $this->db->bind('fungsi', $fungsi);
        $this->db->bind('id_tp', $id_tp);
        return $this->db->single() ? true : false;
    }

    /**
     * Assign function to guru
     */
    public function setFungsi($id_guru, $fungsi, $id_tp, $created_by = null)
    {
        // Check if already exists
        if ($this->hasFungsi($id_guru, $fungsi, $id_tp)) {
            return true; // Already has this function
        }

        $this->db->query('INSERT INTO guru_fungsi (id_guru, fungsi, id_tp, is_active, created_by) 
                          VALUES (:id_guru, :fungsi, :id_tp, 1, :created_by)');
        $this->db->bind('id_guru', $id_guru);
        $this->db->bind('fungsi', $fungsi);
        $this->db->bind('id_tp', $id_tp);
        $this->db->bind('created_by', $created_by);
        return $this->db->execute();
    }

    /**
     * Remove function from guru
     */
    public function removeFungsi($id_guru, $fungsi, $id_tp)
    {
        $this->db->query('DELETE FROM guru_fungsi WHERE id_guru = :id_guru AND fungsi = :fungsi AND id_tp = :id_tp');
        $this->db->bind('id_guru', $id_guru);
        $this->db->bind('fungsi', $fungsi);
        $this->db->bind('id_tp', $id_tp);
        return $this->db->execute();
    }

    /**
     * Get all bendahara for given TP
     */
    public function getAllBendahara($id_tp)
    {
        $this->db->query('SELECT gf.*, g.nama_guru, g.nik 
                          FROM guru_fungsi gf 
                          JOIN guru g ON gf.id_guru = g.id_guru 
                          WHERE gf.fungsi = "bendahara" AND gf.id_tp = :id_tp AND gf.is_active = 1
                          ORDER BY g.nama_guru');
        $this->db->bind('id_tp', $id_tp);
        return $this->db->resultSet();
    }

    /**
     * Get all guru with their functions for given TP
     */
    public function getGuruWithFungsi($id_tp)
    {
        $this->db->query('SELECT g.id_guru, g.nama_guru, g.nik,
                          GROUP_CONCAT(gf.fungsi) as fungsi_list
                          FROM guru g
                          LEFT JOIN guru_fungsi gf ON g.id_guru = gf.id_guru AND gf.id_tp = :id_tp AND gf.is_active = 1
                          GROUP BY g.id_guru
                          ORDER BY g.nama_guru');
        $this->db->bind('id_tp', $id_tp);
        return $this->db->resultSet();
    }

    /**
     * Get all guru for dropdown
     */
    public function getAllGuru()
    {
        $this->db->query('SELECT id_guru, nama_guru, nik FROM guru ORDER BY nama_guru');
        return $this->db->resultSet();
    }

    /**
     * Bulk update functions for a guru
     */
    public function updateFungsiGuru($id_guru, $fungsiList, $id_tp, $created_by = null)
    {
        // Remove all existing functions for this guru and TP
        $this->db->query('DELETE FROM guru_fungsi WHERE id_guru = :id_guru AND id_tp = :id_tp');
        $this->db->bind('id_guru', $id_guru);
        $this->db->bind('id_tp', $id_tp);
        $this->db->execute();

        // Add new functions
        if (!empty($fungsiList)) {
            foreach ($fungsiList as $fungsi) {
                $this->setFungsi($id_guru, $fungsi, $id_tp, $created_by);
            }
        }

        return true;
    }

    /**
     * Get available functions list
     */
    public static function getAvailableFungsi()
    {
        return [
            'bendahara' => 'Bendahara',
            'petugas_psb' => 'Petugas PSB',
            'petugas_buku_tamu' => 'Petugas Buku Tamu',
            'admin_cms' => 'Admin CMS',
            'kurikulum' => 'Kurikulum',
            'kesiswaan' => 'Kesiswaan'
        ];
    }
}
