package utils

import (
	"fmt"
	"os"
	"time"

	"github.com/golang-jwt/jwt/v5"
)

func envOr(key, fallback string) string {
	if v := os.Getenv(key); v != "" {
		return v
	}
	return fallback
}

var jwtSecret = []byte(envOr("JWT_SECRET", "nomorewaste"))

func GenerateJWT(id int, role string) (string, error) {
	return GenerateJWTWithAdminRole(id, role, "")
}

func GenerateJWTWithAdminRole(id int, role string, adminRole string) (string, error) {
	claims := jwt.MapClaims{
		"id":         id,
		"role":       role,
		"admin_role": adminRole,
		"exp":        time.Now().Add(time.Hour * 72).Unix(),
	}
	token := jwt.NewWithClaims(jwt.SigningMethodHS256, claims)
	tokenString, err := token.SignedString(jwtSecret)
	if err != nil {
		return "", err
	}
	return tokenString, nil
}

type Claims struct {
	ID        int    `json:"id"`
	Role      string `json:"role"`
	AdminRole string `json:"admin_role"`
}

func VerifyJWT(tokenString string) (*Claims, error) {
	token, err := jwt.Parse(tokenString, func(token *jwt.Token) (any,
		error) {
		_, ok := token.Method.(*jwt.SigningMethodHMAC)
		if !ok {
			return nil, fmt.Errorf("unexpected signing method")
		}
		return jwtSecret, nil
	})
	if err != nil {
		return nil, err
	}
	claims, ok := token.Claims.(jwt.MapClaims)
	if ok && token.Valid {
		id, _ := claims["id"].(float64)
		role, _ := claims["role"].(string)
		adminRole, _ := claims["admin_role"].(string)
		return &Claims{
			ID:        int(id),
			Role:      role,
			AdminRole: adminRole,
		}, nil
	}
	return nil, fmt.Errorf("invalid token")
}
