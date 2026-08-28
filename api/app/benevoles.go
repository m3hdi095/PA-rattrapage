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
		utils.JSONError(w, "corps de requête JSON invalide", http.StatusBadRequest)
		return
	}

	if req.Email == "" || req.Password == "" || req.Nom == "" || req.Prenom == "" {
		utils.JSONError(w, "email, password, nom et prenom sont obligatoires", http.StatusBadRequest)
		return
	}

	if !utils.IsValidEmail(req.Email) {
		utils.JSONError(w, "email invalide", http.StatusBadRequest)
		return
	}

	if !utils.IsValidPassword(req.Password) {
		utils.JSONError(w, "mot de passe trop court (6 caractères minimum)", http.StatusBadRequest)
		return
	}

	passwordHash, err := utils.HashPassword(req.Password)
	if err != nil {
		utils.JSONError(w, "impossible de hasher le mot de passe", http.StatusInternalServerError)
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
		utils.JSONError(w, "erreur lors de la création du bénévole", http.StatusInternalServerError)
		return
	}
	benevole.StatutCandidature = "en_attente"

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(benevole)
}

func ValidateBenevole(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		utils.JSONError(w, "token invalide", http.StatusUnauthorized)
		return
	}

	if claims.Role != "admin" {
		utils.JSONError(w, "accès réservé aux admins", http.StatusForbidden)
		return
	}

	var req struct {
		ID int `json:"id"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.JSONError(w, "corps de requête JSON invalide", http.StatusBadRequest)
		return
	}

	if err := db.ValidateBenevole(req.ID); err != nil {
		utils.JSONError(w, "erreur lors de la validation du bénévole", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusOK)
}

func RejectBenevole(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		utils.JSONError(w, "token invalide", http.StatusUnauthorized)
		return
	}

	if claims.Role != "admin" {
		utils.JSONError(w, "accès réservé aux admins", http.StatusForbidden)
		return
	}

	var req struct {
		ID int `json:"id"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.JSONError(w, "corps de requête JSON invalide", http.StatusBadRequest)
		return
	}

	if err := db.RejectBenevole(req.ID); err != nil {
		utils.JSONError(w, "erreur lors de la rejection du bénévole", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusOK)
}

func GetAllBenevoles(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)

	if err != nil {
		utils.JSONError(w, "token invalide", http.StatusUnauthorized)
		return
	}

	if claims.Role != "admin" {
		utils.JSONError(w, "accès réservé aux admins", http.StatusForbidden)
		return
	}

	benevoles, err := db.GetAllBenevoles()
	if err != nil {
		utils.JSONError(w, "erreur lors de la récupération des bénévoles", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(benevoles)
}

func UpdateBenevoleProfile(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		utils.JSONError(w, "token JWT invalide", http.StatusUnauthorized)
		return
	}

	if claims.Role != "benevole" {
		utils.JSONError(w, "accès réservé aux bénévoles", http.StatusForbidden)
		return
	}

	var req struct {
		Nom       string `json:"nom"`
		Prenom    string `json:"prenom"`
		Telephone string `json:"telephone"`
	}
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.JSONError(w, "corps de requête JSON invalide", http.StatusBadRequest)
		return
	}

	if err := db.UpdateBenevole(claims.ID, req.Nom, req.Prenom, req.Telephone); err != nil {
		utils.JSONError(w, "erreur lors de la mise à jour du profil", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusOK)
}

func GetOwnBenevole(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		utils.JSONError(w, "token invalide", http.StatusUnauthorized)
		return
	}
	if claims.Role != "benevole" {
		utils.JSONError(w, "accès réservé aux bénévoles", http.StatusForbidden)
		return
	}

	benevole, err := db.GetBenevoleByID(claims.ID)
	if err != nil {
		utils.JSONError(w, "erreur lors de la récupération du bénévole", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(benevole)
}

func UpdateBenevoleCapacites(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		utils.JSONError(w, "token invalide", http.StatusUnauthorized)
		return
	}
	if claims.Role != "benevole" {
		utils.JSONError(w, "accès réservé aux bénévoles", http.StatusForbidden)
		return
	}

	var req struct {
		Capacites []string `json:"capacites"`
	}
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.JSONError(w, "données invalides", http.StatusBadRequest)
		return
	}

	if err := db.UpdateBenevoleCapacites(claims.ID, req.Capacites); err != nil {
		utils.JSONError(w, "erreur lors de la mise à jour des compétences", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusOK)
}

func UpdateBenevolePassword(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		utils.JSONError(w, "token JWT invalide", http.StatusUnauthorized)
		return
	}

	if claims.Role != "benevole" {
		utils.JSONError(w, "accès réservé aux bénévoles", http.StatusForbidden)
		return
	}

	var req struct {
		OldPassword string `json:"old_password"`
		NewPassword string `json:"new_password"`
	}
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.JSONError(w, "corps de requête JSON invalide", http.StatusBadRequest)
		return
	}

	benevole, err := db.GetBenevoleByID(claims.ID)
	if err != nil {
		utils.JSONError(w, "erreur lors de la récupération du bénévole", http.StatusInternalServerError)
		return
	}

	if !utils.CheckPasswordHash(req.OldPassword, benevole.PasswordHash) {
		utils.JSONError(w, "mot de passe actuel incorrect", http.StatusUnauthorized)
		return
	}

	if !utils.IsValidPassword(req.NewPassword) {
		utils.JSONError(w, "mot de passe trop court (6 caractères minimum)", http.StatusBadRequest)
		return
	}

	newHash, err := utils.HashPassword(req.NewPassword)
	if err != nil {
		utils.JSONError(w, "impossible de hasher le mot de passe", http.StatusInternalServerError)
		return
	}

	if err := db.UpdateBenevolePassword(claims.ID, newHash); err != nil {
		utils.JSONError(w, "erreur lors de la mise à jour du mot de passe", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusOK)
}

func DeleteBenevole(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		utils.JSONError(w, "token invalide", http.StatusUnauthorized)
		return
	}

	if claims.Role != "admin" {
		utils.JSONError(w, "accès réservé aux admins", http.StatusForbidden)
		return
	}

	var req struct {
		ID int `json:"id"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.JSONError(w, "données invalides", http.StatusBadRequest)
		return
	}

	if err := db.DeleteBenevole(req.ID); err != nil {
		utils.JSONError(w, "erreur lors de la suppression du bénévole", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusNoContent)
}
