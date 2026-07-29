package cis

import (
	"encoding/json"
	"fmt"
	"log"
	"math"
	"regexp"
	"strconv"
	"strings"
	"sync"
	"time"
	"unicode"

	"github.com/jmoiron/sqlx"

	"github.com/yugavto/apps/internal/orientation"
	"github.com/yugavto/apps/pkg/autocrm"
)

const (
	statusInStock = 1
	statusOnWay   = 2
)

var UsedDealershipIDs = []int{1364, 1367, 1370, 1373, 1489, 1492, 1499, 1502, 1533, 1328}

type BrandAlias struct {
	Codes       []string // expanded brand codes, e.g. ["chery", "tenet"]
	DisplayName string   // meta display, e.g. "Chery и Tenet"
}

// brandAlias returns alias brands for a given brand code.
// When a brand has aliases, filtering by it (without model) should also include the aliases.


// expandFilterBrands expands f.Brand to include alias brands when applicable.
// Only applies to new cars (typeID == 1). Returns the alias info for meta overrides, or nil if no expansion.
func expandFilterBrands(f *VehicleFilter, typeID int) *BrandAlias {
	if typeID == 1 && len(f.Brand) == 1 && len(f.Model) == 0 {
		// No specific model — check brand aliases
		alias := defaultBrandAlias(f.Brand[0])
		if len(alias.Codes) > 1 {
			f.Brand = alias.Codes
			return &alias
		}
	}
	return nil
}

// defaultBrandAlias is a package-level version for use outside Service methods.
func defaultBrandAlias(brandCode string) BrandAlias {
	switch brandCode {
	case "chery":
		return BrandAlias{Codes: []string{"chery", "tenet"}, DisplayName: "Chery и Tenet"}
	case "tank":
		return BrandAlias{Codes: []string{"tank", "wey"}, DisplayName: "Tank и Wey"}
	case "wey":
		return BrandAlias{Codes: []string{"wey", "tank"}, DisplayName: "Tank и Wey"}
	}
	return BrandAlias{Codes: []string{brandCode}}
}

type Service struct {
	db             *sqlx.DB
	crm            *autocrm.Client
	bodies         []Body
	colors         []Color
	transmissions  []Transmission
	engines        []Engine
	drives         []Drive
	comparisons    []Comparison
	uploadDir      string
	imageBaseURL   string
	failedBrandsMu sync.RWMutex
	failedBrands   map[int]bool
	tableNamesMu   sync.RWMutex
	prodTable      string
	cronTable      string
	orient         *orientation.Detector

	blockedUntilMu sync.RWMutex
	blockedUntil   time.Time
}

func NewService(db *sqlx.DB, crm *autocrm.Client, uploadDir, imageBaseURL, onnxModelPath string) *Service {
	var det *orientation.Detector
	if onnxModelPath != "" {
		var err error
		det, err = orientation.NewDetector(onnxModelPath)
		if err != nil {
			log.Printf("orientation detector disabled: %v", err)
		}
	}
	return &Service{db: db, crm: crm, uploadDir: uploadDir, imageBaseURL: imageBaseURL, failedBrands: make(map[int]bool), orient: det}
}

func (s *Service) Init() error {
	if err := s.loadReferenceData(); err != nil {
		return fmt.Errorf("load reference: %w", err)
	}
	if err := s.LoadTableNames(); err != nil {
		return fmt.Errorf("load table names: %w", err)
	}

	// Запускаем фоновое периодическое обновление имен таблиц каждые 10 секунд
	go func() {
		ticker := time.NewTicker(10 * time.Second)
		for range ticker.C {
			if err := s.LoadTableNames(); err != nil {
				log.Printf("LoadTableNames background error: %v", err)
			}
		}
	}()

	return nil
}

func (s *Service) loadReferenceData() error {
	s.bodies = nil
	if err := s.db.Select(&s.bodies, "SELECT id, code, name FROM yapps_app_cis_bodies"); err != nil {
		return err
	}
	s.colors = nil
	if err := s.db.Select(&s.colors, "SELECT id, code, name, param FROM yapps_app_cis_colors"); err != nil {
		return err
	}
	s.transmissions = nil
	if err := s.db.Select(&s.transmissions, "SELECT id, code, name, meta FROM yapps_app_cis_transmissions"); err != nil {
		return err
	}
	s.engines = nil
	if err := s.db.Select(&s.engines, "SELECT id, code, name FROM yapps_app_cis_engines"); err != nil {
		return err
	}
	s.drives = nil
	if err := s.db.Select(&s.drives, "SELECT id, code, name, meta FROM yapps_app_cis_drives"); err != nil {
		return err
	}
	s.comparisons = nil
	if err := s.db.Select(&s.comparisons, "SELECT id, entity, desired, value FROM yapps_app_cis_comparisons"); err != nil {
		return err
	}
	return nil
}

func (s *Service) LoadTableNames() error {
	prod, cron, err := s.queryTableNames()
	if err != nil {
		return err
	}
	s.tableNamesMu.Lock()
	s.prodTable = prod
	s.cronTable = cron
	s.tableNamesMu.Unlock()
	return nil
}

func (s *Service) queryTableNames() (prod, cron string, err error) {
	var tbls []struct {
		Name  string `db:"name"`
		Value string `db:"value"`
	}
	err = s.db.Select(&tbls, "SELECT name, value FROM yapps_app_cis_tables WHERE id = 1")
	if err != nil {
		return "", "", err
	}
	for _, t := range tbls {
		if t.Name == "prod" {
			prod = t.Value
		} else if t.Name == "cron" {
			cron = t.Value
		}
	}
	if prod == "" && cron == "" {
		prod = "yapps_app_cis_vehicles_one"
		cron = "yapps_app_cis_vehicles_two"
	} else if prod == "" {
		if cron == "yapps_app_cis_vehicles_one" {
			prod = "yapps_app_cis_vehicles_two"
		} else {
			prod = "yapps_app_cis_vehicles_one"
		}
	} else if cron == "" {
		if prod == "yapps_app_cis_vehicles_one" {
			cron = "yapps_app_cis_vehicles_two"
		} else {
			cron = "yapps_app_cis_vehicles_one"
		}
	}
	return prod, cron, nil
}

func (s *Service) TableNames() (prod, cron string) {
	s.tableNamesMu.RLock()
	defer s.tableNamesMu.RUnlock()
	return s.prodTable, s.cronTable
}

func (s *Service) ToggleTables() error {
	tx, err := s.db.Beginx()
	if err != nil {
		return fmt.Errorf("begin tx: %w", err)
	}
	defer tx.Rollback()

	var prod string
	_ = tx.QueryRow("SELECT value FROM yapps_app_cis_tables WHERE id = 1 AND name = 'prod'").Scan(&prod)

	if prod == "" {
		prod = "yapps_app_cis_vehicles_two"
	}

	var cron string
	if prod == "yapps_app_cis_vehicles_one" {
		cron = "yapps_app_cis_vehicles_two"
	} else {
		cron = "yapps_app_cis_vehicles_one"
	}

	// Обновляем только имя prod-таблицы (cron вычисляется динамически)
	if _, err := tx.Exec("UPDATE yapps_app_cis_tables SET value = ? WHERE id = 1 AND name = 'prod'", cron); err != nil {
		return fmt.Errorf("update prod table: %w", err)
	}

	if err := tx.Commit(); err != nil {
		return fmt.Errorf("commit tx: %w", err)
	}

	s.tableNamesMu.Lock()
	s.prodTable = cron
	s.cronTable = prod
	s.tableNamesMu.Unlock()

	return nil
}

func (s *Service) IsOK() (bool, error) {
	prod, cron := s.TableNames()
	var newCount int
	if err := s.db.Get(&newCount, fmt.Sprintf("SELECT COUNT(*) FROM %s WHERE type_id = 1", cron)); err != nil {
		return false, fmt.Errorf("count new vehicles: %w", err)
	}
	if newCount == 0 {
		return false, nil
	}
	var prodCount int
	if err := s.db.Get(&prodCount, fmt.Sprintf("SELECT COUNT(*) FROM %s WHERE type_id = 1", prod)); err != nil {
		return false, fmt.Errorf("count prod vehicles: %w", err)
	}
	if newCount < prodCount {
		deficit := prodCount - newCount
		if deficit > 100 && float64(newCount) < float64(prodCount)*0.95 {
			log.Printf("cron %d < prod %d (deficit %d > 100 and > 5%%), waiting", newCount, prodCount, deficit)
			return false, nil
		}
		log.Printf("cron %d < prod %d (deficit %d, within tolerance), toggling anyway", newCount, prodCount, deficit)
	}
	var usedCount int
	if err := s.db.Get(&usedCount, fmt.Sprintf("SELECT COUNT(*) FROM %s WHERE type_id = 2", cron)); err != nil {
		return false, fmt.Errorf("count new used vehicles: %w", err)
	}
	var prodUsedCount int
	if err := s.db.Get(&prodUsedCount, fmt.Sprintf("SELECT COUNT(*) FROM %s WHERE type_id = 2", prod)); err != nil {
		return false, fmt.Errorf("count prod used vehicles: %w", err)
	}
	if usedCount == 0 && prodUsedCount > 0 {
		log.Printf("cron usedCount is 0 while prodUsedCount is %d, waiting for used sync to finish", prodUsedCount)
		return false, nil
	}
	if usedCount < prodUsedCount {
		deficit := prodUsedCount - usedCount
		if deficit > 50 && float64(usedCount) < float64(prodUsedCount)*0.9 {
			log.Printf("cron used %d < prod used %d (deficit %d), waiting for used sync", usedCount, prodUsedCount, deficit)
			return false, nil
		}
	}
	return true, nil
}

func (s *Service) ClearCronTable() error {
	cron := s.vehicleTable()
	_, err := s.db.Exec(fmt.Sprintf("TRUNCATE %s", cron))
	return err
}

func (s *Service) vehicleTable() string {
	s.tableNamesMu.RLock()
	defer s.tableNamesMu.RUnlock()
	return s.cronTable
}

func (s *Service) apiTable() string {
	s.tableNamesMu.RLock()
	defer s.tableNamesMu.RUnlock()
	return s.prodTable
}

func (s *Service) SyncBrands(section string) error {
	brands, err := s.crm.GetBrands()
	if err != nil {
		return fmt.Errorf("get brands: %w", err)
	}

	for _, b := range brands.Items {
		code := generateBrandAlias(b.Name)

		code = strings.ToLower(code)
		code = strings.NewReplacer(" ", "-", "_", "-", "--", "-").Replace(code)

		var ruName string
		if section == "new" {
			ruName = transliterateBrandToRu(b.Name)
		}

		_, err := s.db.Exec(`
			INSERT INTO yapps_app_cis_brands (ext_id, code, name, ru_name)
			VALUES (?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE name = VALUES(name), ru_name = VALUES(ru_name)
		`, b.ID, code, b.Name, ruName)
		if err != nil {
			return fmt.Errorf("save brand %s: %w", b.Name, err)
		}
	}

	log.Printf("synced %d brands for %s", len(brands.Items), section)
	return nil
}

func (s *Service) SyncModels(brandExtID int, section string) error {
	models, err := s.crm.GetModels(brandExtID)
	if err != nil {
		return err
	}

	var brand Brand
	err = s.db.Get(&brand, "SELECT id, ext_id FROM yapps_app_cis_brands WHERE ext_id = ?", brandExtID)
	if err != nil {
		return fmt.Errorf("brand %d: %w", brandExtID, err)
	}

	table := "yapps_app_cis_models_new"
	if section == "used" {
		table = "yapps_app_cis_models_used"
	}

	bodyIDs := make(map[string]int)
	for _, b := range s.bodies {
		bodyIDs[b.Code] = b.ID
	}

	for _, m := range models.Items {
		code := generateModelAlias(m.Name, section)
		code = strings.ToLower(code)
		code = strings.NewReplacer(" ", "-", "_", "-", "--", "-").Replace(code)

		bodyID := bodyIDs[m.BodyType]

		_, err := s.db.Exec(fmt.Sprintf(`
			INSERT INTO %s (ext_id, brand_id, code, name, ru_name, image, body_id)
			VALUES (?, ?, ?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE name = VALUES(name), image = VALUES(image), body_id = VALUES(body_id)
		`, table), m.ID, brand.ID, code, m.Name, "", m.Image, bodyID)
		if err != nil {
			return fmt.Errorf("save model %s: %w", m.Name, err)
		}
	}
	return nil
}

func (s *Service) SyncAllBrands() error {
	if err := s.SyncBrands("new"); err != nil {
		return err
	}
	brands, err := s.crm.GetBrands()
	if err != nil {
		return err
	}

	for _, b := range brands.Items {
		if err := s.SyncModels(b.ID, "new"); err != nil {
			return err
		}
	}
	return nil
}

func (s *Service) SyncVehicles(section string) error {
	page := 1
	var total int

	for {
		resp, err := s.crm.GetVehiclesList(section, page)
		if err != nil {
			return fmt.Errorf("page %d: %w", page, err)
		}

		for _, v := range resp.Items {
			if v.Dealership != nil && v.Dealership.ID == 1514 {
				continue
			}
			if section == "used" && !contains(UsedDealershipIDs, v.Dealership.ID) {
				continue
			}

			if err := s.processVehicle(&v, section); err != nil {
				log.Printf("process vehicle %d: %v", v.ID, err)
			}
		}

		total += len(resp.Items)
		if resp.Meta == nil || page >= resp.Meta.PageCount {
			break
		}
		page++
	}

	log.Printf("synced %d %s vehicles", total, section)
	return nil
}

var allowedUsedDealerships = map[int]bool{
	1364: true, 1367: true, 1370: true, 1373: true,
	1489: true, 1492: true, 1499: true, 1502: true,
	1533: true, 1328: true,
}
func (s *Service) SyncVehicleDetail(extID int, typeID int, tableName string) (vin string, updateImages bool, alert *EquipmentAlert, err error) {
	raw, err := s.crm.GetVehicleDetail(extID)
	if err != nil {
		return "", false, nil, err
	}
	vin = raw.Vin

	// Filter used vehicles by allowed dealerships
	if typeID == 2 {
		if extID == 1314264 {
			return vin, false, nil, nil
		}
		if raw.Dealership == nil || !allowedUsedDealerships[raw.Dealership.ID] {
			return vin, false, nil, nil
		}
	}

	updImg, alert, err := s.saveVehicle(raw, typeID, tableName)
	return vin, updImg, alert, err
}

func (s *Service) processVehicle(raw *autocrm.VehicleRaw, section string) error {
	typeID := 1
	if section == "used" {
		typeID = 2
	}

	prodTable := s.apiTable()
	cronTable := s.vehicleTable()

	// 1. Проверяем совпадение цен в prodTable
	var dbRow struct {
		Price    float64 `db:"price"`
		MinPrice float64 `db:"min_price"`
	}
	err := s.db.Get(&dbRow, fmt.Sprintf("SELECT price, min_price FROM %s WHERE ext_id = ?", prodTable), raw.ID)
	if err == nil {
		price := raw.Price
		minPrice := raw.MinPrice

		// Учитываем доп. оборудование в цене, если применимо
		var model Model
		modelTable := "yapps_app_cis_models_new"
		if typeID == 2 && raw.RefModelID > 0 {
			modelTable = "yapps_app_cis_models_used"
		}
		modelExtID := raw.ModelID
		if typeID == 2 && raw.RefModelID > 0 {
			modelExtID = raw.RefModelID
		}
		if s.db.Get(&model, fmt.Sprintf("SELECT use_additional_equipment_in_price FROM %s WHERE ext_id = ?", modelTable), modelExtID) == nil {
			if model.UseUAEP {
				price -= raw.AdditionalEquipmentPrice
				minPrice -= raw.AdditionalEquipmentPrice
			}
		}

		// Если цена не изменилась, переносим запись по SQL напрямую
		if dbRow.Price == price && dbRow.MinPrice == minPrice {
			_, copyErr := s.db.Exec(fmt.Sprintf(`
				INSERT INTO %s
					(ext_id, type_id, brand_id, model_id, vin, name, price, min_price,
					 transmission, engine, drive, body, color, dealership_id,
					 volume, power, year, mileage, instock, onway, discount,
					 update_images, use_internal_images, raw, created, synced_at)
				SELECT 
					ext_id, type_id, brand_id, model_id, vin, name, price, min_price,
					transmission, engine, drive, body, color, dealership_id,
					volume, power, year, mileage, instock, onway, discount,
					update_images, use_internal_images, raw, created, synced_at
				FROM %s WHERE ext_id = ?
				ON DUPLICATE KEY UPDATE
					price = VALUES(price),
					min_price = VALUES(min_price)
			`, cronTable, prodTable), raw.ID)
			if copyErr == nil {
				return nil
			}
		}
	}

	// 2. В противном случае делаем детальный запрос к AutoCRM
	_, _, _, err = s.SyncVehicleDetail(raw.ID, typeID, cronTable)
	return err
}

func (s *Service) saveVehicle(raw *autocrm.VehicleRaw, typeID int, tableName string) (bool, *EquipmentAlert, error) {
	if raw.Status == nil || (raw.Status.ID != statusInStock && raw.Status.ID != statusOnWay) {
		return false, nil, nil
	}

	brandExtID := raw.BrandID
	if typeID == 2 && raw.RefModelID > 0 {
		brandExtID = raw.BrandID
	}

	var brand Brand
	err := s.db.Get(&brand, "SELECT id, ext_id, code, name, ru_name FROM yapps_app_cis_brands WHERE ext_id = ?", brandExtID)
	if err != nil {
		return false, nil, fmt.Errorf("brand %d: %w", brandExtID, err)
	}

	modelExtID := raw.ModelID
	modelTable := "yapps_app_cis_models_new"
	if typeID == 2 && raw.RefModelID > 0 {
		modelExtID = raw.RefModelID
		modelTable = "yapps_app_cis_models_used"
	}

	var model Model
	if typeID == 2 && raw.RefModelID > 0 {
		err = s.db.Get(&model, fmt.Sprintf("SELECT id, ext_id, brand_id, code, name, ru_name, image, body_id FROM %s WHERE ext_id = ? AND brand_id = ?", modelTable), modelExtID, brand.ID)
	} else {
		err = s.db.Get(&model, fmt.Sprintf("SELECT id, ext_id, brand_id, code, name, ru_name, image, body_id, use_additional_equipment_in_price FROM %s WHERE ext_id = ? AND brand_id = ?", modelTable), modelExtID, brand.ID)
	}
	if err != nil {
		section := "new"
		if typeID == 2 {
			section = "used"
		}
		s.failedBrandsMu.RLock()
		brandFailed := s.failedBrands[brandExtID]
		s.failedBrandsMu.RUnlock()
		if brandFailed {
			return false, nil, fmt.Errorf("model %d brand %d: %w (brand previously failed)", modelExtID, brand.ID, err)
		}
		log.Printf("model %d brand %d not found, syncing %s models for brand %d", modelExtID, brand.ID, section, brandExtID)
		if syncErr := s.SyncModels(brandExtID, section); syncErr != nil {
			s.failedBrandsMu.Lock()
			s.failedBrands[brandExtID] = true
			s.failedBrandsMu.Unlock()
			return false, nil, fmt.Errorf("model %d brand %d: %w (sync models failed: %v)", modelExtID, brand.ID, err, syncErr)
		}
		if typeID == 2 && raw.RefModelID > 0 {
			err = s.db.Get(&model, fmt.Sprintf("SELECT id, ext_id, brand_id, code, name, ru_name, image, body_id FROM %s WHERE ext_id = ? AND brand_id = ?", modelTable), modelExtID, brand.ID)
		} else {
			err = s.db.Get(&model, fmt.Sprintf("SELECT id, ext_id, brand_id, code, name, ru_name, image, body_id, use_additional_equipment_in_price FROM %s WHERE ext_id = ? AND brand_id = ?", modelTable), modelExtID, brand.ID)
		}
		if err != nil {
			// Fallback: model might exist under a different brand (autocrm data inconsistency)
			var fallbackErr error
			if typeID == 2 && raw.RefModelID > 0 {
				fallbackErr = s.db.Get(&model, fmt.Sprintf("SELECT id, ext_id, brand_id, code, name, ru_name, image, body_id FROM %s WHERE ext_id = ?", modelTable), modelExtID)
			} else {
				fallbackErr = s.db.Get(&model, fmt.Sprintf("SELECT id, ext_id, brand_id, code, name, ru_name, image, body_id, use_additional_equipment_in_price FROM %s WHERE ext_id = ?", modelTable), modelExtID)
			}
			if fallbackErr == nil {
				log.Printf("model %d found under brand %d (vehicle brand %d), using model's brand", modelExtID, model.BrandID, brand.ID)
				var modelBrand Brand
				if fbErr := s.db.Get(&modelBrand, "SELECT id, ext_id, code, name, ru_name FROM yapps_app_cis_brands WHERE id = ?", model.BrandID); fbErr == nil {
					brand = modelBrand
				} else {
					s.failedBrandsMu.Lock()
					s.failedBrands[brandExtID] = true
					s.failedBrandsMu.Unlock()
					return false, nil, fmt.Errorf("model %d brand %d not found in brands: %w", model.ID, model.BrandID, fbErr)
				}
			} else {
				s.failedBrandsMu.Lock()
				s.failedBrands[brandExtID] = true
				s.failedBrandsMu.Unlock()
				return false, nil, fmt.Errorf("model %d brand %d: %w (after sync, fallback: %v)", modelExtID, brand.ID, err, fallbackErr)
			}
		}
	}

	price := raw.Price
	minPrice := raw.MinPrice
	if model.UseUAEP {
		price -= raw.AdditionalEquipmentPrice
		minPrice -= raw.AdditionalEquipmentPrice
	}

	engineCode := resolveComparison(s.comparisons, "engines", extractGeneral(raw.General, "Двигатель"))
	transmissionCode := resolveComparison(s.comparisons, "transmissions", extractGeneral(raw.General, "Трансмиссия"))
	driveCode := resolveComparison(s.comparisons, "drives", extractSpec(raw.Specifications, 11))
	bodyCode := resolveBody(s.comparisons, raw.BodyType, model.Name)
	colorCode := resolveComparison(s.comparisons, "colors", extractGeneral(raw.General, "Цвет"))

	// Resolve comparison ValueID → actual code from reference table
	if bodyCode != "" {
		if id, err := strconv.Atoi(bodyCode); err == nil {
			for _, b := range s.bodies {
				if b.ID == id {
					bodyCode = b.Code
					break
				}
			}
		}
	}
	if engineCode != "" {
		if id, err := strconv.Atoi(engineCode); err == nil {
			for _, e := range s.engines {
				if e.ID == id {
					engineCode = e.Code
					break
				}
			}
		}
	}
	if transmissionCode != "" {
		if id, err := strconv.Atoi(transmissionCode); err == nil {
			for _, t := range s.transmissions {
				if t.ID == id {
					transmissionCode = t.Code
					break
				}
			}
		}
	}
	if driveCode != "" {
		if id, err := strconv.Atoi(driveCode); err == nil {
			for _, d := range s.drives {
				if d.ID == id {
					driveCode = d.Code
					break
				}
			}
		}
	}
	if colorCode != "" {
		if id, err := strconv.Atoi(colorCode); err == nil {
			for _, c := range s.colors {
				if c.ID == id {
					colorCode = c.Code
					break
				}
			}
		}
	}

	volume, power := parseEngine(extractGeneral(raw.General, "Двигатель"))
	year := parseIntField(raw.General, 4)
	mileage := 0
	if typeID == 2 {
		mileage = parseIntField(raw.General, 5)
	}

	instock := raw.Status.ID == statusInStock
	onway := raw.Status.ID == statusOnWay
	discount := raw.Price > raw.MinPrice

	created := parseDate(raw.VehicleEntryDate)
	if created == 0 {
		created = parseDate(raw.VehicleReceiptDate)
	}

	cronTable := tableName
	var oldRawJSON string
	s.db.Get(&oldRawJSON, "SELECT raw FROM "+s.apiTable()+" WHERE ext_id = ?", raw.ID)
	updateImages := true
	if oldRawJSON != "" {
		var oldRaw autocrm.VehicleRaw
		if json.Unmarshal([]byte(oldRawJSON), &oldRaw) == nil {
			oldJSON, _ := json.Marshal(oldRaw.Images)
			newJSON, _ := json.Marshal(raw.Images)
			updateImages = string(oldJSON) != string(newJSON)
		}
	}

	// Preserve use_internal_images from prod table (carry over if already processed)
	useInternalImages := 0
	prodTable := s.apiTable()
	var prodUseInternal int
	if err := s.db.Get(&prodUseInternal, fmt.Sprintf("SELECT use_internal_images FROM %s WHERE ext_id = ?", prodTable), raw.ID); err == nil && prodUseInternal == 1 {
		useInternalImages = 1
	}
	// Also check images table directly (in case prod table was cleared)
	if useInternalImages == 0 {
		var imgCount int
		s.db.Get(&imgCount, "SELECT COUNT(*) FROM yapps_app_cis_images WHERE ext_id = ?", raw.ID)
		if imgCount > 0 {
			useInternalImages = 1
		}
	}

	name := brand.Name + " " + model.Name
	if raw.EquipmentName != "" {
		name += " " + raw.EquipmentName
	} else if raw.Equipment != "" {
		name += " " + raw.Equipment
	}

	rawJSON, _ := json.Marshal(raw)

	_, err = s.db.Exec(fmt.Sprintf(`
		INSERT INTO %s
			(ext_id, type_id, brand_id, model_id, vin, name, price, min_price,
			 transmission, engine, drive, body, color, dealership_id,
			 volume, power, year, mileage, instock, onway, discount,
			 update_images, use_internal_images, raw, created)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
		ON DUPLICATE KEY UPDATE
			price = VALUES(price),
			min_price = VALUES(min_price),
			transmission = VALUES(transmission),
			engine = VALUES(engine),
			drive = VALUES(drive),
			body = VALUES(body),
			color = VALUES(color),
			volume = VALUES(volume),
			power = VALUES(power),
			year = VALUES(year),
			mileage = VALUES(mileage),
			instock = VALUES(instock),
			onway = VALUES(onway),
			discount = VALUES(discount),
			update_images = VALUES(update_images),
			use_internal_images = VALUES(use_internal_images),
			raw = VALUES(raw),
			name = VALUES(name),
			vin = VALUES(vin),
			dealership_id = VALUES(dealership_id),
			brand_id = VALUES(brand_id),
			model_id = VALUES(model_id),
			created = VALUES(created)
	`, cronTable),
		raw.ID, typeID, brand.ID, model.ID, raw.Vin, name, price, minPrice,
		transmissionCode, engineCode, driveCode, bodyCode, colorCode,
		raw.Dealership.ID, volume, power, year, mileage, instock, onway, discount,
		updateImages, useInternalImages, rawJSON, created,
	)

	var alert *EquipmentAlert
	if typeID == 1 {
		origEquipment := ""
		if raw.EquipmentName != "" {
			origEquipment = raw.EquipmentName
		} else if raw.Equipment != "" {
			origEquipment = raw.Equipment
		}

		if origEquipment != "" {
			rxCyrillic := regexp.MustCompile(`[а-яА-ЯёЁ]`)
			if !rxCyrillic.MatchString(origEquipment) {
				// Проверить наличие перевода в таблице yapps_app_cis_equipments
				var count int
				checkErr := s.db.Get(&count, `
					SELECT COUNT(*) FROM yapps_app_cis_equipments 
					WHERE brand_id = ? AND model_id = ? AND name = ?
				`, brand.ID, model.ID, origEquipment)
				if checkErr == nil && count == 0 {
					alert = &EquipmentAlert{
						Brand:     brand.Name,
						Model:     model.Name,
						Equipment: origEquipment,
					}
				}
			}
		}
	}

	return updateImages, alert, err
}

func resolveComparison(comparisons []Comparison, entity, value string) string {
	if value == "" {
		return ""
	}
	for _, c := range comparisons {
		if c.Entity == entity && strings.Contains(strings.ToLower(value), strings.ToLower(c.Desired)) {
			return strconv.Itoa(c.Value)
		}
	}
	return ""
}

func resolveBody(comparisons []Comparison, bodyType, modelName string) string {
	if bodyType != "" {
		r := resolveComparison(comparisons, "bodies", bodyType)
		if r != "" {
			return r
		}
	}
	return resolveComparison(comparisons, "bodies", modelName)
}

func extractGeneral(general []autocrm.GeneralField, name string) string {
	for _, g := range general {
		if g.Name == name {
			return fmt.Sprintf("%v", g.Value)
		}
	}
	return ""
}

func extractSpec(specs []autocrm.SpecField, index int) string {
	if index < len(specs) {
		return fmt.Sprintf("%v", specs[index].Value)
	}
	return ""
}

func parseIntField(general []autocrm.GeneralField, index int) int {
	if index >= len(general) {
		return 0
	}
	v, _ := strconv.Atoi(fmt.Sprintf("%v", general[index].Value))
	return v
}

var engineRegex = regexp.MustCompile(`([\d,]+)\s*\((\d+)\s*л\.с\.\)`)

func parseEngine(val string) (volume int, power int) {
	matches := engineRegex.FindStringSubmatch(val)
	if len(matches) < 3 {
		return 0, 0
	}
	v, _ := strconv.ParseFloat(strings.Replace(matches[1], ",", ".", 1), 64)
	volume = int(math.Round(v * 1000))
	p, _ := strconv.Atoi(matches[2])
	power = p
	return
}

func parseDate(date string) int64 {
	if date == "" {
		return 0
	}
	t, err := time.Parse("2006-01-02 15:04:05", date)
	if err != nil {
		t, err = time.Parse("2006-01-02", date)
		if err != nil {
			return 0
		}
	}
	return t.Unix()
}

func generateBrandAlias(name string) string {
	name = strings.TrimSpace(name)
	name = strings.NewReplacer(" ", "-", "_", "-", "/", "-", "\\", "-").Replace(name)

	var result strings.Builder
	for _, r := range name {
		if unicode.IsLetter(r) || unicode.IsDigit(r) || r == '-' {
			result.WriteRune(r)
		}
	}
	return result.String()
}

func generateModelAlias(name, section string) string {
	alias := transliterate(name)
	alias = strings.ToLower(alias)
	alias = strings.NewReplacer(" ", "-", "_", "-", "--", "-").Replace(alias)
	return alias
}

func transliterate(text string) string {
	mapping := map[rune]string{
		'А': "A", 'Б': "B", 'В': "V", 'Г': "G", 'Д': "D",
		'Е': "E", 'Ё': "Yo", 'Ж': "Zh", 'З': "Z", 'И': "I",
		'Й': "Y", 'К': "K", 'Л': "L", 'М': "M", 'Н': "N",
		'О': "O", 'П': "P", 'Р': "R", 'С': "S", 'Т': "T",
		'У': "U", 'Ф': "F", 'Х': "Kh", 'Ц': "Ts", 'Ч': "Ch",
		'Ш': "Sh", 'Щ': "Shch", 'Ъ': "", 'Ы': "Y", 'Ь': "",
		'Э': "E", 'Ю': "Yu", 'Я': "Ya",
		'а': "a", 'б': "b", 'в': "v", 'г': "g", 'д': "d",
		'е': "e", 'ё': "yo", 'ж': "zh", 'з': "z", 'и': "i",
		'й': "y", 'к': "k", 'л': "l", 'м': "m", 'н': "n",
		'о': "o", 'п': "p", 'р': "r", 'с': "s", 'т': "t",
		'у': "u", 'ф': "f", 'х': "kh", 'ц': "ts", 'ч': "ch",
		'ш': "sh", 'щ': "shch", 'ъ': "", 'ы': "y", 'ь': "",
		'э': "e", 'ю': "yu", 'я': "ya",
	}

	var result strings.Builder
	for _, r := range text {
		if s, ok := mapping[r]; ok {
			result.WriteString(s)
		} else if unicode.IsLetter(r) || unicode.IsDigit(r) || r == '-' || r == ' ' {
			result.WriteRune(r)
		}
	}
	return result.String()
}

func transliterateBrandToRu(text string) string {
	mapping := map[string]string{
		"Kia": "Киа", "Hyundai": "Хендэ", "Lada": "Лада",
		"Renault": "Рено", "Nissan": "Ниссан", "Toyota": "Тойота",
		"Volkswagen": "Фольксваген", "Skoda": "Шкода", "Ford": "Форд",
		"Chevrolet": "Шевроле", "BMW": "БМВ", "Mercedes-Benz": "Мерседес-Бенц",
		"Audi": "Ауди", "Mitsubishi": "Митсубиси", "Mazda": "Мазда",
		"Lexus": "Лексус", "Honda": "Хонда", "Suzuki": "Сузуки",
		"Subaru": "Субару", "Peugeot": "Пежо", "Citroen": "Ситроен",
		"Opel": "Опель", "Volvo": "Вольво", "Land Rover": "Ленд Ровер",
		"Jaguar": "Ягуар", "Mini": "Мини", "Porsche": "Порше",
		"Chery": "Чери", "Geely": "Джили", "Haval": "Хавал",
		"Changan": "Чанган", "Jetour": "Джетур", "Omoda": "Омода",
		"Exeed": "Эксид", "Lixiang": "Лисян", "Zeekr": "Зикр",
		"BYD": "БИД", "Voyah": "Воях", "Xcite": "Иксит",
	}
	if v, ok := mapping[text]; ok {
		return v
	}
	return text
}

func contains(slice []int, val int) bool {
	for _, s := range slice {
		if s == val {
			return true
		}
	}
	return false
}

func (s *Service) StartBackgroundRefresher() {
	log.Println("CIS background detail refresher started")
	ticker := time.NewTicker(15 * time.Second)
	for range ticker.C {
		if s.isBlocked() {
			continue
		}
		prodTable := s.apiTable()

		var oldest struct {
			ExtID  int `db:"ext_id"`
			TypeID int `db:"type_id"`
		}
		// Выбираем самый давно обновленный по synced_at автомобиль из prodTable
		err := s.db.Get(&oldest, fmt.Sprintf("SELECT ext_id, type_id FROM %s ORDER BY synced_at ASC LIMIT 1", prodTable))
		if err != nil {
			continue
		}

		log.Printf("Background sync vehicle detail: ext_id=%d type_id=%d", oldest.ExtID, oldest.TypeID)
		_, _, _, err = s.SyncVehicleDetail(oldest.ExtID, oldest.TypeID, prodTable)
		if err != nil {
			log.Printf("Background sync vehicle detail error for %d: %v", oldest.ExtID, err)
			if strings.Contains(err.Error(), "status 403") || strings.Contains(err.Error(), "forbidden") {
				log.Println("DDoS-Guard block detected on refresher. Initiating cooldown...")
				s.setBlock(3 * time.Minute)
			}
		}
		
		// Обновляем synced_at в любом случае, чтобы не зацикливаться на одном авто
		s.db.Exec(fmt.Sprintf("UPDATE %s SET synced_at = CURRENT_TIMESTAMP WHERE ext_id = ?", prodTable), oldest.ExtID)
	}
}

func (s *Service) isBlocked() bool {
	s.blockedUntilMu.RLock()
	defer s.blockedUntilMu.RUnlock()
	return time.Now().Before(s.blockedUntil)
}

func (s *Service) setBlock(duration time.Duration) {
	s.blockedUntilMu.Lock()
	defer s.blockedUntilMu.Unlock()
	s.blockedUntil = time.Now().Add(duration)
}
