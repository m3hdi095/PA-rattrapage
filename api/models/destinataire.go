package models

type Destinataire struct {
	ID         int    `json:"id"`
	Type       string `json:"type"`
	Nom        string `json:"nom"`
	Adresse    string `json:"adresse"`
	CodePostal string `json:"code_postal"`
	Ville      string `json:"ville"`
	Telephone  string `json:"telephone"`
}
