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

func GetBenevoleByEmail(email string) (*models.Benevole, error) {
	var benevole models.Benevole
	row := Connection.QueryRow("SELECT id, email, password_hash, nom, prenom, telephone, statut_candidature FROM benevoles WHERE email = ?", email)

	err := row.Scan(&benevole.ID, &benevole.Email, &benevole.PasswordHash, &benevole.Nom, &benevole.Prenom, &benevole.Telephone, &benevole.StatutCandidature)
	if err != nil {
		return nil, fmt.Errorf("failed to get benevole by email: %w", err)
	}

	return &benevole, nil
}

func ValidateBenevole(id int) error {
	_, err := Connection.Exec("UPDATE benevoles SET statut_candidature = 'valide' WHERE id = ?", id)
	if err != nil {
		return fmt.Errorf("failed to validate benevole: %w", err)
	}
	return nil
}

func RejectBenevole(id int) error {
	_, err := Connection.Exec("UPDATE benevoles SET statut_candidature = 'refuse' WHERE id = ?", id)
	if err != nil {
		return fmt.Errorf("failed to reject benevole: %w", err)
	}
	return nil
}

func GetBenevoleByID(id int) (*models.Benevole, error) {
	var benevole models.Benevole
	row := Connection.QueryRow("SELECT id, email, password_hash, nom, prenom, telephone, statut_candidature FROM benevoles WHERE id = ?", id)

	err := row.Scan(&benevole.ID, &benevole.Email, &benevole.PasswordHash, &benevole.Nom, &benevole.Prenom, &benevole.Telephone, &benevole.StatutCandidature)
	if err != nil {
		return nil, fmt.Errorf("failed to get benevole by id: %w", err)
	}

	return &benevole, nil
}

func UpdateBenevole(id int, nom, prenom, telephone string) error {
	_, err := Connection.Exec(
		"UPDATE benevoles SET nom = ?, prenom = ?, telephone = ? WHERE id = ?",
		nom, prenom, telephone, id,
	)
	if err != nil {
		return fmt.Errorf("failed to update benevole: %w", err)
	}
	return nil
}

func UpdateBenevolePassword(id int, newHash string) error {
	_, err := Connection.Exec("UPDATE benevoles SET password_hash = ? WHERE id = ?", newHash, id)
	if err != nil {
		return fmt.Errorf("failed to update benevole password: %w", err)
	}
	return nil
}

func GetAllBenevoles() ([]models.Benevole, error) {
	rows, err := Connection.Query("SELECT id, email, nom, prenom, telephone, statut_candidature FROM benevoles")
	if err != nil {
		return nil, fmt.Errorf("failed to query benevoles: %w", err)
	}
	defer rows.Close()

	var benevoles []models.Benevole
	for rows.Next() {
		var benevole models.Benevole
		err := rows.Scan(&benevole.ID, &benevole.Email, &benevole.Nom, &benevole.Prenom, &benevole.Telephone, &benevole.StatutCandidature)
		if err != nil {
			return nil, fmt.Errorf("failed to scan benevole: %w", err)
		}
		benevoles = append(benevoles, benevole)
	}
	return benevoles, nil
}

func DeleteBenevole(id int) error {
	_, err := Connection.Exec("DELETE FROM benevoles WHERE id = ?", id)
	if err != nil {
		return fmt.Errorf("failed to delete benevole: %w", err)
	}
	return nil
}
