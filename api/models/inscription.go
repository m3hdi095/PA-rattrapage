package models

type Inscription struct {
	ID         int    `json:"id"`
	PlanningID int    `json:"planning_id"`
	AdherentID int    `json:"adherent_id"`
	Statut     string `json:"statut"`
}
