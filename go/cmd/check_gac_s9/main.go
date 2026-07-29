package main

import (
	"fmt"
	"log"
	"strings"

	"github.com/yugavto/apps/config"
	"github.com/yugavto/apps/pkg/autocrm"
)

func main() {
	cfg, err := config.Load()
	if err != nil {
		log.Fatalf("config: %v", err)
	}

	crm := autocrm.NewClient(cfg.AutoCRMBaseURL, cfg.AutoCRMAPIKey)

	fmt.Println("=== Checking GAC S9 in AutoCRM API ===")
	page := 1
	found := false
	for {
		resp, err := crm.GetVehiclesList("new", page)
		if err != nil {
			log.Fatalf("error fetching vehicles: %v", err)
		}

		for _, v := range resp.Items {
			// Check brand and model name
			isGAC := strings.Contains(strings.ToLower(v.BrandName), "gac")
			isS9 := strings.Contains(strings.ToLower(v.ModelName), "s9") || strings.Contains(strings.ToLower(v.RefModelName), "s9")

			if isGAC && isS9 {
				fmt.Printf("FOUND VEHICLE: ID=%d, VIN=%s, Brand=%s, Model=%s, RefModel=%s, Name=%s, Price=%.2f\n",
					v.ID, v.Vin, v.BrandName, v.ModelName, v.RefModelName, v.ModificationName, v.Price)
				found = true
			}
		}

		if resp.Meta == nil || page >= resp.Meta.PageCount {
			break
		}
		page++
	}

	if !found {
		fmt.Println("No GAC S9 vehicles found in AutoCRM API new vehicles list.")
	}
}
