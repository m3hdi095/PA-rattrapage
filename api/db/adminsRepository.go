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
		"INSERT INTO admins (email, password_hash, nom, prenom, role) VALUES (?, ?, ?, ?, ?)",
		admin.Email, admin.PasswordHash, admin.Nom, admin.Prenom, admin.Role,
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
	row := Connection.QueryRow("SELECT id, email, password_hash, nom, prenom, role FROM admins WHERE email = ?", email)
	err := row.Scan(&admin.ID, &admin.Email, &admin.PasswordHash, &admin.Nom, &admin.Prenom, &admin.Role)
	if err != nil {
		return nil, fmt.Errorf("failed to get admin by email: %w", err)
	}
	return &admin, nil
}

func GetAdminByID(id int) (*models.Admin, error) {
	var admin models.Admin
	row := Connection.QueryRow("SELECT id, email, nom, prenom, role FROM admins WHERE id = ?", id)
	err := row.Scan(&admin.ID, &admin.Email, &admin.Nom, &admin.Prenom, &admin.Role)
	if err != nil {
		return nil, fmt.Errorf("failed to get admin by id: %w", err)
	}
	return &admin, nil
}

func GetAllAdmins() ([]models.Admin, error) {
	rows, err := Connection.Query("SELECT id, email, nom, prenom, role FROM admins ORDER BY email")
	if err != nil {
		return nil, fmt.Errorf("failed to query admins: %w", err)
	}
	defer rows.Close()

	var admins []models.Admin
	for rows.Next() {
		var admin models.Admin
		if err := rows.Scan(&admin.ID, &admin.Email, &admin.Nom, &admin.Prenom, &admin.Role); err != nil {
			return nil, fmt.Errorf("failed to scan admin: %w", err)
		}
		admins = append(admins, admin)
	}
	if err := rows.Err(); err != nil {
		return nil, fmt.Errorf("failed to iterate admins: %w", err)
	}
	return admins, nil
}

func CountSuperAdmins() (int, error) {
	var count int
	err := Connection.QueryRow("SELECT COUNT(*) FROM admins WHERE role = 'super_admin'").Scan(&count)
	if err != nil {
		return 0, fmt.Errorf("failed to count super admins: %w", err)
	}
	return count, nil
}

func DeleteAdmin(id int) error {
	_, err := Connection.Exec("DELETE FROM admins WHERE id = ?", id)
	if err != nil {
		return fmt.Errorf("failed to delete admin: %w", err)
	}
	return nil
}
