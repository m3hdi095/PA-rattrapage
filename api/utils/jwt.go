package utils

import (
	"fmt"
	"time"

	"github.com/golang-jwt/jwt/v5"
)

var jwtSecret = []byte("nomorewaste")

func GenerateJWT(id int, role string) (string, error) {
	return GenerateJWTWithAdminRole(id, role, "")
}

// GenerateJWTWithAdminRole embarque en plus le sous-rôle admin/super_admin
// (vide pour les adhérents et bénévoles) pour éviter un aller-retour DB à
// chaque requête protégée réservée aux super_admins.
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
