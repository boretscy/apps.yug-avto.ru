package widgets

import (
	"encoding/json"
	"io"
	"net/http"

	"github.com/gorilla/mux"
)

type Handler struct {
	svc *Service
}

func NewHandler(svc *Service) *Handler {
	return &Handler{svc: svc}
}

func (h *Handler) RegisterRoutes(r *mux.Router) {
	// Маршрут генерации бандла скрипта
	r.HandleFunc("/api/v1/widgets/script", h.handleScript).Methods("GET", "OPTIONS")
	r.HandleFunc("/API/get/widgets3-script/", h.handleScript).Methods("GET", "OPTIONS")

	// Маршруты приема статистики / формы
	r.HandleFunc("/api/v1/widgets/stat", h.handleStat).Methods("POST", "OPTIONS")
	r.HandleFunc("/API/stat/", h.handleStat).Methods("POST", "OPTIONS")
}

func (h *Handler) handleScript(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
	w.Header().Set("Access-Control-Allow-Headers", "Content-Type, Authorization")
	w.Header().Set("Content-Type", "application/javascript; charset=utf-8")

	if r.Method == http.MethodOptions {
		w.WriteHeader(http.StatusOK)
		return
	}

	pageURL := r.URL.Query().Get("r")
	if pageURL == "" {
		pageURL = r.Header.Get("Referer")
	}

	if pageURL == "" {
		w.Write([]byte("// yapps widgets: referer or 'r' query param missing"))
		return
	}

	script, err := h.svc.GetScript(pageURL)
	if err != nil {
		w.WriteHeader(http.StatusInternalServerError)
		w.Write([]byte("// yapps widgets error: " + err.Error()))
		return
	}

	w.WriteHeader(http.StatusOK)
	w.Write([]byte(script))
}

func (h *Handler) handleStat(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
	w.Header().Set("Access-Control-Allow-Headers", "Content-Type, Authorization")
	w.Header().Set("Content-Type", "application/json; charset=utf-8")

	if r.Method == http.MethodOptions {
		w.WriteHeader(http.StatusOK)
		return
	}

	var payload StatPayload

	// Попытка декодировать JSON
	bodyBytes, err := io.ReadAll(r.Body)
	if err == nil && len(bodyBytes) > 0 {
		_ = json.Unmarshal(bodyBytes, &payload)
	}

	// Если данные переданы как form-urlencoded
	if payload.WidgetFormPhone == "" {
		_ = r.ParseForm()
		if r.Form.Get("yapps-widget-form-phone") != "" {
			payload.WidgetFormName = r.Form.Get("yapps-widget-form-name")
			payload.WidgetFormPhone = r.Form.Get("yapps-widget-form-phone")
			payload.WidgetFormTime = r.Form.Get("yapps-widget-form-time")
			payload.EventName = r.Form.Get("EventName")
			payload.Source = r.Form.Get("Source")
			payload.CTSiteID = r.Form.Get("CT_site_id")
			payload.CTSessionID = r.Form.Get("CT_sessionId")
		}
	}

	clientIP := r.Header.Get("X-Real-IP")
	if clientIP == "" {
		clientIP = r.RemoteAddr
	}

	_ = h.svc.PushStat(payload, clientIP)

	resp := map[string]string{"status": "success"}
	json.NewEncoder(w).Encode(resp)
}
