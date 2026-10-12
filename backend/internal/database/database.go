package database

import (
	"fmt"
	"time"

	"github.com/rs/zerolog/log"
	"github.com/sabilillah/backend/internal/config"
	"gorm.io/driver/mysql"
	"gorm.io/gorm"
	"gorm.io/gorm/logger"
)

var DB *gorm.DB

func Connect(cfg *config.DatabaseConfig) (*gorm.DB, error) {
	dsn := fmt.Sprintf("%s:%s@tcp(%s:%d)/%s?charset=utf8mb4&parseTime=True&loc=Asia%%2FJakarta",
		cfg.User, cfg.Password, cfg.Host, cfg.Port, cfg.Name,
	)

	logLevel := logger.Silent
	if config.C.Server.Mode == "debug" {
		logLevel = logger.Info
	}

	db, err := gorm.Open(mysql.Open(dsn), &gorm.Config{
		Logger:                 logger.Default.LogMode(logLevel),
		SkipDefaultTransaction: true,
	})
	if err != nil {
		return nil, fmt.Errorf("failed to connect database: %w", err)
	}

	sqlDB, err := db.DB()
	if err != nil {
		return nil, fmt.Errorf("failed to get sql.DB: %w", err)
	}

	sqlDB.SetMaxOpenConns(cfg.MaxOpenConns)
	sqlDB.SetMaxIdleConns(cfg.MaxIdleConns)
	sqlDB.SetConnMaxLifetime(cfg.ConnMaxLifetime)

	// Ping to verify
	if err := sqlDB.Ping(); err != nil {
		return nil, fmt.Errorf("failed to ping database: %w", err)
	}

	DB = db
	log.Info().
		Str("host", cfg.Host).
		Str("database", cfg.Name).
		Msg("Database connected")

	return db, nil
}

func Close() {
	if DB != nil {
		sqlDB, err := DB.DB()
		if err == nil {
			sqlDB.Close()
		}
	}
}

// GetSetting reads a single value from pengaturan_sistem table.
// Returns defaultVal if key not found.
func GetSetting(key string, defaultVal string) string {
	var value string
	result := DB.Table("pengaturan_sistem").
		Select("value").
		Where("key_name = ?", key).
		Limit(1).
		Scan(&value)

	if result.Error != nil || result.RowsAffected == 0 {
		return defaultVal
	}
	return value
}

// AISettings holds cached AI configuration from pengaturan_sistem.
type AISettings struct {
	Enabled         bool
	Name            string
	BaseURL         string
	APIKey          string
	Model           string
	Temperature     float64
	MaxTokens       int
	MaxHistory      int
	DailyLimit      int
	SystemPrompt    string
	MaxContextChars int
	FileUpload      bool
	MaxFileSize     int
	CachedAt        time.Time
}

var cachedAISettings *AISettings

// GetAISettings returns AI settings from pengaturan_sistem, cached for 60 seconds.
func GetAISettings() *AISettings {
	if cachedAISettings != nil && time.Since(cachedAISettings.CachedAt) < 60*time.Second {
		return cachedAISettings
	}

	// Load all AI settings in one query
	type KV struct {
		KeyName string `gorm:"column:key_name"`
		Value   string `gorm:"column:value"`
	}
	var rows []KV
	DB.Table("pengaturan_sistem").
		Select("key_name, value").
		Where("key_name LIKE 'ai_chat_%'").
		Find(&rows)

	m := make(map[string]string)
	for _, r := range rows {
		m[r.KeyName] = r.Value
	}

	s := &AISettings{
		Enabled:         m["ai_chat_enabled"] == "1",
		Name:            getOrDefault(m, "ai_chat_name", "Pak Admin"),
		BaseURL:         getOrDefault(m, "ai_chat_base_url", ""),
		APIKey:          getOrDefault(m, "ai_chat_api_key", ""),
		Model:           getOrDefault(m, "ai_chat_model", "gpt-4o-mini"),
		Temperature:     parseFloat(getOrDefault(m, "ai_chat_temperature", "0.7")),
		MaxTokens:       parseInt(getOrDefault(m, "ai_chat_max_tokens", "65536")),
		MaxHistory:      parseInt(getOrDefault(m, "ai_chat_max_history", "100")),
		DailyLimit:      parseInt(getOrDefault(m, "ai_chat_daily_limit", "50")),
		SystemPrompt:    getOrDefault(m, "ai_chat_system_prompt", ""),
		MaxContextChars: parseInt(getOrDefault(m, "ai_chat_max_context_chars", "500000")),
		FileUpload:      m["ai_chat_file_upload_enabled"] == "1",
		MaxFileSize:     parseInt(getOrDefault(m, "ai_chat_max_file_size", "10485760")),
		CachedAt:        time.Now(),
	}

	cachedAISettings = s
	return s
}

// InvalidateAISettingsCache forces next GetAISettings to reload from DB.
func InvalidateAISettingsCache() {
	cachedAISettings = nil
}

func getOrDefault(m map[string]string, key, def string) string {
	if v, ok := m[key]; ok && v != "" {
		return v
	}
	return def
}

func parseFloat(s string) float64 {
	var f float64
	fmt.Sscanf(s, "%f", &f)
	return f
}

func parseInt(s string) int {
	var i int
	fmt.Sscanf(s, "%d", &i)
	return i
}
