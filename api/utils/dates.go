package utils

import (
	"fmt"
	"time"
)

var dateLayouts = []string{
	"2006-01-02T15:04:05",
	"2006-01-02T15:04",
	"2006-01-02 15:04:05",
	"2006-01-02 15:04",
	"2006-01-02",
}

func ParseFlexibleDateTime(s string) (time.Time, error) {
	for _, layout := range dateLayouts {
		if t, err := time.Parse(layout, s); err == nil {
			return t, nil
		}
	}
	return time.Time{}, fmt.Errorf("format de date invalide : %s", s)
}

func ValidateFutureDate(s string) error {
	t, err := ParseFlexibleDateTime(s)
	if err != nil {
		return err
	}
	if t.Before(time.Now()) {
		return fmt.Errorf("la date ne peut pas être dans le passé")
	}
	return nil
}

func ValidateDateRange(debut, fin string) error {
	debutT, err := ParseFlexibleDateTime(debut)
	if err != nil {
		return err
	}
	finT, err := ParseFlexibleDateTime(fin)
	if err != nil {
		return err
	}
	if debutT.Before(time.Now()) {
		return fmt.Errorf("la date de début ne peut pas être dans le passé")
	}
	if !finT.After(debutT) {
		return fmt.Errorf("la date de fin doit être postérieure à la date de début")
	}
	return nil
}
