package cis

import (
	"fmt"
	"log"
	"net/http"
	"strconv"
	"strings"
)

func (s *Service) handleRandom(w http.ResponseWriter, r *http.Request) {
	q := r.URL.Query()
	typeID := 0
	switch q.Get("type") {
	case "1", "new":
		typeID = 1
	case "2", "used":
		typeID = 2
	}
	if typeID == 0 {
		writeJSON(w, http.StatusBadRequest, map[string]string{"error": "invalid type"})
		return
	}

	limit := 12
	if l, err := strconv.Atoi(q.Get("limit")); err == nil && l > 0 && l <= 50 {
		limit = l
	}

	// Parse filters from query, resolve special formats
	f := parseFilter(r)
	f.TypeID = typeID
	f.Sort = "random"
	f.Limit = limit

	// Parse price=min,max format
	if price := q.Get("price"); price != "" {
		parts := strings.Split(price, ",")
		if len(parts) == 2 {
			f.PriceFrom, _ = strconv.ParseFloat(parts[0], 64)
			f.PriceTo, _ = strconv.ParseFloat(parts[1], 64)
		}
	}

	// Resolve dealership slugs to numeric IDs
	if ds := q.Get("dealership"); ds != "" {
		slugs := splitQuery(ds)
		var ids []int
		for _, slug := range slugs {
			id, err := strconv.Atoi(slug)
			if err == nil {
				ids = append(ids, id)
				continue
			}
			var row struct {
				Code int `db:"code"`
			}
			if err := s.db.Get(&row, "SELECT code FROM yapps_app_cis_dealerships WHERE url = ?", slug); err == nil {
				ids = append(ids, row.Code)
			}
		}
		f.Dealership = ids
	}

	table := s.apiTable()
	where, args := s.buildConditions(f)
	args = append(args, limit)

	var rows []VehicleRow
	err := s.db.Select(&rows, fmt.Sprintf(`
		SELECT v.*,
			b.code AS brand_code,
			COALESCE(b.name,'') AS brand_name,
			COALESCE(b.ru_name,'') AS brand_ru_name,
			COALESCE(mn.code, mu.code, '') AS model_code,
			COALESCE(mn.name, mu.name, '') AS model_name,
			COALESCE(mn.ru_name, mu.ru_name, '') AS model_ru_name,
			COALESCE(mn.image, mu.image, '') AS model_image
		FROM %s v
		JOIN yapps_app_cis_brands b ON b.id = v.brand_id
		LEFT JOIN yapps_app_cis_models_new mn ON mn.id = v.model_id AND v.type_id = 1
		LEFT JOIN yapps_app_cis_models_used mu ON mu.id = v.model_id AND v.type_id = 2
		%s
		ORDER BY RAND()
		LIMIT ?
	`, table, where), args...)
	if err != nil {
		log.Printf("random vehicles select error: %v", err)
		writeJSON(w, http.StatusInternalServerError, map[string]string{"error": "internal server error"})
		return
	}

	// Query images for these vehicles
	extIDs := make([]string, len(rows))
	for i, r := range rows {
		extIDs[i] = strconv.Itoa(r.ExtID)
	}
	imagesByVehicle := make(map[int][]ImageResp)
	if len(extIDs) > 0 {
		var dbImages []struct {
			ExtID   int    `db:"ext_id"`
			Detail  string `db:"detail"`
			Preview string `db:"preview"`
			Number  int    `db:"number"`
		}
		inPlaceholders := make([]string, len(extIDs))
		for i := range extIDs {
			inPlaceholders[i] = "?"
		}
		imgArgs := make([]interface{}, len(extIDs))
		for i := range extIDs {
			imgArgs[i] = extIDs[i]
		}
		s.db.Select(&dbImages, fmt.Sprintf(`
			SELECT ext_id, detail, preview, number FROM yapps_app_cis_images
			WHERE ext_id IN (%s)
			ORDER BY number ASC
		`, strings.Join(inPlaceholders, ",")), imgArgs...)
		for _, img := range dbImages {
			detail := img.Detail
			if !strings.HasPrefix(detail, "http") {
				detail = s.imageBaseURL + detail
			}
			preview := img.Preview
			if !strings.HasPrefix(preview, "http") {
				preview = s.imageBaseURL + preview
			}
			imagesByVehicle[img.ExtID] = append(imagesByVehicle[img.ExtID], ImageResp{
				ID:           strconv.Itoa(img.ExtID),
				Detail:       detail,
				Preview:      preview,
				PreviewLarge: detail,
				PreviewSmall: preview,
				Big:          detail,
				Thumb:        preview,
			})
		}
	}

	var items []VehicleFull
	for _, r := range rows {
		imgs := imagesByVehicle[r.ExtID]
		if imgs == nil {
			imgs = []ImageResp{}
		}
		item := s.rowToVehicleFull(&r, typeID, imgs)
		item.Images = normalizeImages(item.Images)
		items = append(items, item)
	}

	if items == nil {
		items = []VehicleFull{}
	}

	totalCount, _ := s.CountVehicles(f)

	var priceRange struct {
		Min float64 `db:"min" json:"min"`
		Max float64 `db:"max" json:"max"`
	}
	s.db.Get(&priceRange, fmt.Sprintf(`
		SELECT COALESCE(MIN(v.min_price),0) AS min, COALESCE(MAX(v.min_price),0) AS max
		FROM %s v`, table))

	var valueRange struct {
		Min float64 `db:"min" json:"min"`
		Max float64 `db:"max" json:"max"`
	}
	cond, condArgs := s.buildConditions(f)
	s.db.Get(&valueRange, fmt.Sprintf(`
		SELECT COALESCE(MIN(v.min_price),0) AS min, COALESCE(MAX(v.min_price),0) AS max
		FROM %s v
		JOIN yapps_app_cis_brands b ON b.id = v.brand_id
		%s`, table, cond), condArgs...)

	resp := map[string]interface{}{
		"items":      items,
		"totalCount": totalCount,
		"ranges": map[string]interface{}{
			"price": map[string]interface{}{
				"min":   priceRange.Min,
				"max":   priceRange.Max,
				"value": valueRange,
			},
		},
	}

	writeJSON(w, http.StatusOK, resp)
}
