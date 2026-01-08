<?php

class RPPTemplate_model {
    private $db;

    public function __construct() {
        require_once APPROOT . '/app/core/Database.php';
        $this->db = new Database;
    }

    // ============ SECTION METHODS ============
    
    public function getAllSections($activeOnly = true) {
        $sql = "SELECT * FROM rpp_template_section";
        if ($activeOnly) $sql .= " WHERE is_active = 1";
        $sql .= " ORDER BY urutan ASC";
        $this->db->query($sql);
        return $this->db->resultSet();
    }

    public function getSectionById($id_section) {
        $this->db->query("SELECT * FROM rpp_template_section WHERE id_section = :id");
        $this->db->bind('id', $id_section);
        return $this->db->single();
    }

    public function tambahSection($data) {
        $this->db->query("INSERT INTO rpp_template_section (kode_section, nama_section, urutan, is_active) 
                          VALUES (:kode, :nama, :urutan, :active)");
        $this->db->bind('kode', $data['kode_section']);
        $this->db->bind('nama', $data['nama_section']);
        $this->db->bind('urutan', $data['urutan'] ?? 0);
        $this->db->bind('active', $data['is_active'] ?? 1);
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    public function updateSection($id_section, $data) {
        $this->db->query("UPDATE rpp_template_section SET 
                          kode_section = :kode, nama_section = :nama, urutan = :urutan, is_active = :active
                          WHERE id_section = :id");
        $this->db->bind('id', $id_section);
        $this->db->bind('kode', $data['kode_section']);
        $this->db->bind('nama', $data['nama_section']);
        $this->db->bind('urutan', $data['urutan'] ?? 0);
        $this->db->bind('active', $data['is_active'] ?? 1);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function hapusSection($id_section) {
        $this->db->query("DELETE FROM rpp_template_section WHERE id_section = :id");
        $this->db->bind('id', $id_section);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function toggleSection($id_section) {
        $this->db->query("UPDATE rpp_template_section SET is_active = NOT is_active WHERE id_section = :id");
        $this->db->bind('id', $id_section);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // ============ FIELD METHODS ============

    public function getFieldsBySection($id_section, $activeOnly = true) {
        $sql = "SELECT * FROM rpp_template_field WHERE id_section = :id";
        if ($activeOnly) $sql .= " AND is_active = 1";
        $sql .= " ORDER BY urutan ASC";
        $this->db->query($sql);
        $this->db->bind('id', $id_section);
        return $this->db->resultSet();
    }

    public function getFieldById($id_field) {
        $this->db->query("SELECT f.*, s.kode_section, s.nama_section 
                          FROM rpp_template_field f
                          JOIN rpp_template_section s ON f.id_section = s.id_section
                          WHERE f.id_field = :id");
        $this->db->bind('id', $id_field);
        return $this->db->single();
    }

    public function getAllFields($activeOnly = true) {
        $sql = "SELECT f.*, s.kode_section, s.nama_section 
                FROM rpp_template_field f
                JOIN rpp_template_section s ON f.id_section = s.id_section";
        if ($activeOnly) $sql .= " WHERE f.is_active = 1 AND s.is_active = 1";
        $sql .= " ORDER BY s.urutan ASC, f.urutan ASC";
        $this->db->query($sql);
        return $this->db->resultSet();
    }

    public function tambahField($data) {
        $this->db->query("INSERT INTO rpp_template_field 
                          (id_section, nama_field, kode_field, tipe_input, placeholder, urutan, is_required, is_active) 
                          VALUES (:section, :nama, :kode, :tipe, :placeholder, :urutan, :required, :active)");
        $this->db->bind('section', $data['id_section']);
        $this->db->bind('nama', $data['nama_field']);
        $this->db->bind('kode', $data['kode_field']);
        $this->db->bind('tipe', $data['tipe_input'] ?? 'textarea');
        $this->db->bind('placeholder', $data['placeholder'] ?? '');
        $this->db->bind('urutan', $data['urutan'] ?? 0);
        $this->db->bind('required', $data['is_required'] ?? 0);
        $this->db->bind('active', $data['is_active'] ?? 1);
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    public function updateField($id_field, $data) {
        $this->db->query("UPDATE rpp_template_field SET 
                          id_section = :section, nama_field = :nama, kode_field = :kode, 
                          tipe_input = :tipe, placeholder = :placeholder, urutan = :urutan, 
                          is_required = :required, is_active = :active
                          WHERE id_field = :id");
        $this->db->bind('id', $id_field);
        $this->db->bind('section', $data['id_section']);
        $this->db->bind('nama', $data['nama_field']);
        $this->db->bind('kode', $data['kode_field']);
        $this->db->bind('tipe', $data['tipe_input'] ?? 'textarea');
        $this->db->bind('placeholder', $data['placeholder'] ?? '');
        $this->db->bind('urutan', $data['urutan'] ?? 0);
        $this->db->bind('required', $data['is_required'] ?? 0);
        $this->db->bind('active', $data['is_active'] ?? 1);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function hapusField($id_field) {
        $this->db->query("DELETE FROM rpp_template_field WHERE id_field = :id");
        $this->db->bind('id', $id_field);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function toggleField($id_field) {
        $this->db->query("UPDATE rpp_template_field SET is_active = NOT is_active WHERE id_field = :id");
        $this->db->bind('id', $id_field);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // ============ RPP DATA METHODS ============

    public function saveRPPData($id_rpp, $id_field, $nilai) {
        $this->db->query("INSERT INTO rpp_data (id_rpp, id_field, nilai) 
                          VALUES (:rpp, :field, :nilai)
                          ON DUPLICATE KEY UPDATE nilai = :nilai2");
        $this->db->bind('rpp', $id_rpp);
        $this->db->bind('field', $id_field);
        $this->db->bind('nilai', $nilai);
        $this->db->bind('nilai2', $nilai);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getRPPData($id_rpp) {
        $this->db->query("SELECT d.*, f.nama_field, f.kode_field, f.tipe_input, s.kode_section, s.nama_section
                          FROM rpp_data d
                          JOIN rpp_template_field f ON d.id_field = f.id_field
                          JOIN rpp_template_section s ON f.id_section = s.id_section
                          WHERE d.id_rpp = :rpp
                          ORDER BY s.urutan ASC, f.urutan ASC");
        $this->db->bind('rpp', $id_rpp);
        return $this->db->resultSet();
    }

    public function getRPPDataByField($id_rpp, $id_field) {
        $this->db->query("SELECT * FROM rpp_data WHERE id_rpp = :rpp AND id_field = :field");
        $this->db->bind('rpp', $id_rpp);
        $this->db->bind('field', $id_field);
        return $this->db->single();
    }

    // Get full template with sections and fields for form rendering
    public function getFullTemplate() {
        $sections = $this->getAllSections(true);
        $result = [];
        foreach ($sections as $section) {
            $fields = $this->getFieldsBySection($section['id_section'], true);
            $result[] = [
                'section' => $section,
                'fields' => $fields
            ];
        }
        return $result;
    }

    // Get full RPP data grouped by section
    public function getFullRPPData($id_rpp) {
        $template = $this->getFullTemplate();
        $rppData = $this->getRPPData($id_rpp);
        
        // Index rpp data by field id
        $dataByField = [];
        foreach ($rppData as $d) {
            $dataByField[$d['id_field']] = $d['nilai'];
        }
        
        // Merge with template
        foreach ($template as &$section) {
            foreach ($section['fields'] as &$field) {
                $field['nilai'] = $dataByField[$field['id_field']] ?? '';
            }
        }
        return $template;
    }
}
