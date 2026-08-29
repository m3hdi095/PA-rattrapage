package app

import (
	"api/db"
	"api/models"
	"api/utils"
	"encoding/json"
	"errors"
	"net/http"
	"strconv"
)

func CreateLivraison(w http.ResponseWriter, r *http.Request) {
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
		TourneeID      int `json:"tournee_id"`
		DestinataireID int `json:"destinataire_id"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.JSONError(w, "données invalides", http.StatusBadRequest)
		return
	}

	livraison := &models.Livraison{
		TourneeID:      req.TourneeID,
		DestinataireID: req.DestinataireID,
	}

	id, err := db.CreateLivraison(livraison)
	if err != nil {
		utils.JSONError(w, "erreur lors de la création de la livraison", http.StatusInternalServerError)
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
		utils.JSONError(w, "token invalide", http.StatusUnauthorized)
		return
	}

	if claims.Role != "admin" {
		utils.JSONError(w, "accès réservé aux admins", http.StatusForbidden)
		return
	}

	livraisons, err := db.GetAllLivraisons()
	if err != nil {
		utils.JSONError(w, "erreur lors de la récupération des livraisons", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(livraisons)
}

func UpdateLivraisonStatut(w http.ResponseWriter, r *http.Request) {
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

	if req.Statut != "prevue" && req.Statut != "livree" && req.Statut != "annulee" {
		utils.JSONError(w, "statut invalide", http.StatusBadRequest)
		return
	}

	if err := db.UpdateStatutLivraison(req.ID, req.Statut); err != nil {
		utils.JSONError(w, "erreur lors de la mise à jour du statut de la livraison", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusOK)
}

func AddProduitLivraison(w http.ResponseWriter, r *http.Request) {
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
		LivraisonID int `json:"livraison_id"`
		ProduitID   int `json:"produit_id"`
		Quantite    int `json:"quantite"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.JSONError(w, "données invalides", http.StatusBadRequest)
		return
	}

	if req.Quantite < 1 {
		utils.JSONError(w, "la quantité doit être supérieure à 0", http.StatusBadRequest)
		return
	}

	if err := db.AddProduitToLivraison(req.LivraisonID, req.ProduitID, req.Quantite); err != nil {
		if errors.Is(err, db.ErrStockInsuffisant) {
			utils.JSONError(w, "stock insuffisant pour ce produit", http.StatusConflict)
			return
		}
		if errors.Is(err, db.ErrLivraisonNonModifiable) {
			utils.JSONError(w, "seule une livraison prévue peut être modifiée", http.StatusConflict)
			return
		}
		utils.JSONError(w, "erreur lors de l'ajout du produit à la livraison", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusOK)
}

func GetLivraisonRecapOptions(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Access-Control-Allow-Methods", "GET")
	w.Header().Set("Access-Control-Allow-Headers", "Authorization")
	w.WriteHeader(http.StatusNoContent)
}

func GetLivraisonRecap(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Access-Control-Allow-Origin", "*")

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

	livraisonID := r.URL.Query().Get("livraison_id")
	if livraisonID == "" {
		utils.JSONError(w, "livraison_id manquant", http.StatusBadRequest)
		return
	}

	livraisonIDInt, err := strconv.Atoi(livraisonID)
	if err != nil {
		utils.JSONError(w, "livraison_id invalide", http.StatusBadRequest)
		return
	}

	produits, err := db.GetProduitsByLivraison(livraisonIDInt)
	if err != nil {
		utils.JSONError(w, "erreur lors de la récupération des produits de la livraison", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(produits)
}

func DeleteLivraison(w http.ResponseWriter, r *http.Request) {
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

	if err := db.DeleteLivraison(req.ID); err != nil {
		utils.JSONError(w, "erreur lors de la suppression de la livraison", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusNoContent)
}
