package models

type LivraisonProduit struct {
	LivraisonID int `json:"livraison_id"`
	ProduitID   int `json:"produit_id"`
	Quantite    int `json:"quantite"`
}

type ProduitLivre struct {
	ProduitID int    `json:"produit_id"`
	Nom       string `json:"nom"`
	CodeBarre string `json:"code_barre"`
	Quantite  int    `json:"quantite"`
}
