package app

import (
	"api/db"
	"api/models"
	"api/utils"
	"encoding/json"
	"net/http"
)

func CreateDestinataire(w http.ResponseWriter, r *http.Request) {
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
		Type       string `json:"type"`
		Nom        string `json:"nom"`
		Adresse    string `json:"adresse"`
		CodePostal string `json:"code_postal"`
		Ville      string `json:"ville"`
		Telephone  string `json:"telephone"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "données invalides", http.StatusBadRequest)
		return
	}

	destinataire := &models.Destinataire{
		Type:       req.Type,
		Nom:        req.Nom,
		Adresse:    req.Adresse,
		CodePostal: req.CodePostal,
		Ville:      req.Ville,
		Telephone:  req.Telephone,
	}

	id, err := db.CreateDestinataire(destinataire)
	if err != nil {
		http.Error(w, "erreur lors de la création du destinataire", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]int{"id": id})
}

func ListDestinataires(w http.ResponseWriter, r *http.Request) {
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

	destinataires, err := db.GetAllDestinataires()
	if err != nil {
		http.Error(w, "erreur lors de la récupération des destinataires", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(destinataires)
}

func DeleteDestinataire(w http.ResponseWriter, r *http.Request) {
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

	if err := db.DeleteDestinataire(req.ID); err != nil {
		http.Error(w, "erreur lors de la suppression du destinataire", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusNoContent)
}
