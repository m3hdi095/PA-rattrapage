package db

import (
	"api/models"
	"database/sql"
	"fmt"
)

func CreateService(service *models.Service) (int, error) {
	res, err := Connection.Exec(
		"INSERT INTO services (nom, description, capacite_id) VALUES (?, ?, ?)",
		service.Nom, service.Description, service.CapaciteID,
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
	rows, err := Connection.Query(
		"SELECT s.id, s.nom, s.description, s.capacite_id, c.libelle FROM services s LEFT JOIN capacites c ON c.id = s.capacite_id",
	)
	if err != nil {
		return nil, fmt.Errorf("failed to get all services: %w", err)
	}
	defer rows.Close()

	var services []models.Service
	for rows.Next() {
		var service models.Service
		var capaciteID sql.NullInt64
		var capaciteLibelle sql.NullString
		err := rows.Scan(&service.ID, &service.Nom, &service.Description, &capaciteID, &capaciteLibelle)
		if err != nil {
			return nil, fmt.Errorf("failed to scan service: %w", err)
		}
		if capaciteID.Valid {
			id := int(capaciteID.Int64)
			service.CapaciteID = &id
			service.CapaciteLibelle = capaciteLibelle.String
		}
		services = append(services, service)
	}
	if err := rows.Err(); err != nil {
		return nil, fmt.Errorf("failed to iterate services: %w", err)
	}
	return services, nil
}

func GetServiceByID(id int) (*models.Service, error) {
	var service models.Service
	var capaciteID sql.NullInt64
	var capaciteLibelle sql.NullString

	row := Connection.QueryRow(
		"SELECT s.id, s.nom, s.description, s.capacite_id, c.libelle FROM services s LEFT JOIN capacites c ON c.id = s.capacite_id WHERE s.id = ?",
		id,
	)
	if err := row.Scan(&service.ID, &service.Nom, &service.Description, &capaciteID, &capaciteLibelle); err != nil {
		return nil, fmt.Errorf("failed to get service by id: %w", err)
	}
	if capaciteID.Valid {
		cid := int(capaciteID.Int64)
		service.CapaciteID = &cid
		service.CapaciteLibelle = capaciteLibelle.String
	}
	return &service, nil
}

func DeleteService(id int) error {
	_, err := Connection.Exec("DELETE FROM services WHERE id = ?", id)
	if err != nil {
		return fmt.Errorf("failed to delete service: %w", err)
	}
	return nil
}
