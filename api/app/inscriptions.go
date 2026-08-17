package app

import (
	"api/db"
	"api/models"
	"api/utils"
	"encoding/json"
	"net/http"
	"strconv"
)

func CreateInscription(w http.ResponseWriter, r *http.Request) {
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

	var req struct {
		PlanningID int `json:"planning_id"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "données invalides", http.StatusBadRequest)
		return
	}

	inscription := &models.Inscription{
		PlanningID: req.PlanningID,
		AdherentID: claims.ID,
	}

	id, err := db.CreateInscription(inscription)
	if err != nil {
		http.Error(w, "erreur lors de la création de l'inscription", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]int{"id": id})
}

func ListInscriptions(w http.ResponseWriter, r *http.Request) {
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

	planningIDStr := r.URL.Query().Get("planning_id")
	planningID, err := strconv.Atoi(planningIDStr)
	if err != nil {
		http.Error(w, "planning_id invalide", http.StatusBadRequest)
		return
	}

	inscriptions, err := db.GetInscriptionsByPlanning(planningID)
	if err != nil {
		http.Error(w, "erreur lors de la récupération des inscriptions", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(inscriptions)
}
