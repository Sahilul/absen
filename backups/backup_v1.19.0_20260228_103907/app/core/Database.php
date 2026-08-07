<?php

// File: app/core/Database.php
class Database {
    private $host;
    private $user;
    private $pass;
    private $db_name;

    private $dbh;
    private $stmt;

    public function __construct()
    {
        $this->host = DB_HOST;
        $this->user = DB_USER;
        $this->pass = DB_PASS;
        $this->db_name = DB_NAME;
        
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name;
        $options = [
            PDO::ATTR_PERSISTENT => false, // Disable persistent connection untuk performa
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false, // Native prepared statements
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function query($query)
    {
        $this->stmt = $this->dbh->prepare($query);
    }

        public function bind($param, $value, $type = null)
    {
        // List of date/datetime fields that should be NULL if empty (safety check)
        $dateFields = [':tanggal_lahir', ':ayah_tanggal_lahir', ':ibu_tanggal_lahir', ':wali_tanggal_lahir'];
        
        // Convert empty strings to NULL for date/datetime fields
        if (in_array($param, $dateFields) && $value === '') {
            $value = null;
        }

        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }
    public function execute()
    {
        return $this->stmt->execute();
    }

    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    /**
     * FUNGSI BARU: Mengambil ID terakhir yang di-insert.
     * @return string ID terakhir.
     */
    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }

    // Tambahan: dukungan transaksi untuk operasi batch aman (dengan guard)
    public function beginTransaction() {
        try {
            if (!$this->dbh) return false;
            return $this->dbh->beginTransaction();
        } catch (Throwable $e) { return false; }
    }

    public function commit() {
        try {
            if (!$this->dbh) return false;
            if ($this->dbh->inTransaction()) {
                return $this->dbh->commit();
            }
            return false;
        } catch (Throwable $e) { return false; }
    }

    public function rollBack() {
        try {
            if (!$this->dbh) return false;
            if ($this->dbh->inTransaction()) {
                return $this->dbh->rollBack();
            }
            return false;
        } catch (Throwable $e) { return false; }
    }

    public function inTransaction() {
        try { return $this->dbh ? $this->dbh->inTransaction() : false; } catch (Throwable $e) { return false; }
    }

    public function toggleForeignKeyChecks($enable) {
        try {
            $this->query('SET FOREIGN_KEY_CHECKS=' . ($enable ? '1' : '0'));
            return $this->execute();
        } catch (Throwable $e) { return false; }
    }
}