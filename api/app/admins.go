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
}

func CreateAdmin(w http.ResponseWriter, r *http.Request) {
	var req CreateAdminRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "corps de requête JSON invalide", http.StatusBadRequest)
		return
	}

	if req.Email == "" || req.Password == "" || req.Nom == "" || req.Prenom == "" {
		http.Error(w, "email, password, nom et prenom sont obligatoires", http.StatusBadRequest)
		return
	}

	passwordHash, err := utils.HashPassword(req.Password)
	if err != nil {
		http.Error(w, "impossible de hasher le mot de passe", http.StatusInternalServerError)
		return
	}

	admin := models.Admin{
		Email:        req.Email,
		PasswordHash: passwordHash,
		Nom:          req.Nom,
		Prenom:       req.Prenom,
	}

	if _, err := db.CreateAdmin(&admin); err != nil {
		http.Error(w, "erreur lors de la création de l'admin", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(admin)
}
