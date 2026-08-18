package app

import (
	"encoding/json"
	"net/http"

	"api/db"
	"api/models"
	"api/utils"
)

type CreateBenevoleRequest struct {
	Email     string   `json:"email"`
	Password  string   `json:"password"`
	Nom       string   `json:"nom"`
	Prenom    string   `json:"prenom"`
	Telephone string   `json:"telephone"`
	Capacites []string `json:"capacites"`
}

func CreateBenevole(w http.ResponseWriter, r *http.Request) {
	var req CreateBenevoleRequest
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

	benevole := models.Benevole{
		Email:        req.Email,
		PasswordHash: passwordHash,
		Nom:          req.Nom,
		Prenom:       req.Prenom,
		Telephone:    req.Telephone,
	}

	if _, err := db.CreateBenevole(&benevole, req.Capacites); err != nil {
		http.Error(w, "erreur lors de la création du bénévole", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(benevole)
}

func ValidateBenevole(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		http.Error(w, "token invalide", http.StatusUnauthorized)
		return
	}

	if claims.Role != "admin" {
		http.Error(w, "accès réservé aux admins", http.StatusForbidden)
		return
	}

	var req struct {
		ID int `json:"id"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "corps de requête JSON invalide", http.StatusBadRequest)
		return
	}

	if err := db.ValidateBenevole(req.ID); err != nil {
		http.Error(w, "erreur lors de la validation du bénévole", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusOK)
}

func RejectBenevole(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		http.Error(w, "token invalide", http.StatusUnauthorized)
		return
	}

	if claims.Role != "admin" {
		http.Error(w, "accès réservé aux admins", http.StatusForbidden)
		return
	}

	var req struct {
		ID int `json:"id"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "corps de requête JSON invalide", http.StatusBadRequest)
		return
	}

	if err := db.RejectBenevole(req.ID); err != nil {
		http.Error(w, "erreur lors de la rejection du bénévole", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusOK)
}

func GetAllBenevoles(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)

	if err != nil {
		http.Error(w, "token invalide", http.StatusUnauthorized)
		return
	}

	if claims.Role != "admin" {
		http.Error(w, "accès réservé aux admins", http.StatusForbidden)
		return
	}

	benevoles, err := db.GetAllBenevoles()
	if err != nil {
		http.Error(w, "erreur lors de la récupération des bénévoles", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(benevoles)
}
