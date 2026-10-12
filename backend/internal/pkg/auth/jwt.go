package auth

import (
	"crypto/hmac"
	"crypto/sha256"
	"encoding/hex"
	"errors"
	"fmt"
	"strconv"
	"strings"
	"time"

	"github.com/golang-jwt/jwt/v5"
	"github.com/sabilillah/backend/internal/config"
)

// Claims represents JWT token claims.
type Claims struct {
	UserID      uint   `json:"user_id"`
	Role        string `json:"role"`
	NamaLengkap string `json:"nama_lengkap"`
	jwt.RegisteredClaims
}

// GenerateToken creates a new JWT token for a user.
func GenerateToken(userID uint, role, namaLengkap string) (string, error) {
	cfg := config.C.Auth
	claims := Claims{
		UserID:      userID,
		Role:        role,
		NamaLengkap: namaLengkap,
		RegisteredClaims: jwt.RegisteredClaims{
			ExpiresAt: jwt.NewNumericDate(time.Now().Add(cfg.JWTExpiry)),
			IssuedAt:  jwt.NewNumericDate(time.Now()),
			Issuer:    "sabilillah-api",
		},
	}

	token := jwt.NewWithClaims(jwt.SigningMethodHS256, claims)
	return token.SignedString([]byte(cfg.JWTSecret))
}

// ValidateToken parses and validates a JWT token.
func ValidateToken(tokenString string) (*Claims, error) {
	cfg := config.C.Auth
	token, err := jwt.ParseWithClaims(tokenString, &Claims{}, func(token *jwt.Token) (interface{}, error) {
		if _, ok := token.Method.(*jwt.SigningMethodHMAC); !ok {
			return nil, fmt.Errorf("unexpected signing method: %v", token.Header["alg"])
		}
		return []byte(cfg.JWTSecret), nil
	})

	if err != nil {
		return nil, err
	}

	claims, ok := token.Claims.(*Claims)
	if !ok || !token.Valid {
		return nil, errors.New("invalid token claims")
	}

	return claims, nil
}

// ValidateHMAC validates an HMAC token from PHP bridge.
// Format: "userId:role:namaLengkap:timestamp:signature"
// Token valid for 5 minutes.
func ValidateHMAC(token string) (*Claims, error) {
	parts := strings.SplitN(token, ":", 5)
	if len(parts) != 5 {
		return nil, errors.New("invalid HMAC token format")
	}

	userIDStr, role, namaLengkap, timestampStr, signature := parts[0], parts[1], parts[2], parts[3], parts[4]

	// Verify timestamp (5 minute window)
	ts, err := strconv.ParseInt(timestampStr, 10, 64)
	if err != nil {
		return nil, errors.New("invalid timestamp")
	}
	if time.Since(time.Unix(ts, 0)) > 5*time.Minute {
		return nil, errors.New("HMAC token expired")
	}

	// Verify signature
	payload := fmt.Sprintf("%s:%s:%s:%s", userIDStr, role, namaLengkap, timestampStr)
	expectedSig := generateHMAC(payload)
	if !hmac.Equal([]byte(signature), []byte(expectedSig)) {
		return nil, errors.New("invalid HMAC signature")
	}

	userID, _ := strconv.ParseUint(userIDStr, 10, 32)
	return &Claims{
		UserID:      uint(userID),
		Role:        role,
		NamaLengkap: namaLengkap,
	}, nil
}

// GenerateHMACToken creates an HMAC token (called from PHP bridge helper).
func GenerateHMACToken(userID uint, role, namaLengkap string) string {
	ts := strconv.FormatInt(time.Now().Unix(), 10)
	payload := fmt.Sprintf("%d:%s:%s:%s", userID, role, namaLengkap, ts)
	sig := generateHMAC(payload)
	return fmt.Sprintf("%d:%s:%s:%s:%s", userID, role, namaLengkap, ts, sig)
}

func generateHMAC(payload string) string {
	mac := hmac.New(sha256.New, []byte(config.C.Auth.HMACSecret))
	mac.Write([]byte(payload))
	return hex.EncodeToString(mac.Sum(nil))
}
