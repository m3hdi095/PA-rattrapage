package db

import (
	"database/sql"
	"fmt"

	_ "github.com/go-sql-driver/mysql"
)

const (
	driver   = "mysql"
	host     = "localhost"
	port     = 3306
	user     = "root"
	password = ""
	dbname   = "no_more_waste"
)

var Connection *sql.DB

func NewDB() *sql.DB {
	var msqlInfo = fmt.Sprintf("%s:%s@tcp(%s:%d)/%s",
		user, password, host, port, dbname)

	conn, err := sql.Open(driver, msqlInfo)
	if err != nil {
		panic(err.Error())
	}
	fmt.Println("connected to database !")
	return conn
}
