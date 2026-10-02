package main

import (
	"flag"
	"fmt"
	"log"
	"strconv"

	"github.com/yugavto/apps/config"
	"github.com/yugavto/apps/internal/cis"
	"github.com/yugavto/apps/pkg/autocrm"
	"github.com/yugavto/apps/pkg/db"
)

func main() {
	flag.Parse()
	args := flag.Args()
	if len(args) < 1 {
		log.Fatalf("Usage: sync_single <vehicle_ext_id> [type_id]")
	}

	extID, err := strconv.Atoi(args[0])
	if err != nil {
		log.Fatalf("invalid ext_id: %v", err)
	}

	typeID := 1
	if len(args) > 1 {
		typeID, _ = strconv.Atoi(args[1])
	}

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

	fmt.Printf("=== Syncing single vehicle %d (type %d) into both tables ===\n", extID, typeID)

	tables := []string{"yapps_app_cis_vehicles_one", "yapps_app_cis_vehicles_two"}
	for _, tbl := range tables {
		fmt.Printf("Syncing into %s...\n", tbl)
		vin, updImg, _, err := cisSvc.SyncVehicleDetail(extID, typeID, tbl)
		if err != nil {
			fmt.Printf("ERROR for %s: %v\n", tbl, err)
		} else {
			fmt.Printf("SUCCESS for %s: VIN=%s, updateImages=%v\n", tbl, vin, updImg)
		}
	}

	fmt.Println("Done single sync!")
}
