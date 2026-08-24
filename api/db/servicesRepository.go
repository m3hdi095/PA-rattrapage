package db

import (
	"api/models"
	"fmt"
)

func CreateService(service *models.Service) (int, error) {
	res, err := Connection.Exec(
		"INSERT INTO services (nom, description) VALUES (?, ?)",
		service.Nom, service.Description,
	)
	if err != nil {
		return 0, fmt.Errorf("failed to create service: %w", err)
	}
	id64, err := res.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("failed to get last insert ID: %w", err)
	}

	id := int(id64)
	service.ID = int(id)
	return int(id), nil
}

func GetAllServices() ([]models.Service, error) {
	rows, err := Connection.Query("SELECT id, nom, description FROM services")
	if err != nil {
		return nil, fmt.Errorf("failed to get all services: %w", err)
	}
	defer rows.Close()

	var services []models.Service
	for rows.Next() {
		var service models.Service
		err := rows.Scan(&service.ID, &service.Nom, &service.Description)
		if err != nil {
			return nil, fmt.Errorf("failed to scan service: %w", err)
		}
		services = append(services, service)
	}
	if err := rows.Err(); err != nil {
		return nil, fmt.Errorf("failed to iterate services: %w", err)
	}
	return services, nil
}

func DeleteService(id int) error {
	_, err := Connection.Exec("DELETE FROM services WHERE id = ?", id)
	if err != nil {
		return fmt.Errorf("failed to delete service: %w", err)
	}
	return nil
}
