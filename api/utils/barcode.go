package utils

import (
	"fmt"
	"math/rand"
)

func GenerateCodeBarre() string {
	return fmt.Sprintf("%013d", rand.Int63n(10_000_000_000_000))
}
