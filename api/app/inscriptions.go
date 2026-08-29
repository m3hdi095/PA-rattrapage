package app

import (
	"api/db"
	"api/models"
	"api/utils"
	"database/sql"
	"encoding/json"
	"errors"
	"net/http"
	"strconv"
)

func CreateInscription(w http.ResponseWriter, r *http.Request) {
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
		PlanningID int `json:"planning_id"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.JSONError(w, "données invalides", http.StatusBadRequest)
		return
	}

	// Un adherent ne peut avoir qu'une seule ligne pour un meme creneau
	// (contrainte UNIQUE en base). S'il en avait deja une "annulee", on la
	// reactive plutot que d'essayer d'en creer une nouvelle (ce qui violerait
	// la contrainte et remontait une erreur 500 incomprehensible).
	existing, err := db.GetInscription(req.PlanningID, claims.ID)
	if err != nil && !errors.Is(err, sql.ErrNoRows) {
		utils.JSONError(w, "erreur lors de la vérification de l'inscription", http.StatusInternalServerError)
		return
	}

	if existing != nil && existing.Statut == "inscrit" {
		utils.JSONError(w, "vous êtes déjà inscrit à ce créneau", http.StatusConflict)
		return
	}

	// Un adherent ne peut pas etre inscrit a deux creneaux le meme jour,
	// meme sur des services differents.
	dateDebut, err := db.GetPlanningDateDebut(req.PlanningID)
	if err != nil {
		utils.JSONError(w, "créneau introuvable", http.StatusBadRequest)
		return
	}
	jourCible := dateDebut[:10] // "2026-09-05 09:00:00" -> "2026-09-05"

	mesInscriptions, err := db.GetInscriptionsByAdherent(claims.ID)
	if err != nil {
		utils.JSONError(w, "erreur lors de la vérification des inscriptions existantes", http.StatusInternalServerError)
		return
	}
	for _, mi := range mesInscriptions {
		if mi.DateDebut[:10] == jourCible {
			utils.JSONError(w, "vous êtes déjà inscrit à un autre créneau ce jour-là", http.StatusConflict)
			return
		}
	}

	// A ce stade, l'inscription va prendre une place (nouvelle ou reactivee) :
	// on verifie qu'il en reste.
	placesRestantes, err := db.GetPlanningPlacesRestantes(req.PlanningID)
	if err != nil {
		utils.JSONError(w, "erreur lors de la vérification des places disponibles", http.StatusInternalServerError)
		return
	}
	if placesRestantes <= 0 {
		utils.JSONError(w, "il n'y a plus de places disponibles pour ce créneau", http.StatusConflict)
		return
	}

	if existing != nil {
		if err := db.ReactivateInscription(existing.ID); err != nil {
			utils.JSONError(w, "erreur lors de la réinscription", http.StatusInternalServerError)
			return
		}

		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusOK)
		json.NewEncoder(w).Encode(map[string]int{"id": existing.ID})
		return
	}

	inscription := &models.Inscription{
		PlanningID: req.PlanningID,
		AdherentID: claims.ID,
	}

	id, err := db.CreateInscription(inscription)
	if err != nil {
		utils.JSONError(w, "erreur lors de la création de l'inscription", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]int{"id": id})
}

func CancelInscription(w http.ResponseWriter, r *http.Request) {
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
		ID int `json:"id"`
	}
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.JSONError(w, "données invalides", http.StatusBadRequest)
		return
	}

	if err := db.CancelInscription(req.ID, claims.ID); err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			utils.JSONError(w, "inscription introuvable", http.StatusNotFound)
			return
		}
		utils.JSONError(w, "erreur lors de la désinscription", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusOK)
}

func ListMesInscriptions(w http.ResponseWriter, r *http.Request) {
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

	inscriptions, err := db.GetInscriptionsByAdherent(claims.ID)
	if err != nil {
		utils.JSONError(w, "erreur lors de la récupération des inscriptions", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(inscriptions)
}

func ListInscriptions(w http.ResponseWriter, r *http.Request) {
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

	planningIDStr := r.URL.Query().Get("planning_id")
	planningID, err := strconv.Atoi(planningIDStr)
	if err != nil {
		utils.JSONError(w, "planning_id invalide", http.StatusBadRequest)
		return
	}

	inscriptions, err := db.GetInscriptionsByPlanning(planningID)
	if err != nil {
		utils.JSONError(w, "erreur lors de la récupération des inscriptions", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(inscriptions)
}
