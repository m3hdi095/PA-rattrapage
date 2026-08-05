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

	http.HandleFunc("POST /benevoles", app.CreateBenevole)
	http.HandleFunc("/health", healthCheck)
	http.ListenAndServe(":8081", nil)

}
