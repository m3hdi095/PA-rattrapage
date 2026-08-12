package models

type Collecte struct {
	ID           int    `json:"id"`
	DateCollecte string `json:"date_collecte"`
	Statut       string `json:"statut"`
	AdherentID   int    `json:"adherent_id"`
}
