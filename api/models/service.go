package models

type Service struct {
	ID              int    `json:"id"`
	Nom             string `json:"nom"`
	Description     string `json:"description"`
	CapaciteID      *int   `json:"capacite_id,omitempty"`
	CapaciteLibelle string `json:"capacite_libelle,omitempty"`
}
