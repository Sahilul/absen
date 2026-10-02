<?php
/**
 * File: app/models/AiMonitoring_model.php
 * Model untuk dashboard monitoring penggunaan AI Chat.
 * Membaca data dari tabel ai_chat_usage, ai_chat_conversations, ai_chat_messages, users, guru.
 */
class AiMonitoring_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Ringkasan statistik utama (total request, token, biaya, error rate, dll).
     *
     * @param string $startDate  Format Y-m-d
     * @param string $endDate    Format Y-m-d
     * @return array
     */
    public function getSummary(string $startDate, string $endDate): array
    {
        $this->db->query("
            SELECT
                COUNT(*) AS total_requests,
                SUM(CASE WHEN purpose = 'chat' THEN 1 ELSE 0 END) AS chat_requests,
                SUM(CASE WHEN purpose = 'title' THEN 1 ELSE 0 END) AS title_requests,
                SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) AS error_count,
                COALESCE(SUM(prompt_tokens), 0) AS total_prompt_tokens,
                COALESCE(SUM(completion_tokens), 0) AS total_completion_tokens,
                COALESCE(SUM(total_tokens), 0) AS total_tokens,
                ROUND(AVG(CASE WHEN status = 'success' AND purpose = 'chat' THEN latency_ms END)) AS avg_latency_ms,
                COUNT(DISTINCT id_user) AS unique_users,
                COUNT(DISTINCT id_conversation) AS unique_conversations
            FROM ai_chat_usage
            WHERE DATE(created_at) BETWEEN :start AND :end
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        $row = $this->db->single();

        return $row ?: [
            'total_requests' => 0, 'chat_requests' => 0, 'title_requests' => 0,
            'error_count' => 0, 'total_prompt_tokens' => 0, 'total_completion_tokens' => 0,
            'total_tokens' => 0, 'avg_latency_ms' => 0, 'unique_users' => 0, 'unique_conversations' => 0,
        ];
    }

    /**
     * Penggunaan per user (top users).
     */
    public function getUsagePerUser(string $startDate, string $endDate, int $limit = 20): array
    {
        $this->db->query("
            SELECT
                u.id_user,
                u.id_user AS user_id,
                COALESCE(g.nama_guru, u.username) AS nama,
                u.role,
                COUNT(*) AS total_requests,
                SUM(CASE WHEN purpose = 'chat' THEN 1 ELSE 0 END) AS chat_requests,
                COALESCE(SUM(total_tokens), 0) AS total_tokens,
                COALESCE(SUM(prompt_tokens), 0) AS prompt_tokens,
                COALESCE(SUM(completion_tokens), 0) AS completion_tokens,
                COUNT(DISTINCT acu.id_conversation) AS conversations,
                ROUND(AVG(CASE WHEN acu.status = 'success' AND purpose = 'chat' THEN latency_ms END)) AS avg_latency
            FROM ai_chat_usage acu
            JOIN users u ON acu.id_user = u.id_user
            LEFT JOIN guru g ON u.role = 'guru' AND u.id_ref = g.id_guru
            WHERE DATE(acu.created_at) BETWEEN :start AND :end
            GROUP BY u.id_user
            ORDER BY total_tokens DESC
            LIMIT :lim
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        $this->db->bind(':lim', $limit);
        return $this->db->resultSet();
    }

    /**
     * Tren harian (untuk chart).
     */
    public function getDailyTrend(string $startDate, string $endDate): array
    {
        $this->db->query("
            SELECT
                DATE(created_at) AS tanggal,
                COUNT(*) AS requests,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS success_count,
                SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) AS error_count,
                COALESCE(SUM(total_tokens), 0) AS tokens,
                COALESCE(SUM(prompt_tokens), 0) AS prompt_tokens,
                COALESCE(SUM(completion_tokens), 0) AS completion_tokens,
                COUNT(DISTINCT id_user) AS users,
                ROUND(AVG(CASE WHEN status = 'success' AND purpose = 'chat' THEN latency_ms END)) AS avg_latency
            FROM ai_chat_usage
            WHERE DATE(created_at) BETWEEN :start AND :end
            GROUP BY DATE(created_at)
            ORDER BY tanggal ASC
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        return $this->db->resultSet();
    }

    /**
     * Topik/keyword yang sering dibahas.
     * Mengambil judul conversation sebagai proxy topik.
     */
    public function getTopKeywords(string $startDate, string $endDate, int $limit = 30): array
    {
        $this->db->query("
            SELECT
                c.judul,
                c.id AS id_conversation,
                COUNT(acu.id) AS usage_count,
                COALESCE(SUM(acu.total_tokens), 0) AS total_tokens,
                c.created_at
            FROM ai_chat_usage acu
            JOIN ai_chat_conversations c ON acu.id_conversation = c.id
            WHERE DATE(acu.created_at) BETWEEN :start AND :end
              AND acu.purpose = 'chat'
              AND c.judul IS NOT NULL
              AND c.judul != ''
              AND c.judul != 'Percakapan Baru'
            GROUP BY c.id
            ORDER BY usage_count DESC
            LIMIT :lim
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        $this->db->bind(':lim', $limit);
        return $this->db->resultSet();
    }

    /**
     * Percakapan terbaru (untuk tabel).
     */
    public function getRecentConversations(string $startDate, string $endDate, int $limit = 50): array
    {
        $this->db->query("
            SELECT
                c.id AS id_conversation,
                c.judul,
                COALESCE(g.nama_guru, u.username) AS nama_user,
                u.role,
                COUNT(acu.id) AS request_count,
                COALESCE(SUM(acu.total_tokens), 0) AS total_tokens,
                ROUND(AVG(CASE WHEN acu.status = 'success' THEN acu.latency_ms END)) AS avg_latency,
                MAX(acu.created_at) AS last_activity,
                c.created_at
            FROM ai_chat_conversations c
            JOIN users u ON c.id_user = u.id_user
            LEFT JOIN guru g ON u.role = 'guru' AND u.id_ref = g.id_guru
            JOIN ai_chat_usage acu ON acu.id_conversation = c.id
            WHERE DATE(acu.created_at) BETWEEN :start AND :end
            GROUP BY c.id
            ORDER BY last_activity DESC
            LIMIT :lim
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        $this->db->bind(':lim', $limit);
        return $this->db->resultSet();
    }

    /**
     * Detail satu percakapan (pesan + usage).
     */
    public function getConversationDetail(int $conversationId): array
    {
        // Info conversation
        $this->db->query("
            SELECT c.*, COALESCE(g.nama_guru, u.username) AS nama_user, u.role
            FROM ai_chat_conversations c
            JOIN users u ON c.id_user = u.id_user
            LEFT JOIN guru g ON u.role = 'guru' AND u.id_ref = g.id_guru
            WHERE c.id = :id
        ");
        $this->db->bind(':id', $conversationId);
        $conversation = $this->db->single();

        // Pesan
        $this->db->query("
            SELECT * FROM ai_chat_messages
            WHERE id_conversation = :id
            ORDER BY created_at ASC
        ");
        $this->db->bind(':id', $conversationId);
        $messages = $this->db->resultSet();

        // Usage logs
        $this->db->query("
            SELECT * FROM ai_chat_usage
            WHERE id_conversation = :id
            ORDER BY created_at ASC
        ");
        $this->db->bind(':id', $conversationId);
        $usage = $this->db->resultSet();

        return [
            'conversation' => $conversation,
            'messages'     => $messages,
            'usage'        => $usage,
        ];
    }

    /**
     * Statistik penggunaan file/attachment.
     */
    public function getFileUsage(string $startDate, string $endDate): array
    {
        $this->db->query("
            SELECT
                COUNT(*) AS total_with_attachment,
                COUNT(DISTINCT m.id_conversation) AS conversations_with_files
            FROM ai_chat_messages m
            WHERE m.attachment IS NOT NULL
              AND m.attachment != ''
              AND DATE(m.created_at) BETWEEN :start AND :end
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        return $this->db->single() ?: ['total_with_attachment' => 0, 'conversations_with_files' => 0];
    }

    /**
     * Statistik latency (distribusi).
     */
    public function getLatencyStats(string $startDate, string $endDate): array
    {
        $this->db->query("
            SELECT
                MIN(latency_ms) AS min_latency,
                MAX(latency_ms) AS max_latency,
                ROUND(AVG(latency_ms)) AS avg_latency,
                ROUND(AVG(CASE WHEN latency_ms > 0 THEN latency_ms END)) AS avg_latency_nonzero,
                COUNT(CASE WHEN latency_ms < 1000 THEN 1 END) AS under_1s,
                COUNT(CASE WHEN latency_ms BETWEEN 1000 AND 3000 THEN 1 END) AS between_1_3s,
                COUNT(CASE WHEN latency_ms BETWEEN 3001 AND 5000 THEN 1 END) AS between_3_5s,
                COUNT(CASE WHEN latency_ms > 5000 THEN 1 END) AS over_5s
            FROM ai_chat_usage
            WHERE status = 'success'
              AND purpose = 'chat'
              AND DATE(created_at) BETWEEN :start AND :end
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        return $this->db->single() ?: [];
    }

    /**
     * Breakdown per model.
     */
    public function getModelBreakdown(string $startDate, string $endDate): array
    {
        $this->db->query("
            SELECT
                COALESCE(model, 'unknown') AS model,
                COUNT(*) AS requests,
                COALESCE(SUM(total_tokens), 0) AS total_tokens,
                COALESCE(SUM(prompt_tokens), 0) AS prompt_tokens,
                COALESCE(SUM(completion_tokens), 0) AS completion_tokens,
                ROUND(AVG(CASE WHEN status = 'success' THEN latency_ms END)) AS avg_latency,
                SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) AS errors
            FROM ai_chat_usage
            WHERE DATE(created_at) BETWEEN :start AND :end
            GROUP BY model
            ORDER BY requests DESC
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        return $this->db->resultSet();
    }

    /**
     * Tren per jam (hourly heatmap data).
     */
    public function getHourlyTrend(string $startDate, string $endDate): array
    {
        $this->db->query("
            SELECT
                HOUR(created_at) AS jam,
                COUNT(*) AS requests,
                COUNT(DISTINCT id_user) AS users
            FROM ai_chat_usage
            WHERE DATE(created_at) BETWEEN :start AND :end
              AND purpose = 'chat'
            GROUP BY HOUR(created_at)
            ORDER BY jam ASC
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        return $this->db->resultSet();
    }

    /**
     * Error log terbaru.
     */
    public function getRecentErrors(string $startDate, string $endDate, int $limit = 20): array
    {
        $this->db->query("
            SELECT
                acu.*,
                COALESCE(g.nama_guru, u.username) AS nama_user
            FROM ai_chat_usage acu
            JOIN users u ON acu.id_user = u.id_user
            LEFT JOIN guru g ON u.role = 'guru' AND u.id_ref = g.id_guru
            WHERE acu.status = 'error'
              AND DATE(acu.created_at) BETWEEN :start AND :end
            ORDER BY acu.created_at DESC
            LIMIT :lim
        ");
        $this->db->bind(':start', $startDate);
        $this->db->bind(':end', $endDate);
        $this->db->bind(':lim', $limit);
        return $this->db->resultSet();
    }
}
