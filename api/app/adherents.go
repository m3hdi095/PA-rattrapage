package app

import (
	"api/db"
	"api/models"
	"api/utils"
	"encoding/json"
	"net/http"
	"time"
)

type CreateAdherentRequest struct {
	Email      string `json:"email"`
	Password   string `json:"password"`
	Nom        string `json:"nom"`
	Siret      string `json:"siret"`
	Adresse    string `json:"adresse"`
	CodePostal string `json:"code_postal"`
	Ville      string `json:"ville"`
	Telephone  string `json:"telephone"`
}

func CreateAdherent(w http.ResponseWriter, r *http.Request) {
	var req CreateAdherentRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "corps de requête JSON invalide", http.StatusBadRequest)
		return
	}

	if req.Email == "" || req.Password == "" || req.Nom == "" || req.Siret == "" {
		http.Error(w, "email, password, nom et siret sont obligatoires", http.StatusBadRequest)
		return
	}

	passwordHash, err := utils.HashPassword(req.Password)
	if err != nil {
		http.Error(w, "impossible de hasher le mot de passe", http.StatusInternalServerError)
		return
	}

	// L'adhésion démarre aujourd'hui et est valable un an, ce n'est pas au client de le décider.
	now := time.Now()
	adherent := models.Adherent{
		Email:          req.Email,
		PasswordHash:   passwordHash,
		Nom:            req.Nom,
		Siret:          req.Siret,
		Adresse:        req.Adresse,
		CodePostal:     req.CodePostal,
		Ville:          req.Ville,
		Telephone:      req.Telephone,
		DateAdhesion:   now.Format("2006-01-02"),
		DateExpiration: now.AddDate(1, 0, 0).Format("2006-01-02"),
	}

	if _, err := db.CreateAdherent(&adherent); err != nil {
		http.Error(w, "erreur lors de la création de l'adhérent", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(adherent)
}
