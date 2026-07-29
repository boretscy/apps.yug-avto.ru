package main

import (
	"log"

	"github.com/robfig/cron/v3"

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

	c := cron.New(cron.WithChain(cron.SkipIfStillRunning(cron.DefaultLogger)))

	runSync := func() {
		log.Println("CIS new vehicles sync start")
		newResult, err := cisSvc.SyncNewVehicles()
		if err != nil {
			log.Printf("new vehicles sync error: %v", err)
		} else {
			log.Printf("new: %d ok, %d err", newResult.OK, newResult.Errors)
		}

		usedResult, err := cisSvc.SyncUsedVehicles()
		if err != nil {
			log.Printf("used vehicles sync error (non-fatal): %v", err)
		} else {
			log.Printf("used: %d ok, %d err", usedResult.OK, usedResult.Errors)
		}

		ok, err := cisSvc.IsOK()
		if err != nil {
			log.Printf("isok error: %v", err)
			return
		}
		if ok {
			if err := cisSvc.ToggleTables(); err != nil {
				log.Printf("toggle error: %v", err)
				return
			}
			log.Println("tables toggled")

			if err := cisSvc.ProcessImages(); err != nil {
				log.Printf("process images error: %v", err)
			}
		}
	}

	c.AddFunc("@every 2m", runSync)
	go runSync()

	c.AddFunc("0 6 * * *", func() {
		log.Println("CIS brands/models daily sync")
		if err := cisSvc.SyncBrands("new"); err != nil {
			log.Printf("brands sync error: %v", err)
		}
		brands, err := crm.GetBrands()
		if err != nil {
			log.Printf("get brands error: %v", err)
			return
		}
		for _, b := range brands.Items {
			if err := cisSvc.SyncModels(b.ID, "new"); err != nil {
				log.Printf("models sync %d error: %v", b.ID, err)
			}
		}
		log.Println("brands/models sync done")
	})

	go cisSvc.StartBackgroundRefresher()

	c.Start()
	log.Println("cron worker started")
	select {}
}
