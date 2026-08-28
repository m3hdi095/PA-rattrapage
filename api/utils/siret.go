package utils

// IsValidSiret verifie qu'un SIRET est compose de 14 chiffres et
// respecte l'algorithme de Luhn (norme utilisee par l'INSEE).
func IsValidSiret(siret string) bool {
	if len(siret) != 14 {
		return false
	}

	sum := 0
	for i, c := range siret {
		if c < '0' || c > '9' {
			return false
		}
		digit := int(c - '0')
		if i%2 == 0 {
			digit *= 2
			if digit > 9 {
				digit -= 9
			}
		}
		sum += digit
	}

	return sum%10 == 0
}
