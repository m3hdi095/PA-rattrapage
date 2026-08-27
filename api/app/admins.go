package app

import (
	"api/db"
	"api/models"
	"api/utils"
	"encoding/json"
	"net/http"
)

type CreateAdminRequest struct {
	Email    string `json:"email"`
	Password string `json:"password"`
	Nom      string `json:"nom"`
	Prenom   string `json:"prenom"`
	Role     string `json:"role"`
}

func requireSuperAdmin(w http.ResponseWriter, r *http.Request) *utils.Claims {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		utils.JSONError(w, "token invalide", http.StatusUnauthorized)
		return nil
	}
	if claims.Role != "admin" || claims.AdminRole != "super_admin" {
		utils.JSONError(w, "accès réservé aux super admins", http.StatusForbidden)
		return nil
	}
	return claims
}

func CreateAdmin(w http.ResponseWriter, r *http.Request) {
	if requireSuperAdmin(w, r) == nil {
		return
	}

	var req CreateAdminRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.JSONError(w, "corps de requête JSON invalide", http.StatusBadRequest)
		return
	}

	if req.Email == "" || req.Password == "" || req.Nom == "" || req.Prenom == "" {
		utils.JSONError(w, "email, password, nom et prenom sont obligatoires", http.StatusBadRequest)
		return
	}

	role := req.Role
	if role != "super_admin" {
		role = "admin"
	}

	passwordHash, err := utils.HashPassword(req.Password)
	if err != nil {
		utils.JSONError(w, "impossible de hasher le mot de passe", http.StatusInternalServerError)
		return
	}

	admin := models.Admin{
		Email:        req.Email,
		PasswordHash: passwordHash,
		Nom:          req.Nom,
		Prenom:       req.Prenom,
		Role:         role,
	}

	if _, err := db.CreateAdmin(&admin); err != nil {
		utils.JSONError(w, "erreur lors de la création de l'admin", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(admin)
}

func ListAdmins(w http.ResponseWriter, r *http.Request) {
	if requireSuperAdmin(w, r) == nil {
		return
	}

	admins, err := db.GetAllAdmins()
	if err != nil {
		utils.JSONError(w, "erreur lors de la récupération des admins", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(admins)
}

func DeleteAdmin(w http.ResponseWriter, r *http.Request) {
	claims := requireSuperAdmin(w, r)
	if claims == nil {
		return
	}

	var req struct {
		ID int `json:"id"`
	}
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.JSONError(w, "données invalides", http.StatusBadRequest)
		return
	}

	if req.ID == claims.ID {
		utils.JSONError(w, "impossible de supprimer son propre compte", http.StatusBadRequest)
		return
	}

	target, err := db.GetAdminByID(req.ID)
	if err != nil {
		utils.JSONError(w, "admin introuvable", http.StatusNotFound)
		return
	}

	if target.Role == "super_admin" {
		count, err := db.CountSuperAdmins()
		if err != nil {
			utils.JSONError(w, "erreur lors de la vérification des super admins", http.StatusInternalServerError)
			return
		}
		if count <= 1 {
			utils.JSONError(w, "impossible de supprimer le dernier super admin", http.StatusBadRequest)
			return
		}
	}

	if err := db.DeleteAdmin(req.ID); err != nil {
		utils.JSONError(w, "erreur lors de la suppression de l'admin", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusNoContent)
}
