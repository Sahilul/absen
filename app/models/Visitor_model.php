<?php

/**
 * Visitor_model
 * Model untuk tracking dan menghitung pengunjung website
 */
class Visitor_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    /**
     * Record kunjungan baru
     * @param string $pageUrl URL halaman yang dikunjungi
     * @return bool
     */
    public function recordVisit($pageUrl = '/')
    {
        $ipAddress = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $visitDate = date('Y-m-d');
        $visitTime = date('H:i:s');

        // Cek apakah IP ini sudah berkunjung hari ini (untuk unique visitor)
        $isNewVisitor = !$this->hasVisitedToday($ipAddress);

        // Simpan data kunjungan
        $this->db->query("INSERT INTO website_visitors (ip_address, user_agent, page_url, visit_date, visit_time) 
                          VALUES (:ip, :ua, :url, :vdate, :vtime)");
        $this->db->bind(':ip', $ipAddress);
        $this->db->bind(':ua', $userAgent);
        $this->db->bind(':url', $pageUrl);
        $this->db->bind(':vdate', $visitDate);
        $this->db->bind(':vtime', $visitTime);
        $this->db->execute();

        // Update statistik harian
        $this->updateDailyStats($visitDate, $isNewVisitor);

        return true;
    }

    /**
     * Cek apakah IP sudah berkunjung hari ini
     */
    private function hasVisitedToday($ipAddress)
    {
        $today = date('Y-m-d');
        $this->db->query("SELECT COUNT(*) as count FROM website_visitors 
                          WHERE ip_address = :ip AND visit_date = :vdate");
        $this->db->bind(':ip', $ipAddress);
        $this->db->bind(':vdate', $today);
        $result = $this->db->single();
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Update statistik harian
     */
    private function updateDailyStats($date, $isNewVisitor)
    {
        // Cek apakah sudah ada record untuk tanggal ini
        $this->db->query("SELECT id FROM visitor_stats WHERE stat_date = :date");
        $this->db->bind(':date', $date);
        $existing = $this->db->single();

        if ($existing) {
            // Update existing record
            $uniqueIncrement = $isNewVisitor ? 1 : 0;
            $this->db->query("UPDATE visitor_stats 
                              SET total_hits = total_hits + 1, 
                                  unique_visitors = unique_visitors + :unique_inc 
                              WHERE stat_date = :date");
            $this->db->bind(':unique_inc', $uniqueIncrement);
            $this->db->bind(':date', $date);
        } else {
            // Insert new record
            $this->db->query("INSERT INTO visitor_stats (stat_date, total_hits, unique_visitors) 
                              VALUES (:date, 1, 1)");
            $this->db->bind(':date', $date);
        }
        $this->db->execute();
    }

    /**
     * Mendapatkan total semua kunjungan (total hits)
     */
    public function getTotalHits()
    {
        $this->db->query("SELECT COALESCE(SUM(total_hits), 0) as total FROM visitor_stats");
        $result = $this->db->single();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Mendapatkan total pengunjung unik
     */
    public function getTotalUniqueVisitors()
    {
        $this->db->query("SELECT COALESCE(SUM(unique_visitors), 0) as total FROM visitor_stats");
        $result = $this->db->single();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Mendapatkan pengunjung hari ini
     */
    public function getTodayVisitors()
    {
        $today = date('Y-m-d');
        $this->db->query("SELECT COALESCE(unique_visitors, 0) as today FROM visitor_stats WHERE stat_date = :date");
        $this->db->bind(':date', $today);
        $result = $this->db->single();
        return (int) ($result['today'] ?? 0);
    }

    /**
     * Mendapatkan hits hari ini
     */
    public function getTodayHits()
    {
        $today = date('Y-m-d');
        $this->db->query("SELECT COALESCE(total_hits, 0) as today FROM visitor_stats WHERE stat_date = :date");
        $this->db->bind(':date', $today);
        $result = $this->db->single();
        return (int) ($result['today'] ?? 0);
    }

    /**
     * Mendapatkan pengunjung yang sedang online (dalam 5 menit terakhir)
     */
    public function getOnlineVisitors($minutes = 5)
    {
        $threshold = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
        $this->db->query("SELECT COUNT(DISTINCT ip_address) as online 
                          FROM website_visitors 
                          WHERE CONCAT(visit_date, ' ', visit_time) >= :threshold");
        $this->db->bind(':threshold', $threshold);
        $result = $this->db->single();
        return (int) ($result['online'] ?? 0);
    }

    /**
     * Mendapatkan semua statistik untuk ditampilkan
     */
    public function getVisitorStats()
    {
        return [
            'total_hits' => $this->getTotalHits(),
            'total_visitors' => $this->getTotalUniqueVisitors(),
            'today_visitors' => $this->getTodayVisitors(),
            'today_hits' => $this->getTodayHits(),
            'online' => $this->getOnlineVisitors()
        ];
    }

    /**
     * Mendapatkan IP client dengan benar
     */
    private function getClientIP()
    {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
        return trim($ip);
    }
}
