package dto

// StreamRequest is the request body for POST /api/v1/ai/stream
type StreamRequest struct {
	ConversationID uint   `json:"conversation_id" binding:"required"`
	Message        string `json:"message" binding:"required"`
}

// StreamEvent is a single SSE event sent to the client.
type StreamEvent struct {
	Content string `json:"content,omitempty"`
	Done    bool   `json:"done"`
	Error   string `json:"error,omitempty"`
	Usage   *Usage `json:"usage,omitempty"`
}

// Usage contains token usage info from the AI API.
type Usage struct {
	Model            string `json:"model,omitempty"`
	PromptTokens     int    `json:"prompt_tokens"`
	CompletionTokens int    `json:"completion_tokens"`
	TotalTokens      int    `json:"total_tokens"`
	LatencyMs        int    `json:"latency_ms"`
}

// SendPrepareRequest is sent from JS to PHP to prepare context.
type SendPrepareRequest struct {
	ConversationID uint   `json:"conversation_id"`
	Message        string `json:"message"`
}

// SendPrepareResponse is returned by PHP after saving user message and building context.
type SendPrepareResponse struct {
	Success      bool              `json:"success"`
	HMACToken    string            `json:"hmac_token"`
	SystemPrompt string            `json:"system_prompt"`
	Messages     []ContextMessage  `json:"messages"`
	IsFirst      bool              `json:"is_first"`
	Attachment   interface{}       `json:"attachment,omitempty"`
	Error        string            `json:"error,omitempty"`
}

// ContextMessage is a message in the AI context (from PHP).
type ContextMessage struct {
	Role       string `json:"role"`
	Content    string `json:"content"`
	Attachment string `json:"attachment,omitempty"`
}

// SaveAssistantRequest is sent from JS to PHP to save the AI reply.
type SaveAssistantRequest struct {
	ConversationID uint   `json:"conversation_id"`
	Content        string `json:"content"`
	Model          string `json:"model,omitempty"`
	PromptTokens   int    `json:"prompt_tokens,omitempty"`
	CompletionTokens int  `json:"completion_tokens,omitempty"`
	TotalTokens    int    `json:"total_tokens,omitempty"`
	LatencyMs      int    `json:"latency_ms,omitempty"`
}

// GoStreamRequest is the request body sent to Go's /api/v1/ai/stream.
// Contains pre-built context from PHP.
type GoStreamRequest struct {
	SystemPrompt string           `json:"system_prompt" binding:"required"`
	Messages     []ContextMessage `json:"messages" binding:"required"`
}
