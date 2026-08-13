package db

type Produit struct {
	ID   int     `json:"id"`
	Nom  string  `json:"nom"`
	Prix float64 `json:"prix"`
}

func GetProduits() ([]Produit, error) {
	rows, err := Connection.Query("SELECT id, nom, prix FROM produits")
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var produits []Produit
	for rows.Next() {
		var p Produit
		if err := rows.Scan(&p.ID, &p.Nom, &p.Prix); err != nil {
			return nil, err
		}
		produits = append(produits, p)
	}

	return produits, nil
}
