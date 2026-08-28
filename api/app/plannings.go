package app

import (
	"api/db"
	"api/models"
	"api/utils"
	"encoding/json"
	"fmt"
	"log"
	"net/http"

	"github.com/xuri/excelize/v2"
)

func CreatePlanning(w http.ResponseWriter, r *http.Request) {
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
		ServiceID  int    `json:"service_id"`
		BenevoleID int    `json:"benevole_id"`
		DateDebut  string `json:"date_debut"`
		DateFin    string `json:"date_fin"`
		Lieu       string `json:"lieu"`
		PlacesMax  int    `json:"places_max"`
	}

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.JSONError(w, "données invalides", http.StatusBadRequest)
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
		utils.JSONError(w, "erreur lors de la création du planning", http.StatusInternalServerError)
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
		utils.JSONError(w, "token invalide", http.StatusUnauthorized)
		return
	}

	plannings, err := db.GetAllPlannings()
	if err != nil {
		utils.JSONError(w, "erreur lors de la récupération des plannings", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(plannings)
}

func buildPlanningExcel(plannings []models.Planning) (*excelize.File, error) {
	f := excelize.NewFile()

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

	return f, nil
}

func ExportPlanningsExcel(w http.ResponseWriter, r *http.Request) {
	tokenString := r.Header.Get("Authorization")
	claims, err := utils.VerifyJWT(tokenString)
	if err != nil {
		utils.JSONError(w, "token invalide", http.StatusUnauthorized)
		return
	}

	if claims.Role != "benevole" {
		utils.JSONError(w, "accès réservé aux bénévoles", http.StatusForbidden)
		return
	}

	plannings, err := db.GetPlanningsByBenevole(claims.ID)
	if err != nil {
		utils.JSONError(w, "erreur lors de la récupération des plannings", http.StatusInternalServerError)
		return
	}

	f, _ := buildPlanningExcel(plannings)
	defer f.Close()

	w.Header().Set("Content-Type", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
	w.Header().Set("Content-Disposition", "attachment; filename=planning.xlsx")
	f.Write(w)
}

func EnvoiPlanningsBenevoles(w http.ResponseWriter, r *http.Request) {
	benevoles, err := db.GetAllBenevoles()
	if err != nil {
		utils.JSONError(w, "erreur lors de la récupération des bénévoles", http.StatusInternalServerError)
		return
	}

	envoyes := 0
	for _, benevole := range benevoles {
		if benevole.StatutCandidature != "valide" {
			continue
		}

		plannings, err := db.GetPlanningsByBenevole(benevole.ID)
		if err != nil || len(plannings) == 0 {
			continue
		}

		f, _ := buildPlanningExcel(plannings)
		buf, err := f.WriteToBuffer()
		f.Close()
		if err != nil {
			log.Printf("échec génération excel pour %s: %v", benevole.Email, err)
			continue
		}

		subject := "Votre planning NO MORE WASTE"
		body := "Bonjour " + benevole.Prenom + ",\r\n\r\n" +
			"Veuillez trouver ci-joint votre planning.\r\n\r\n" +
			"L'équipe NO MORE WASTE"

		if err := utils.SendEmailWithAttachment(benevole.Email, subject, body, "planning.xlsx", buf.Bytes()); err != nil {
			log.Printf("échec envoi planning à %s: %v", benevole.Email, err)
			continue
		}
		envoyes++
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]int{"emails_envoyes": envoyes})
}

func DeletePlanning(w http.ResponseWriter, r *http.Request) {
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

	if err := db.DeletePlanning(req.ID); err != nil {
		utils.JSONError(w, "erreur lors de la suppression du planning", http.StatusInternalServerError)
		return
	}

	w.WriteHeader(http.StatusNoContent)
}
