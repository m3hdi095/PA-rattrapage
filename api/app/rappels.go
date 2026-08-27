package app

import (
	"api/db"
	"api/utils"
	"encoding/json"
	"log"
	"net/http"
)

func RappelsRenouvellement(w http.ResponseWriter, r *http.Request) {
	adherents, err := db.GetAdherentsExpiredSoon(30)
	if err != nil {
		utils.JSONError(w, "erreur lors de la récupération des adhérents", http.StatusInternalServerError)
		return
	}

	envoyes := 0
	for _, adherent := range adherents {
		subject := "Renouvellement de votre adhésion NO MORE WASTE"
		body := "Bonjour " + adherent.Nom + ",\r\n\r\n" +
			"Votre adhésion expire le " + adherent.DateExpiration + ". " +
			"Pensez à la renouveler pour continuer à profiter des services de l'association.\r\n\r\n" +
			"L'équipe NO MORE WASTE"

		if err := utils.SendEmail(adherent.Email, subject, body); err != nil {
			log.Printf("échec envoi rappel à %s: %v", adherent.Email, err)
			continue
		}
		envoyes++
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]int{"emails_envoyes": envoyes})
}
