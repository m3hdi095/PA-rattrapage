package db

import (
	"api/models"
	"database/sql"
	"fmt"
)

func GetProduits() ([]models.Produit, error) {
	rows, err := Connection.Query("SELECT id, collecte_id, code_barre, nom, quantite, date_limite_conso, emplacement_stock, statut FROM produits")
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var produits []models.Produit
	for rows.Next() {
		var p models.Produit
		var dateLimiteConso sql.NullString
		if err := rows.Scan(&p.ID, &p.CollecteID, &p.CodeBarre, &p.Nom, &p.Quantite, &dateLimiteConso, &p.EmplacementStock, &p.Statut); err != nil {
			return nil, err
		}
		p.DateLimiteConso = dateLimiteConso.String
		produits = append(produits, p)
	}

	if err := rows.Err(); err != nil {
		return nil, fmt.Errorf("failed to iterate produits: %w", err)
	}

	return produits, nil
}

func CreateProduit(produit *models.Produit) (int, error) {
	var dateLimiteConso interface{}
	if produit.DateLimiteConso != "" {
		dateLimiteConso = produit.DateLimiteConso
	}

	res, err := Connection.Exec(
		"INSERT INTO produits (collecte_id, code_barre, nom, quantite, date_limite_conso, emplacement_stock) VALUES (?, ?, ?, ?, ?, ?)",
		produit.CollecteID, produit.CodeBarre, produit.Nom, produit.Quantite, dateLimiteConso, produit.EmplacementStock,
	)
	if err != nil {
		return 0, fmt.Errorf("failed to create produit: %w", err)
	}

	id64, err := res.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("failed to get inserted id: %w", err)
	}

	id := int(id64)
	produit.ID = id
	return id, nil
}

func UpdateStatutProduit(produitID int, newStatut string) error {
	_, err := Connection.Exec(
		"UPDATE produits SET statut = ? WHERE id = ?", newStatut, produitID,
	)
	if err != nil {
		return fmt.Errorf("failed to update produit statut: %w", err)
	}
	return nil
}

func DeleteProduit(id int) error {
	_, err := Connection.Exec("DELETE FROM produits WHERE id = ?", id)
	if err != nil {
		return fmt.Errorf("failed to delete produit: %w", err)
	}
	return nil
}
