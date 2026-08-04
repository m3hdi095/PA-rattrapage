package app

import "net/http"

type CreateBenevoleRequest struct {
	Email     string   `json:"email"`
	Password  string   `json:"password"`
	Nom       string   `json:"nom"`
	Prenom    string   `json:"prenom"`
	Telephone string   `json:"telephone"`
	Capacites []string `json:"capacites"`
}

func CreateBenevole(w http.ResponseWriter, r *http.Request) {
	json
}
