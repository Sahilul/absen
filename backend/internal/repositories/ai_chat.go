package repositories

import (
	"time"

	"github.com/sabilillah/backend/internal/models"
	"gorm.io/gorm"
)

type AiChatRepository struct {
	db *gorm.DB
}

func NewAiChatRepository(db *gorm.DB) *AiChatRepository {
	return &AiChatRepository{db: db}
}

// GetConversation returns a conversation if owned by the user.
func (r *AiChatRepository) GetConversation(id, userID uint) (*models.AiChatConversation, error) {
	var conv models.AiChatConversation
	result := r.db.Where("id = ? AND id_user = ?", id, userID).First(&conv)
	if result.Error != nil {
		return nil, result.Error
	}
	return &conv, nil
}

// GetConversations returns paginated conversations for a user.
func (r *AiChatRepository) GetConversations(userID uint, limit, offset int) ([]models.AiChatConversation, error) {
	var convs []models.AiChatConversation

	// Use raw query for subquery fields (jumlah_pesan, preview)
	result := r.db.Raw(`
		SELECT c.*,
			(SELECT COUNT(*) FROM ai_chat_messages WHERE id_conversation = c.id) AS jumlah_pesan,
			(SELECT SUBSTRING(content, 1, 80) FROM ai_chat_messages WHERE id_conversation = c.id ORDER BY created_at DESC LIMIT 1) AS preview
		FROM ai_chat_conversations c
		WHERE c.id_user = ?
		ORDER BY c.updated_at DESC
		LIMIT ? OFFSET ?
	`, userID, limit, offset).Scan(&convs)

	return convs, result.Error
}

// CreateConversation creates a new conversation.
func (r *AiChatRepository) CreateConversation(userID uint, judul string) (uint, error) {
	conv := models.AiChatConversation{
		IDUser: userID,
		Judul:  judul,
	}
	result := r.db.Create(&conv)
	return conv.ID, result.Error
}

// DeleteConversation deletes a conversation owned by the user.
func (r *AiChatRepository) DeleteConversation(id, userID uint) error {
	// Delete messages first (in case no FK cascade)
	r.db.Where("id_conversation = ?", id).Delete(&models.AiChatMessage{})
	// Delete usage logs
	r.db.Where("id_conversation = ?", id).Delete(&models.AiChatUsage{})
	// Delete conversation
	result := r.db.Where("id = ? AND id_user = ?", id, userID).Delete(&models.AiChatConversation{})
	return result.Error
}

// GetMessages returns all messages in a conversation.
func (r *AiChatRepository) GetMessages(conversationID uint, limit int) ([]models.AiChatMessage, error) {
	var msgs []models.AiChatMessage
	result := r.db.Where("id_conversation = ?", conversationID).
		Order("created_at ASC").
		Limit(limit).
		Find(&msgs)
	return msgs, result.Error
}

// GetRecentMessages returns the N most recent messages (for AI context).
func (r *AiChatRepository) GetRecentMessages(conversationID uint, limit int) ([]models.AiChatMessage, error) {
	var msgs []models.AiChatMessage
	result := r.db.Raw(`
		SELECT * FROM (
			SELECT * FROM ai_chat_messages
			WHERE id_conversation = ?
			ORDER BY created_at DESC
			LIMIT ?
		) sub ORDER BY created_at ASC
	`, conversationID, limit).Scan(&msgs)
	return msgs, result.Error
}

// AddMessage saves a message to a conversation.
func (r *AiChatRepository) AddMessage(conversationID uint, role, content string, attachment *string) (uint, error) {
	msg := models.AiChatMessage{
		IDConversation: conversationID,
		Role:           role,
		Content:        content,
		Attachment:     attachment,
	}
	result := r.db.Create(&msg)
	return msg.ID, result.Error
}

// CountTodayMessages counts user messages today (for rate limiting).
func (r *AiChatRepository) CountTodayMessages(userID uint) (int64, error) {
	var count int64
	result := r.db.Raw(`
		SELECT COUNT(*) 
		FROM ai_chat_messages m
		JOIN ai_chat_conversations c ON m.id_conversation = c.id
		WHERE c.id_user = ?
		  AND m.role = 'user'
		  AND m.created_at >= CONVERT_TZ(CURDATE(), '+00:00', '+07:00')
	`, userID).Scan(&count)
	return count, result.Error
}

// CountMessages counts messages in a conversation.
func (r *AiChatRepository) CountMessages(conversationID uint) (int64, error) {
	var count int64
	result := r.db.Model(&models.AiChatMessage{}).
		Where("id_conversation = ?", conversationID).
		Count(&count)
	return count, result.Error
}

// TouchConversation updates the updated_at timestamp.
func (r *AiChatRepository) TouchConversation(conversationID uint) error {
	return r.db.Model(&models.AiChatConversation{}).
		Where("id = ?", conversationID).
		Update("updated_at", time.Now()).Error
}

// UpdateJudul updates the conversation title.
func (r *AiChatRepository) UpdateJudul(conversationID uint, judul string) error {
	return r.db.Model(&models.AiChatConversation{}).
		Where("id = ?", conversationID).
		Update("judul", judul).Error
}

// LogUsage saves AI usage data.
func (r *AiChatRepository) LogUsage(usage *models.AiChatUsage) error {
	return r.db.Create(usage).Error
}
