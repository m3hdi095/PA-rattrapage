package utils

// IsValidSiret verifie qu'un SIRET est compose de 14 chiffres.
func IsValidSiret(siret string) bool {
	if len(siret) != 14 {
		return false
	}
	for _, c := range siret {
		if c < '0' || c > '9' {
			return false
		}
	}
	return true
}
