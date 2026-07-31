package cis

import (
	"fmt"
	"log"
	"math"
	"net/http"
	"strings"
	"sync"
)

type filterBrandRow struct {
	Code     string  `db:"code" json:"code"`
	Name     string  `db:"name" json:"name"`
	RuName   string  `db:"ru_name" json:"ru_name"`
	Vehicles int     `db:"vehicles" json:"vehicles"`
	Min      float64 `db:"min" json:"min"`
	Max      float64 `db:"max" json:"max"`
}

type filterItemRow struct {
	Code  string `db:"code" json:"code"`
	Name  string `db:"name" json:"name"`
	Count int    `db:"cnt" json:"count"`
}

type filterColorRow struct {
	Code  string `db:"code" json:"code"`
	Name  string `db:"name" json:"name"`
	Param string `db:"param" json:"param"`
	Count int    `db:"cnt" json:"count"`
}

type filterYearRow struct {
	Name  string `db:"name" json:"name"`
	Count int    `db:"cnt" json:"count"`
}

type filterModelBrandRow struct {
	Code      string `db:"code" json:"code"`
	Name      string `db:"name" json:"name"`
	BrandCode string `db:"brand_code" json:"-"`
	BrandName string `db:"brand_name" json:"-"`
	Vehicles  int    `db:"vehicles" json:"vehicles"`
}

func (s *Service) handleFilter(w http.ResponseWriter, r *http.Request) {
	q := r.URL.Query()

	typeID := 0
	switch q.Get("type") {
	case "new", "1":
		typeID = 1
	case "used", "2":
		typeID = 2
	}

	filter := parseFilter(r)
	filter.TypeID = typeID

	brandsParam := splitQuery(q.Get("brand"))
	modelsParam := splitQuery(q.Get("model"))
	priceFrom := parseFloatQuery(q.Get("price_from"))
	priceTo := parseFloatQuery(q.Get("price_to"))

	// Brand alias expansion (e.g. chery → chery, tenet), new cars only
	var activeAlias *BrandAlias
	metaBrandCode := ""
	if typeID == 1 && len(brandsParam) == 1 && len(modelsParam) == 0 {
		a := defaultBrandAlias(brandsParam[0])
		if len(a.Codes) > 1 {
			activeAlias = &a
			metaBrandCode = brandsParam[0]
			brandsParam = a.Codes
		}
	}

	table := s.apiTable()

	joins := "JOIN yapps_app_cis_brands b ON b.id = v.brand_id"
	joins += "\nLEFT JOIN yapps_app_cis_models_new mn ON mn.id = v.model_id AND v.type_id = 1"
	joins += "\nLEFT JOIN yapps_app_cis_models_used mu ON mu.id = v.model_id AND v.type_id = 2"

	var wheres []string
	var args []interface{}
	if typeID > 0 {
		wheres = append(wheres, "v.type_id = ?")
		args = append(args, typeID)
	}
	if len(brandsParam) > 0 {
		phs := make([]string, len(brandsParam))
		for i, b := range brandsParam {
			phs[i] = "?"
			args = append(args, b)
		}
		wheres = append(wheres, fmt.Sprintf("b.code IN (%s)", strings.Join(phs, ",")))
	}
	if len(modelsParam) > 0 {
		phs := make([]string, len(modelsParam))
		for i, m := range modelsParam {
			phs[i] = "?"
			args = append(args, m)
		}
		wheres = append(wheres, fmt.Sprintf("COALESCE(mn.code, mu.code) IN (%s)", strings.Join(phs, ",")))
	}
	wheresNoPrice := append([]string{}, wheres...)
	argsNoPrice := append([]interface{}{}, args...)

	if priceFrom > 0 {
		wheres = append(wheres, "v.min_price >= ?")
		args = append(args, priceFrom)
	}
	if priceTo > 0 {
		wheres = append(wheres, "v.min_price <= ?")
		args = append(args, priceTo)
	}

	where := ""
	if len(wheres) > 0 {
		where = "WHERE " + strings.Join(wheres, " AND ")
	}
	whereNoPrice := ""
	if len(wheresNoPrice) > 0 {
		whereNoPrice = "WHERE " + strings.Join(wheresNoPrice, " AND ")
	}

	baseFrom := fmt.Sprintf("%s v\n%s", table, joins)


	var brands []filterBrandRow
	var modelBrandRows []filterModelBrandRow
	var transmissions []filterItemRow
	var engines []filterItemRow
	var drives []filterItemRow
	var bodies []filterItemRow
	var colors []filterColorRow
	var years []filterYearRow
	var dealerships []struct {
		Code      int    `db:"code" json:"code"`
		Name      string `db:"name" json:"name"`
		Url       string `db:"url" json:"url"`
		BrandCode string `db:"brand_code" json:"brand_code"`
		Vehicles  int    `db:"vehicles" json:"vehicles"`
	}

	var wg sync.WaitGroup
	wg.Add(9)

	go func() {
		defer wg.Done()
		if err := s.db.Select(&brands, fmt.Sprintf(`
			SELECT b.code, b.name, COALESCE(b.ru_name,'') AS ru_name,
				COUNT(*) AS vehicles,
				COALESCE(MIN(v.min_price),0) AS min,
				COALESCE(MAX(v.min_price),0) AS max
			FROM %s
			%s
			GROUP BY b.id
			ORDER BY name
		`, baseFrom, where), args...); err != nil {
			log.Printf("filter brands error: %v", err)
		}
	}()

	go func() {
		defer wg.Done()
		if len(brandsParam) > 0 || len(modelsParam) > 0 {
			if err := s.db.Select(&modelBrandRows, fmt.Sprintf(`
				SELECT COALESCE(mn.code, mu.code) AS code,
					COALESCE(mn.name, mu.name) AS name,
					b.code AS brand_code,
					b.name AS brand_name,
					COUNT(*) AS vehicles
				FROM %s
				%s
				GROUP BY COALESCE(mn.code, mu.code), COALESCE(mn.name, mu.name), b.code, b.name
				ORDER BY name
			`, baseFrom, where), args...); err != nil {
				log.Printf("filter models error: %v", err)
			}
		}
	}()

	go func() {
		defer wg.Done()
		if err := s.db.Select(&transmissions, fmt.Sprintf(`
			SELECT t.code, t.name, COUNT(*) AS cnt
			FROM %s
			JOIN yapps_app_cis_transmissions t ON t.code = v.transmission
			%s
			GROUP BY t.code ORDER BY cnt DESC
		`, baseFrom, where), args...); err != nil {
			log.Printf("filter transmissions error: %v", err)
		}
	}()

	go func() {
		defer wg.Done()
		if err := s.db.Select(&engines, fmt.Sprintf(`
			SELECT e.code, e.name, COUNT(*) AS cnt
			FROM %s
			JOIN yapps_app_cis_engines e ON e.code = v.engine
			%s
			GROUP BY e.code ORDER BY cnt DESC
		`, baseFrom, where), args...); err != nil {
			log.Printf("filter engines error: %v", err)
		}
	}()

	go func() {
		defer wg.Done()
		if err := s.db.Select(&drives, fmt.Sprintf(`
			SELECT d.code, d.name, COUNT(*) AS cnt
			FROM %s
			JOIN yapps_app_cis_drives d ON d.code = v.drive
			%s
			GROUP BY d.code ORDER BY cnt DESC
		`, baseFrom, where), args...); err != nil {
			log.Printf("filter drives error: %v", err)
		}
	}()

	go func() {
		defer wg.Done()
		if err := s.db.Select(&bodies, fmt.Sprintf(`
			SELECT bo.code, bo.name, COUNT(*) AS cnt
			FROM %s
			JOIN yapps_app_cis_bodies bo ON bo.code = v.body
			%s
			GROUP BY bo.code ORDER BY cnt DESC
		`, baseFrom, where), args...); err != nil {
			log.Printf("filter bodies error: %v", err)
		}
	}()

	go func() {
		defer wg.Done()
		if err := s.db.Select(&colors, fmt.Sprintf(`
			SELECT c.code, c.name, COALESCE(c.param,'') AS param, COUNT(*) AS cnt
			FROM %s
			JOIN yapps_app_cis_colors c ON c.code = v.color
			%s
			GROUP BY c.code ORDER BY cnt DESC
		`, baseFrom, where), args...); err != nil {
			log.Printf("filter colors error: %v", err)
		}
	}()

	go func() {
		defer wg.Done()
		if err := s.db.Select(&years, fmt.Sprintf(`
			SELECT v.year AS name, COUNT(*) AS cnt
			FROM %s
			%s
			GROUP BY v.year ORDER BY v.year DESC
		`, baseFrom, where), args...); err != nil {
			log.Printf("filter years error: %v", err)
		}
	}()

	go func() {
		defer wg.Done()
		var dealershipFrom = fmt.Sprintf(`%s v
			JOIN yapps_app_cis_brands b ON b.id = v.brand_id
			LEFT JOIN yapps_app_cis_models_new mn ON mn.id = v.model_id AND v.type_id = 1
			LEFT JOIN yapps_app_cis_models_used mu ON mu.id = v.model_id AND v.type_id = 2
			JOIN yapps_app_cis_dealerships d ON d.code = v.dealership_id`, table)
		if typeID != 2 {
			dealershipFrom += " AND d.brand_id = v.brand_id"
		}

		fDS := filter
		fDS.Dealership = nil
		fDS.NotDealership = nil
		dealershipWhereClause, dealershipArgsAll := s.buildConditions(fDS)

		var dealershipQuery string
		if typeID == 2 {
			dealershipQuery = fmt.Sprintf(`
				SELECT d.code, d.name, d.url, '' AS brand_code, COUNT(*) AS vehicles
				FROM %s
				%s
				GROUP BY d.code, d.name, d.url
				ORDER BY d.name
			`, dealershipFrom, dealershipWhereClause)
		} else {
			dealershipQuery = fmt.Sprintf(`
				SELECT d.code, d.name, d.url, b.code AS brand_code, COUNT(*) AS vehicles
				FROM %s
				%s
				GROUP BY d.code, d.name, d.url, b.code
				ORDER BY d.name
			`, dealershipFrom, dealershipWhereClause)
		}
		if err := s.db.Select(&dealerships, dealershipQuery, dealershipArgsAll...); err != nil {
			log.Printf("filter dealerships error: %v", err)
		}
	}()

	wg.Wait()

	brandsOut := make([]map[string]interface{}, 0, len(brands))
	if activeAlias != nil {
		var mergedVeh int
		var mergedMin, mergedMax float64
		for _, b := range brands {
			mergedVeh += b.Vehicles
			if mergedMin == 0 || b.Min < mergedMin {
				mergedMin = b.Min
			}
			if b.Max > mergedMax {
				mergedMax = b.Max
			}
		}
		path := "/cars/new/" + brandsParam[0]
		if typeID == 2 {
			path = "/cars/used/" + brandsParam[0]
		}
		brandsOut = append(brandsOut, map[string]interface{}{
			"code": brandsParam[0], "name": activeAlias.DisplayName, "ru_name": activeAlias.DisplayName,
			"vehicles": mergedVeh, "min": mergedMin, "max": mergedMax, "path": path,
		})
	} else {
		for _, b := range brands {
			path := "/cars/new/" + b.Code
			if typeID == 2 {
				path = "/cars/used/" + b.Code
			}
			brandsOut = append(brandsOut, map[string]interface{}{
				"code": b.Code, "name": b.Name, "ru_name": b.RuName,
				"vehicles": b.Vehicles, "min": b.Min, "max": b.Max, "path": path,
			})
		}
	}

	modelsOut := make([]map[string]interface{}, 0, len(modelBrandRows))
	for _, m := range modelBrandRows {
		modelsOut = append(modelsOut, map[string]interface{}{
			"code": m.Code,
			"name": m.Name,
			"vehicles": m.Vehicles,
			"brand": map[string]interface{}{
				"code": m.BrandCode,
				"name": m.BrandName,
			},
		})
	}

	dealershipsOut := make([]map[string]interface{}, 0, len(dealerships))
	for _, d := range dealerships {
		dealershipsOut = append(dealershipsOut, map[string]interface{}{
			"code":       d.Code,
			"name":       d.Name,
			"url":        d.Url,
			"brand_code": d.BrandCode,
			"vehicles":   d.Vehicles,
		})
	}

	var priceMin, priceMax float64
	var volumeMin, volumeMax, powerMin, powerMax, yearMin, yearMax int
	var totalCount int

	var wgRanges sync.WaitGroup
	wgRanges.Add(9)

	go func() {
		defer wgRanges.Done()
		if err := s.db.Get(&priceMin, fmt.Sprintf(`SELECT COALESCE(MIN(v.min_price),0) FROM %s %s`, baseFrom, whereNoPrice), argsNoPrice...); err != nil {
			log.Printf("filter price_min error: %v", err)
		}
	}()
	go func() {
		defer wgRanges.Done()
		if err := s.db.Get(&priceMax, fmt.Sprintf(`SELECT COALESCE(MAX(v.min_price),0) FROM %s %s`, baseFrom, whereNoPrice), argsNoPrice...); err != nil {
			log.Printf("filter price_max error: %v", err)
		}
	}()
	go func() {
		defer wgRanges.Done()
		s.db.Get(&volumeMin, fmt.Sprintf(`SELECT COALESCE(MIN(NULLIF(v.volume, 0)),0) FROM %s %s`, baseFrom, where), args...)
	}()
	go func() {
		defer wgRanges.Done()
		s.db.Get(&volumeMax, fmt.Sprintf(`SELECT COALESCE(MAX(v.volume),0) FROM %s %s`, baseFrom, where), args...)
	}()
	go func() {
		defer wgRanges.Done()
		s.db.Get(&powerMin, fmt.Sprintf(`SELECT COALESCE(MIN(NULLIF(v.power, 0)),0) FROM %s %s`, baseFrom, where), args...)
	}()
	go func() {
		defer wgRanges.Done()
		s.db.Get(&powerMax, fmt.Sprintf(`SELECT COALESCE(MAX(v.power),0) FROM %s %s`, baseFrom, where), args...)
	}()
	go func() {
		defer wgRanges.Done()
		s.db.Get(&yearMin, fmt.Sprintf(`SELECT COALESCE(MIN(v.year),0) FROM %s %s`, baseFrom, where), args...)
	}()
	go func() {
		defer wgRanges.Done()
		s.db.Get(&yearMax, fmt.Sprintf(`SELECT COALESCE(MAX(v.year),0) FROM %s %s`, baseFrom, where), args...)
	}()
	go func() {
		defer wgRanges.Done()
		if err := s.db.Get(&totalCount, fmt.Sprintf(`SELECT COUNT(*) FROM %s %s`, baseFrom, where), args...); err != nil {
			log.Printf("filter totalCount error: %v", err)
		}
	}()

	wgRanges.Wait()

	priceMin = math.Floor(priceMin/10000) * 10000
	priceMax = math.Ceil(priceMax/10000) * 10000

	priceVal0 := priceMin
	priceVal1 := priceMax
	if filter.PriceFrom > 0 && filter.PriceFrom > priceMin {
		priceVal0 = filter.PriceFrom
	}
	if filter.PriceTo > 0 && filter.PriceTo < priceMax {
		priceVal1 = filter.PriceTo
	}

	volVal0 := volumeMin
	volVal1 := volumeMax
	if filter.VolumeFrom > 0 && filter.VolumeFrom > volumeMin {
		volVal0 = filter.VolumeFrom
	}
	if filter.VolumeTo > 0 && filter.VolumeTo < volumeMax {
		volVal1 = filter.VolumeTo
	}

	powVal0 := powerMin
	powVal1 := powerMax
	if filter.PowerFrom > 0 && filter.PowerFrom > powerMin {
		powVal0 = filter.PowerFrom
	}
	if filter.PowerTo > 0 && filter.PowerTo < powerMax {
		powVal1 = filter.PowerTo
	}

	yearVal0 := yearMin
	yearVal1 := yearMax
	if filter.YearFrom > 0 && filter.YearFrom > yearMin {
		yearVal0 = filter.YearFrom
	}
	if filter.YearTo > 0 && filter.YearTo < yearMax {
		yearVal1 = filter.YearTo
	}

	// Restore original brand code for meta level detection
	if activeAlias != nil {
		filter.Brand = []string{metaBrandCode}
	}
	meta := s.buildCatalogMeta(r, typeID, filter, totalCount)

	modeItems := []map[string]interface{}{
		{"code": "new", "name": "Новые автомобили"},
		{"code": "used", "name": "Автомобили с пробегом"},
	}

	resp := map[string]interface{}{
		"in_city":    "Краснодаре, Майкопе, Новороссийске и Яблоновском",
		"totalCount": totalCount,
		"dropLists": map[string]interface{}{
			"mode":          modeItems,
			"brands":        brandsOut,
			"models":        modelsOut,
			"transmissions": transmissions,
			"engines":       engines,
			"drives":        drives,
			"bodies":        bodies,
			"colors":        colors,
			"years":         years,
			"dealerships":   dealershipsOut,
		},
		"ranges": map[string]interface{}{
			"price":  map[string]interface{}{"min": priceMin, "max": priceMax, "value": []float64{priceVal0, priceVal1}},
			"volume": map[string]interface{}{"min": volumeMin, "max": volumeMax, "value": []int{volVal0, volVal1}},
			"power":  map[string]interface{}{"min": powerMin, "max": powerMax, "value": []int{powVal0, powVal1}},
			"year":   map[string]interface{}{"min": yearMin, "max": yearMax, "value": []int{yearVal0, yearVal1}},
		},
		"meta": meta,
	}

	writeJSON(w, http.StatusOK, resp)
}
