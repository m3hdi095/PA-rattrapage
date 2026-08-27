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
		utils.JSONError(w, "corps de requête JSON invalide", http.StatusBadRequest)
		return
	}

	switch req.Role {
	case "benevole":
		benevole, err := db.GetBenevoleByEmail(req.Email)
		if err != nil {
			if errors.Is(err, sql.ErrNoRows) {
				utils.JSONError(w, "email ou mot de passe incorrect", http.StatusUnauthorized)
				return
			}
			utils.JSONError(w, "erreur lors de la récupération du bénévole", http.StatusInternalServerError)
			return
		}
		if !utils.CheckPasswordHash(req.Password, benevole.PasswordHash) {
			utils.JSONError(w, "email ou mot de passe incorrect", http.StatusUnauthorized)
			return
		}
		if benevole.StatutCandidature != "valide" {
			utils.JSONError(w, "ta candidature n'a pas encore été validée par un administrateur", http.StatusForbidden)
			return
		}
		token, err := utils.GenerateJWT(benevole.ID, "benevole")
		if err != nil {
			utils.JSONError(w, "erreur lors de la génération du token", http.StatusInternalServerError)
			return
		}
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]string{"token": token})
	case "admin":
		admin, err := db.GetAdminByEmail(req.Email)
		if err != nil {
			if errors.Is(err, sql.ErrNoRows) {
				utils.JSONError(w, "email ou mot de passe incorrect", http.StatusUnauthorized)
				return
			}
			utils.JSONError(w, "erreur lors de la récupération de l'admin", http.StatusInternalServerError)
			return
		}
		if !utils.CheckPasswordHash(req.Password, admin.PasswordHash) {
			utils.JSONError(w, "email ou mot de passe incorrect", http.StatusUnauthorized)
			return
		}
		token, err := utils.GenerateJWTWithAdminRole(admin.ID, "admin", admin.Role)
		if err != nil {
			utils.JSONError(w, "erreur lors de la génération du token", http.StatusInternalServerError)
			return
		}
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]string{"token": token})
	case "adherent":
		adherent, err := db.GetAdherentByEmail(req.Email)
		if err != nil {
			if errors.Is(err, sql.ErrNoRows) {
				utils.JSONError(w, "email ou mot de passe incorrect", http.StatusUnauthorized)
				return
			}
			utils.JSONError(w, "erreur lors de la récupération de l'adherent", http.StatusInternalServerError)
			return
		}
		if !utils.CheckPasswordHash(req.Password, adherent.PasswordHash) {
			utils.JSONError(w, "email ou mot de passe incorrect", http.StatusUnauthorized)
			return
		}
		token, err := utils.GenerateJWT(adherent.ID, "adherent")
		if err != nil {
			utils.JSONError(w, "erreur lors de la génération du token", http.StatusInternalServerError)
			return
		}
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]string{"token": token})

	default:
		utils.JSONError(w, "rôle invalide", http.StatusBadRequest)
		return
	}
}
