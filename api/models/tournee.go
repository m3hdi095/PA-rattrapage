package models

type Tournee struct {
	ID          int    `json:"id"`
	BenevoleID  int    `json:"benevole_id"`
	DateTournee string `json:"date_tournee"`
	Statut      string `json:"statut"`
}
