package app

import (
	"api/db"
	"api/models"
	"api/utils"
	"encoding/json"
	"net/http"
)

func CreateLivraison(w http.ResponseWriter, r *http.Request) {
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
		TourneeID      int `json:"tournee_id"`
		DestinataireID int `json:"destinataire_id"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "données invalides", http.StatusBadRequest)
		return
	}

	livraison := &models.Livraison{
		TourneeID:      req.TourneeID,
		DestinataireID: req.DestinataireID,
	}

	id, err := db.CreateLivraison(livraison)
	if err != nil {
		http.Error(w, "erreur lors de la création de la livraison", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]int{"id": id})
}

func ListLivraisons(w http.ResponseWriter, r *http.Request) {
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

	livraisons, err := db.GetAllLivraisons()
	if err != nil {
		http.Error(w, "erreur lors de la récupération des livraisons", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(livraisons)
}

func UpdateLivraisonStatut(w http.ResponseWriter, r *http.Request) {
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

	if req.Statut != "prevue" && req.Statut != "livree" && req.Statut != "annulee" {
		http.Error(w, "statut invalide", http.StatusBadRequest)
		return
	}

	if err := db.UpdateStatutLivraison(req.ID, req.Statut); err != nil {
		http.Error(w, "erreur lors de la mise à jour du statut de la livraison", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusOK)
}
