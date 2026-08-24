package models

type Admin struct {
	ID           int    `json:"id"`
	Email        string `json:"email"`
	PasswordHash string `json:"-"`
	Nom          string `json:"nom"`
	Prenom       string `json:"prenom"`
	Role         string `json:"role"`
}
