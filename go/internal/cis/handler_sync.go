package cis

import (
	"log"
	"net/http"
)

func (s *Service) handleSync(w http.ResponseWriter, r *http.Request) {
	go func() {
		if err := s.FullSync(); err != nil {
			log.Printf("sync error: %v", err)
		}
	}()

	writeJSON(w, http.StatusAccepted, map[string]string{"status": "sync_started"})
}
