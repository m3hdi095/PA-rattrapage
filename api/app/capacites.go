package app

import (
	"api/db"
	"api/models"
	"api/utils"
	"encoding/json"
	"net/http"
)

func ListCapacites(w http.ResponseWriter, r *http.Request) {
	capacites, err := db.GetAllCapacites()
	if err != nil {
		http.Error(w, "erreur lors de la récupération des compétences", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(capacites)
}

func CreateCapacite(w http.ResponseWriter, r *http.Request) {
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
		Libelle string `json:"libelle"`
	}
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "données invalides", http.StatusBadRequest)
		return
	}
	if req.Libelle == "" {
		http.Error(w, "libelle obligatoire", http.StatusBadRequest)
		return
	}

	capacite := &models.Capacite{Libelle: req.Libelle}
	if _, err := db.CreateCapacite(capacite); err != nil {
		http.Error(w, "erreur lors de la création de la compétence", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(capacite)
}

func DeleteCapacite(w http.ResponseWriter, r *http.Request) {
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

	if err := db.DeleteCapacite(req.ID); err != nil {
		http.Error(w, "erreur lors de la suppression de la compétence", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusNoContent)
}
