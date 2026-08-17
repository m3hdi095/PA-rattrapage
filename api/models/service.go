package models

type Service struct {
	ID          int    `json:"id"`
	Nom         string `json:"nom"`
	Description string `json:"description"`
}
