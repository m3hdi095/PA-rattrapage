package db

import (
	"api/models"
	"fmt"
)

func CreateCollecte(collecte *models.Collecte) (int, error) {
	res, err := Connection.Exec(
		"INSERT INTO collectes (adherent_id, date_collecte) VALUES (?, ?)",
		collecte.AdherentID, collecte.DateCollecte,
	)
	if err != nil {
		return 0, fmt.Errorf("failed to create collecte: %w", err)
	}

	id64, err := res.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("failed to get inserted id: %w", err)
	}

	id := int(id64)
	collecte.ID = id
	return id, nil
}

func GetAllCollectes() ([]models.Collecte, error) {
	rows, err := Connection.Query("SELECT id, adherent_id, date_collecte, statut FROM collectes ORDER BY date_collecte DESC")
	if err != nil {
		return nil, fmt.Errorf("failed to get collectes: %w", err)
	}
	defer rows.Close()

	var collectes []models.Collecte
	for rows.Next() {
		var c models.Collecte
		if err := rows.Scan(&c.ID, &c.AdherentID, &c.DateCollecte, &c.Statut); err != nil {
			return nil, fmt.Errorf("failed to scan collecte: %w", err)
		}
		collectes = append(collectes, c)
	}
	return collectes, nil
}

func GetCollectesByAdherent(adherentID int) ([]models.Collecte, error) {
	rows, err := Connection.Query("SELECT id, adherent_id, date_collecte, statut FROM collectes WHERE adherent_id = ? ORDER BY date_collecte DESC", adherentID)
	if err != nil {
		return nil, fmt.Errorf("failed to get collectes for adherent %d: %w", adherentID, err)
	}
	defer rows.Close()

	var collectes []models.Collecte
	for rows.Next() {
		var c models.Collecte
		if err := rows.Scan(&c.ID, &c.AdherentID, &c.DateCollecte, &c.Statut); err != nil {
			return nil, fmt.Errorf("failed to scan collecte: %w", err)
		}
		collectes = append(collectes, c)
	}
	return collectes, nil
}

func UpdateStatutCollecte(id int, statut string) error {
	_, err := Connection.Exec("UPDATE collectes SET statut = ? WHERE id = ?", statut, id)
	if err != nil {
		return fmt.Errorf("failed to update collecte statut: %w", err)
	}
	return nil
}

func DeleteCollecte(id int) error {
	_, err := Connection.Exec("DELETE FROM collectes WHERE id = ?", id)
	if err != nil {
		return fmt.Errorf("failed to delete collecte: %w", err)
	}
	return nil
}
