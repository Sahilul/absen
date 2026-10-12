package config

import (
	"time"

	"github.com/spf13/viper"
)

type Config struct {
	Server   ServerConfig   `mapstructure:"server"`
	Database DatabaseConfig `mapstructure:"database"`
	Auth     AuthConfig     `mapstructure:"auth"`
	CORS     CORSConfig     `mapstructure:"cors"`
	AI       AIConfig       `mapstructure:"ai"`
}

type ServerConfig struct {
	Port int    `mapstructure:"port"`
	Mode string `mapstructure:"mode"`
}

type DatabaseConfig struct {
	Host            string        `mapstructure:"host"`
	Port            int           `mapstructure:"port"`
	User            string        `mapstructure:"user"`
	Password        string        `mapstructure:"password"`
	Name            string        `mapstructure:"name"`
	MaxOpenConns    int           `mapstructure:"max_open_conns"`
	MaxIdleConns    int           `mapstructure:"max_idle_conns"`
	ConnMaxLifetime time.Duration `mapstructure:"conn_max_lifetime"`
}

type AuthConfig struct {
	JWTSecret           string        `mapstructure:"jwt_secret"`
	JWTExpiry           time.Duration `mapstructure:"jwt_expiry"`
	HMACSecret          string        `mapstructure:"hmac_secret"`
	StudentBridgeSecret string        `mapstructure:"student_bridge_secret"`
}

type CORSConfig struct {
	AllowedOrigins []string `mapstructure:"allowed_origins"`
	AllowedMethods []string `mapstructure:"allowed_methods"`
	AllowedHeaders []string `mapstructure:"allowed_headers"`
}

type AIConfig struct {
	StreamTimeout  time.Duration `mapstructure:"stream_timeout"`
	ConnectTimeout time.Duration `mapstructure:"connect_timeout"`
	IdleTimeout    time.Duration `mapstructure:"idle_timeout"`
	MaxTokens      int           `mapstructure:"max_tokens"`
}

var C Config

func Load(path string) (*Config, error) {
	viper.SetConfigFile(path)
	viper.SetConfigType("yaml")

	// Environment variable overrides (e.g. DB_PASSWORD)
	viper.AutomaticEnv()
	viper.BindEnv("auth.hmac_secret", "AI_BRIDGE_HMAC_SECRET")
	viper.BindEnv("auth.student_bridge_secret", "STUDENT_BRIDGE_SECRET")
	viper.BindEnv("database.host", "DB_HOST")
	viper.BindEnv("database.port", "DB_PORT")
	viper.BindEnv("database.name", "DB_NAME")
	viper.BindEnv("database.user", "DB_USER")
	viper.BindEnv("database.password", "DB_PASSWORD")

	if err := viper.ReadInConfig(); err != nil {
		return nil, err
	}

	if err := viper.Unmarshal(&C); err != nil {
		return nil, err
	}

	// Defaults
	if C.Server.Port == 0 {
		C.Server.Port = 8090
	}
	if C.Server.Mode == "" {
		C.Server.Mode = "release"
	}
	if C.Database.Port == 0 {
		C.Database.Port = 3306
	}
	if C.Database.MaxOpenConns == 0 {
		C.Database.MaxOpenConns = 25
	}
	if C.Database.MaxIdleConns == 0 {
		C.Database.MaxIdleConns = 10
	}
	if C.Database.ConnMaxLifetime == 0 {
		C.Database.ConnMaxLifetime = 5 * time.Minute
	}
	if C.AI.StreamTimeout == 0 {
		C.AI.StreamTimeout = 2 * time.Hour
	}
	if C.AI.ConnectTimeout == 0 {
		C.AI.ConnectTimeout = 15 * time.Second
	}
	if C.AI.IdleTimeout == 0 {
		C.AI.IdleTimeout = 5 * time.Minute
	}
	if C.AI.MaxTokens == 0 {
		C.AI.MaxTokens = 65536
	}

	return &C, nil
}
