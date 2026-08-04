package db

import (
	"api/models"
	"fmt"
)

func CreateBenevole(benevole *models.Benevole, capacites []string) (int, error) {
	tx, err := Connection.Begin()
	if err != nil {
		return 0, fmt.Errorf("failed to start transaction: %v", err)
	}
	defer tx.Rollback()

	res, err := tx.Exec(
		"INSERT INTO benevoles (email, password_hash, nom, prenom, telephone, statut_candidature) VALUES (?, ?, ?, ?, ?, ?)",
		benevole.Email, benevole.PasswordHash, benevole.Nom, benevole.Prenom, benevole.Telephone, benevole.StatutCandidature,
	)
	if err != nil {
		return 0, fmt.Errorf("failed to create benevole: %v", err)
	}

	id64, err := res.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("failed to get inserted id: %v", err)
	}
	id := int(id64)

	for _, capacite := range capacites {
		_, err := tx.Exec(
			"INSERT INTO benevole_capacites (benevole_id, capacite_id) SELECT ?, id FROM capacites WHERE libelle = ?",
			id, capacite,
		)
		if err != nil {
			return 0, fmt.Errorf("failed to link capacite %q: %v", capacite, err)
		}
	}

	if err := tx.Commit(); err != nil {
		return 0, fmt.Errorf("failed to commit transaction: %v", err)
	}

	benevole.ID = id
	return id, nil
}
