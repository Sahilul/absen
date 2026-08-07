<?php
/**
 * File: app/models/KelasGrupWa_model.php
 * Model untuk CRUD grup WhatsApp per kelas
 */
class KelasGrupWa_model
{
    private $db;
    private $table = 'kelas_grup_wa';

    public function __construct()
    {
        $this->db = new Database;
        $this->ensureTableExists();
    }

    /**
     * Pastikan tabel ada
     */
    private function ensureTableExists()
    {
        try {
            $this->db->query("SELECT 1 FROM {$this->table} LIMIT 1");
            $this->db->execute();
        } catch (Exception $e) {
            // Create table if not exists
            $this->db->query("
                CREATE TABLE IF NOT EXISTS {$this->table} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_kelas INT NOT NULL,
                    nama_grup VARCHAR(100) NOT NULL,
                    grup_wa_id VARCHAR(50) NOT NULL,
                    is_active TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_kelas (id_kelas),
                    INDEX idx_active (is_active)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            $this->db->execute();
        }
    }

    /**
     * Get semua grup WA untuk kelas tertentu
     * @param int $id_kelas
     * @return array
     */
    public function getGrupByKelas($id_kelas)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE id_kelas = :id_kelas ORDER BY created_at ASC");
        $this->db->bind(':id_kelas', $id_kelas);
        return $this->db->resultSet();
    }

    /**
     * Get semua grup WA yang aktif untuk kelas tertentu
     * @param int $id_kelas
     * @return array
     */
    public function getActiveGrupByKelas($id_kelas)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE id_kelas = :id_kelas AND is_active = 1 ORDER BY created_at ASC");
        $this->db->bind(':id_kelas', $id_kelas);
        return $this->db->resultSet();
    }

    /**
     * Get grup by ID
     * @param int $id
     * @return array|false
     */
    public function getGrupById($id)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Tambah grup baru
     * @param int $id_kelas
     * @param string $nama_grup
     * @param string $grup_wa_id
     * @return int|false Last insert ID or false
     */
    public function addGrup($id_kelas, $nama_grup, $grup_wa_id)
    {
        try {
            $this->db->query("INSERT INTO {$this->table} (id_kelas, nama_grup, grup_wa_id) VALUES (:id_kelas, :nama_grup, :grup_wa_id)");
            $this->db->bind(':id_kelas', $id_kelas);
            $this->db->bind(':nama_grup', trim($nama_grup));
            $this->db->bind(':grup_wa_id', $this->formatNumber(trim($grup_wa_id)));
            $this->db->execute();
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("KelasGrupWa_model::addGrup error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update grup
     * @param int $id
     * @param string $nama_grup
     * @param string $grup_wa_id
     * @return bool
     */
    public function updateGrup($id, $nama_grup, $grup_wa_id)
    {
        try {
            $this->db->query("UPDATE {$this->table} SET nama_grup = :nama_grup, grup_wa_id = :grup_wa_id WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->bind(':nama_grup', trim($nama_grup));
            $this->db->bind(':grup_wa_id', $this->formatNumber(trim($grup_wa_id)));
            $this->db->execute();
            return true;
        } catch (Exception $e) {
            error_log("KelasGrupWa_model::updateGrup error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hapus grup
     * @param int $id
     * @return bool
     */
    public function deleteGrup($id)
    {
        try {
            $this->db->query("DELETE FROM {$this->table} WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (Exception $e) {
            error_log("KelasGrupWa_model::deleteGrup error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle status aktif grup
     * @param int $id
     * @return bool
     */
    public function toggleActive($id)
    {
        try {
            $this->db->query("UPDATE {$this->table} SET is_active = NOT is_active WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();
            return $this->db->rowCount() > 0;
        } catch (Exception $e) {
            error_log("KelasGrupWa_model::toggleActive error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get semua grup dengan info kelas
     * @return array
     */
    public function getAllGrupWithKelas()
    {
        $this->db->query("
            SELECT g.*, k.nama_kelas 
            FROM {$this->table} g 
            JOIN kelas k ON g.id_kelas = k.id_kelas 
            ORDER BY k.nama_kelas ASC, g.created_at ASC
        ");
        return $this->db->resultSet();
    }

    /**
     * Format nomor WA ke format internasional
     * Mendukung Group ID WhatsApp (tidak ditambahkan prefix 62)
     * @param string $number
     * @return string
     */
    private function formatNumber($number)
    {
        $number = trim($number);

        // 1. Jika sudah format JID (@g.us atau @s.whatsapp.net), kembalikan apa adanya
        if (strpos($number, '@') !== false) {
            return $number;
        }

        // 2. Deteksi Legacy Group ID (format: 628xxx-14xxx)
        // JANGAN hapus tanda hubung (-) dan tambahkan @g.us jika belum ada
        if (preg_match('/^\d+-\d+$/', $number)) {
            return $number . '@g.us';
        }

        // Bersihkan karakter non-numeric
        $cleanNumber = preg_replace('/[^0-9]/', '', $number);

        // 2. Deteksi Group ID (biasanya panjang > 15 digit)
        // Contoh Group ID: 120363287671196238 (18 digit)
        // JANGAN tambahkan 62 ke Group ID
        if (strlen($cleanNumber) > 15) {
            return $cleanNumber; // Simpan as-is, @g.us akan ditambahkan saat kirim
        }

        // 3. Format Nomor HP Indonesia (Standard) - hanya untuk nomor HP
        if (substr($cleanNumber, 0, 1) === '0') {
            $cleanNumber = '62' . substr($cleanNumber, 1);
        } elseif (substr($cleanNumber, 0, 2) !== '62' && strlen($cleanNumber) > 0) {
            $cleanNumber = '62' . $cleanNumber;
        }

        return $cleanNumber;
    }

    /**
     * Cek apakah kelas memiliki grup WA aktif
     * @param int $id_kelas
     * @return bool
     */
    public function hasActiveGrup($id_kelas)
    {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE id_kelas = :id_kelas AND is_active = 1");
        $this->db->bind(':id_kelas', $id_kelas);
        $result = $this->db->single();
        return ($result['total'] ?? 0) > 0;
    }
}
