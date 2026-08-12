package main

import (
	"api/app"
	"api/db"
	"fmt"
	"net/http"
)

func healthCheck(w http.ResponseWriter, r *http.Request) {
	fmt.Fprintf(w, "En vie")
}

func main() {

	db.Connection = db.NewDB()

	http.HandleFunc("POST /login", app.Login)
	http.HandleFunc("POST /benevoles", app.CreateBenevole)
	http.HandleFunc("POST /adherents", app.CreateAdherent)
	http.HandleFunc("POST /collectes", app.CreateCollecte)
	http.HandleFunc("GET /collectes", app.ListCollectes)
	http.HandleFunc("PATCH /collectes/statut", app.UpdateCollecteStatut)
	http.HandleFunc("PATCH /benevoles/valider", app.ValidateBenevole)
	http.HandleFunc("PATCH /benevoles/rejeter", app.RejectBenevole)
	http.HandleFunc("/health", healthCheck)
	http.ListenAndServe(":8081", nil)

}
