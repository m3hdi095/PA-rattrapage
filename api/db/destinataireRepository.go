package db

import (
	"api/models"
	"fmt"
)

func CreateDestinataire(destinataire *models.Destinataire) (int, error) {
	res, err := Connection.Exec(
		"INSERT INTO destinataires (type, nom, adresse, code_postal, ville, telephone) VALUES (?, ?, ?, ?, ?, ?)",
		destinataire.Type, destinataire.Nom, destinataire.Adresse, destinataire.CodePostal, destinataire.Ville, destinataire.Telephone,
	)
	if err != nil {
		return 0, fmt.Errorf("failed to create destinataire: %w", err)
	}

	id64, err := res.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("failed to get inserted id: %w", err)
	}

	id := int(id64)
	destinataire.ID = id
	return id, nil
}

func GetAllDestinataires() ([]models.Destinataire, error) {
	rows, err := Connection.Query("SELECT id, type, nom, adresse, code_postal, ville, telephone FROM destinataires")
	if err != nil {
		return nil, fmt.Errorf("failed to get all destinataires: %w", err)
	}
	defer rows.Close()

	var destinataires []models.Destinataire
	for rows.Next() {
		var destinataire models.Destinataire
		err := rows.Scan(&destinataire.ID, &destinataire.Type, &destinataire.Nom, &destinataire.Adresse, &destinataire.CodePostal, &destinataire.Ville, &destinataire.Telephone)
		if err != nil {
			return nil, fmt.Errorf("failed to scan destinataire: %w", err)
		}
		destinataires = append(destinataires, destinataire)
	}

	return destinataires, nil
}

func DeleteDestinataire(id int) error {
	_, err := Connection.Exec("DELETE FROM destinataires WHERE id = ?", id)
	if err != nil {
		return fmt.Errorf("failed to delete destinataire: %w", err)
	}
	return nil
}
