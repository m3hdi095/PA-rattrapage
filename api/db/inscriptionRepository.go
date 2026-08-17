package db

import (
	"api/models"
	"fmt"
)

func CreateInscription(inscription *models.Inscription) (int, error) {
	res, err := Connection.Exec(
		"INSERT INTO inscriptions_service (planning_id, adherent_id) VALUES (?, ?)",
		inscription.PlanningID,
		inscription.AdherentID,
	)
	if err != nil {
		return 0, fmt.Errorf("failed to create inscription: %w", err)
	}
	id64, err := res.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("failed to get last insert ID: %w", err)
	}
	id := int(id64)
	inscription.ID = id
	return id, nil
}

func GetInscriptionsByPlanning(planningID int) ([]models.Inscription, error) {
	rows, err := Connection.Query("SELECT id, planning_id, adherent_id, statut FROM inscriptions_service WHERE planning_id = ?", planningID)
	if err != nil {
		return nil, fmt.Errorf("failed to query inscriptions: %w", err)
	}
	defer rows.Close()

	var inscriptions []models.Inscription
	for rows.Next() {
		var inscription models.Inscription
		err := rows.Scan(&inscription.ID, &inscription.PlanningID, &inscription.AdherentID, &inscription.Statut)
		if err != nil {
			return nil, fmt.Errorf("failed to scan inscription: %w", err)
		}
		inscriptions = append(inscriptions, inscription)
	}

	return inscriptions, nil
}
