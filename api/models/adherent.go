package models

type Adherent struct {
	ID             int    `json:"id"`
	Email          string `json:"email"`
	PasswordHash   string `json:"-"`
	Nom            string `json:"nom"`
	Siret          string `json:"siret"`
	Adresse        string `json:"adresse"`
	CodePostal     string `json:"code_postal"`
	Ville          string `json:"ville"`
	Telephone      string `json:"telephone"`
	DateAdhesion   string `json:"date_adhesion"`
	DateExpiration string `json:"date_expiration"`
}
