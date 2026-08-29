package db

import (
	"api/models"
	"fmt"
)

func CreateTournee(tournee *models.Tournee) (int, error) {
	res, err := Connection.Exec(
		"INSERT INTO tournees (benevole_id, date_tournee) VALUES (?, ?)",
		tournee.BenevoleID, tournee.DateTournee,
	)
	if err != nil {
		return 0, fmt.Errorf("failed to create tournee: %w", err)
	}

	id64, err := res.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("failed to get inserted id: %w", err)
	}

	id := int(id64)
	tournee.ID = id
	return id, nil
}

func GetAllTournees() ([]models.Tournee, error) {
	rows, err := Connection.Query("SELECT id, benevole_id, date_tournee, statut FROM tournees")
	if err != nil {
		return nil, fmt.Errorf("failed to get all tournees: %w", err)
	}
	defer rows.Close()

	var tournees []models.Tournee
	for rows.Next() {
		var t models.Tournee
		if err := rows.Scan(&t.ID, &t.BenevoleID, &t.DateTournee, &t.Statut); err != nil {
			return nil, fmt.Errorf("failed to scan tournee: %w", err)
		}
		tournees = append(tournees, t)
	}
	if err := rows.Err(); err != nil {
		return nil, fmt.Errorf("failed to iterate tournees: %w", err)
	}
	return tournees, nil
}

func GetTourneeStatut(id int) (string, error) {
	var statut string
	err := Connection.QueryRow("SELECT statut FROM tournees WHERE id = ?", id).Scan(&statut)
	if err != nil {
		return "", fmt.Errorf("failed to get tournee statut: %w", err)
	}
	return statut, nil
}

func UpdateStatutTournee(id int, statut string) error {
	_, err := Connection.Exec("UPDATE tournees SET statut = ? WHERE id = ?", statut, id)
	if err != nil {
		return fmt.Errorf("failed to update tournee statut: %w", err)
	}
	return nil
}

func DeleteTournee(id int) error {
	_, err := Connection.Exec("DELETE FROM tournees WHERE id = ?", id)
	if err != nil {
		return fmt.Errorf("failed to delete tournee: %w", err)
	}
	return nil
}
