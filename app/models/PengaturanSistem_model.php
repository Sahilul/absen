<?php

class PengaturanSistem_model
{
    private $db;
    private static $cache = null;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getAll()
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $this->db->query("SELECT key_name, value FROM pengaturan_sistem ORDER BY id ASC");
        $results = $this->db->resultSet();

        $settings = [];
        foreach ($results as $row) {
            $settings[$row['key_name']] = $row['value'];
        }

        self::$cache = $settings;
        return $settings;
    }

    public function get($key, $default = null)
    {
        $all = $this->getAll();
        return $all[$key] ?? $default;
    }

    public function set($key, $value)
    {
        $this->db->query("INSERT INTO pengaturan_sistem (key_name, value) VALUES (:key, :value) 
                          ON DUPLICATE KEY UPDATE value = :value2");
        $this->db->bind(':key', $key);
        $this->db->bind(':value', $value);
        $this->db->bind(':value2', $value);
        $this->db->execute();

        self::$cache = null;
    }

    public function updateMultiple($data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function getAllWithDescription()
    {
        $this->db->query("SELECT * FROM pengaturan_sistem ORDER BY id");
        return $this->db->resultSet();
    }

    public static function clearCache()
    {
        self::$cache = null;
    }
}
