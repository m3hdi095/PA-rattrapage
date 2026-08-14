package models

type Livraison struct {
	ID             int    `json:"id"`
	TourneeID      int    `json:"tournee_id"`
	DestinataireID int    `json:"destinataire_id"`
	Statut         string `json:"statut"`
}
