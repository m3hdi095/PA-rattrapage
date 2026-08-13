package models

type Produit struct {
	ID               int     `json:"id"`
	CollecteID       int     `json:"collecte_id"`
	CodeBarre        string  `json:"code_barre"`
	Nom              string  `json:"nom"`
	Quantite         float64 `json:"quantite"`
	DateLimiteConso  string  `json:"date_limite_conso"`
	EmplacementStock string  `json:"emplacement_stock"`
	Statut           string  `json:"statut"`
}
