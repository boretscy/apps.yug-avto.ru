package cis

import (
	"encoding/json"
	"fmt"
	"log"
	"math/rand"
	"net/http"
	"strconv"
	"strings"
	"sync"

	"github.com/gorilla/mux"
	"github.com/yugavto/apps/pkg/autocrm"
)

type CTAResp struct {
	Type      string `json:"type"`
	Code      string `json:"code"`
	Title     string `json:"title"`
	Button    string `json:"button"`
	Title1    string `json:"title1"`
	Title2    string `json:"title2"`
	Title3    string `json:"title3"`
	Text      string `json:"text"`
	Sizes     []int  `json:"sizes"`
	Sizes2025 []int  `json:"sizes_2025"`
}

var ctaCards = []CTAResp{
	{
		Type:      "random_cta",
		Code:      "credit",
		Title:     "Рассчитайте ежемесячный платеж",
		Button:    "Получить одобрение",
		Title1:    "Рассчитайте",
		Title2:    "ежемесячный",
		Title3:    "платеж",
		Text:      "Получите персональное предложение по кредиту",
		Sizes:     []int{35, 25, 35},
		Sizes2025: []int{36, 24, 36},
	},
	{
		Type:      "random_cta",
		Code:      "trade-in",
		Title:     "Обменяйте автомобиль в Трейд-ин",
		Button:    "Оценить онлайн",
		Title1:    "Обменяйте",
		Title2:    "автомобиль",
		Title3:    "в Трейд-ин",
		Text:      "Используйте оценку автомобиля в качестве первого взноса по кредиту",
		Sizes:     []int{35, 30, 45},
		Sizes2025: []int{35, 30, 45},
	},
	{
		Type:      "random_cta",
		Code:      "sell",
		Title:     "Продайте текущий автомобиль",
		Button:    "Продать автомобиль",
		Title1:    "Продайте",
		Title2:    "текущий",
		Title3:    "автомобиль",
		Text:      "Укажите автомобиль и получите его оценочную стоимость",
		Sizes:     []int{35, 30, 35},
		Sizes2025: []int{35, 30, 35},
	},
	{
		Type:      "random_cta",
		Code:      "offer",
		Title:     "Не знаете, какой автомобиль выбрать?",
		Button:    "Подберите автомобиль",
		Title1:    "Не знаете",
		Title2:    "какой автомобиль",
		Title3:    "выбрать?",
		Text:      "Оставьте контакт и мы сделаем индивидуальное предложение",
		Sizes:     []int{35, 25, 35},
		Sizes2025: []int{35, 25, 35},
	},
}

type vehicleDetailRow struct {
	VehicleRow
}

func (s *Service) handleVehicles(w http.ResponseWriter, r *http.Request) {
	f := parseFilter(r)
	q := r.URL.Query()

	typeID := 0
	if q.Get("type") == "new" || q.Get("mode") == "new" {
		typeID = 1
	} else if q.Get("type") == "used" || q.Get("mode") == "used" {
		typeID = 2
	}
	f.TypeID = typeID

	if !s.SanitizeFilter(&f, typeID) {
		writeJSON(w, http.StatusOK, map[string]interface{}{
			"code":      404,
			"force_404": true,
			"error":     "not_found",
			"message":   "invalid filter parameter",
			"items":     []interface{}{},
			"total":     0,
		})
		return
	}

	alias := expandFilterBrands(&f, typeID)

	// Parse dealership param
	if ds := q.Get("dealership"); ds != "" {
		for _, slug := range splitQuery(ds) {
			id, err := strconv.Atoi(slug)
			if err == nil {
				f.Dealership = append(f.Dealership, id)
			} else {
				var row struct{ Code int `db:"code"` }
				if err := s.db.Get(&row, "SELECT code FROM yapps_app_cis_dealerships WHERE url = ?", slug); err == nil {
					f.Dealership = append(f.Dealership, row.Code)
				}
			}
		}
	}

	query, args, err := s.BuildVehicleQuery(f)
	if err != nil {
		log.Printf("BuildVehicleQuery error: %v", err)
		writeJSON(w, http.StatusInternalServerError, map[string]string{"error": "internal server error"})
		return
	}

	var rows []VehicleRow
	err = s.db.Select(&rows, query, args...)
	if err != nil {
		log.Printf("handleVehicles select error: %v", err)
		writeJSON(w, http.StatusInternalServerError, map[string]string{"error": "internal server error"})
		return
	}

	if rows == nil {
		rows = []VehicleRow{}
	}

	// Query images
	extIDs := make([]string, len(rows))
	for i, r := range rows {
		extIDs[i] = strconv.Itoa(r.ExtID)
	}
	imagesByVehicle := s.queryImages(extIDs)

	// Build items
	respItems := make([]interface{}, 0, len(rows)+4)
	for _, v := range rows {
		imgs := imagesByVehicle[v.ExtID]
		if imgs == nil {
			imgs = []ImageResp{}
		}
		item := s.rowToVehicleFull(&v, v.TypeID, imgs)
		item.Images = normalizeImages(item.Images)
		respItems = append(respItems, item)
	}

	// Insert CTA cards if not getting by id
	idParam := r.URL.Query().Get("id")
	if idParam == "" && len(respItems) > 0 {
		perPage := f.PerPage
		if perPage <= 0 {
			perPage = 30
		}
		page := f.Page
		if page <= 0 {
			page = 1
		}

		// minGap: минимальный гарантированный интервал между любыми двумя CTA (в элементах).
		// 12 элементов = 3 полных ряда при 4 карточках в ряду.
		minGap := 12

		// Первая CTA карточка на 1-й странице ставится в интервал 5..8 (например на 6-ю позицию).
		startOffset := 5 + rand.Intn(4)

		if page > 1 {
			prevTotalItems := (page - 1) * perPage
			rem := prevTotalItems % minGap
			if rem > 0 {
				startOffset = minGap - rem
				if startOffset < 3 {
					startOffset += minGap / 2
				}
			}
		}

		lastInsertedIdx := -minGap
		currentIdx := startOffset

		for currentIdx < len(respItems) {
			if currentIdx-lastInsertedIdx >= minGap {
				ctaCard := ctaCards[rand.Intn(len(ctaCards))]
				respItems = append(respItems[:currentIdx], append([]interface{}{ctaCard}, respItems[currentIdx:]...)...)
				lastInsertedIdx = currentIdx
				currentIdx += minGap + 1 + rand.Intn(3)
			} else {
				currentIdx += minGap
			}
		}
	}

	var totalCount, hasDiscount, hasInStock, hasOnWay int
	var err1, err2, err3, err4 error
	var wg sync.WaitGroup

	wg.Add(4)
	go func() {
		defer wg.Done()
		totalCount, err1 = s.CountVehicles(f)
	}()
	go func() {
		defer wg.Done()
		discountF := f
		discountF.Tag = "discount"
		hasDiscount, err2 = s.CountVehicles(discountF)
	}()
	go func() {
		defer wg.Done()
		instockF := f
		instockF.Tag = "instock"
		hasInStock, err3 = s.CountVehicles(instockF)
	}()
	go func() {
		defer wg.Done()
		onwayF := f
		onwayF.Tag = "onway"
		hasOnWay, err4 = s.CountVehicles(onwayF)
	}()
	wg.Wait()

	if err1 != nil {
		log.Printf("CountVehicles totalCount error: %v", err1)
		writeJSON(w, http.StatusInternalServerError, map[string]string{"error": "internal server error"})
		return
	}
	if err2 != nil {
		log.Printf("CountVehicles hasDiscount error: %v", err2)
		writeJSON(w, http.StatusInternalServerError, map[string]string{"error": "internal server error"})
		return
	}
	if err3 != nil {
		log.Printf("CountVehicles hasInStock error: %v", err3)
		writeJSON(w, http.StatusInternalServerError, map[string]string{"error": "internal server error"})
		return
	}
	if err4 != nil {
		log.Printf("CountVehicles hasOnWay error: %v", err4)
		writeJSON(w, http.StatusInternalServerError, map[string]string{"error": "internal server error"})
		return
	}

	// Build meta — restore original brand code for level detection
	if alias != nil {
		f.Brand = []string{r.URL.Query().Get("brand")}
	}
	meta := s.buildCatalogMeta(r, typeID, f, totalCount)

	writeJSON(w, http.StatusOK, map[string]interface{}{
		"items":       respItems,
		"totalCount":  totalCount,
		"Discount":    hasDiscount > 0,
		"InStock":     hasInStock > 0,
		"OnWay":       hasOnWay > 0,
		"meta":        meta,
	})
}

func (s *Service) queryImages(extIDs []string) map[int][]ImageResp {
	imagesByVehicle := make(map[int][]ImageResp)
	if len(extIDs) == 0 {
		return imagesByVehicle
	}
	var dbImages []struct {
		ExtID   int    `db:"ext_id"`
		Detail  string `db:"detail"`
		Preview string `db:"preview"`
		Number  int    `db:"number"`
	}
	in := make([]string, len(extIDs))
	for i := range extIDs {
		in[i] = "?"
	}
	args := make([]interface{}, len(extIDs))
	for i := range extIDs {
		args[i] = extIDs[i]
	}
	s.db.Select(&dbImages, fmt.Sprintf(`
		SELECT ext_id, detail, preview, number FROM yapps_app_cis_images
		WHERE ext_id IN (%s)
		ORDER BY number ASC
	`, strings.Join(in, ",")), args...)
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
	return imagesByVehicle
}

func (s *Service) handleVehicle(w http.ResponseWriter, r *http.Request) {
	id := mux.Vars(r)["id"]
	table := s.apiTable()

	var row vehicleDetailRow
	err := s.db.Get(&row, fmt.Sprintf(`
		SELECT v.*,
			b.code AS brand_code,
			COALESCE(b.name,'') AS brand_name,
			COALESCE(b.ru_name,'') AS brand_ru_name,
			COALESCE(mn.code, mu.code, '') AS model_code,
			COALESCE(mn.name, mu.name, '') AS model_name,
			COALESCE(mn.ru_name, mu.ru_name, '') AS model_ru_name,
			COALESCE(mn.image, mu.image, '') AS model_image
		FROM %s v
		LEFT JOIN yapps_app_cis_brands b ON b.id = v.brand_id
		LEFT JOIN yapps_app_cis_models_new mn ON mn.id = v.model_id AND v.type_id = 1
		LEFT JOIN yapps_app_cis_models_used mu ON mu.id = v.model_id AND v.type_id = 2
		WHERE v.ext_id = ?
		LIMIT 1
	`, table), id)
	if err != nil {
		writeJSON(w, http.StatusNotFound, map[string]string{"error": "not found"})
		return
	}

	typeID := row.TypeID

	// Query images
	extIDs := []string{id}
	imagesByVehicle := s.queryImages(extIDs)
	imgs := imagesByVehicle[row.ExtID]
	if imgs == nil {
		imgs = []ImageResp{}
	}

	// Build base vehicle
	vf := s.rowToVehicleFull(&row.VehicleRow, typeID, imgs)

	// Build response with detail fields
	resp := map[string]interface{}{
		"id":         row.ExtID,
		"ext_id":     row.ExtID,
		"type":       "vehicle",
		"entity":     map[int]string{1: "new", 2: "used"}[typeID],
		"is_used":    typeID == 2,

		"brand":      map[string]string{"code": row.BrandCode, "name": row.BrandName},
		"model":      map[string]string{"code": row.ModelCode, "name": row.ModelName},

		"price":      row.Price,
		"min_price":  row.MinPrice,
		"vin":        row.Vin,
		"name":       row.Name,
		"equipment":  "",

		"status":     vf.Status,
		"body":       vf.Body,
		"engine":     vf.Engine,
		"transmission": vf.Transmission,
		"drive":      vf.Drive,

		"images":     vf.Images,
		"_images":    vf.Images,
		"_tags":      vf.Tags,
		"_general":   vf.General,
		"image":      vf.Image,

		"link":       vf.Link,
		"dealership": vf.Dealership,
		"discount":   row.Discount,
		"created":    row.Created,
	}

	// Parse raw autocrm JSON for detailed fields
	var raw autocrm.VehicleRaw
	if row.Raw != "" {
		if err := json.Unmarshal([]byte(row.Raw), &raw); err == nil {
			origEquipment := ""
			if raw.EquipmentName != "" {
				origEquipment = raw.EquipmentName
			} else if raw.Equipment != "" {
				origEquipment = raw.Equipment
			}

			if origEquipment != "" {
				var ruName string
				err := s.db.Get(&ruName, `
					SELECT ru_name FROM yapps_app_cis_equipments 
					WHERE brand_id = ? AND model_id = ? AND name = ? 
					LIMIT 1
				`, row.BrandID, row.ModelID, origEquipment)
				if err == nil && ruName != "" {
					resp["equipment"] = ruName
				} else {
					resp["equipment"] = origEquipment
				}
			} else {
				resp["equipment"] = ""
			}

			// Structured general info
			general := make([]map[string]string, 0, len(raw.General))
			for _, g := range raw.General {
				general = append(general, map[string]string{
					"name": g.Name, "value": fmt.Sprintf("%v", g.Value),
				})
			}
			resp["general"] = general

			// Specifications
			specs := make([]map[string]string, 0, len(raw.Specifications))
			for _, spec := range raw.Specifications {
				specs = append(specs, map[string]string{
					"name": spec.Name, "value": fmt.Sprintf("%v", spec.Value),
				})
			}
			resp["specifications"] = specs

			// Split specs into 2 groups for 2-column layout
			specGroups := make([][]map[string]string, 0, 2)
			if len(specs) > 0 {
				half := (len(specs) + 1) / 2
				specGroups = append(specGroups, specs[:half])
				if half < len(specs) {
					specGroups = append(specGroups, specs[half:])
				}
			}
			resp["_specifications"] = specGroups

			// Options (filter empty strings)
			options := make([]map[string]interface{}, 0)
			nonEmptyOpts := make([]string, 0, len(raw.Options))
			for _, o := range raw.Options {
				if o != "" {
					nonEmptyOpts = append(nonEmptyOpts, o)
				}
			}
			if len(nonEmptyOpts) > 0 {
				options = append(options, map[string]interface{}{
					"group": "Комплектация", "options": nonEmptyOpts,
				})
			}
			resp["options"] = options

			// Discounts
			discounts := make([]map[string]interface{}, 0)
			if len(raw.Discounts) > 0 {
				rawItems := make([]RawDiscountItem, 0, len(raw.Discounts))
				for _, d := range raw.Discounts {
					rawItems = append(rawItems, RawDiscountItem{
						ID:          d.ID,
						Name:        d.Name,
						Sum:         d.Sum,
						Types:       d.Types,
						Description: d.Description,
						IsDefault:   d.IsDefault,
					})
				}
				normalized := NormalizeDiscounts(rawItems)
				for _, d := range normalized {
					discounts = append(discounts, map[string]interface{}{
						"id":          d.ID,
						"name":        d.Name,
						"sum":         d.Sum,
						"types":       d.Types,
						"description": d.Description,
						"isDefault":   d.IsDefault,
						"active":      d.Active,
					})
				}
			} else if row.Price > row.MinPrice {
				discounts = append(discounts, map[string]interface{}{
					"name": "Скидка", "description": "Специальное предложение",
					"sum": row.Price - row.MinPrice, "active": true,
				})
			}
			resp["discounts"] = discounts

			// Additional info
			additional := make([]string, 0)
			if raw.ModificationName != "" {
				additional = append(additional, raw.ModificationName)
			}
			if raw.EquipmentName != "" {
				additional = append(additional, raw.EquipmentName)
			}
			if raw.GenerationName != "" {
				additional = append(additional, raw.GenerationName)
			}
			resp["_additional"] = additional

			// Updated date
			updated := raw.VehicleReceiptDate
			if updated == "" {
				updated = raw.VehicleEntryDate
			}
			if len(updated) >= 10 {
				updated = updated[:10]
				parts := strings.Split(updated, "-")
				if len(parts) == 3 {
					resp["_updated"] = parts[2] + "." + parts[1] + "." + parts[0]
				}
			}
		}
	}

	// Query recommended (same brand, random 4)
	recFilter := VehicleFilter{
		TypeID: typeID,
		Brand:  []string{row.BrandCode},
		Limit:  4,
		Sort:   "random",
	}
	recQuery, recArgs, _ := s.BuildVehicleQuery(recFilter)
	var recRows []VehicleRow
	s.db.Select(&recRows, recQuery, recArgs...)
	recItems := make([]VehicleFull, 0, len(recRows))
	if len(recRows) > 0 {
		recExtIDs := make([]string, len(recRows))
		for i, r := range recRows {
			recExtIDs[i] = strconv.Itoa(r.ExtID)
		}
		recImages := s.queryImages(recExtIDs)
		for _, r := range recRows {
			rimgs := recImages[r.ExtID]
			if rimgs == nil {
				rimgs = []ImageResp{}
			}
			item := s.rowToVehicleFull(&r, typeID, rimgs)
			item.Images = normalizeImages(item.Images)
			recItems = append(recItems, item)
		}
	}
	resp["recomended"] = recItems

	// Query others (different type, random 4)
	otherTypeID := 2
	if typeID == 2 {
		otherTypeID = 1
	}
	othFilter := VehicleFilter{
		TypeID: otherTypeID,
		Limit:  4,
		Sort:   "random",
	}
	othQuery, othArgs, _ := s.BuildVehicleQuery(othFilter)
	var othRows []VehicleRow
	s.db.Select(&othRows, othQuery, othArgs...)
	othItems := make([]VehicleFull, 0, len(othRows))
	if len(othRows) > 0 {
		othExtIDs := make([]string, len(othRows))
		for i, r := range othRows {
			othExtIDs[i] = strconv.Itoa(r.ExtID)
		}
		othImages := s.queryImages(othExtIDs)
		for _, r := range othRows {
			oimgs := othImages[r.ExtID]
			if oimgs == nil {
				oimgs = []ImageResp{}
			}
			item := s.rowToVehicleFull(&r, otherTypeID, oimgs)
			item.Images = normalizeImages(item.Images)
			othItems = append(othItems, item)
		}
	}
	resp["others"] = othItems

	// Build vehicle meta
	meta := s.buildVehicleMeta(r, typeID, &row.VehicleRow, row.Raw, imgs)
	resp["meta"] = meta

	writeJSON(w, http.StatusOK, resp)
}
