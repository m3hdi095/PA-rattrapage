package main

import (
	"api/app"
	"api/db"
	"api/models"
	"api/utils"
	"flag"
	"fmt"
	"net/http"
	"os"
)

func healthCheck(w http.ResponseWriter, r *http.Request) {
	fmt.Fprintf(w, "En vie")
}

func createSuperAdmin(email, password, nom, prenom string) {
	if email == "" || password == "" || nom == "" || prenom == "" {
		fmt.Println("Usage: -create-super-admin -email=... -password=... -nom=... -prenom=...")
		os.Exit(1)
	}

	hash, err := utils.HashPassword(password)
	if err != nil {
		fmt.Println("Erreur lors du hash du mot de passe :", err)
		os.Exit(1)
	}

	admin := models.Admin{Email: email, PasswordHash: hash, Nom: nom, Prenom: prenom, Role: "super_admin"}
	if _, err := db.CreateAdmin(&admin); err != nil {
		fmt.Println("Erreur lors de la création du super admin :", err)
		os.Exit(1)
	}

	fmt.Printf("Super admin créé : %s (id=%d)\n", admin.Email, admin.ID)
}

func main() {
	createSuperAdminFlag := flag.Bool("create-super-admin", false, "crée un compte super_admin et quitte, sans démarrer le serveur")
	email := flag.String("email", "", "email du super_admin à créer")
	password := flag.String("password", "", "mot de passe du super_admin à créer")
	nom := flag.String("nom", "", "nom du super_admin à créer")
	prenom := flag.String("prenom", "", "prénom du super_admin à créer")
	flag.Parse()

	db.Connection = db.NewDB()

	if *createSuperAdminFlag {
		createSuperAdmin(*email, *password, *nom, *prenom)
		return
	}

	http.HandleFunc("POST /login", app.Login)

	http.HandleFunc("POST /admins", app.CreateAdmin)
	http.HandleFunc("GET /admins", app.ListAdmins)
	http.HandleFunc("DELETE /admins", app.DeleteAdmin)

	http.HandleFunc("POST /adherents", app.CreateAdherent)
	http.HandleFunc("GET /adherents", app.GetAllAdherents)
	http.HandleFunc("GET /adherents/me", app.GetOwnAdherent)
	http.HandleFunc("PATCH /adherents/profil", app.UpdateAdherentProfile)
	http.HandleFunc("PATCH /adherents/mot-de-passe", app.UpdateAdherentPassword)
	http.HandleFunc("DELETE /adherents", app.DeleteAdherent)

	http.HandleFunc("POST /benevoles", app.CreateBenevole)
	http.HandleFunc("PATCH /benevoles/valider", app.ValidateBenevole)
	http.HandleFunc("PATCH /benevoles/rejeter", app.RejectBenevole)
	http.HandleFunc("GET /benevoles", app.GetAllBenevoles)
	http.HandleFunc("GET /benevoles/me", app.GetOwnBenevole)
	http.HandleFunc("PATCH /benevoles/profil", app.UpdateBenevoleProfile)
	http.HandleFunc("PATCH /benevoles/mot-de-passe", app.UpdateBenevolePassword)
	http.HandleFunc("PATCH /benevoles/capacites", app.UpdateBenevoleCapacites)
	http.HandleFunc("DELETE /benevoles", app.DeleteBenevole)

	http.HandleFunc("GET /capacites", app.ListCapacites)
	http.HandleFunc("POST /capacites", app.CreateCapacite)
	http.HandleFunc("DELETE /capacites", app.DeleteCapacite)

	http.HandleFunc("POST /collectes", app.CreateCollecte)
	http.HandleFunc("GET /collectes", app.ListCollectes)
	http.HandleFunc("PATCH /collectes/statut", app.UpdateCollecteStatut)
	http.HandleFunc("DELETE /collectes", app.DeleteCollecte)

	http.HandleFunc("POST /destinataires", app.CreateDestinataire)
	http.HandleFunc("GET /destinataires", app.ListDestinataires)
	http.HandleFunc("DELETE /destinataires", app.DeleteDestinataire)

	http.HandleFunc("POST /inscriptions", app.CreateInscription)
	http.HandleFunc("GET /inscriptions", app.ListInscriptions)

	http.HandleFunc("POST /livraisons", app.CreateLivraison)
	http.HandleFunc("POST /livraisons/produits", app.AddProduitLivraison)
	http.HandleFunc("GET /livraisons", app.ListLivraisons)
	http.HandleFunc("GET /livraisons/recap", app.GetLivraisonRecap)
	http.HandleFunc("OPTIONS /livraisons/recap", app.GetLivraisonRecapOptions)
	http.HandleFunc("PATCH /livraisons/statut", app.UpdateLivraisonStatut)
	http.HandleFunc("DELETE /livraisons", app.DeleteLivraison)

	http.HandleFunc("POST /plannings", app.CreatePlanning)
	http.HandleFunc("GET /plannings", app.ListPlannings)
	http.HandleFunc("GET /plannings/excel", app.ExportPlanningsExcel)
	http.HandleFunc("DELETE /plannings", app.DeletePlanning)

	http.HandleFunc("POST /produits", app.CreateProduit)
	http.HandleFunc("GET /produits", app.ListProduits)
	http.HandleFunc("PATCH /produits/statut", app.UpdateProduitStatut)
	http.HandleFunc("DELETE /produits", app.DeleteProduit)

	http.HandleFunc("GET /rappels/renouvellement", app.RappelsRenouvellement)

	http.HandleFunc("POST /services", app.CreateService)
	http.HandleFunc("GET /services", app.ListServices)
	http.HandleFunc("DELETE /services", app.DeleteService)

	http.HandleFunc("POST /tournees", app.CreateTournee)
	http.HandleFunc("GET /tournees", app.ListTournees)
	http.HandleFunc("PATCH /tournees/statut", app.UpdateTourneeStatut)
	http.HandleFunc("DELETE /tournees", app.DeleteTournee)

	http.HandleFunc("/health", healthCheck)

	port := os.Getenv("PORT")
	if port == "" {
		port = "8081"
	}
	fmt.Println("API démarrée sur le port " + port)
	http.ListenAndServe(":"+port, nil)

}
