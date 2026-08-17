package models

type Planning struct {
	ID         int    `json:"id"`
	ServiceID  int    `json:"service_id"`
	BenevoleID int    `json:"benevole_id"`
	DateDebut  string `json:"date_debut"`
	DateFin    string `json:"date_fin"`
	Lieu       string `json:"lieu"`
	PlacesMax  int    `json:"places_max"`
}
