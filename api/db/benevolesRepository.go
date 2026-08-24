package db

import (
	"api/models"
	"fmt"
)

func CreateBenevole(benevole *models.Benevole, capacites []string) (int, error) {
	tx, err := Connection.Begin()
	if err != nil {
		return 0, fmt.Errorf("failed to start transaction: %w", err)
	}
	defer tx.Rollback()

	res, err := tx.Exec(
		"INSERT INTO benevoles (email, password_hash, nom, prenom, telephone) VALUES (?, ?, ?, ?, ?)",
		benevole.Email, benevole.PasswordHash, benevole.Nom, benevole.Prenom, benevole.Telephone,
	)
	if err != nil {
		return 0, fmt.Errorf("failed to create benevole: %w", err)
	}

	id64, err := res.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("failed to get inserted id: %w", err)
	}
	id := int(id64)

	for _, capacite := range capacites {
		_, err := tx.Exec(
			"INSERT INTO benevole_capacites (benevole_id, capacite_id) SELECT ?, id FROM capacites WHERE libelle = ?",
			id, capacite,
		)
		if err != nil {
			return 0, fmt.Errorf("failed to link capacite %q: %w", capacite, err)
		}
	}

	if err := tx.Commit(); err != nil {
		return 0, fmt.Errorf("failed to commit transaction: %w", err)
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

func GetBenevoleCapacites(benevoleId int) ([]models.Capacite, error) {
	rows, err := Connection.Query(
		"SELECT c.id, c.libelle FROM capacites c JOIN benevole_capacites bc ON bc.capacite_id = c.id WHERE bc.benevole_id = ? ORDER BY c.libelle",
		benevoleId,
	)
	if err != nil {
		return nil, fmt.Errorf("failed to query benevole capacites: %w", err)
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
		return nil, fmt.Errorf("failed to iterate benevole capacites: %w", err)
	}
	return capacites, nil
}

func UpdateBenevoleCapacites(benevoleId int, capacites []string) error {
	tx, err := Connection.Begin()
	if err != nil {
		return fmt.Errorf("failed to start transaction: %w", err)
	}
	defer tx.Rollback()

	if _, err := tx.Exec("DELETE FROM benevole_capacites WHERE benevole_id = ?", benevoleId); err != nil {
		return fmt.Errorf("failed to clear benevole capacites: %w", err)
	}

	for _, capacite := range capacites {
		if _, err := tx.Exec(
			"INSERT INTO benevole_capacites (benevole_id, capacite_id) SELECT ?, id FROM capacites WHERE libelle = ?",
			benevoleId, capacite,
		); err != nil {
			return fmt.Errorf("failed to link capacite %q: %w", capacite, err)
		}
	}

	if err := tx.Commit(); err != nil {
		return fmt.Errorf("failed to commit transaction: %w", err)
	}
	return nil
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

	capacites, err := GetBenevoleCapacites(benevole.ID)
	if err != nil {
		return nil, err
	}
	benevole.Capacites = capacites

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
	if err := rows.Err(); err != nil {
		return nil, fmt.Errorf("failed to iterate benevoles: %w", err)
	}

	for i := range benevoles {
		capacites, err := GetBenevoleCapacites(benevoles[i].ID)
		if err != nil {
			return nil, err
		}
		benevoles[i].Capacites = capacites
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
