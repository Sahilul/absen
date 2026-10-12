package middleware

import (
	"net/http"
	"strings"

	"github.com/gin-gonic/gin"
	"github.com/sabilillah/backend/internal/pkg/auth"
	"github.com/sabilillah/backend/internal/pkg/response"
)

const (
	// Context keys for user info
	CtxUserID   = "user_id"
	CtxUserRole = "user_role"
	CtxUserName = "user_name"
)

// Auth middleware validates JWT or HMAC tokens.
// Supports:
//   - Authorization: Bearer <jwt_token>
//   - X-HMAC-Token: <hmac_token> (PHP bridge)
func Auth() gin.HandlerFunc {
	return func(c *gin.Context) {
		// Try JWT first (Authorization: Bearer ...)
		if authHeader := c.GetHeader("Authorization"); authHeader != "" {
			if strings.HasPrefix(authHeader, "Bearer ") {
				tokenStr := strings.TrimPrefix(authHeader, "Bearer ")
				claims, err := auth.ValidateToken(tokenStr)
				if err != nil {
					response.Unauthorized(c, "Token tidak valid: "+err.Error())
					c.Abort()
					return
				}
				setUserContext(c, claims)
				c.Next()
				return
			}
		}

		// Try HMAC token (PHP bridge)
		if hmacToken := c.GetHeader("X-HMAC-Token"); hmacToken != "" {
			claims, err := auth.ValidateHMAC(hmacToken)
			if err != nil {
				response.Unauthorized(c, "HMAC token tidak valid: "+err.Error())
				c.Abort()
				return
			}
			setUserContext(c, claims)
			c.Next()
			return
		}

		response.Unauthorized(c, "Token autentikasi diperlukan")
		c.Abort()
	}
}

// OptionalAuth tries to authenticate but doesn't block if no token.
func OptionalAuth() gin.HandlerFunc {
	return func(c *gin.Context) {
		if authHeader := c.GetHeader("Authorization"); authHeader != "" {
			if strings.HasPrefix(authHeader, "Bearer ") {
				tokenStr := strings.TrimPrefix(authHeader, "Bearer ")
				if claims, err := auth.ValidateToken(tokenStr); err == nil {
					setUserContext(c, claims)
				}
			}
		} else if hmacToken := c.GetHeader("X-HMAC-Token"); hmacToken != "" {
			if claims, err := auth.ValidateHMAC(hmacToken); err == nil {
				setUserContext(c, claims)
			}
		}
		c.Next()
	}
}

// RequireRole checks that the authenticated user has one of the allowed roles.
func RequireRole(roles ...string) gin.HandlerFunc {
	return func(c *gin.Context) {
		userRole, exists := c.Get(CtxUserRole)
		if !exists {
			response.Forbidden(c, "Akses ditolak")
			c.Abort()
			return
		}

		role := userRole.(string)
		for _, r := range roles {
			if role == r {
				c.Next()
				return
			}
		}

		response.Forbidden(c, "Role '"+role+"' tidak memiliki akses")
		c.Abort()
	}
}

// BlockRole blocks specific roles (e.g., siswa).
func BlockRole(roles ...string) gin.HandlerFunc {
	return func(c *gin.Context) {
		userRole, exists := c.Get(CtxUserRole)
		if !exists {
			c.Next()
			return
		}

		role := userRole.(string)
		for _, r := range roles {
			if role == r {
				response.Err(c, http.StatusForbidden, "Akses ditolak untuk role '"+role+"'")
				c.Abort()
				return
			}
		}
		c.Next()
	}
}

func setUserContext(c *gin.Context, claims *auth.Claims) {
	c.Set(CtxUserID, claims.UserID)
	c.Set(CtxUserRole, claims.Role)
	c.Set(CtxUserName, claims.NamaLengkap)
}

// GetUserID extracts user ID from gin context. Returns 0 if not set.
func GetUserID(c *gin.Context) uint {
	if v, exists := c.Get(CtxUserID); exists {
		return v.(uint)
	}
	return 0
}

// GetUserRole extracts user role from gin context.
func GetUserRole(c *gin.Context) string {
	if v, exists := c.Get(CtxUserRole); exists {
		return v.(string)
	}
	return ""
}

// GetUserName extracts user name from gin context.
func GetUserName(c *gin.Context) string {
	if v, exists := c.Get(CtxUserName); exists {
		return v.(string)
	}
	return ""
}
