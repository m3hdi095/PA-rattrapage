package db

import (
	"database/sql"
	"fmt"
	"os"

	_ "github.com/go-sql-driver/mysql"
)

const driver = "mysql"

func envOr(key, fallback string) string {
	if v := os.Getenv(key); v != "" {
		return v
	}
	return fallback
}

var Connection *sql.DB

func NewDB() *sql.DB {
	host := envOr("DB_HOST", "localhost")
	port := envOr("DB_PORT", "3306")
	user := envOr("DB_USER", "root")
	password := envOr("DB_PASSWORD", "")
	dbname := envOr("DB_NAME", "no_more_waste")

	var msqlInfo = fmt.Sprintf("%s:%s@tcp(%s:%s)/%s",
		user, password, host, port, dbname)

	conn, err := sql.Open(driver, msqlInfo)
	if err != nil {
		panic(err.Error())
	}
	fmt.Println("connected to database !")
	return conn
}
