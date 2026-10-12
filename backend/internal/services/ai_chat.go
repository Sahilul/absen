package services

import (
	"bufio"
	"bytes"
	"context"
	"encoding/base64"
	"encoding/json"
	"fmt"
	"image"
	"image/jpeg"
	"image/png"
	"io"
	"net/http"
	"os"
	"strings"
	"sync/atomic"
	"time"

	"github.com/rs/zerolog/log"
	"github.com/sabilillah/backend/internal/config"
	"github.com/sabilillah/backend/internal/database"
	"github.com/sabilillah/backend/internal/dto"

	_ "image/gif"
)

// AiChatService handles AI chat business logic.
type AiChatService struct{}

func NewAiChatService() *AiChatService {
	return &AiChatService{}
}

// openAIMessage is the message format for OpenAI-compatible APIs.
type openAIMessage struct {
	Role    string      `json:"role"`
	Content interface{} `json:"content"`
}

// openAIRequest is the request body for /chat/completions.
type openAIRequest struct {
	Model       string          `json:"model"`
	Messages    []openAIMessage `json:"messages"`
	Temperature float64         `json:"temperature"`
	Stream      bool            `json:"stream"`
	MaxTokens   int             `json:"max_tokens,omitempty"`
}

// openAIStreamChunk represents a single SSE chunk from OpenAI streaming.
type openAIStreamChunk struct {
	ID      string `json:"id"`
	Object  string `json:"object"`
	Created int64  `json:"created"`
	Model   string `json:"model"`
	Choices []struct {
		Index int `json:"index"`
		Delta struct {
			Role    string `json:"role,omitempty"`
			Content string `json:"content,omitempty"`
		} `json:"delta"`
		FinishReason *string `json:"finish_reason"`
	} `json:"choices"`
	Usage *struct {
		PromptTokens     int `json:"prompt_tokens"`
		CompletionTokens int `json:"completion_tokens"`
		TotalTokens      int `json:"total_tokens"`
	} `json:"usage,omitempty"`
}

// StreamResult contains the accumulated result after streaming completes.
type StreamResult struct {
	FullContent      string
	Model            string
	PromptTokens     int
	CompletionTokens int
	TotalTokens      int
	LatencyMs        int
	Error            string
}

// visionMimes lists MIME types that can be sent as vision content.
var visionMimes = map[string]bool{
	"image/jpeg": true, "image/png": true, "image/gif": true, "image/webp": true,
	"application/pdf": true,
}

// attachmentMeta represents the JSON metadata stored in ContextMessage.Attachment.
type attachmentMeta struct {
	Filename     string `json:"filename"`
	OriginalName string `json:"original_name"`
	MimeType     string `json:"mime_type"`
	FilePath     string `json:"file_path"`
}

// maxVisionBytes is the max raw file size before we attempt to resize (1MB).
// Images larger than this will be downscaled to reduce the base64 payload.
const maxVisionBytes = 1 * 1024 * 1024

// buildMessageContent converts a ContextMessage into the appropriate content
// value for the OpenAI API. For plain text it returns a string; for messages
// with image/PDF attachment(s) it returns a multimodal content array.
// Supports both single attachment object and array of attachments.
// Large images are automatically resized to keep the API payload manageable.
func buildMessageContent(msg dto.ContextMessage) interface{} {
	if msg.Attachment == "" || msg.Role != "user" {
		return msg.Content
	}

	// Try to parse as array first, then single object
	var metas []attachmentMeta
	trimmed := strings.TrimSpace(msg.Attachment)
	if len(trimmed) > 0 && trimmed[0] == '[' {
		if err := json.Unmarshal([]byte(msg.Attachment), &metas); err != nil {
			return msg.Content
		}
	} else {
		var single attachmentMeta
		if err := json.Unmarshal([]byte(msg.Attachment), &single); err != nil || single.Filename == "" {
			return msg.Content
		}
		metas = []attachmentMeta{single}
	}

	// Filter to only vision-capable attachments
	var visionMetas []attachmentMeta
	for _, m := range metas {
		if visionMimes[m.MimeType] {
			visionMetas = append(visionMetas, m)
		}
	}

	if len(visionMetas) == 0 {
		// No vision files — text was already prepended by PHP
		return msg.Content
	}

	// Build multimodal content parts
	parts := []map[string]interface{}{}
	if text := strings.TrimSpace(msg.Content); text != "" {
		parts = append(parts, map[string]interface{}{
			"type": "text",
			"text": text,
		})
	}

	for _, meta := range visionMetas {
		fileBytes, err := os.ReadFile(meta.FilePath)
		if err != nil {
			log.Warn().Err(err).Str("path", meta.FilePath).Msg("cannot read attachment file for vision")
			continue
		}

		mime := meta.MimeType
		payload := fileBytes

		// For images (not PDF), resize if too large
		if mime != "application/pdf" && len(fileBytes) > maxVisionBytes {
			resized, resizedMime, err := resizeImage(fileBytes, 1280)
			if err != nil {
				log.Warn().Err(err).Msg("failed to resize image, using original")
			} else {
				payload = resized
				mime = resizedMime
				log.Info().
					Int("original_bytes", len(fileBytes)).
					Int("resized_bytes", len(resized)).
					Msg("resized large image for vision API")
			}
		}

		b64 := base64.StdEncoding.EncodeToString(payload)
		dataURL := fmt.Sprintf("data:%s;base64,%s", mime, b64)

		parts = append(parts, map[string]interface{}{
			"type": "image_url",
			"image_url": map[string]string{
				"url": dataURL,
			},
		})
	}

	if len(parts) == 0 {
		return msg.Content
	}

	return parts
}

// resizeImage decodes an image, scales it so the longest side is at most maxDim
// pixels, and re-encodes as JPEG (quality 80). Returns the bytes and MIME type.
func resizeImage(data []byte, maxDim int) ([]byte, string, error) {
	img, _, err := image.Decode(bytes.NewReader(data))
	if err != nil {
		return nil, "", fmt.Errorf("decode: %w", err)
	}

	bounds := img.Bounds()
	w, h := bounds.Dx(), bounds.Dy()

	// Calculate new dimensions keeping aspect ratio
	if w <= maxDim && h <= maxDim {
		// Already small enough — just re-encode as JPEG to compress
		var buf bytes.Buffer
		if err := jpeg.Encode(&buf, img, &jpeg.Options{Quality: 80}); err != nil {
			return nil, "", fmt.Errorf("encode: %w", err)
		}
		return buf.Bytes(), "image/jpeg", nil
	}

	newW, newH := w, h
	if w > h {
		newW = maxDim
		newH = int(float64(h) * float64(maxDim) / float64(w))
	} else {
		newH = maxDim
		newW = int(float64(w) * float64(maxDim) / float64(h))
	}
	if newW < 1 {
		newW = 1
	}
	if newH < 1 {
		newH = 1
	}

	// Simple nearest-neighbor resize (no external dependency)
	dst := image.NewRGBA(image.Rect(0, 0, newW, newH))
	for y := 0; y < newH; y++ {
		srcY := y * h / newH
		for x := 0; x < newW; x++ {
			srcX := x * w / newW
			dst.Set(x, y, img.At(bounds.Min.X+srcX, bounds.Min.Y+srcY))
		}
	}

	var buf bytes.Buffer
	if err := jpeg.Encode(&buf, dst, &jpeg.Options{Quality: 80}); err != nil {
		return nil, "", fmt.Errorf("encode: %w", err)
	}

	return buf.Bytes(), "image/jpeg", nil
}

// resizePNG re-encodes as PNG (used if we need lossless, currently unused).
func resizePNG(img image.Image) ([]byte, error) {
	var buf bytes.Buffer
	if err := png.Encode(&buf, img); err != nil {
		return nil, err
	}
	return buf.Bytes(), nil
}

// idleReader mereset timer watchdog setiap kali ada data masuk dari server AI.
type idleReader struct {
	r       io.Reader
	timer   *time.Timer
	timeout time.Duration
}

func (ir *idleReader) Read(p []byte) (int, error) {
	n, err := ir.r.Read(p)
	if n > 0 {
		ir.timer.Reset(ir.timeout)
	}
	return n, err
}

// StreamChat streams AI response to the client via SSE.
// It calls the OpenAI-compatible API with stream=true and forwards chunks.
//
// Parameters:
//   - ctx: context with timeout
//   - systemPrompt: the system prompt
//   - messages: conversation context messages
//   - writer: function called for each SSE event to write to client
//
// Returns StreamResult with accumulated content and usage.
func (s *AiChatService) StreamChat(
	ctx context.Context,
	systemPrompt string,
	messages []dto.ContextMessage,
	writer func(event dto.StreamEvent) error,
) (*StreamResult, error) {
	aiSettings := database.GetAISettings()

	if !aiSettings.Enabled {
		return nil, fmt.Errorf("fitur AI Chat tidak aktif")
	}
	if aiSettings.APIKey == "" {
		return nil, fmt.Errorf("API key belum dikonfigurasi")
	}
	if aiSettings.BaseURL == "" {
		return nil, fmt.Errorf("base URL belum dikonfigurasi")
	}

	// Build OpenAI messages
	apiMessages := make([]openAIMessage, 0, len(messages)+1)
	apiMessages = append(apiMessages, openAIMessage{
		Role:    "system",
		Content: systemPrompt,
	})

	for _, msg := range messages {
		content := buildMessageContent(msg)
		apiMessages = append(apiMessages, openAIMessage{
			Role:    msg.Role,
			Content: content,
		})
	}

	// Build request — selalu minta max_tokens besar agar respons panjang tidak terpotong
	maxTokens := aiSettings.MaxTokens
	if maxTokens <= 0 {
		maxTokens = config.C.AI.MaxTokens
	}
	if maxTokens <= 0 {
		maxTokens = 65536
	}

	reqBody := openAIRequest{
		Model:       aiSettings.Model,
		Messages:    apiMessages,
		Temperature: aiSettings.Temperature,
		Stream:      true,
		MaxTokens:   maxTokens,
	}

	bodyBytes, err := json.Marshal(reqBody)
	if err != nil {
		return nil, fmt.Errorf("failed to marshal request: %w", err)
	}

	ctx, cancel := context.WithCancel(ctx)
	defer cancel()

	// Idle watchdog — batalkan HANYA jika server AI diam (tidak kirim data)
	// selama idle_timeout. Tidak ada batas durasi total: respons panjang
	// boleh berjalan berapa lama pun selama stream tetap mengalir.
	idleTimeout := config.C.AI.IdleTimeout
	if idleTimeout <= 0 {
		idleTimeout = 5 * time.Minute
	}
	var idleHit atomic.Bool
	idleTimer := time.AfterFunc(idleTimeout, func() {
		idleHit.Store(true)
		cancel()
	})
	defer idleTimer.Stop()

	url := strings.TrimRight(aiSettings.BaseURL, "/") + "/chat/completions"

	req, err := http.NewRequestWithContext(ctx, "POST", url, bytes.NewReader(bodyBytes))
	if err != nil {
		return nil, fmt.Errorf("failed to create request: %w", err)
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Authorization", "Bearer "+aiSettings.APIKey)
	req.Header.Set("Accept", "text/event-stream")

	// HTTP client — force HTTP/1.1 to avoid http2 timeout issues with SSE,
	// and use generous timeouts since AI APIs can be slow to start generating.
	client := &http.Client{
		Timeout: 0, // No client-level timeout; use context deadline instead
		Transport: &http.Transport{
			ForceAttemptHTTP2:      false,                       // Force HTTP/1.1
			ResponseHeaderTimeout: config.C.AI.StreamTimeout,   // Wait for first response header (5m)
			TLSHandshakeTimeout:   config.C.AI.ConnectTimeout,  // TLS handshake (15s)
			DisableKeepAlives:     true,                        // Fresh connection each time
		},
	}

	startTime := time.Now()

	resp, err := client.Do(req)
	if err != nil {
		if idleHit.Load() {
			return nil, fmt.Errorf("server AI tidak mengirim data selama %s — koneksi direset. Coba lagi.", idleTimeout)
		}
		return nil, fmt.Errorf("gagal menghubungi server AI: %w", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode < 200 || resp.StatusCode >= 300 {
		body, _ := io.ReadAll(io.LimitReader(resp.Body, 2048))
		// Try to parse error message
		var errResp struct {
			Error struct {
				Message string `json:"message"`
			} `json:"error"`
			Message string `json:"message"`
		}
		if json.Unmarshal(body, &errResp) == nil {
			msg := errResp.Error.Message
			if msg == "" {
				msg = errResp.Message
			}
			if msg != "" {
				return nil, fmt.Errorf("error dari AI (HTTP %d): %s", resp.StatusCode, msg)
			}
		}
		return nil, fmt.Errorf("error dari AI: HTTP %d", resp.StatusCode)
	}

	// Parse SSE stream
	result := &StreamResult{
		Model: aiSettings.Model,
	}

	// Bungkus body dengan idle watchdog (reset timer tiap ada data)
	body := &idleReader{r: resp.Body, timer: idleTimer, timeout: idleTimeout}
	scanner := bufio.NewScanner(body)
	// Increase scanner buffer for large chunks
	scanner.Buffer(make([]byte, 0, 64*1024), 1024*1024)

	var fullContent strings.Builder

	for scanner.Scan() {
		select {
		case <-ctx.Done():
			result.FullContent = fullContent.String()
			result.LatencyMs = int(time.Since(startTime).Milliseconds())
			result.Error = "timeout"
			return result, ctx.Err()
		default:
		}

		line := scanner.Text()

		// Skip empty lines and comments
		if line == "" || strings.HasPrefix(line, ":") {
			continue
		}

		// Parse SSE data line
		if !strings.HasPrefix(line, "data: ") {
			continue
		}

		data := strings.TrimPrefix(line, "data: ")

		// [DONE] marker
		if data == "[DONE]" {
			break
		}

		// Parse chunk
		var chunk openAIStreamChunk
		if err := json.Unmarshal([]byte(data), &chunk); err != nil {
			log.Warn().Err(err).Str("data", data[:min(len(data), 200)]).Msg("Failed to parse SSE chunk")
			continue
		}

		// Extract content delta
		if len(chunk.Choices) > 0 {
			content := chunk.Choices[0].Delta.Content
			if content != "" {
				fullContent.WriteString(content)

				// Send to client
				if err := writer(dto.StreamEvent{
					Content: content,
					Done:    false,
				}); err != nil {
					result.FullContent = fullContent.String()
					result.LatencyMs = int(time.Since(startTime).Milliseconds())
					return result, fmt.Errorf("client disconnected: %w", err)
				}
			}
		}

		// Capture model from chunk
		if chunk.Model != "" {
			result.Model = chunk.Model
		}

		// Capture usage from final chunk (some APIs include it)
		if chunk.Usage != nil {
			result.PromptTokens = chunk.Usage.PromptTokens
			result.CompletionTokens = chunk.Usage.CompletionTokens
			result.TotalTokens = chunk.Usage.TotalTokens
		}
	}

	if err := scanner.Err(); err != nil {
		result.FullContent = fullContent.String()
		result.LatencyMs = int(time.Since(startTime).Milliseconds())
		if idleHit.Load() {
			result.Error = "idle-timeout"
			return result, fmt.Errorf("server AI tidak mengirim data selama %s — koneksi direset. Coba lagi.", idleTimeout)
		}
		if ctx.Err() == context.DeadlineExceeded {
			result.Error = "timeout"
			return result, fmt.Errorf("batas waktu maksimum streaming terlampaui")
		}
		log.Error().Err(err).Msg("SSE scanner error")
	}

	result.FullContent = fullContent.String()
	result.LatencyMs = int(time.Since(startTime).Milliseconds())

	// Send done event
	_ = writer(dto.StreamEvent{
		Done: true,
		Usage: &dto.Usage{
			Model:            result.Model,
			PromptTokens:     result.PromptTokens,
			CompletionTokens: result.CompletionTokens,
			TotalTokens:      result.TotalTokens,
			LatencyMs:        result.LatencyMs,
		},
	})

	return result, nil
}
