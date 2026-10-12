package main

import (
	"fmt"
	"os"
	"os/signal"
	"path/filepath"
	"syscall"

	"github.com/gin-gonic/gin"
	"github.com/rs/zerolog"
	"github.com/rs/zerolog/log"

	"github.com/sabilillah/backend/internal/config"
	"github.com/sabilillah/backend/internal/database"
	"github.com/sabilillah/backend/internal/handlers"
	"github.com/sabilillah/backend/internal/middleware"
	"github.com/sabilillah/backend/internal/student"
)

func main() {
	// Find config file relative to binary or working directory
	configPath := findConfigPath()

	// Load config
	cfg, err := config.Load(configPath)
	if err != nil {
		fmt.Fprintf(os.Stderr, "Failed to load config from %s: %v\n", configPath, err)
		os.Exit(1)
	}

	// Setup zerolog
	setupLogger(cfg.Server.Mode)

	log.Info().
		Str("mode", cfg.Server.Mode).
		Int("port", cfg.Server.Port).
		Msg("Starting sabilillah-api")

	// Connect database
	db, err := database.Connect(&cfg.Database)
	if err != nil {
		log.Fatal().Err(err).Msg("Failed to connect database")
	}
	defer database.Close()
	_ = db

	// Setup Gin
	if cfg.Server.Mode == "release" {
		gin.SetMode(gin.ReleaseMode)
	} else if cfg.Server.Mode == "test" {
		gin.SetMode(gin.TestMode)
	}

	r := gin.New()

	// Global middleware
	r.Use(middleware.Logger())
	r.Use(middleware.CORS())
	r.Use(gin.Recovery())

	// Routes
	setupRoutes(r)

	// Graceful shutdown
	go func() {
		quit := make(chan os.Signal, 1)
		signal.Notify(quit, syscall.SIGINT, syscall.SIGTERM)
		sig := <-quit
		log.Info().Str("signal", sig.String()).Msg("Shutting down server")
		database.Close()
		os.Exit(0)
	}()

	// Start server
	addr := fmt.Sprintf(":%d", cfg.Server.Port)
	log.Info().Str("addr", addr).Msg("Server listening")
	if err := r.Run(addr); err != nil {
		log.Fatal().Err(err).Msg("Server failed")
	}
}

func setupRoutes(r *gin.Engine) {
	aiHandler := handlers.NewAiChatHandler()

	// API v1
	v1 := r.Group("/api/v1")
	{
		student.Register(v1, database.DB, config.C.Auth.StudentBridgeSecret)

		// Public
		v1.GET("/health", handlers.Health)

		// AI Chat (requires auth)
		ai := v1.Group("/ai")
		ai.Use(middleware.Auth())
		ai.Use(middleware.BlockRole("siswa"))
		{
			ai.POST("/stream", aiHandler.Stream)
		}

		// Debug endpoints (only in debug mode)
		if config.C.Server.Mode == "debug" {
			debug := v1.Group("/debug")
			{
				debug.GET("/ai/stream-test", aiHandler.StreamTest)
			}
		}
	}
}

func setupLogger(mode string) {
	zerolog.TimeFieldFormat = zerolog.TimeFormatUnix

	if mode == "debug" {
		zerolog.SetGlobalLevel(zerolog.DebugLevel)
		log.Logger = log.Output(zerolog.ConsoleWriter{Out: os.Stdout})
	} else {
		zerolog.SetGlobalLevel(zerolog.InfoLevel)
		// JSON output for production (structured logging)
		log.Logger = zerolog.New(os.Stdout).With().Timestamp().Logger()
	}
}

func findConfigPath() string {
	// Try paths in order:
	// 1. ./config.yaml (working directory)
	// 2. ../config.yaml (relative to binary in cmd/server/)
	// 3. /www/wwwroot/sabilillah.id/backend/config.yaml (absolute)
	candidates := []string{
		"config.yaml",
		"../config.yaml",
		"../../config.yaml",
		"/www/wwwroot/sabilillah.id/backend/config.yaml",
	}

	// Also try relative to executable
	if exe, err := os.Executable(); err == nil {
		dir := filepath.Dir(exe)
		candidates = append(candidates,
			filepath.Join(dir, "config.yaml"),
			filepath.Join(dir, "..", "config.yaml"),
			filepath.Join(dir, "..", "..", "config.yaml"),
		)
	}

	for _, p := range candidates {
		if _, err := os.Stat(p); err == nil {
			abs, _ := filepath.Abs(p)
			return abs
		}
	}

	return "config.yaml" // fallback, will error on load
}
