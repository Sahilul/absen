<?php
/**
 * File: app/models/AiChat_model.php
 * CRUD conversations & messages dengan ownership check.
 * Pattern mengikuti app/core/Database.php
 */
class AiChat_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // =========================================
    // CONVERSATIONS
    // =========================================

    /**
     * List conversation milik user (urut updated_at DESC).
     * Include jumlah pesan & preview pesan terakhir.
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getConversations($userId, $limit = 50, $offset = 0)
    {
        $this->db->query('
            SELECT c.*,
                   (SELECT COUNT(*) FROM ai_chat_messages WHERE id_conversation = c.id) AS jumlah_pesan,
                   (SELECT SUBSTRING(content, 1, 80) FROM ai_chat_messages WHERE id_conversation = c.id ORDER BY created_at DESC LIMIT 1) AS preview
            FROM ai_chat_conversations c
            WHERE c.id_user = :id_user
            ORDER BY c.updated_at DESC
            LIMIT :lim OFFSET :ofs
        ');
        $this->db->bind(':id_user', $userId);
        $this->db->bind(':lim', (int) $limit);
        $this->db->bind(':ofs', (int) $offset);
        return $this->db->resultSet();
    }

    /**
     * Get 1 conversation + cek kepemilikan.
     *
     * @param int $id
     * @param int $userId
     * @return object|false
     */
    public function getConversation($id, $userId)
    {
        $this->db->query('SELECT * FROM ai_chat_conversations WHERE id = :id AND id_user = :id_user');
        $this->db->bind(':id', $id);
        $this->db->bind(':id_user', $userId);
        return $this->db->single();
    }

    /**
     * Buat conversation baru.
     *
     * @param int    $userId
     * @param string $judul
     * @return int ID conversation baru
     */
    public function createConversation($userId, $judul = 'Percakapan Baru')
    {
        $this->db->query('INSERT INTO ai_chat_conversations (id_user, judul) VALUES (:id_user, :judul)');
        $this->db->bind(':id_user', $userId);
        $this->db->bind(':judul', $judul);
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Hapus conversation (cascade ke messages via FK).
     *
     * @param int $id
     * @param int $userId
     * @return bool
     */
    public function deleteConversation($id, $userId)
    {
        $this->db->query('DELETE FROM ai_chat_conversations WHERE id = :id AND id_user = :id_user');
        $this->db->bind(':id', $id);
        $this->db->bind(':id_user', $userId);
        return $this->db->execute();
    }

    /**
     * Update judul conversation.
     *
     * @param int    $id
     * @param string $judul
     * @return bool
     */
    public function updateJudul($id, $judul)
    {
        $this->db->query('UPDATE ai_chat_conversations SET judul = :judul WHERE id = :id');
        $this->db->bind(':judul', $judul);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Touch updated_at agar urutan riwayat benar.
     *
     * @param int $id
     * @return bool
     */
    public function touchConversation($id)
    {
        $this->db->query('UPDATE ai_chat_conversations SET updated_at = NOW() WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // =========================================
    // MESSAGES
    // =========================================

    /**
     * Get pesan dalam satu conversation (untuk context).
     *
     * @param int $conversationId
     * @param int $limit
     * @return array
     */
    public function getMessages($conversationId, $limit = 50)
    {
        $this->db->query('
            SELECT * FROM ai_chat_messages
            WHERE id_conversation = :id_conversation
            ORDER BY created_at ASC
            LIMIT :lim
        ');
        $this->db->bind(':id_conversation', $conversationId);
        $this->db->bind(':lim', (int) $limit);
        return $this->db->resultSet();
    }

    /**
     * Get pesan terakhir untuk context AI (urut DESC, ambil N terakhir, lalu balik).
     *
     * @param int $conversationId
     * @param int $limit
     * @return array
     */
    public function getRecentMessages($conversationId, $limit = 20)
    {
        $this->db->query('
            SELECT * FROM (
                SELECT * FROM ai_chat_messages
                WHERE id_conversation = :id_conversation
                ORDER BY created_at DESC
                LIMIT :lim
            ) sub ORDER BY created_at ASC
        ');
        $this->db->bind(':id_conversation', $conversationId);
        $this->db->bind(':lim', (int) $limit);
        return $this->db->resultSet();
    }

    /**
     * Simpan pesan.
     *
     * @param int         $conversationId
     * @param string      $role       'user' atau 'assistant'
     * @param string      $content
     * @param string|null $attachment  JSON metadata attachment (optional)
     * @return int ID pesan baru
     */
    public function addMessage($conversationId, $role, $content, $attachment = null)
    {
        $this->db->query('INSERT INTO ai_chat_messages (id_conversation, role, content, attachment) VALUES (:id_conversation, :role, :content, :attachment)');
        $this->db->bind(':id_conversation', $conversationId);
        $this->db->bind(':role', $role);
        $this->db->bind(':content', $content);
        $this->db->bind(':attachment', $attachment);
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Hitung pesan user hari ini (untuk rate limiting).
     * Reset pada 00:00 Asia/Jakarta.
     *
     * @param int $userId
     * @return int
     */
    public function countTodayMessages($userId)
    {
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM ai_chat_messages m
            JOIN ai_chat_conversations c ON m.id_conversation = c.id
            WHERE c.id_user = :id_user
              AND m.role = 'user'
              AND m.created_at >= CONVERT_TZ(CURDATE(), '+00:00', '+07:00')
        ");
        $this->db->bind(':id_user', $userId);
        $row = $this->db->single();
        return $row ? (int) $row->total : 0;
    }

    /**
     * Hitung jumlah pesan dalam conversation.
     *
     * @param int $conversationId
     * @return int
     */
    public function countMessages($conversationId)
    {
        $this->db->query('SELECT COUNT(*) AS total FROM ai_chat_messages WHERE id_conversation = :id_conversation');
        $this->db->bind(':id_conversation', $conversationId);
        $row = $this->db->single();
        return $row ? (int) $row['total'] : 0;
    }

    /**
     * Auto-generate judul dari pesan pertama user.
     * Potong ~50 karakter di batas kata terakhir.
     *
     * @param int    $conversationId
     * @param string $firstMessage
     * @return void
     */
    public function autoSetJudul($conversationId, $firstMessage)
    {
        $judul = trim($firstMessage);
        // Hapus newline
        $judul = preg_replace('/\s+/', ' ', $judul);

        if (mb_strlen($judul) > 50) {
            $judul = mb_substr($judul, 0, 50);
            // Potong di batas kata terakhir
            $lastSpace = mb_strrpos($judul, ' ');
            if ($lastSpace !== false && $lastSpace > 20) {
                $judul = mb_substr($judul, 0, $lastSpace);
            }
            $judul .= '…';
        }

        if (empty($judul)) {
            $judul = 'Percakapan Baru';
        }

        $this->updateJudul($conversationId, $judul);
    }
}
