package db

import (
	"api/models"
	"errors"
	"fmt"
)

var ErrStockInsuffisant = errors.New("stock insuffisant")

func CreateLivraison(livraison *models.Livraison) (int, error) {
	res, err := Connection.Exec(
		"INSERT INTO livraisons (tournee_id, destinataire_id) VALUES (?, ?)",
		livraison.TourneeID, livraison.DestinataireID,
	)
	if err != nil {
		return 0, fmt.Errorf("failed to create livraison: %w", err)
	}

	id64, err := res.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("failed to get last insert ID: %w", err)
	}

	id := int(id64)
	livraison.ID = id
	return id, nil
}

func GetAllLivraisons() ([]models.Livraison, error) {
	rows, err := Connection.Query("SELECT id, tournee_id, destinataire_id, statut FROM livraisons")
	if err != nil {
		return nil, fmt.Errorf("failed to get all livraisons: %w", err)
	}

	defer rows.Close()

	var livraisons []models.Livraison
	for rows.Next() {
		var l models.Livraison
		if err := rows.Scan(&l.ID, &l.TourneeID, &l.DestinataireID, &l.Statut); err != nil {
			return nil, fmt.Errorf("failed to scan livraison: %w", err)
		}
		livraisons = append(livraisons, l)
	}

	if err := rows.Err(); err != nil {
		return nil, fmt.Errorf("failed to iterate livraisons: %w", err)
	}

	return livraisons, nil
}

func UpdateStatutLivraison(livraisonID int, newStatut string) error {
	_, err := Connection.Exec(
		"UPDATE livraisons SET statut = ? WHERE id = ?", newStatut, livraisonID,
	)
	if err != nil {
		return fmt.Errorf("failed to update livraison statut: %w", err)
	}

	return nil
}

func AddProduitToLivraison(livraisonID, produitID, quantite int) error {
	tx, err := Connection.Begin()
	if err != nil {
		return fmt.Errorf("failed to start transaction: %w", err)
	}
	defer tx.Rollback()

	var stockDisponible int
	if err := tx.QueryRow("SELECT quantite FROM produits WHERE id = ? FOR UPDATE", produitID).Scan(&stockDisponible); err != nil {
		return fmt.Errorf("failed to read produit stock: %w", err)
	}

	if stockDisponible < quantite {
		return ErrStockInsuffisant
	}

	if _, err := tx.Exec("UPDATE produits SET quantite = quantite - ? WHERE id = ?", quantite, produitID); err != nil {
		return fmt.Errorf("failed to decrement produit stock: %w", err)
	}

	if _, err := tx.Exec(
		"INSERT INTO livraison_produits (livraison_id, produit_id, quantite) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantite = quantite + VALUES(quantite)",
		livraisonID, produitID, quantite,
	); err != nil {
		return fmt.Errorf("failed to add produit to livraison: %w", err)
	}

	if err := tx.Commit(); err != nil {
		return fmt.Errorf("failed to commit transaction: %w", err)
	}

	return nil
}

func GetProduitsByLivraison(livraisonID int) ([]models.ProduitLivre, error) {
	rows, err := Connection.Query(
		"SELECT p.id, p.nom, p.code_barre, lp.quantite FROM livraison_produits lp JOIN produits p ON p.id = lp.produit_id WHERE lp.livraison_id = ?",
		livraisonID,
	)
	if err != nil {
		return nil, fmt.Errorf("failed to get produits for livraison %d: %w", livraisonID, err)
	}
	defer rows.Close()

	var produits []models.ProduitLivre
	for rows.Next() {
		var p models.ProduitLivre
		if err := rows.Scan(&p.ProduitID, &p.Nom, &p.CodeBarre, &p.Quantite); err != nil {
			return nil, fmt.Errorf("failed to scan produit livre: %w", err)
		}
		produits = append(produits, p)
	}

	if err := rows.Err(); err != nil {
		return nil, fmt.Errorf("failed to iterate produits: %w", err)
	}

	return produits, nil
}

func DeleteLivraison(id int) error {
	_, err := Connection.Exec("DELETE FROM livraisons WHERE id = ?", id)
	if err != nil {
		return fmt.Errorf("failed to delete livraison: %w", err)
	}
	return nil
}
