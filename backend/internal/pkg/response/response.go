package response

import (
	"net/http"

	"github.com/gin-gonic/gin"
)

// Response is the standard JSON response format for all API endpoints.
type Response struct {
	Success bool        `json:"success"`
	Data    interface{} `json:"data,omitempty"`
	Error   string      `json:"error,omitempty"`
	Meta    *Meta       `json:"meta,omitempty"`
}

// Meta contains pagination or extra metadata.
type Meta struct {
	Total  int `json:"total,omitempty"`
	Offset int `json:"offset,omitempty"`
	Limit  int `json:"limit,omitempty"`
}

// OK sends a successful response with data.
func OK(c *gin.Context, data interface{}) {
	c.JSON(http.StatusOK, Response{
		Success: true,
		Data:    data,
	})
}

// OKWithMeta sends a successful response with data and pagination meta.
func OKWithMeta(c *gin.Context, data interface{}, meta *Meta) {
	c.JSON(http.StatusOK, Response{
		Success: true,
		Data:    data,
		Meta:    meta,
	})
}

// Created sends a 201 response.
func Created(c *gin.Context, data interface{}) {
	c.JSON(http.StatusCreated, Response{
		Success: true,
		Data:    data,
	})
}

// Err sends an error response with the given HTTP status code.
func Err(c *gin.Context, status int, message string) {
	c.JSON(status, Response{
		Success: false,
		Error:   message,
	})
}

// BadRequest sends a 400 error.
func BadRequest(c *gin.Context, message string) {
	Err(c, http.StatusBadRequest, message)
}

// Unauthorized sends a 401 error.
func Unauthorized(c *gin.Context, message string) {
	Err(c, http.StatusUnauthorized, message)
}

// Forbidden sends a 403 error.
func Forbidden(c *gin.Context, message string) {
	Err(c, http.StatusForbidden, message)
}

// NotFound sends a 404 error.
func NotFound(c *gin.Context, message string) {
	Err(c, http.StatusNotFound, message)
}

// TooManyRequests sends a 429 error.
func TooManyRequests(c *gin.Context, message string) {
	Err(c, http.StatusTooManyRequests, message)
}

// InternalError sends a 500 error.
func InternalError(c *gin.Context, message string) {
	Err(c, http.StatusInternalServerError, message)
}

// BadGateway sends a 502 error.
func BadGateway(c *gin.Context, message string) {
	Err(c, http.StatusBadGateway, message)
}
