package app

import (
	"api/db"
	"api/models"
	"api/utils"
	"encoding/json"
	"net/http"
)

func CreateService(w http.ResponseWriter, r *http.Request) {
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
		Nom         string `json:"nom"`
		Description string `json:"description"`
		CapaciteID  *int   `json:"capacite_id"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "données invalides", http.StatusBadRequest)
		return
	}

	service := &models.Service{
		Nom:         req.Nom,
		Description: req.Description,
		CapaciteID:  req.CapaciteID,
	}

	id, err := db.CreateService(service)
	if err != nil {
		http.Error(w, "erreur lors de la création du service", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]int{"id": id})

}

func ListServices(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	_, err := utils.VerifyJWT(tokenString)

	if err != nil {
		http.Error(w, "token invalide", http.StatusUnauthorized)
		return
	}

	// Lecture ouverte à tout utilisateur connecté : les adhérents doivent
	// pouvoir parcourir les services pour s'y inscrire.

	services, err := db.GetAllServices()
	if err != nil {
		http.Error(w, "erreur lors de la récupération des services", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(services)
}

func DeleteService(w http.ResponseWriter, r *http.Request) {
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

	if err := db.DeleteService(req.ID); err != nil {
		http.Error(w, "erreur lors de la suppression du service", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusNoContent)
}
