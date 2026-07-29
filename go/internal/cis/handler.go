package cis

import (
	"encoding/json"
	"log"
	"net/http"
	"net/url"
	"strconv"
	"strings"

	"github.com/gorilla/mux"
)

func (s *Service) RegisterRoutes(r *mux.Router) {
	r.HandleFunc("/api/v1/cis/brands", s.handleBrands).Methods("GET")
	r.HandleFunc("/api/v1/cis/models", s.handleModels).Methods("GET")
	r.HandleFunc("/api/v1/cis/vehicles", s.handleVehicles).Methods("GET")
	r.HandleFunc("/api/v1/cis/vehicle/{id}", s.handleVehicle).Methods("GET")
	r.HandleFunc("/api/v1/cis/filter", s.handleFilter).Methods("GET")
	r.HandleFunc("/api/v1/cis/sync", s.handleSync).Methods("POST")
	r.HandleFunc("/api/v1/cis/search", s.handleSearch).Methods("GET", "POST")
	r.HandleFunc("/api/v1/cis/random", s.handleRandom).Methods("GET")
}

func writeJSON(w http.ResponseWriter, statusCode int, data any) {
	w.Header().Set("Content-Type", "application/json; charset=utf-8")
	w.WriteHeader(statusCode)
	if err := json.NewEncoder(w).Encode(data); err != nil {
		log.Printf("json encode error: %v", err)
	}
}

func parseFilter(r *http.Request) VehicleFilter {
	q := r.URL.Query()
	f := VehicleFilter{}

	f.Brand = getMultiQuery(q, "brand")
	f.Model = getMultiQuery(q, "model")
	f.Transmission = getMultiQuery(q, "transmission")
	f.Engine = getMultiQuery(q, "engine")
	f.Drive = getMultiQuery(q, "drive")
	f.Body = getMultiQuery(q, "body")
	f.Color = getMultiQuery(q, "color")

	// Price
	if priceFrom := parseFloatQuery(q.Get("price_from")); priceFrom > 0 {
		f.PriceFrom = priceFrom
	}
	if priceTo := parseFloatQuery(q.Get("price_to")); priceTo > 0 {
		f.PriceTo = priceTo
	}
	if f.PriceFrom == 0 && f.PriceTo == 0 {
		if parts := splitQuery(q.Get("price")); len(parts) == 2 {
			f.PriceFrom = parseFloatQuery(parts[0])
			f.PriceTo = parseFloatQuery(parts[1])
		}
	}

	// Volume
	if volFrom := parseIntQuery(q.Get("volume_from")); volFrom > 0 {
		f.VolumeFrom = volFrom
	}
	if volTo := parseIntQuery(q.Get("volume_to")); volTo > 0 {
		f.VolumeTo = volTo
	}
	if f.VolumeFrom == 0 && f.VolumeTo == 0 {
		if parts := splitQuery(q.Get("volume")); len(parts) == 2 {
			f.VolumeFrom = parseIntQuery(parts[0])
			f.VolumeTo = parseIntQuery(parts[1])
		}
	}

	// Power
	if powFrom := parseIntQuery(q.Get("power_from")); powFrom > 0 {
		f.PowerFrom = powFrom
	}
	if powTo := parseIntQuery(q.Get("power_to")); powTo > 0 {
		f.PowerTo = powTo
	}
	if f.PowerFrom == 0 && f.PowerTo == 0 {
		if parts := splitQuery(q.Get("power")); len(parts) == 2 {
			f.PowerFrom = parseIntQuery(parts[0])
			f.PowerTo = parseIntQuery(parts[1])
		}
	}

	// Year
	if yearFrom := parseIntQuery(q.Get("year_from")); yearFrom > 0 {
		f.YearFrom = yearFrom
	}
	if yearTo := parseIntQuery(q.Get("year_to")); yearTo > 0 {
		f.YearTo = yearTo
	}
	if f.YearFrom == 0 && f.YearTo == 0 {
		if parts := splitQuery(q.Get("year")); len(parts) == 2 {
			f.YearFrom = parseIntQuery(parts[0])
			f.YearTo = parseIntQuery(parts[1])
		}
	}

	if mileageTo := parseIntQuery(q.Get("mileage_to")); mileageTo > 0 {
		f.MileageTo = mileageTo
	}

	f.Tag = q.Get("tag")
	f.Sort = q.Get("sort")
	
	if l, err := strconv.Atoi(q.Get("limit")); err == nil && l > 0 {
		f.Limit = l
	}

	if perPage, err := strconv.Atoi(q.Get("perpage")); err == nil && perPage > 0 {
		f.PerPage = perPage
	} else if perPage, err := strconv.Atoi(q.Get("per_page")); err == nil && perPage > 0 {
		f.PerPage = perPage
	}

	if page, err := strconv.Atoi(q.Get("page")); err == nil && page > 0 {
		f.Page = page
	}

	for _, idStr := range getMultiQuery(q, "id") {
		if idVal, err := strconv.Atoi(idStr); err == nil {
			f.ID = append(f.ID, idVal)
		}
	}

	return f
}

func getMultiQuery(q url.Values, key string) []string {
	var result []string
	seen := make(map[string]bool)

	add := func(val string) {
		for _, part := range strings.Split(val, ",") {
			part = strings.TrimSpace(part)
			if part != "" && !seen[part] {
				seen[part] = true
				result = append(result, part)
			}
		}
	}

	if vals, ok := q[key]; ok {
		for _, v := range vals {
			add(v)
		}
	}
	if vals, ok := q[key+"[]"]; ok {
		for _, v := range vals {
			add(v)
		}
	}
	prefix := key + "["
	for k, vals := range q {
		if strings.HasPrefix(k, prefix) {
			for _, v := range vals {
				add(v)
			}
		}
	}

	return result
}

func splitQuery(s string) []string {
	if s == "" {
		return nil
	}
	parts := strings.Split(s, ",")
	for i := range parts {
		parts[i] = strings.TrimSpace(parts[i])
	}
	return parts
}

func parseIntQuery(s string) int {
	v, _ := strconv.Atoi(s)
	return v
}

func parseFloatQuery(s string) float64 {
	v, _ := strconv.ParseFloat(s, 64)
	return v
}

func normalizeImages(imgs []ImageResp) []ImageResp {
	if len(imgs) >= 4 {
		return imgs[:4]
	} else if len(imgs) > 0 {
		last := imgs[len(imgs)-1]
		for len(imgs) < 4 {
			imgs = append(imgs, last)
		}
	}
	return imgs
}

func (s *Service) getDealership(id int, brandID int) DealershipResp {
	d := DealershipResp{ID: id}
	var row struct {
		Name    string `db:"name"`
		Phone   string `db:"phone"`
		Email   string `db:"email"`
		Address string `db:"address"`
		URL     string `db:"url"`
		City    string `db:"city"`
		InCity  string `db:"in_city"`
	}
	err := s.db.Get(&row, "SELECT name, phone, email, address, url, city, in_city FROM yapps_app_cis_dealerships WHERE code = ? AND brand_id = ?", id, brandID)
	if err != nil {
		err = s.db.Get(&row, "SELECT name, phone, email, address, url, city, in_city FROM yapps_app_cis_dealerships WHERE code = ?", id)
		if err != nil {
			return d
		}
	}
	d.Name = row.Name
	d.Phone = row.Phone
	d.Email = row.Email
	d.Address = row.Address
	d.Site = row.URL
	d.City = row.City
	d.InCity = row.InCity
	return d
}
