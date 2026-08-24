package db

import (
	"api/models"
	"fmt"
)

func CreatePlanning(planning *models.Planning) (int, error) {
	res, err := Connection.Exec(
		"INSERT INTO plannings (service_id, benevole_id, date_debut, date_fin, lieu, places_max) VALUES (?, ?, ?, ?, ?, ?)",
		planning.ServiceID, planning.BenevoleID, planning.DateDebut, planning.DateFin, planning.Lieu, planning.PlacesMax,
	)
	if err != nil {
		return 0, fmt.Errorf("failed to create planning: %w", err)
	}

	id64, err := res.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("failed to get last insert ID: %w", err)
	}

	id := int(id64)
	planning.ID = int(id)
	return int(id), nil
}

func GetAllPlannings() ([]models.Planning, error) {
	rows, err := Connection.Query("SELECT id, service_id, benevole_id, date_debut, date_fin, lieu, places_max FROM plannings")
	if err != nil {
		return nil, fmt.Errorf("failed to get all plannings: %w", err)
	}
	defer rows.Close()

	var plannings []models.Planning
	for rows.Next() {
		var planning models.Planning
		err := rows.Scan(&planning.ID, &planning.ServiceID, &planning.BenevoleID, &planning.DateDebut, &planning.DateFin, &planning.Lieu, &planning.PlacesMax)
		if err != nil {
			return nil, fmt.Errorf("failed to scan planning row: %w", err)
		}
		plannings = append(plannings, planning)
	}

	if err := rows.Err(); err != nil {
		return nil, fmt.Errorf("failed to iterate plannings: %w", err)
	}

	return plannings, nil
}

func GetPlanningsByBenevole(benevoleID int) ([]models.Planning, error) {
	rows, err := Connection.Query("SELECT id, service_id, benevole_id, date_debut, date_fin, lieu, places_max FROM plannings WHERE benevole_id = ?", benevoleID)
	if err != nil {
		return nil, fmt.Errorf("failed to get plannings by benevole: %w", err)
	}
	defer rows.Close()

	var plannings []models.Planning
	for rows.Next() {
		var planning models.Planning
		err := rows.Scan(&planning.ID, &planning.ServiceID, &planning.BenevoleID, &planning.DateDebut, &planning.DateFin, &planning.Lieu, &planning.PlacesMax)
		if err != nil {
			return nil, fmt.Errorf("failed to scan planning: %w", err)
		}
		plannings = append(plannings, planning)
	}

	if err := rows.Err(); err != nil {
		return nil, fmt.Errorf("failed to iterate plannings: %w", err)
	}

	return plannings, nil
}

func DeletePlanning(id int) error {
	_, err := Connection.Exec("DELETE FROM plannings WHERE id = ?", id)
	if err != nil {
		return fmt.Errorf("failed to delete planning: %w", err)
	}
	return nil
}
