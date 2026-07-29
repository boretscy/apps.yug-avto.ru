package main

import (
	"context"
	"log"
	"net/http"
	"os"
	"os/signal"
	"strings"
	"syscall"
	"time"

	"github.com/gorilla/mux"
	"github.com/yugavto/apps/config"
	"github.com/yugavto/apps/internal/cis"
	"github.com/yugavto/apps/internal/widgets"
	"github.com/yugavto/apps/pkg/autocrm"
	"github.com/yugavto/apps/pkg/db"
)

func tokenAuth(token string) mux.MiddlewareFunc {
	return func(next http.Handler) http.Handler {
		return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
			reqToken := r.URL.Query().Get("token")
			if reqToken == "" {
				authHeader := r.Header.Get("Authorization")
				if strings.HasPrefix(authHeader, "Bearer ") {
					reqToken = strings.TrimPrefix(authHeader, "Bearer ")
				}
			}
			if reqToken != token {
				w.Header().Set("Content-Type", "application/json; charset=utf-8")
				w.WriteHeader(http.StatusUnauthorized)
				w.Write([]byte(`{"error":"unauthorized"}`))
				return
			}
			next.ServeHTTP(w, r)
		})
	}
}

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
		log.Fatalf("init: %v", err)
	}

	widgetsSvc := widgets.NewService(database, cfg.ProjectRoot, cfg.APIToken)
	widgetsHdr := widgets.NewHandler(widgetsSvc)

	r := mux.NewRouter()

	if cfg.APIToken != "" {
		r.Use(tokenAuth(cfg.APIToken))
	}

	cisSvc.RegisterRoutes(r)
	widgetsHdr.RegisterRoutes(r)

	addr := cfg.APIAddr
	if addr == "" {
		addr = ":8080"
	}

	srv := &http.Server{
		Addr:         addr,
		Handler:      r,
		ReadTimeout:  30 * time.Second,
		WriteTimeout: 60 * time.Second,
	}

	// Channel to listen for interrupts
	stop := make(chan os.Signal, 1)
	signal.Notify(stop, os.Interrupt, syscall.SIGTERM, syscall.SIGINT)

	log.Printf("starting API server on %s", addr)
	go func() {
		if err := srv.ListenAndServe(); err != nil && err != http.ErrServerClosed {
			log.Fatalf("server: %v", err)
		}
	}()

	// Wait for signal
	<-stop

	log.Println("shutting down API server...")
	ctx, cancel := context.WithTimeout(context.Background(), 15*time.Second)
	defer cancel()

	if err := srv.Shutdown(ctx); err != nil {
		log.Fatalf("server graceful shutdown failed: %v", err)
	}
	log.Println("API server stopped gracefully")
}
