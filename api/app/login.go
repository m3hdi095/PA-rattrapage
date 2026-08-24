package app

import (
	"api/db"
	"api/utils"
	"database/sql"
	"encoding/json"
	"errors"
	"net/http"
)

type LoginRequest struct {
	Email    string `json:"email"`
	Password string `json:"password"`
	Role     string `json:"role"`
}

func Login(w http.ResponseWriter, r *http.Request) {
	var req LoginRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		http.Error(w, "corps de requête JSON invalide", http.StatusBadRequest)
		return
	}

	switch req.Role {
	case "benevole":
		benevole, err := db.GetBenevoleByEmail(req.Email)
		if err != nil {
			if errors.Is(err, sql.ErrNoRows) {
				http.Error(w, "email ou mot de passe incorrect", http.StatusUnauthorized)
				return
			}
			http.Error(w, "erreur lors de la récupération du bénévole", http.StatusInternalServerError)
			return
		}
		if !utils.CheckPasswordHash(req.Password, benevole.PasswordHash) {
			http.Error(w, "email ou mot de passe incorrect", http.StatusUnauthorized)
			return
		}
		if benevole.StatutCandidature != "valide" {
			http.Error(w, "ta candidature n'a pas encore été validée par un administrateur", http.StatusForbidden)
			return
		}
		token, err := utils.GenerateJWT(benevole.ID, "benevole")
		if err != nil {
			http.Error(w, "erreur lors de la génération du token", http.StatusInternalServerError)
			return
		}
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]string{"token": token})
	case "admin":
		admin, err := db.GetAdminByEmail(req.Email)
		if err != nil {
			if errors.Is(err, sql.ErrNoRows) {
				http.Error(w, "email ou mot de passe incorrect", http.StatusUnauthorized)
				return
			}
			http.Error(w, "erreur lors de la récupération de l'admin", http.StatusInternalServerError)
			return
		}
		if !utils.CheckPasswordHash(req.Password, admin.PasswordHash) {
			http.Error(w, "email ou mot de passe incorrect", http.StatusUnauthorized)
			return
		}
		token, err := utils.GenerateJWT(admin.ID, "admin")
		if err != nil {
			http.Error(w, "erreur lors de la génération du token", http.StatusInternalServerError)
			return
		}
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]string{"token": token})
	case "adherent":
		adherent, err := db.GetAdherentByEmail(req.Email)
		if err != nil {
			if errors.Is(err, sql.ErrNoRows) {
				http.Error(w, "email ou mot de passe incorrect", http.StatusUnauthorized)
				return
			}
			http.Error(w, "erreur lors de la récupération de l'adherent", http.StatusInternalServerError)
			return
		}
		if !utils.CheckPasswordHash(req.Password, adherent.PasswordHash) {
			http.Error(w, "email ou mot de passe incorrect", http.StatusUnauthorized)
			return
		}
		token, err := utils.GenerateJWT(adherent.ID, "adherent")
		if err != nil {
			http.Error(w, "erreur lors de la génération du token", http.StatusInternalServerError)
			return
		}
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]string{"token": token})

	default:
		http.Error(w, "rôle invalide", http.StatusBadRequest)
		return
	}
}
