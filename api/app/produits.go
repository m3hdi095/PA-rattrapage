package app

import (
	"api/db"
	"api/models"
	"api/utils"
	"encoding/json"
	"net/http"
	"strings"
)

func CreateProduit(w http.ResponseWriter, r *http.Request) {
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
		CollecteID       int    `json:"collecte_id"`
		Nom              string `json:"nom"`
		Quantite         int    `json:"quantite"`
		DateLimiteConso  string `json:"date_limite_conso"`
		EmplacementStock string `json:"emplacement_stock"`
	}
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.JSONError(w, "données invalides", http.StatusBadRequest)
		return
	}

	if req.Quantite < 1 {
		utils.JSONError(w, "la quantité doit être supérieure à 0", http.StatusBadRequest)
		return
	}

	collecteStatut, err := db.GetCollecteStatut(req.CollecteID)
	if err != nil {
		utils.JSONError(w, "collecte introuvable", http.StatusBadRequest)
		return
	}
	if collecteStatut == "annulee" {
		utils.JSONError(w, "impossible d'ajouter un produit à une collecte annulée", http.StatusConflict)
		return
	}

	produit := &models.Produit{
		CollecteID:       req.CollecteID,
		Nom:              req.Nom,
		Quantite:         req.Quantite,
		DateLimiteConso:  req.DateLimiteConso,
		EmplacementStock: req.EmplacementStock,
	}

	var id int
	const maxTentatives = 5
	for i := 0; i < maxTentatives; i++ {
		produit.CodeBarre = utils.GenerateCodeBarre()
		id, err = db.CreateProduit(produit)
		if err == nil || !strings.Contains(err.Error(), "Duplicate entry") {
			break
		}
	}
	if err != nil {
		utils.JSONError(w, "erreur lors de la création du produit", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]int{"id": id})
}

func ListProduits(w http.ResponseWriter, r *http.Request) {
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

	produits, err := db.GetProduits()
	if err != nil {
		utils.JSONError(w, "erreur lors de la récupération des produits", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(produits)
}

func UpdateProduitStatut(w http.ResponseWriter, r *http.Request) {
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

	if req.Statut != "en_stock" && req.Statut != "distribue" && req.Statut != "perime" {
		utils.JSONError(w, "statut invalide", http.StatusBadRequest)
		return
	}

	if err := db.UpdateStatutProduit(req.ID, req.Statut); err != nil {
		utils.JSONError(w, "erreur lors de la mise à jour du statut du produit", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusOK)
}

func DeleteProduit(w http.ResponseWriter, r *http.Request) {
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

	if err := db.DeleteProduit(req.ID); err != nil {
		utils.JSONError(w, "erreur lors de la suppression du produit", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusNoContent)
}
