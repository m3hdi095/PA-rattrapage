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
	rows, err := Connection.Query(`
		SELECT p.id, p.service_id, p.benevole_id, p.date_debut, p.date_fin, p.lieu, p.places_max,
			p.places_max - (SELECT COUNT(*) FROM inscriptions_service i WHERE i.planning_id = p.id AND i.statut = 'inscrit') AS places_restantes
		FROM plannings p
	`)
	if err != nil {
		return nil, fmt.Errorf("failed to get all plannings: %w", err)
	}
	defer rows.Close()

	var plannings []models.Planning
	for rows.Next() {
		var planning models.Planning
		err := rows.Scan(&planning.ID, &planning.ServiceID, &planning.BenevoleID, &planning.DateDebut, &planning.DateFin, &planning.Lieu, &planning.PlacesMax, &planning.PlacesRestantes)
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

func GetPlanningDateDebut(planningID int) (string, error) {
	var dateDebut string
	err := Connection.QueryRow("SELECT date_debut FROM plannings WHERE id = ?", planningID).Scan(&dateDebut)
	if err != nil {
		return "", fmt.Errorf("failed to get planning date_debut: %w", err)
	}
	return dateDebut, nil
}

func GetPlanningPlacesRestantes(planningID int) (int, error) {
	var placesRestantes int
	err := Connection.QueryRow(`
		SELECT p.places_max - (SELECT COUNT(*) FROM inscriptions_service i WHERE i.planning_id = p.id AND i.statut = 'inscrit')
		FROM plannings p WHERE p.id = ?
	`, planningID).Scan(&placesRestantes)
	if err != nil {
		return 0, fmt.Errorf("failed to get planning places restantes: %w", err)
	}
	return placesRestantes, nil
}

func HasPlanningsFuturs(benevoleID int) (bool, error) {
	var count int
	err := Connection.QueryRow(
		"SELECT COUNT(*) FROM plannings WHERE benevole_id = ? AND date_debut >= NOW()",
		benevoleID,
	).Scan(&count)
	if err != nil {
		return false, fmt.Errorf("failed to count future plannings: %w", err)
	}
	return count > 0, nil
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

func UpdatePlanning(planning *models.Planning) error {
	_, err := Connection.Exec(
		"UPDATE plannings SET service_id = ?, benevole_id = ?, date_debut = ?, date_fin = ?, lieu = ?, places_max = ? WHERE id = ?",
		planning.ServiceID, planning.BenevoleID, planning.DateDebut, planning.DateFin, planning.Lieu, planning.PlacesMax, planning.ID,
	)
	if err != nil {
		return fmt.Errorf("failed to update planning: %w", err)
	}
	return nil
}

func DeletePlanning(id int) error {
	_, err := Connection.Exec("DELETE FROM plannings WHERE id = ?", id)
	if err != nil {
		return fmt.Errorf("failed to delete planning: %w", err)
	}
	return nil
}
