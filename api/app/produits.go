package app

import (
	"api/db"
)

type Produit struct {
	ID   int     `json:"id"`
	Nom  string  `json:"nom"`
	Prix float64 `json:"prix"`
}

func GetProduits() ([]Produit, error) {
	produits, err := db.GetProduits()
	if err != nil {
		return nil, err
	}

	var produitsList []Produit
	for _, p := range produits {
		produitsList = append(produitsList, Produit{
			ID:   p.ID,
			Nom:  p.Nom,
			Prix: p.Prix,
		})
	}

	return produitsList, nil
}
