package models

type Benevole struct {
	ID                int        `json:"id"`
	Email             string     `json:"email"`
	PasswordHash      string     `json:"-"`
	Nom               string     `json:"nom"`
	Prenom            string     `json:"prenom"`
	Telephone         string     `json:"telephone,omitempty"`
	StatutCandidature string     `json:"statut_candidature"`
	Capacites         []Capacite `json:"capacites,omitempty"`
}
