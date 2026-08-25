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

func GetAllAdherents(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		http.Error(w, "token JWT invalide", http.StatusUnauthorized)
		return
	}

	if claims.Role != "admin" {
		http.Error(w, "accès réservé aux admins", http.StatusForbidden)
		return
	}

	adherents, err := db.GetAllAdherents()
	if err != nil {
		http.Error(w, "erreur lors de la récupération des adhérents", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(adherents)
}

func GetOwnAdherent(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		http.Error(w, "token invalide", http.StatusUnauthorized)
		return
	}
	if claims.Role != "adherent" {
		http.Error(w, "accès réservé aux adhérents", http.StatusForbidden)
		return
	}

	adherent, err := db.GetAdherentByID(claims.ID)
	if err != nil {
		http.Error(w, "erreur lors de la récupération de l'adhérent", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(adherent)
}

func UpdateAdherentProfile(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		http.Error(w, "token JWT invalide", http.StatusUnauthorized)
		return
	}

	if claims.Role != "adherent" {
		http.Error(w, "accès réservé aux adhérents", http.StatusForbidden)
		return
	}

	var req struct {
		Nom        string `json:"nom"`
		Adresse    string `json:"adresse"`
		CodePostal string `json:"code_postal"`
		Ville      string `json:"ville"`
		Telephone  string `json:"telephone"`
	}
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "corps de requête JSON invalide", http.StatusBadRequest)
		return
	}

	if err := db.UpdateAdherent(claims.ID, req.Nom, req.Adresse, req.CodePostal, req.Ville, req.Telephone); err != nil {
		http.Error(w, "erreur lors de la mise à jour du profil", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusOK)
}

func UpdateAdherentPassword(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		http.Error(w, "token JWT invalide", http.StatusUnauthorized)
		return
	}

	if claims.Role != "adherent" {
		http.Error(w, "accès réservé aux adhérents", http.StatusForbidden)
		return
	}

	var req struct {
		OldPassword string `json:"old_password"`
		NewPassword string `json:"new_password"`
	}
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "corps de requête JSON invalide", http.StatusBadRequest)
		return
	}

	adherent, err := db.GetAdherentByID(claims.ID)
	if err != nil {
		http.Error(w, "erreur lors de la récupération de l'adhérent", http.StatusInternalServerError)
		return
	}

	if !utils.CheckPasswordHash(req.OldPassword, adherent.PasswordHash) {
		http.Error(w, "mot de passe actuel incorrect", http.StatusUnauthorized)
		return
	}

	newHash, err := utils.HashPassword(req.NewPassword)
	if err != nil {
		http.Error(w, "impossible de hasher le mot de passe", http.StatusInternalServerError)
		return
	}

	if err := db.UpdateAdherentPassword(claims.ID, newHash); err != nil {
		http.Error(w, "erreur lors de la mise à jour du mot de passe", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusOK)
}

func DeleteAdherent(w http.ResponseWriter, r *http.Request) {
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
		http.Error(w, "données invalides", http.StatusBadRequest)
		return
	}

	if err := db.DeleteAdherent(req.ID); err != nil {
		http.Error(w, "erreur lors de la suppression de l'adhérent", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusNoContent)
}
