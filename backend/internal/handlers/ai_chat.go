package handlers

import (
	"context"
	"encoding/json"
	"fmt"
	"io"
	"net/http"

	"github.com/gin-gonic/gin"
	"github.com/rs/zerolog/log"
	"github.com/sabilillah/backend/internal/config"
	"github.com/sabilillah/backend/internal/dto"
	"github.com/sabilillah/backend/internal/middleware"
	"github.com/sabilillah/backend/internal/pkg/response"
	"github.com/sabilillah/backend/internal/services"
)

// AiChatHandler handles AI chat HTTP endpoints.
type AiChatHandler struct {
	service *services.AiChatService
}

func NewAiChatHandler() *AiChatHandler {
	return &AiChatHandler{
		service: services.NewAiChatService(),
	}
}

// Stream handles POST /api/v1/ai/stream
// Receives pre-built context from PHP and streams AI response via SSE.
//
// Request body (JSON):
//
//	{
//	  "system_prompt": "...",
//	  "messages": [{"role": "user", "content": "..."}]
//	}
//
// Response: SSE stream
//
//	data: {"content":"token","done":false}
//	data: {"content":"","done":true,"usage":{...}}
func (h *AiChatHandler) Stream(c *gin.Context) {
	// Parse request
	var req dto.GoStreamRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.BadRequest(c, "Request tidak valid: "+err.Error())
		return
	}

	if len(req.Messages) == 0 {
		response.BadRequest(c, "Messages tidak boleh kosong")
		return
	}

	userID := middleware.GetUserID(c)
	userName := middleware.GetUserName(c)

	log.Info().
		Uint("user_id", userID).
		Str("user_name", userName).
		Int("message_count", len(req.Messages)).
		Msg("AI stream request")

	// Set SSE headers
	c.Header("Content-Type", "text/event-stream")
	c.Header("Cache-Control", "no-cache")
	c.Header("Connection", "keep-alive")
	c.Header("X-Accel-Buffering", "no") // Disable nginx buffering

	// Create context with timeout
	ctx, cancel := context.WithTimeout(c.Request.Context(), config.C.AI.StreamTimeout)
	defer cancel()

	// Writer function that sends SSE events
	flusher, ok := c.Writer.(http.Flusher)
	if !ok {
		response.InternalError(c, "Streaming tidak didukung")
		return
	}

	writer := func(event dto.StreamEvent) error {
		data, err := json.Marshal(event)
		if err != nil {
			return err
		}

		_, writeErr := fmt.Fprintf(c.Writer, "data: %s\n\n", data)
		if writeErr != nil {
			return writeErr
		}
		flusher.Flush()
		return nil
	}

	// Stream!
	result, err := h.service.StreamChat(ctx, req.SystemPrompt, req.Messages, writer)

	if err != nil {
		log.Error().Err(err).
			Uint("user_id", userID).
			Msg("AI stream error")

		// Try to send error event (client may have disconnected)
		errEvent := dto.StreamEvent{
			Done:  true,
			Error: err.Error(),
		}
		data, _ := json.Marshal(errEvent)
		fmt.Fprintf(c.Writer, "data: %s\n\n", data)
		flusher.Flush()
		return
	}

	log.Info().
		Uint("user_id", userID).
		Int("latency_ms", result.LatencyMs).
		Str("model", result.Model).
		Int("prompt_tokens", result.PromptTokens).
		Int("completion_tokens", result.CompletionTokens).
		Int("content_length", len(result.FullContent)).
		Msg("AI stream completed")
}

// Health handles GET /api/v1/health
func Health(c *gin.Context) {
	response.OK(c, gin.H{
		"status":  "ok",
		"service": "sabilillah-api",
		"version": "1.0.0",
	})
}

// StreamTest handles GET /api/v1/ai/stream-test (debug only)
// Sends a test SSE stream to verify streaming works.
func (h *AiChatHandler) StreamTest(c *gin.Context) {
	c.Header("Content-Type", "text/event-stream")
	c.Header("Cache-Control", "no-cache")
	c.Header("Connection", "keep-alive")
	c.Header("X-Accel-Buffering", "no")

	flusher, ok := c.Writer.(http.Flusher)
	if !ok {
		response.InternalError(c, "Streaming tidak didukung")
		return
	}

	words := []string{"Halo", " ", "ini", " ", "adalah", " ", "test", " ", "streaming", " ", "dari", " ", "Go", " ", "backend", ".", " ", "Semuanya", " ", "berjalan", " ", "lancar", "! 🚀"}

	for _, word := range words {
		event := dto.StreamEvent{Content: word, Done: false}
		data, _ := json.Marshal(event)
		fmt.Fprintf(c.Writer, "data: %s\n\n", data)
		flusher.Flush()

		// Small delay to simulate streaming
		select {
		case <-c.Request.Context().Done():
			return
		default:
		}
	}

	// Done event
	doneEvent := dto.StreamEvent{
		Done: true,
		Usage: &dto.Usage{
			Model:            "test-model",
			PromptTokens:     10,
			CompletionTokens: 23,
			TotalTokens:      33,
			LatencyMs:        100,
		},
	}
	data, _ := json.Marshal(doneEvent)
	fmt.Fprintf(c.Writer, "data: %s\n\n", data)
	flusher.Flush()
}

// GenerateHMACToken handles POST /api/v1/auth/hmac-token (internal use)
// This is called by PHP to generate an HMAC token for the current user.
// Protected by a shared secret header.
func GenerateHMACToken(c *gin.Context) {
	// Read request body
	body, err := io.ReadAll(c.Request.Body)
	if err != nil {
		response.BadRequest(c, "Failed to read body")
		return
	}

	var req struct {
		UserID      uint   `json:"user_id"`
		Role        string `json:"role"`
		NamaLengkap string `json:"nama_lengkap"`
		Secret      string `json:"secret"`
	}
	if err := json.Unmarshal(body, &req); err != nil {
		response.BadRequest(c, "Invalid JSON")
		return
	}

	// Verify shared secret
	if req.Secret != config.C.Auth.HMACSecret {
		response.Forbidden(c, "Invalid secret")
		return
	}

	token := fmt.Sprintf("%d:%s:%s:%d:%s",
		req.UserID, req.Role, req.NamaLengkap,
		0, "placeholder") // Will be generated properly
	_ = token

	// Use the auth package to generate
	// For now, return the HMAC components that PHP can assemble
	response.OK(c, gin.H{
		"hmac_secret": config.C.Auth.HMACSecret,
	})
}
