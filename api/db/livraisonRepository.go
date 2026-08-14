package db

import (
	"api/models"
	"fmt"
)

func CreateLivraison(livraison *models.Livraison) (int, error) {
	res, err := Connection.Exec(
		"INSERT INTO livraisons (tournee_id, destinataire_id) VALUES (?, ?)",
		livraison.TourneeID, livraison.DestinataireID,
	)
	if err != nil {
		return 0, fmt.Errorf("failed to create livraison: %w", err)
	}

	id64, err := res.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("failed to get last insert ID: %w", err)
	}

	id := int(id64)
	livraison.ID = id
	return id, nil
}

func GetAllLivraisons() ([]models.Livraison, error) {
	rows, err := Connection.Query("SELECT id, tournee_id, destinataire_id, statut FROM livraisons")
	if err != nil {
		return nil, fmt.Errorf("failed to get all livraisons: %w", err)
	}

	defer rows.Close()

	var livraisons []models.Livraison
	for rows.Next() {
		var l models.Livraison
		if err := rows.Scan(&l.ID, &l.TourneeID, &l.DestinataireID, &l.Statut); err != nil {
			return nil, fmt.Errorf("failed to scan livraison: %w", err)
		}
		livraisons = append(livraisons, l)
	}

	return livraisons, nil
}

func UpdateStatutLivraison(livraisonID int, newStatut string) error {
	_, err := Connection.Exec(
		"UPDATE livraisons SET statut = ? WHERE id = ?", newStatut, livraisonID,
	)
	if err != nil {
		return fmt.Errorf("failed to update livraison statut: %w", err)
	}

	return nil
}
