package main

import (
	"fmt"
	"log"
	"time"

	"github.com/yugavto/apps/config"
	"github.com/yugavto/apps/internal/cis"
	"github.com/yugavto/apps/pkg/autocrm"
	"github.com/yugavto/apps/pkg/db"
)

func main() {
	cfg, err := config.Load()
	if err != nil {
		log.Fatalf("config: %v", err)
	}

	database, err := db.Connect(db.Config{
		Host: cfg.DBHost, Port: cfg.DBPort,
		User: cfg.DBUser, Password: cfg.DBPassword, Name: cfg.DBName,
	})
	if err != nil {
		log.Fatalf("db: %v", err)
	}

	crm := autocrm.NewClient(cfg.AutoCRMBaseURL, cfg.AutoCRMAPIKey)
	cisSvc := cis.NewService(database, crm, cfg.ImageUploadDir, cfg.ImageBaseURL, cfg.ONNXModelPath)
	if err := cisSvc.Init(); err != nil {
		log.Fatalf("cis init: %v", err)
	}

	start := time.Now()
	fmt.Println("Starting ProcessImages...")
	if err := cisSvc.ProcessImages(); err != nil {
		fmt.Printf("ProcessImages error: %v\n", err)
	}
	fmt.Printf("Done in %v\n", time.Since(start))

	var remaining int
	database.Get(&remaining, "SELECT COUNT(*) FROM yapps_app_cis_tables WHERE `name` = 'prod'")
	var activeTable string
	if remaining > 0 {
		database.Get(&activeTable, "SELECT `value` FROM yapps_app_cis_tables WHERE `name` = 'prod'")
	} else {
		activeTable = "yapps_app_cis_vehicles_one"
	}
	var left int
	database.Get(&left, "SELECT COUNT(*) FROM "+activeTable+" WHERE update_images = 1")
	fmt.Printf("Remaining update_images=1: %d\n", left)
}
