package utils

import (
	"regexp"
	"strings"
)

var emailRegex = regexp.MustCompile(`^[^\s@]+@[^\s@]+\.[^\s@]+$`)

const MinPasswordLength = 6

func IsValidEmail(email string) bool {
	return emailRegex.MatchString(email)
}

func IsValidPassword(password string) bool {
	return len(password) >= MinPasswordLength
}

var codePostalRegex = regexp.MustCompile(`^\d{5}$`)

func IsValidCodePostal(codePostal string) bool {
	return codePostalRegex.MatchString(codePostal)
}

func IsDuplicateEntryError(err error) bool {
	return err != nil && strings.Contains(err.Error(), "Duplicate entry")
}
