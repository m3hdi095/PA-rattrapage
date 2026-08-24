package db

import (
	"api/models"
	"fmt"
)

func GetAllCapacites() ([]models.Capacite, error) {
	rows, err := Connection.Query("SELECT id, libelle FROM capacites ORDER BY libelle")
	if err != nil {
		return nil, fmt.Errorf("failed to query capacites: %w", err)
	}
	defer rows.Close()

	var capacites []models.Capacite
	for rows.Next() {
		var c models.Capacite
		if err := rows.Scan(&c.ID, &c.Libelle); err != nil {
			return nil, fmt.Errorf("failed to scan capacite: %w", err)
		}
		capacites = append(capacites, c)
	}
	if err := rows.Err(); err != nil {
		return nil, fmt.Errorf("failed to iterate capacites: %w", err)
	}
	return capacites, nil
}

func CreateCapacite(capacite *models.Capacite) (int, error) {
	res, err := Connection.Exec("INSERT INTO capacites (libelle) VALUES (?)", capacite.Libelle)
	if err != nil {
		return 0, fmt.Errorf("failed to create capacite: %w", err)
	}
	id64, err := res.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("failed to get inserted id: %w", err)
	}
	capacite.ID = int(id64)
	return capacite.ID, nil
}

func DeleteCapacite(id int) error {
	_, err := Connection.Exec("DELETE FROM capacites WHERE id = ?", id)
	if err != nil {
		return fmt.Errorf("failed to delete capacite: %w", err)
	}
	return nil
}
