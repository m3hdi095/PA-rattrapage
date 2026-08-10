package db

import (
	"api/models"
	"fmt"
)

func CreateAdherent(adherent *models.Adherent) (int, error) {
	tx, err := Connection.Begin()
	if err != nil {
		return 0, fmt.Errorf("failed to start transaction: %v", err)
	}
	defer tx.Rollback()

	res, err := tx.Exec(
		"INSERT INTO adherents (email, password_hash, nom, siret, adresse, code_postal, ville, telephone, date_adhesion, date_expiration) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
		adherent.Email, adherent.PasswordHash, adherent.Nom, adherent.Siret, adherent.Adresse, adherent.CodePostal, adherent.Ville, adherent.Telephone, adherent.DateAdhesion, adherent.DateExpiration,
	)
	if err != nil {
		return 0, fmt.Errorf("failed to create adherent: %v", err)
	}

	id64, err := res.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("failed to get inserted id: %v", err)
	}
	id := int(id64)

	if err := tx.Commit(); err != nil {
		return 0, fmt.Errorf("failed to commit transaction: %v", err)
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
