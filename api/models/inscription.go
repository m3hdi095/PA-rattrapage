package models

type Inscription struct {
	ID         int    `json:"id"`
	PlanningID int    `json:"planning_id"`
	AdherentID int    `json:"adherent_id"`
	Statut     string `json:"statut"`
}

// InscriptionDetail est une inscription enrichie des infos du creneau
// (jointure avec plannings), utilisee pour l'affichage "Mes inscriptions".
type InscriptionDetail struct {
	ID         int    `json:"id"`
	PlanningID int    `json:"planning_id"`
	Statut     string `json:"statut"`
	ServiceID  int    `json:"service_id"`
	DateDebut  string `json:"date_debut"`
	DateFin    string `json:"date_fin"`
	Lieu       string `json:"lieu"`
}
