package cis

import (
	"database/sql"
	"fmt"
	"log"
	"net/http"
)

func (s *Service) handleBrands(w http.ResponseWriter, r *http.Request) {
	section := r.URL.Query().Get("type")
	table := s.apiTable()

	typeID := 0
	if section == "new" {
		typeID = 1
	} else if section == "used" {
		typeID = 2
	}

	f := parseFilter(r)
	f.TypeID = typeID

	// Очищаем фильтрацию по бренду и модели, чтобы в выпадайке оставались другие бренды
	f.Brand = nil
	f.NotBrand = nil
	f.Model = nil
	f.NotModel = nil

	where, args := s.buildConditions(f)

	type brandWithStats struct {
		Code     string  `db:"code"`
		Name     string  `db:"name"`
		RuName   string  `db:"ru_name"`
		Vehicles int     `db:"vehicles"`
		Min      float64 `db:"min"`
		Max      float64 `db:"max"`
	}

	var brands []brandWithStats
	err := s.db.Select(&brands, fmt.Sprintf(`
		SELECT b.code, b.name, COALESCE(b.ru_name,'') AS ru_name,
			COUNT(*) AS vehicles,
			COALESCE(MIN(v.price),0) AS min,
			COALESCE(MAX(v.price),0) AS max
		FROM %s v
		JOIN yapps_app_cis_brands b ON b.id = v.brand_id
		LEFT JOIN yapps_app_cis_models_new mn ON mn.id = v.model_id AND v.type_id = 1
		LEFT JOIN yapps_app_cis_models_used mu ON mu.id = v.model_id AND v.type_id = 2
		%s
		GROUP BY b.id
		ORDER BY b.name
	`, table, where), args...)
	if err != nil {
		log.Printf("handleBrands error: %v", err)
		writeJSON(w, http.StatusInternalServerError, map[string]string{"error": "internal server error"})
		return
	}

	if brands == nil {
		brands = []brandWithStats{}
	}

	out := make([]map[string]interface{}, 0, len(brands))
	for _, b := range brands {
		path := "/cars/new/" + b.Code
		if typeID == 2 {
			path = "/cars/used/" + b.Code
		}
		out = append(out, map[string]interface{}{
			"code":     b.Code,
			"name":     b.Name,
			"vehicles": b.Vehicles,
			"min":      b.Min,
			"max":      b.Max,
			"path":     path,
		})
	}

	writeJSON(w, http.StatusOK, map[string]interface{}{
		"dropLists": map[string]interface{}{
			"brands": out,
		},
	})
}

func (s *Service) handleModels(w http.ResponseWriter, r *http.Request) {
	brandCode := r.URL.Query().Get("brand")
	section := r.URL.Query().Get("type")

	modelTable := "yapps_app_cis_models_new"
	if section == "used" {
		modelTable = "yapps_app_cis_models_used"
	}

	var models []struct {
		ID       int            `db:"id" json:"id"`
		Code     string         `db:"code" json:"code"`
		Name     string         `db:"name" json:"name"`
		RuName   string         `db:"ru_name" json:"ru_name"`
		Image    sql.NullString `db:"image" json:"-"`
		ImageStr string         `json:"image,omitempty"`
	}

	query := `SELECT m.id, m.code, m.name, m.ru_name, m.image
		FROM ` + modelTable + ` m
		JOIN yapps_app_cis_brands b ON b.id = m.brand_id
		WHERE b.code = ? ORDER BY m.name`
	err := s.db.Select(&models, query, brandCode)
	if err != nil {
		log.Printf("handleModels error: %v", err)
		writeJSON(w, http.StatusInternalServerError, map[string]string{"error": "internal server error"})
		return
	}

	for i := range models {
		if models[i].Image.Valid {
			models[i].ImageStr = models[i].Image.String
		}
	}

	writeJSON(w, http.StatusOK, map[string]interface{}{"items": models})
}
