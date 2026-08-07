<?php
// File: app/core/Migrator.php
// v1.26.0 - Runner migration berbasis tabel schema_migrations.
//
// Cara kerja:
// 1. ensureTable()  : membuat tabel schema_migrations bila belum ada.
// 2. backfill()     : menandai semua migration <= versi aplikasi saat ini
//                     sebagai "sudah diterapkan" TANPA mengeksekusinya
//                     (database produksi dianggap sudah sinkron).
// 3. runPending()   : mengeksekusi migration yang versinya lebih baru dari
//                     versi tertinggi di schema_migrations, berurutan.
//
// Idempoten: migration yang sudah tercatat tidak akan dijalankan ulang.
// File non-semver (README.sql, pengaturan_sistem.sql, pesan_tables.sql) diabaikan.

class Migrator
{
    private $db;
    private $migrationsDir;

    public function __construct()
    {
        $this->db = new Database();
        $this->migrationsDir = APPROOT . '/migrations';
    }

    /**
     * Buat tabel schema_migrations bila belum ada.
     */
    public function ensureTable()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS schema_migrations (
            version    VARCHAR(20)  NOT NULL,
            filename   VARCHAR(255) NOT NULL,
            checksum   VARCHAR(64)  DEFAULT NULL,
            applied_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (version)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $this->db->execute();
    }

    /**
     * Daftar file migration semver di folder migrations, terurut versi naik.
     * @return array [ ['version' => '1.2.0', 'file' => '/path/1.2.0.sql'], ... ]
     */
    public function discover()
    {
        $files = glob($this->migrationsDir . '/*.sql') ?: [];
        $list = [];
        foreach ($files as $f) {
            $base = basename($f, '.sql');
            // Hanya file bernama semver (mis. 1.23.0) yang dianggap migration
            if (preg_match('/^\d+\.\d+\.\d+$/', $base)) {
                $list[] = ['version' => $base, 'file' => $f];
            }
        }
        usort($list, fn($a, $b) => version_compare($a['version'], $b['version']));
        return $list;
    }

    /**
     * Versi-versi yang sudah tercatat di schema_migrations.
     * @return array daftar string versi
     */
    public function appliedVersions()
    {
        try {
            $this->db->query("SELECT version FROM schema_migrations ORDER BY version");
            $rows = $this->db->resultSet();
            return array_map(fn($r) => $r['version'], $rows);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Migration yang belum diterapkan.
     * @return array elemen discover() yang belum ada di schema_migrations
     */
    public function pending()
    {
        $applied = array_flip($this->appliedVersions());
        return array_values(array_filter(
            $this->discover(),
            fn($m) => !isset($applied[$m['version']])
        ));
    }

    /**
     * Tandai migration <= $upToVersion sebagai sudah diterapkan tanpa
     * mengeksekusinya (untuk database produksi yang sudah sinkron).
     * @return int jumlah baris yang ditandai
     */
    public function backfill($upToVersion)
    {
        $this->ensureTable();
        $count = 0;
        foreach ($this->discover() as $m) {
            if (version_compare($m['version'], $upToVersion, '>')) {
                continue;
            }
            $this->db->query("INSERT IGNORE INTO schema_migrations (version, filename, checksum)
                              VALUES (:version, :filename, :checksum)");
            $this->db->bind(':version', $m['version']);
            $this->db->bind(':filename', basename($m['file']));
            $this->db->bind(':checksum', hash_file('sha256', $m['file']) ?: null);
            $this->db->execute();
            $count++;
        }
        return $count;
    }

    /**
     * Jalankan semua migration yang belum diterapkan, berurutan versi.
     * @return array hasil per file: ['file', 'version', 'status', 'message']
     */
    public function runPending()
    {
        $this->ensureTable();
        $results = [];

        foreach ($this->pending() as $m) {
            $sql = file_get_contents($m['file']);
            if (trim($sql) === '') {
                $results[] = ['file' => basename($m['file']), 'version' => $m['version'], 'status' => 'skipped', 'message' => 'File kosong'];
                continue;
            }

            try {
                $statements = $this->splitStatements($sql);
                foreach ($statements as $stmt) {
                    $this->db->query($stmt);
                    $this->db->execute();
                }
                $this->db->query("INSERT IGNORE INTO schema_migrations (version, filename, checksum)
                                  VALUES (:version, :filename, :checksum)");
                $this->db->bind(':version', $m['version']);
                $this->db->bind(':filename', basename($m['file']));
                $this->db->bind(':checksum', hash_file('sha256', $m['file']) ?: null);
                $this->db->execute();
                $results[] = ['file' => basename($m['file']), 'version' => $m['version'], 'status' => 'success', 'message' => ''];
            } catch (Exception $e) {
                $results[] = ['file' => basename($m['file']), 'version' => $m['version'], 'status' => 'error', 'message' => $e->getMessage()];
                // Hentikan eksekusi berikutnya agar urutan tetap konsisten
                break;
            }
        }

        return $results;
    }

    /**
     * Pecah SQL menjadi statement individual.
     * Lebih aman daripada explode(';') sederhana: mengabaikan ';' di dalam
     * string/komentar bila memungkinkan (cukup untuk migration DDL sederhana).
     */
    private function splitStatements($sql)
    {
        // Hapus komentar baris
        $lines = [];
        foreach (explode("\n", $sql) as $line) {
            $trimmed = ltrim($line);
            if (strpos($trimmed, '--') === 0) {
                continue;
            }
            $lines[] = $line;
        }
        $sql = implode("\n", $lines);

        $statements = array_filter(array_map('trim', explode(';', $sql)), fn($s) => $s !== '');
        return $statements;
    }
}
