package cis

import (
	"encoding/json"
	"fmt"
	"net/http"
	"strings"
	"time"
)

type SEORow struct {
	ID              int    `db:"id"`
	Site            string `db:"site"`
	Entity          string `db:"entity"`
	Level           string `db:"level"`
	Custom          string `db:"custom"`
	MetaH1          string `db:"meta_h1"`
	MetaTitle       string `db:"meta_title"`
	MetaDescription string `db:"meta_description"`
	SeoTitle        string `db:"seo_title"`
	SeoText         string `db:"seo_text"`
	Phone           string `db:"phone"`
}

func formatNumber(q float64) string {
	s := fmt.Sprintf("%.0f", q)
	n := len(s)
	if n <= 3 {
		return s
	}
	var parts []string
	for i := n; i > 0; i -= 3 {
		start := i - 3
		if start < 0 {
			start = 0
		}
		parts = append([]string{s[start:i]}, parts...)
	}
	return strings.Join(parts, " ")
}

func formatPhoneOut(phone string) string {
	cleaned := make([]byte, 0, 11)
	for i := 0; i < len(phone); i++ {
		if phone[i] >= '0' && phone[i] <= '9' {
			cleaned = append(cleaned, phone[i])
		}
	}
	if len(cleaned) == 10 {
		cleaned = append([]byte{'7'}, cleaned...)
	}
	if len(cleaned) != 11 || cleaned[0] != '7' {
		return phone
	}
	return fmt.Sprintf("+%c (%c%c%c) %c%c%c-%c%c-%c%c",
		cleaned[0], cleaned[1], cleaned[2], cleaned[3],
		cleaned[4], cleaned[5], cleaned[6],
		cleaned[7], cleaned[8], cleaned[9], cleaned[10])
}

func getWorld(count int) string {
	c := count % 100
	if c >= 5 && c <= 20 {
		return "автомобилей"
	}
	c = count % 10
	switch c {
	case 1:
		return "автомобиль"
	case 2, 3, 4:
		return "автомобиля"
	}
	return "автомобилей"
}

func (s *Service) querySEOTemplate(site, entity, level, custom string) *SEORow {
	var row SEORow
	err := s.db.Get(&row, `SELECT * FROM yapps_app_cis_seo WHERE site = ? AND entity = ? AND level = ? AND custom = ? LIMIT 1`,
		site, entity, level, custom)
	if err == nil {
		return &row
	}
	return nil
}

func (s *Service) getPhone(site string) string {
	var phone string
	err := s.db.Get(&phone, `SELECT phone FROM yapps_app_cis_seo WHERE site = ? AND entity = 'phone' LIMIT 1`, site)
	if err == nil && phone != "" {
		return phone
	}
	err = s.db.Get(&phone, `SELECT phone FROM yapps_app_cis_seo WHERE site = ? AND phone != '' LIMIT 1`, site)
	if err == nil {
		return phone
	}
	return "78612031755"
}

func (s *Service) getMinPrice(filter VehicleFilter) float64 {
	table := s.apiTable()
	where, args := s.buildConditions(filter)
	var minPrice float64
	err := s.db.Get(&minPrice, fmt.Sprintf(`SELECT COALESCE(MIN(v.min_price),0) FROM %s v
		LEFT JOIN yapps_app_cis_brands b ON b.id = v.brand_id
		LEFT JOIN yapps_app_cis_models_new mn ON mn.id = v.model_id AND v.type_id = 1
		LEFT JOIN yapps_app_cis_models_used mu ON mu.id = v.model_id AND v.type_id = 2
		%s`, table, where), args...)
	if err != nil {
		return 0
	}
	return minPrice
}

func (s *Service) getBrandName(code string) string {
	var name string
	s.db.Get(&name, `SELECT COALESCE(name,'') FROM yapps_app_cis_brands WHERE code = ? LIMIT 1`, code)
	return name
}

func (s *Service) getBrandRuName(code string) string {
	var name string
	s.db.Get(&name, `SELECT COALESCE(ru_name,'') FROM yapps_app_cis_brands WHERE code = ? LIMIT 1`, code)
	return name
}

func (s *Service) getModelName(code, entity string) string {
	table := "yapps_app_cis_models_new"
	if entity == "used" {
		table = "yapps_app_cis_models_used"
	}
	var name string
	s.db.Get(&name, fmt.Sprintf(`SELECT COALESCE(name,'') FROM %s WHERE code = ? LIMIT 1`, table), code)
	return name
}

func (s *Service) getModelRuName(code, entity string) string {
	table := "yapps_app_cis_models_new"
	if entity == "used" {
		table = "yapps_app_cis_models_used"
	}
	var name string
	s.db.Get(&name, fmt.Sprintf(`SELECT COALESCE(ru_name,'') FROM %s WHERE code = ? LIMIT 1`, table), code)
	return name
}

func (s *Service) getColorName(code string) string {
	var name string
	s.db.Get(&name, `SELECT COALESCE(name,'') FROM yapps_app_cis_colors WHERE code = ? LIMIT 1`, code)
	return name
}

func (s *Service) buildCatalogMeta(r *http.Request, typeID int, filter VehicleFilter, count int) map[string]interface{} {
	site := r.URL.Query().Get("site")
	if site == "" {
		site = r.Host
	}
	if site == "" || site == "apps.avatr-yugavto.ru" || site == "apps.yug-avto.ru" || strings.HasSuffix(site, "localhost") || strings.Contains(site, ":808") {
		site = "yug-avto.ru"
	}

	entity := "new"
	if typeID == 2 {
		entity = "used"
	}

	brandCode := ""
	modelCode := ""
	if len(filter.Brand) == 1 {
		brandCode = filter.Brand[0]
	}
	if len(filter.Model) == 1 {
		modelCode = filter.Model[0]
	}

	level := "brands"
	custom := ""
	if brandCode != "" {
		level = "brand"
		custom = brandCode
		if modelCode != "" {
			level = "model"
			custom = modelCode
		}
	}

	seo := s.querySEOTemplate(site, entity, level, custom)
	if seo == nil {
		seo = s.querySEOTemplate(site, entity, level, "")
	}

	var metaH1, metaTitle, metaDescription string
	if seo != nil {
		metaH1 = seo.MetaH1
		metaTitle = seo.MetaTitle
		metaDescription = seo.MetaDescription
		_ = metaDescription
	} else {
		prefix := "Новые"
		mid := "новые"
		if typeID == 2 {
			prefix = "Автомобили с пробегом"
			mid = "автомобили с пробегом"
		}
		metaH1 = fmt.Sprintf("%s в Краснодаре | Юг-Авто", prefix)
		metaTitle = metaH1
		metaDescription = fmt.Sprintf("Купить %s в Краснодаре. Большой выбор, выгодные цены.", mid)
	}

	brandName := ""
	brandRuName := ""
	modelName := ""
	modelRuName := ""
	if brandCode != "" {
		brandName = s.getBrandName(brandCode)
		brandRuName = s.getBrandRuName(brandCode)
		// Brand alias override (e.g. chery + tenet → "Chery и Tenet"), new cars only
		if entity == "new" && modelCode == "" {
			if a := defaultBrandAlias(brandCode); len(a.Codes) > 1 {
				brandName = a.DisplayName
				brandRuName = a.DisplayName
			}
		}
	}
	if modelCode != "" {
		modelName = s.getModelName(modelCode, entity)
		modelRuName = s.getModelRuName(modelCode, entity)
	}

	minPrice := s.getMinPrice(filter)
	phone := s.getPhone(site)

	q := r.URL.Query()

	engineFilter := ""
	transFilter := ""
	driveFilter := ""
	colorFilter := ""
	var filterParts []string

	if e := q.Get("engine"); e != "" && !strings.Contains(e, ",") {
		for _, en := range s.engines {
			if en.Code == e {
				engineFilter = "двигатель: " + strings.ToLower(en.Name)
				filterParts = append(filterParts, engineFilter)
				break
			}
		}
	}
	if t := q.Get("transmission"); t != "" && !strings.Contains(t, ",") {
		for _, tr := range s.transmissions {
			if tr.Code == t {
				transFilter = "КПП: " + strings.ToLower(tr.Name)
				filterParts = append(filterParts, transFilter)
				break
			}
		}
	}
	if d := q.Get("drive"); d != "" && !strings.Contains(d, ",") {
		for _, dr := range s.drives {
			if dr.Code == d {
				driveFilter = "привод: " + strings.ToLower(dr.Name)
				filterParts = append(filterParts, driveFilter)
				break
			}
		}
	}
	if c := q.Get("color"); c != "" && !strings.Contains(c, ",") {
		name := s.getColorName(c)
		if name != "" {
			colorFilter = "цвет: " + strings.ToLower(name)
			filterParts = append(filterParts, colorFilter)
		}
	}
	filterStr := strings.Join(filterParts, ", ")

	inCity := "Краснодаре, Майкопе, Новороссийске и Яблоновском"

	now := time.Now()
	months := []string{"январь", "февраль", "март", "апрель", "май", "июнь",
		"июль", "август", "сентябрь", "октябрь", "ноябрь", "декабрь"}
	dateStr := months[now.Month()-1] + " " + fmt.Sprintf("%d", now.Year())

	city := ""
	if inCity != "" {
		city = "в " + inCity
	}

	replace := func(tmpl string) string {
		r := tmpl
		r = strings.ReplaceAll(r, "{%year%}", fmt.Sprintf("%d", now.Year()))
		r = strings.ReplaceAll(r, "{%count%}", formatNumber(float64(count)))
		r = strings.ReplaceAll(r, "{%cars%}", getWorld(count))
		r = strings.ReplaceAll(r, "{%tel%}", formatPhoneOut(phone))
		r = strings.ReplaceAll(r, "{%brand%}", brandName)
		r = strings.ReplaceAll(r, "{%brand_rus%}", brandRuName)
		r = strings.ReplaceAll(r, "{%model%}", modelName)
		r = strings.ReplaceAll(r, "{%model_rus%}", modelRuName)
		r = strings.ReplaceAll(r, "{%date%}", dateStr)
		r = strings.ReplaceAll(r, "{%city%}", city)
		r = strings.ReplaceAll(r, "{%price%}", formatNumber(minPrice))
		r = strings.ReplaceAll(r, "{%filter%}", filterStr)
		return strings.ReplaceAll(r, "\"\"", "\"")
	}

	h1 := replace(metaH1)
	title := replace(metaTitle)
	description := replace(metaDescription)

	meta := map[string]interface{}{
		"title":       title,
		"description": description,
		"h1":          h1,
	}

	res := map[string]interface{}{
		"meta":    meta,
		"status":  200,
		"level":   level,
		"in_city": inCity,
		"count":   count,
	}

	if level == "brand" {
		res["brand"] = map[string]string{"code": brandCode, "name": brandName}
	}
	if level == "model" {
		res["brand"] = map[string]string{"code": brandCode, "name": brandName}
		res["model"] = map[string]string{"code": modelCode, "name": modelName}
	}

	return res
}

func (s *Service) buildVehicleMeta(r *http.Request, typeID int, row *VehicleRow, rawJSON string, images []ImageResp) map[string]interface{} {
	site := r.URL.Query().Get("site")
	if site == "" {
		site = r.Host
	}
	if site == "" || site == "apps.avatr-yugavto.ru" || site == "apps.yug-avto.ru" || strings.HasSuffix(site, "localhost") || strings.Contains(site, ":808") {
		site = "yug-avto.ru"
	}

	entity := "new"
	if typeID == 2 {
		entity = "used"
	}

	seo := s.querySEOTemplate(site, entity, "vehicle", "")
	if seo == nil {
		seo = &SEORow{
			MetaH1:          "{%brand%} {%model%} {%year%} {%complectation%}",
			MetaTitle:       "Юг-Авто - купить новый {%brand%} {%model%} {%year%} года по цене {%price%} руб {%city%}",
			MetaDescription: "Объявление о продаже нового {%brand%} {%model%} {%complectation%} {%tth%} {%year%} года {%city%}. Купить по цене {%price%} рублей от официального дилера Юг-Авто. Телефон: {%tel%}",
			Phone:           "78612031755",
		}
	}

	phone := seo.Phone
	if phone == "" {
		phone = s.getPhone(site)
	}

	now := time.Now()
	months := []string{"январь", "февраль", "март", "апрель", "май", "июнь",
		"июль", "август", "сентябрь", "октябрь", "ноябрь", "декабрь"}
	dateStr := months[now.Month()-1] + " " + fmt.Sprintf("%d", now.Year())

	year := now.Year()
	mileage := float64(0)
	engineVal := ""
	colorVal := ""
	modification := ""
	equipment := ""

	var rawGeneral []struct {
		Name  string      `json:"name"`
		Value interface{} `json:"value"`
	}
	if rawJSON != "" {
		var raw struct {
			General          []struct {
				Name  string      `json:"name"`
				Value interface{} `json:"value"`
			} `json:"general"`
			ModificationName string `json:"modification_name"`
			EquipmentName    string `json:"equipment_name"`
		}
		if err := json.Unmarshal([]byte(rawJSON), &raw); err == nil {
			rawGeneral = raw.General
			modification = raw.ModificationName
			equipment = raw.EquipmentName
		}
	}

	if len(rawGeneral) > 4 {
		if v, ok := rawGeneral[4].Value.(float64); ok && v > 0 {
			year = int(v)
		}
	}
	if len(rawGeneral) > 5 {
		if v, ok := rawGeneral[5].Value.(float64); ok {
			mileage = v
		}
	}

	engineIdx := 5
	if entity == "used" {
		engineIdx = 8
	}
	if len(rawGeneral) > engineIdx {
		engineVal = fmt.Sprintf("%v", rawGeneral[engineIdx].Value)
	}
	if len(rawGeneral) > 2 {
		colorVal = fmt.Sprintf("%v", rawGeneral[2].Value)
	}

	if row.Year > 0 {
		year = row.Year
	}
	if row.Mileage > 0 {
		mileage = float64(row.Mileage)
	}

	volume := float64(row.Volume) / 1000.0
	power := float64(row.Power)

	transMeta := ""
	transWithPrep := ""
	for _, t := range s.transmissions {
		if t.Code == row.Transmission {
			transMeta = t.Meta
			if transMeta != "" {
				transWithPrep = " c " + transMeta + " коробкой"
			}
			break
		}
	}

	driveName := ""
	for _, d := range s.drives {
		if d.Code == row.Drive {
			driveName = d.Name
			break
		}
	}

	city := ""
	inCity := ""
	if row.DealershipID > 0 {
		dealership := s.getDealership(row.DealershipID, row.BrandID)
		inCity = dealership.InCity
		if inCity != "" {
			city = "в " + inCity
		}
	}

	replace := func(tmpl string) string {
		r := tmpl
		r = strings.ReplaceAll(r, "{%year%}", fmt.Sprintf("%d", year))
		r = strings.ReplaceAll(r, "{%count%}", "1")
		r = strings.ReplaceAll(r, "{%cars%}", "автомобиль")
		r = strings.ReplaceAll(r, "{%tel%}", formatPhoneOut(phone))
		r = strings.ReplaceAll(r, "{%brand%}", row.BrandName)
		r = strings.ReplaceAll(r, "{%brand_rus%}", row.BrandRuName)
		r = strings.ReplaceAll(r, "{%model%}", row.ModelName)
		r = strings.ReplaceAll(r, "{%model_rus%}", row.ModelRuName)
		r = strings.ReplaceAll(r, "{%date%}", dateStr)
		r = strings.ReplaceAll(r, "{%city%}", city)
		r = strings.ReplaceAll(r, "{%price%}", formatNumber(row.MinPrice))
		r = strings.ReplaceAll(r, "{%mileage%}", formatNumber(mileage))
		r = strings.ReplaceAll(r, "{%ext_id%}", fmt.Sprintf("%d", row.ExtID))
		r = strings.ReplaceAll(r, "{%tth%}", modification)
		r = strings.ReplaceAll(r, "{%complectation%}", equipment)
		r = strings.ReplaceAll(r, "{%engine%}", engineVal)
		r = strings.ReplaceAll(r, "{%transmission%}", transWithPrep)
		r = strings.ReplaceAll(r, "{%transmission_meta%}", transMeta)
		r = strings.ReplaceAll(r, "{%drive%}", driveName)
		r = strings.ReplaceAll(r, "{%power%}", formatNumber(power))
		r = strings.ReplaceAll(r, "{%volume%}", fmt.Sprintf("%.1f", volume))
		r = strings.ReplaceAll(r, "{%color%}", colorVal)
		r = strings.ReplaceAll(r, "{%color_processed%}", s.getColorName(row.Color))
		r = strings.ReplaceAll(r, "{%filter%}", "")
		return strings.ReplaceAll(r, "\"\"", "\"")
	}

	image := ""
	if len(images) > 0 {
		image = images[0].Thumb
	}

	meta := map[string]interface{}{
		"title":       replace(seo.MetaTitle),
		"description": replace(seo.MetaDescription),
		"h1":          replace(seo.MetaH1),
		"image":       image,
		"brand":       row.BrandName,
		"price":       row.MinPrice,
		"level":       "vehicle",
	}

	return map[string]interface{}{
		"meta":    meta,
		"status":  200,
		"level":   "vehicle",
		"in_city": inCity,
	}
}
