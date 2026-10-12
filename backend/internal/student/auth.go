package student

import (
	"context"
	"github.com/gin-gonic/gin"
	"github.com/golang-jwt/jwt/v5"
	"gorm.io/gorm"
	"net/http"
	"strings"
	"time"
)

type Claims struct {
	UserID   uint   `json:"user_id"`
	Role     string `json:"role"`
	Ref      uint   `json:"id_ref"`
	TP       uint   `json:"id_tp"`
	Semester uint   `json:"id_semester"`
	jwt.RegisteredClaims
}

func ValidateToken(raw, secret string) (*Claims, error) {
	if len(secret) < 32 {
		return nil, &APIError{Status: 503, Message: "Modul data siswa belum dikonfigurasi."}
	}
	c := &Claims{}
	_, err := jwt.ParseWithClaims(raw, c, func(t *jwt.Token) (interface{}, error) { return []byte(secret), nil }, jwt.WithValidMethods([]string{"HS256"}), jwt.WithIssuer("sabilillah-php"), jwt.WithAudience("student-data"), jwt.WithExpirationRequired(), jwt.WithIssuedAt())
	if err != nil {
		return nil, err
	}
	if c.UserID == 0 || c.TP == 0 || c.Semester == 0 || c.IssuedAt == nil || c.ExpiresAt.Time.Sub(c.IssuedAt.Time) > 2*time.Minute {
		return nil, bad("Token siswa tidak valid.")
	}
	if c.Role != "admin" && c.Role != "wali_kelas" && c.Role != "siswa" {
		return nil, forbidden()
	}
	return c, nil
}
func Auth(db *gorm.DB, secret string) gin.HandlerFunc {
	return func(c *gin.Context) {
		c.Header("Cache-Control", "no-store")
		claims, err := ValidateToken(strings.TrimPrefix(c.GetHeader("Authorization"), "Bearer "), secret)
		if err != nil {
			if e, ok := err.(*APIError); ok && e.Status == 503 {
				respondError(c, err)
			} else {
				c.JSON(http.StatusUnauthorized, gin.H{"success": false, "error": "Sesi data siswa berakhir. Silakan muat ulang."})
			}
			c.Abort()
			return
		}
		ctx, cancel := context.WithTimeout(c.Request.Context(), 5*time.Second)
		defer cancel()
		var account struct {
			ID     uint `gorm:"column:id_user"`
			Role   string
			Ref    uint `gorm:"column:id_ref"`
			Status string
		}
		q := db.WithContext(ctx).Table("users").Select("id_user,role,id_ref,status").Where("id_user = ?", claims.UserID).Take(&account)
		if q.Error != nil || account.Status != "aktif" || account.Role != claims.Role || account.Ref != claims.Ref {
			c.JSON(401, gin.H{"success": false, "error": "Akun atau sesi tidak lagi aktif."})
			c.Abort()
			return
		}
		var count int64
		if err := db.WithContext(ctx).Table("semester").Where("id_semester = ? AND id_tp = ?", claims.Semester, claims.TP).Count(&count).Error; err != nil || count != 1 {
			c.JSON(401, gin.H{"success": false, "error": "Semester aktif tidak valid. Silakan login ulang."})
			c.Abort()
			return
		}
		c.Set("student_principal", Principal{claims.UserID, claims.Role, claims.Ref, claims.TP, claims.Semester})
		c.Next()
	}
}
func principal(c *gin.Context) Principal { return c.MustGet("student_principal").(Principal) }
