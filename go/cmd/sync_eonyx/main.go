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

	eonyxIDs := []int{1574913, 1651214, 1708465, 1708466, 1708467, 1708468, 1708469, 1708470, 1708471, 1708472, 1708473, 1708474, 1708475}

	fmt.Println("=== Manually Syncing Eonyx Vehicles ===")
	for _, id := range eonyxIDs {
		fmt.Printf("Syncing vehicle %d...\n", id)
		vin, updImg, _, err := cisSvc.SyncVehicleDetail(id, 1, "yapps_app_cis_vehicles_one")
		if err != nil {
			fmt.Printf("VEHICLE %d: ERROR: %v\n", id, err)
		} else {
			fmt.Printf("VEHICLE %d: SUCCESS: VIN=%s, updateImages=%v\n", id, vin, updImg)
		}
		time.Sleep(2 * time.Second) // Be extremely gentle to avoid triggering DDoS-Guard
	}

	fmt.Println("=== Downloading and Processing Images ===")
	if err := cisSvc.ProcessImages(); err != nil {
		fmt.Printf("ProcessImages ERROR: %v\n", err)
	} else {
		fmt.Println("ProcessImages SUCCESS!")
	}
}
