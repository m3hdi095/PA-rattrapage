package db

import (
	"api/models"
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
		if err := rows.Scan(&p.ID, &p.CollecteID, &p.CodeBarre, &p.Nom, &p.Quantite, &p.DateLimiteConso, &p.EmplacementStock, &p.Statut); err != nil {
			return nil, err
		}
		produits = append(produits, p)
	}

	return produits, nil
}

func CreateProduit(produit *models.Produit) (int, error) {
	res, err := Connection.Exec(
		"INSERT INTO produits (collecte_id, code_barre, nom, quantite, date_limite_conso, emplacement_stock) VALUES (?, ?, ?, ?, ?, ?)",
		produit.CollecteID, produit.CodeBarre, produit.Nom, produit.Quantite, produit.DateLimiteConso, produit.EmplacementStock,
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
