package db

import (
	"api/models"
	"fmt"
)

func CreateAdherent(adherent *models.Adherent) (int, error) {
	tx, err := Connection.Begin()
	if err != nil {
		return 0, fmt.Errorf("failed to start transaction: %w", err)
	}
	defer tx.Rollback()

	res, err := tx.Exec(
		"INSERT INTO adherents (email, password_hash, nom, siret, adresse, code_postal, ville, telephone, date_adhesion, date_expiration) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
		adherent.Email, adherent.PasswordHash, adherent.Nom, adherent.Siret, adherent.Adresse, adherent.CodePostal, adherent.Ville, adherent.Telephone, adherent.DateAdhesion, adherent.DateExpiration,
	)
	if err != nil {
		return 0, fmt.Errorf("failed to create adherent: %w", err)
	}

	id64, err := res.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("failed to get inserted id: %w", err)
	}
	id := int(id64)

	if err := tx.Commit(); err != nil {
		return 0, fmt.Errorf("failed to commit transaction: %w", err)
	}

	adherent.ID = id
	return id, nil
}

func GetAdherentByEmail(email string) (*models.Adherent, error) {
	var adherent models.Adherent
	row := Connection.QueryRow("SELECT id, email, password_hash, nom, siret, adresse, code_postal, ville, telephone, date_adhesion, date_expiration FROM adherents WHERE email = ?", email)
	err := row.Scan(&adherent.ID, &adherent.Email, &adherent.PasswordHash, &adherent.Nom, &adherent.Siret, &adherent.Adresse, &adherent.CodePostal, &adherent.Ville, &adherent.Telephone, &adherent.DateAdhesion, &adherent.DateExpiration)
	if err != nil {
		return nil, fmt.Errorf("failed to get adherent by email: %w", err)
	}

	return &adherent, nil
}

func GetAdherentsExpiredSoon(days int) ([]models.Adherent, error) {
	rows, err := Connection.Query("SELECT id, email, nom, adresse, code_postal, ville, telephone, date_adhesion, date_expiration FROM adherents WHERE date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)", days)
	if err != nil {
		return nil, fmt.Errorf("failed to query adherents: %w", err)
	}
	defer rows.Close()

	var adherents []models.Adherent
	for rows.Next() {
		var adherent models.Adherent
		err := rows.Scan(&adherent.ID, &adherent.Email, &adherent.Nom, &adherent.Adresse, &adherent.CodePostal, &adherent.Ville, &adherent.Telephone, &adherent.DateAdhesion, &adherent.DateExpiration)
		if err != nil {
			return nil, fmt.Errorf("failed to scan adherent: %w", err)
		}
		adherents = append(adherents, adherent)
	}
	return adherents, nil
}

func GetAllAdherents() ([]models.Adherent, error) {
	rows, err := Connection.Query("SELECT id, email, nom, adresse,siret, code_postal, ville, telephone, date_adhesion, date_expiration FROM adherents")
	if err != nil {
		return nil, fmt.Errorf("failed to query adherents: %w", err)
	}
	defer rows.Close()

	var adherents []models.Adherent
	for rows.Next() {
		var adherent models.Adherent
		err := rows.Scan(&adherent.ID, &adherent.Email, &adherent.Nom, &adherent.Adresse, &adherent.Siret, &adherent.CodePostal, &adherent.Ville, &adherent.Telephone, &adherent.DateAdhesion, &adherent.DateExpiration)
		if err != nil {
			return nil, fmt.Errorf("failed to scan adherent: %w", err)
		}
		adherents = append(adherents, adherent)
	}
	return adherents, nil
}

func GetAdherentByID(id int) (*models.Adherent, error) {
	var adherent models.Adherent
	row := Connection.QueryRow("SELECT id, email, password_hash, nom, siret, adresse, code_postal, ville, telephone, date_adhesion, date_expiration FROM adherents WHERE id = ?", id)
	err := row.Scan(&adherent.ID, &adherent.Email, &adherent.PasswordHash, &adherent.Nom, &adherent.Siret, &adherent.Adresse, &adherent.CodePostal, &adherent.Ville, &adherent.Telephone, &adherent.DateAdhesion, &adherent.DateExpiration)
	if err != nil {
		return nil, fmt.Errorf("failed to get adherent by id: %w", err)
	}

	return &adherent, nil
}

func UpdateAdherent(id int, nom, adresse, codePostal, ville, telephone string) error {
	_, err := Connection.Exec(
		"UPDATE adherents SET nom = ?, adresse = ?, code_postal = ?, ville = ?, telephone = ? WHERE id = ?",
		nom, adresse, codePostal, ville, telephone, id,
	)
	if err != nil {
		return fmt.Errorf("failed to update adherent: %w", err)
	}
	return nil
}

func UpdateAdherentPassword(id int, newHash string) error {
	_, err := Connection.Exec("UPDATE adherents SET password_hash = ? WHERE id = ?", newHash, id)
	if err != nil {
		return fmt.Errorf("failed to update adherent password: %w", err)
	}
	return nil
}

func DeleteAdherent(id int) error {
	_, err := Connection.Exec("DELETE FROM adherents WHERE id = ?", id)
	if err != nil {
		return fmt.Errorf("failed to delete adherent: %w", err)
	}
	return nil
}
