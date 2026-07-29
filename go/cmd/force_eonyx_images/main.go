package main

import (
	"fmt"
	"log"
	"os"
	"path/filepath"

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

	prodTable, _ := cisSvc.TableNames()
	eonyxIDs := []int{1574913, 1651214, 1708465, 1708466, 1708467, 1708468, 1708469, 1708470, 1708471, 1708472, 1708473, 1708474, 1708475}

	fmt.Printf("=== Resetting PROD Eonyx Image Records (active table: %s) ===\n", prodTable)
	for _, id := range eonyxIDs {
		fmt.Printf("Resetting vehicle %d...\n", id)
		// Delete from images table
		database.Exec("DELETE FROM yapps_app_cis_images WHERE ext_id = ?", id)
		// Update active prod table
		database.Exec(fmt.Sprintf("UPDATE %s SET update_images = 1, use_internal_images = 0 WHERE ext_id = ?", prodTable), id)
		
		// Remove physical files
		dir := filepath.Join(cfg.ImageUploadDir, fmt.Sprintf("%d", id))
		if err := os.RemoveAll(dir); err != nil {
			fmt.Printf("Error removing dir for %d: %v\n", id, err)
		}
	}

	fmt.Println("=== Running ProcessImages on PROD ===")
	if err := cisSvc.ProcessImages(); err != nil {
		fmt.Printf("ProcessImages ERROR: %v\n", err)
	} else {
		fmt.Println("ProcessImages SUCCESS!")
	}
}
