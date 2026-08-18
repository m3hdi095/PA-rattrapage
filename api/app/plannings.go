package app

import (
	"api/db"
	"api/models"
	"api/utils"
	"encoding/json"
	"fmt"
	"net/http"

	"github.com/xuri/excelize/v2"
)

func CreatePlanning(w http.ResponseWriter, r *http.Request) {
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
		ServiceID  int    `json:"service_id"`
		BenevoleID int    `json:"benevole_id"`
		DateDebut  string `json:"date_debut"`
		DateFin    string `json:"date_fin"`
		Lieu       string `json:"lieu"`
		PlacesMax  int    `json:"places_max"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "données invalides", http.StatusBadRequest)
		return
	}

	planning := &models.Planning{
		ServiceID:  req.ServiceID,
		BenevoleID: req.BenevoleID,
		DateDebut:  req.DateDebut,
		DateFin:    req.DateFin,
		Lieu:       req.Lieu,
		PlacesMax:  req.PlacesMax,
	}

	id, err := db.CreatePlanning(planning)
	if err != nil {
		http.Error(w, "erreur lors de la création du planning", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(map[string]int{"id": id})
}

func ListPlannings(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	_, err := utils.VerifyJWT(tokenString)

	if err != nil {
		http.Error(w, "token invalide", http.StatusUnauthorized)
		return
	}

	// Lecture ouverte, même raison que ListServices.

	plannings, err := db.GetAllPlannings()
	if err != nil {
		http.Error(w, "erreur lors de la récupération des plannings", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(plannings)
}

func ExportPlanningsExcel(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		http.Error(w, "token invalide", http.StatusUnauthorized)
		return
	}

	if claims.Role != "benevole" {
		http.Error(w, "accès réservé aux bénévoles", http.StatusForbidden)
		return
	}

	plannings, err := db.GetPlanningsByBenevole(claims.ID)
	if err != nil {
		http.Error(w, "erreur lors de la récupération des plannings", http.StatusInternalServerError)
		return
	}

	f := excelize.NewFile()
	defer f.Close()

	f.SetCellValue("Sheet1", "A1", "Service")
	f.SetCellValue("Sheet1", "B1", "Date début")
	f.SetCellValue("Sheet1", "C1", "Date fin")
	f.SetCellValue("Sheet1", "D1", "Lieu")

	ligne := 2
	for _, p := range plannings {
		f.SetCellValue("Sheet1", fmt.Sprintf("A%d", ligne), p.ServiceID)
		f.SetCellValue("Sheet1", fmt.Sprintf("B%d", ligne), p.DateDebut)
		f.SetCellValue("Sheet1", fmt.Sprintf("C%d", ligne), p.DateFin)
		f.SetCellValue("Sheet1", fmt.Sprintf("D%d", ligne), p.Lieu)
		ligne++
	}

	w.Header().Set("Content-Type", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
	w.Header().Set("Content-Disposition", "attachment; filename=planning.xlsx")
	f.Write(w)
}
