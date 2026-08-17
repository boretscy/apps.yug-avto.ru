package db

import (
	"fmt"
	"log"
	"os"
	"time"

	_ "github.com/go-sql-driver/mysql"
	"github.com/jmoiron/sqlx"
)

type Config struct {
	Host     string
	Port     string
	User     string
	Password string
	Name     string
}

func Connect(cfg Config) (*sqlx.DB, error) {
	dsn := cfg.dsn()
	db, err := sqlx.Connect("mysql", dsn)
	if err != nil {
		return nil, fmt.Errorf("db connect: %w", err)
	}

	db.SetMaxOpenConns(25)
	db.SetMaxIdleConns(5)
	db.SetConnMaxLifetime(10 * time.Minute)
	db.SetConnMaxIdleTime(2 * time.Minute)

	log.Println("connected to mysql")
	return db, nil
}

func (cfg Config) dsn() string {
	socket := "/var/run/mysqld/mysqld.sock"
	if _, err := os.Stat(socket); err == nil && cfg.Host == "localhost" {
		return fmt.Sprintf("%s:%s@unix(%s)/%s?charset=utf8mb4&parseTime=true&timeout=5s&readTimeout=5s&writeTimeout=5s",
			cfg.User, cfg.Password, socket, cfg.Name)
	}
	if cfg.Host == "localhost" {
		return fmt.Sprintf("%s:%s@tcp(127.0.0.1:%s)/%s?charset=utf8mb4&parseTime=true&timeout=5s&readTimeout=5s&writeTimeout=5s",
			cfg.User, cfg.Password, cfg.Port, cfg.Name)
	}
	return fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?charset=utf8mb4&parseTime=true&timeout=5s&readTimeout=5s&writeTimeout=5s",
		cfg.User, cfg.Password, cfg.Host, cfg.Port, cfg.Name)
}
