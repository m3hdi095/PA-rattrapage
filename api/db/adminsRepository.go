package db

import (
	"api/models"
	"fmt"
)

func CreateAdmin(admin *models.Admin) (int, error) {
	tx, err := Connection.Begin()
	if err != nil {
		return 0, fmt.Errorf("failed to start transaction: %w", err)
	}
	defer tx.Rollback()

	res, err := tx.Exec(
		"INSERT INTO admins (email, password_hash, nom, prenom) VALUES (?, ?, ?, ?)",
		admin.Email, admin.PasswordHash, admin.Nom, admin.Prenom,
	)
	if err != nil {
		return 0, fmt.Errorf("failed to create admin: %w", err)
	}

	id64, err := res.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("failed to get inserted id: %w", err)
	}
	id := int(id64)

	if err := tx.Commit(); err != nil {
		return 0, fmt.Errorf("failed to commit transaction: %w", err)
	}

	admin.ID = id
	return id, nil
}

func GetAdminByEmail(email string) (*models.Admin, error) {
	var admin models.Admin
	row := Connection.QueryRow("SELECT id, email, password_hash, nom, prenom FROM admins WHERE email = ?", email)
	err := row.Scan(&admin.ID, &admin.Email, &admin.PasswordHash, &admin.Nom, &admin.Prenom)
	if err != nil {
		return nil, fmt.Errorf("failed to get admin by email: %w", err)
	}
	return &admin, nil
}
