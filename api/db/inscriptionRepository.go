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

func GetInscription(planningID, adherentID int) (*models.Inscription, error) {
	var inscription models.Inscription
	row := Connection.QueryRow(
		"SELECT id, planning_id, adherent_id, statut FROM inscriptions_service WHERE planning_id = ? AND adherent_id = ?",
		planningID, adherentID,
	)
	if err := row.Scan(&inscription.ID, &inscription.PlanningID, &inscription.AdherentID, &inscription.Statut); err != nil {
		return nil, err
	}
	return &inscription, nil
}

func ReactivateInscription(id int) error {
	_, err := Connection.Exec("UPDATE inscriptions_service SET statut = 'inscrit' WHERE id = ?", id)
	if err != nil {
		return fmt.Errorf("failed to reactivate inscription: %w", err)
	}
	return nil
}

func GetInscriptionsByAdherent(adherentID int) ([]models.InscriptionDetail, error) {
	rows, err := Connection.Query(`
		SELECT i.id, i.planning_id, i.statut, pl.service_id, pl.date_debut, pl.date_fin, pl.lieu
		FROM inscriptions_service i
		JOIN plannings pl ON pl.id = i.planning_id
		WHERE i.adherent_id = ? AND i.statut = 'inscrit'
		ORDER BY pl.date_debut
	`, adherentID)
	if err != nil {
		return nil, fmt.Errorf("failed to get inscriptions by adherent: %w", err)
	}
	defer rows.Close()

	var inscriptions []models.InscriptionDetail
	for rows.Next() {
		var i models.InscriptionDetail
		if err := rows.Scan(&i.ID, &i.PlanningID, &i.Statut, &i.ServiceID, &i.DateDebut, &i.DateFin, &i.Lieu); err != nil {
			return nil, fmt.Errorf("failed to scan inscription detail: %w", err)
		}
		inscriptions = append(inscriptions, i)
	}

	if err := rows.Err(); err != nil {
		return nil, fmt.Errorf("failed to iterate inscriptions: %w", err)
	}

	return inscriptions, nil
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

	if err := rows.Err(); err != nil {
		return nil, fmt.Errorf("failed to iterate inscriptions: %w", err)
	}

	return inscriptions, nil
}
