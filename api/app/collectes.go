package app

import (
	"api/db"
	"api/models"
	"api/utils"
	"encoding/json"
	"net/http"
)

func CreateCollecte(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		utils.JSONError(w, "token invalide", http.StatusUnauthorized)
		return
	}

	if claims.Role != "adherent" {
		utils.JSONError(w, "accès réservé aux adhérents", http.StatusForbidden)
		return
	}

	var req struct {
		DateCollecte string `json:"date_collecte"`
	}
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.JSONError(w, "données invalides", http.StatusBadRequest)
		return
	}

	if err := utils.ValidateFutureDate(req.DateCollecte); err != nil {
		utils.JSONError(w, err.Error(), http.StatusBadRequest)
		return
	}

	collecte := &models.Collecte{
		AdherentID:   claims.ID,
		DateCollecte: req.DateCollecte,
	}

	id, err := db.CreateCollecte(collecte)
	if err != nil {
		utils.JSONError(w, "erreur lors de la création de la collecte", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]int{"id": id})
}

func ListCollectes(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		utils.JSONError(w, "token invalide", http.StatusUnauthorized)
		return
	}

	var collectes []models.Collecte
	if claims.Role == "admin" {
		collectes, err = db.GetAllCollectes()
	} else if claims.Role == "adherent" {
		collectes, err = db.GetCollectesByAdherent(claims.ID)
	} else {
		utils.JSONError(w, "accès refusé", http.StatusForbidden)
		return
	}
	if err != nil {
		utils.JSONError(w, "erreur lors de la récupération des collectes", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(collectes)
}

func UpdateCollecteStatut(w http.ResponseWriter, r *http.Request) {
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
		ID     int    `json:"id"`
		Statut string `json:"statut"`
	}
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.JSONError(w, "données invalides", http.StatusBadRequest)
		return
	}

	if req.Statut != "planifiee" && req.Statut != "effectuee" && req.Statut != "annulee" {
		utils.JSONError(w, "statut invalide", http.StatusBadRequest)
		return
	}

	if err := db.UpdateStatutCollecte(req.ID, req.Statut); err != nil {
		utils.JSONError(w, "erreur lors de la mise à jour de la collecte", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusOK)
}

func DeleteCollecte(w http.ResponseWriter, r *http.Request) {
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

	if err := db.DeleteCollecte(req.ID); err != nil {
		utils.JSONError(w, "erreur lors de la suppression de la collecte", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusNoContent)
}
