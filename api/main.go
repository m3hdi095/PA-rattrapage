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

	//Admins
	http.HandleFunc("POST /admins", app.CreateAdmin)
	http.HandleFunc("GET /admins", app.ListAdmins)
	http.HandleFunc("DELETE /admins", app.DeleteAdmin)

	//Adherents
	http.HandleFunc("POST /adherents", app.CreateAdherent)
	http.HandleFunc("GET /adherents", app.GetAllAdherents)
	http.HandleFunc("GET /adherents/me", app.GetOwnAdherent)
	http.HandleFunc("PATCH /adherents/profil", app.UpdateAdherentProfile)
	http.HandleFunc("PATCH /adherents/mot-de-passe", app.UpdateAdherentPassword)
	http.HandleFunc("DELETE /adherents", app.DeleteAdherent)

	//Benevoles
	http.HandleFunc("POST /benevoles", app.CreateBenevole)
	http.HandleFunc("PATCH /benevoles/valider", app.ValidateBenevole)
	http.HandleFunc("PATCH /benevoles/rejeter", app.RejectBenevole)
	http.HandleFunc("GET /benevoles", app.GetAllBenevoles)
	http.HandleFunc("GET /benevoles/me", app.GetOwnBenevole)
	http.HandleFunc("PATCH /benevoles/profil", app.UpdateBenevoleProfile)
	http.HandleFunc("PATCH /benevoles/mot-de-passe", app.UpdateBenevolePassword)
	http.HandleFunc("PATCH /benevoles/capacites", app.UpdateBenevoleCapacites)
	http.HandleFunc("DELETE /benevoles", app.DeleteBenevole)

	//Capacites
	http.HandleFunc("GET /capacites", app.ListCapacites)
	http.HandleFunc("POST /capacites", app.CreateCapacite)
	http.HandleFunc("DELETE /capacites", app.DeleteCapacite)

	//Collectes
	http.HandleFunc("POST /collectes", app.CreateCollecte)
	http.HandleFunc("GET /collectes", app.ListCollectes)
	http.HandleFunc("PATCH /collectes/statut", app.UpdateCollecteStatut)
	http.HandleFunc("DELETE /collectes", app.DeleteCollecte)

	//Destinataires
	http.HandleFunc("POST /destinataires", app.CreateDestinataire)
	http.HandleFunc("GET /destinataires", app.ListDestinataires)
	http.HandleFunc("DELETE /destinataires", app.DeleteDestinataire)

	//Inscriptions
	http.HandleFunc("POST /inscriptions", app.CreateInscription)
	http.HandleFunc("GET /inscriptions", app.ListInscriptions)

	//Livraisons
	http.HandleFunc("POST /livraisons", app.CreateLivraison)
	http.HandleFunc("POST /livraisons/produits", app.AddProduitLivraison)
	http.HandleFunc("GET /livraisons", app.ListLivraisons)
	http.HandleFunc("GET /livraisons/recap", app.GetLivraisonRecap)
	http.HandleFunc("OPTIONS /livraisons/recap", app.GetLivraisonRecapOptions)
	http.HandleFunc("PATCH /livraisons/statut", app.UpdateLivraisonStatut)
	http.HandleFunc("DELETE /livraisons", app.DeleteLivraison)

	//Plannings
	http.HandleFunc("POST /plannings", app.CreatePlanning)
	http.HandleFunc("GET /plannings", app.ListPlannings)
	http.HandleFunc("GET /plannings/excel", app.ExportPlanningsExcel)
	http.HandleFunc("DELETE /plannings", app.DeletePlanning)

	//Produits
	http.HandleFunc("POST /produits", app.CreateProduit)
	http.HandleFunc("GET /produits", app.ListProduits)
	http.HandleFunc("PATCH /produits/statut", app.UpdateProduitStatut)
	http.HandleFunc("DELETE /produits", app.DeleteProduit)

	//Rappels
	http.HandleFunc("GET /rappels/renouvellement", app.RappelsRenouvellement)

	//Services
	http.HandleFunc("POST /services", app.CreateService)
	http.HandleFunc("GET /services", app.ListServices)
	http.HandleFunc("DELETE /services", app.DeleteService)

	//Tournees
	http.HandleFunc("POST /tournees", app.CreateTournee)
	http.HandleFunc("GET /tournees", app.ListTournees)
	http.HandleFunc("PATCH /tournees/statut", app.UpdateTourneeStatut)
	http.HandleFunc("DELETE /tournees", app.DeleteTournee)

	http.HandleFunc("/health", healthCheck)
	http.ListenAndServe(":8081", nil)

}
