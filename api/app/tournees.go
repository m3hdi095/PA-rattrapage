package app

import (
	"api/db"
	"api/models"
	"api/utils"
	"encoding/json"
	"net/http"
)

func CreateTournee(w http.ResponseWriter, r *http.Request) {
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
		BenevoleID  int    `json:"benevole_id"`
		DateTournee string `json:"date_tournee"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "données invalides", http.StatusBadRequest)
		return
	}

	tournee := &models.Tournee{
		BenevoleID:  req.BenevoleID,
		DateTournee: req.DateTournee,
	}

	id, err := db.CreateTournee(tournee)
	if err != nil {
		http.Error(w, "erreur lors de la création de la tournée", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]int{"id": id})
}

func ListTournees(w http.ResponseWriter, r *http.Request) {
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

	tournees, err := db.GetAllTournees()
	if err != nil {
		http.Error(w, "erreur lors de la récupération des tournées", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(tournees)
}

func UpdateTourneeStatut(w http.ResponseWriter, r *http.Request) {
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
		ID     int    `json:"id"`
		Statut string `json:"statut"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "données invalides", http.StatusBadRequest)
		return
	}

	if req.Statut != "planifiee" && req.Statut != "en_cours" && req.Statut != "terminee" {
		http.Error(w, "statut invalide", http.StatusBadRequest)
		return
	}

	if err := db.UpdateStatutTournee(req.ID, req.Statut); err != nil {
		http.Error(w, "erreur lors de la mise à jour du statut de la tournée", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(map[string]interface{}{"message": "statut de la tournée mis à jour avec succès"})
}
