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

	//Login
	http.HandleFunc("POST /login", app.Login)

	//Adherents
	http.HandleFunc("POST /adherents", app.CreateAdherent)

	//Benevoles
	http.HandleFunc("POST /benevoles", app.CreateBenevole)
	http.HandleFunc("PATCH /benevoles/valider", app.ValidateBenevole)
	http.HandleFunc("PATCH /benevoles/rejeter", app.RejectBenevole)

	//Collectes
	http.HandleFunc("POST /collectes", app.CreateCollecte)
	http.HandleFunc("GET /collectes", app.ListCollectes)
	http.HandleFunc("PATCH /collectes/statut", app.UpdateCollecteStatut)

	//Destinataires
	http.HandleFunc("POST /destinataires", app.CreateDestinataire)
	http.HandleFunc("GET /destinataires", app.ListDestinataires)

	//Produits
	http.HandleFunc("POST /produits", app.CreateProduit)
	http.HandleFunc("GET /produits", app.ListProduits)
	http.HandleFunc("PATCH /produits/statut", app.UpdateProduitStatut)

	http.HandleFunc("/health", healthCheck)
	http.ListenAndServe(":8081", nil)

}
