package models

import "time"

// AiChatConversation maps to `ai_chat_conversations` table.
type AiChatConversation struct {
	ID        uint      `gorm:"primaryKey;column:id" json:"id"`
	IDUser    uint      `gorm:"column:id_user;index" json:"id_user"`
	Judul     string    `gorm:"column:judul" json:"judul"`
	CreatedAt time.Time `gorm:"column:created_at" json:"created_at"`
	UpdatedAt time.Time `gorm:"column:updated_at" json:"updated_at"`

	// Computed fields (not in DB, filled by queries)
	JumlahPesan int    `gorm:"-" json:"jumlah_pesan,omitempty"`
	Preview     string `gorm:"-" json:"preview,omitempty"`
}

func (AiChatConversation) TableName() string {
	return "ai_chat_conversations"
}

// AiChatMessage maps to `ai_chat_messages` table.
type AiChatMessage struct {
	ID               uint      `gorm:"primaryKey;column:id" json:"id"`
	IDConversation   uint      `gorm:"column:id_conversation;index" json:"id_conversation"`
	Role             string    `gorm:"column:role;type:enum('user','assistant')" json:"role"`
	Content          string    `gorm:"column:content;type:mediumtext" json:"content"`
	Attachment       *string   `gorm:"column:attachment;type:text" json:"attachment,omitempty"`
	CreatedAt        time.Time `gorm:"column:created_at" json:"created_at"`
}

func (AiChatMessage) TableName() string {
	return "ai_chat_messages"
}

// AiChatUsage maps to `ai_chat_usage` table.
type AiChatUsage struct {
	ID               uint      `gorm:"primaryKey;column:id" json:"id"`
	IDConversation   uint      `gorm:"column:id_conversation;index" json:"id_conversation"`
	IDUser           uint      `gorm:"column:id_user;index" json:"id_user"`
	IDMessage        *uint     `gorm:"column:id_message" json:"id_message,omitempty"`
	Purpose          string    `gorm:"column:purpose;type:enum('chat','title')" json:"purpose"`
	Model            string    `gorm:"column:model" json:"model"`
	PromptTokens     int       `gorm:"column:prompt_tokens" json:"prompt_tokens"`
	CompletionTokens int       `gorm:"column:completion_tokens" json:"completion_tokens"`
	TotalTokens      int       `gorm:"column:total_tokens" json:"total_tokens"`
	LatencyMs        int       `gorm:"column:latency_ms" json:"latency_ms"`
	Status           string    `gorm:"column:status" json:"status"`
	ErrorMessage     *string   `gorm:"column:error_message" json:"error_message,omitempty"`
	CreatedAt        time.Time `gorm:"column:created_at" json:"created_at"`
}

func (AiChatUsage) TableName() string {
	return "ai_chat_usage"
}
