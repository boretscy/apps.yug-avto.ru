package cis

import (
	"encoding/base64"
	"encoding/json"
	"fmt"
	"log"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
	"time"

	"github.com/yugavto/apps/pkg/autocrm"
)

type SyncLogEntry struct {
	ID        int
	VIN       string
	Duration  time.Duration
	Status    string // "ok", "timeout", "error"
	ErrDetail string
	UpdateImg bool
}

type EquipmentAlert struct {
	Brand     string `json:"brand"`
	Model     string `json:"model"`
	Equipment string `json:"equipment"`
}

type MissingModelAlert struct {
	ID         int    `json:"id"`
	VIN        string `json:"vin"`
	Brand      string `json:"brand"`
	BrandExtID int    `json:"brand_ext_id"`
	RefModel   string `json:"ref_model"`
	Dealership string `json:"dealership"`
}

type SyncResult struct {
	Total              int
	OK                 int
	Errors             int
	Timeouts           int
	UpdatedImages      int
	LogEntries         []SyncLogEntry
	EquipmentAlerts    []EquipmentAlert
	MissingModelAlerts []MissingModelAlert
}

const (
	syncWorkers           = 1
	detailRequestInterval = 600 * time.Millisecond
)

func areImagesEqual(oldImgs, newImgs []autocrm.ImageInfo) bool {
	if len(oldImgs) != len(newImgs) {
		return false
	}
	for i := range oldImgs {
		if oldImgs[i].Full != newImgs[i].Full {
			return false
		}
	}
	return true
}

func hasSpecs(rawJSON string) bool {
	if rawJSON == "" {
		return false
	}
	var raw struct {
		Specifications []interface{}   `json:"specifications"`
		Options        []interface{}   `json:"options"`
		RawOptions     json.RawMessage `json:"raw_options"`
	}
	if json.Unmarshal([]byte(rawJSON), &raw) != nil {
		return false
	}
	return len(raw.Specifications) > 0 || len(raw.Options) > 0 || len(raw.RawOptions) > 0
}


func (s *Service) syncVehicles(items []autocrm.VehicleRaw, typeID int) *SyncResult {
	result := &SyncResult{Total: len(items)}
	result.LogEntries = make([]SyncLogEntry, 0, len(items))
	
	cronTable := s.vehicleTable()
	prodTable := s.apiTable()

	// Load existing vehicles basic fields from prodTable and cronTable
	type existingVehicle struct {
		ExtID             int        `db:"ext_id"`
		Price             float64    `db:"price"`
		MinPrice          float64    `db:"min_price"`
		InStock           bool       `db:"instock"`
		OnWay             bool       `db:"onway"`
		Mileage           int        `db:"mileage"`
		DealershipID      int        `db:"dealership_id"`
		Body              string     `db:"body"`
		Drive             string     `db:"drive"`
		Engine            string     `db:"engine"`
		Transmission      string     `db:"transmission"`
		Color             string     `db:"color"`
		RawJSON           string     `db:"raw"`
		SyncedAt          *time.Time `db:"synced_at"`
		FromCron          bool
	}

	var existing []existingVehicle
	existingMap := make(map[int]existingVehicle)
	if prodTable != "" {
		err := s.db.Select(&existing, fmt.Sprintf("SELECT ext_id, price, min_price, instock, onway, mileage, dealership_id, body, drive, engine, transmission, color, raw, synced_at FROM %s", prodTable))
		if err == nil {
			for _, ev := range existing {
				ev.FromCron = false
				existingMap[ev.ExtID] = ev
			}
		}
	}
	if cronTable != "" {
		var existingCron []existingVehicle
		err := s.db.Select(&existingCron, fmt.Sprintf("SELECT ext_id, price, min_price, instock, onway, mileage, dealership_id, body, drive, engine, transmission, color, raw, synced_at FROM %s", cronTable))
		if err == nil {
			for _, ev := range existingCron {
				ev.FromCron = true
				if prev, ok := existingMap[ev.ExtID]; ok {
					// Если в prodTable уже есть характеристики, а в cronTable их нет — не понижаем полноту данных
					if hasSpecs(prev.RawJSON) && !hasSpecs(ev.RawJSON) {
						continue
					}
				}
				existingMap[ev.ExtID] = ev
			}
		}
	}

	// Status constants
	const statusInStock = 1
	const statusOnWay = 2

	var toSync []autocrm.VehicleRaw

	for _, v := range items {
		ev, exists := existingMap[v.ID]
		needDetailSync := true

		if exists {
			instock := v.Status != nil && v.Status.ID == statusInStock
			onway := v.Status != nil && v.Status.ID == statusOnWay
			dealershipID := 0
			if v.Dealership != nil {
				dealershipID = v.Dealership.ID
			}

			// Parse mileage
			mileage := ev.Mileage
			if typeID == 2 && len(v.General) > 0 {
				mileage = parseIntField(v.General, 5)
			}

			changed := ev.Price != v.Price ||
				ev.MinPrice != v.MinPrice ||
				ev.InStock != instock ||
				ev.OnWay != onway ||
				ev.Mileage != mileage ||
				ev.DealershipID != dealershipID

			// Compare entire gallery (count, URLs, order) and check presence of detailed specs
			if !changed {
				var oldRaw autocrm.VehicleRaw
				if json.Unmarshal([]byte(ev.RawJSON), &oldRaw) == nil {
					if !areImagesEqual(oldRaw.Images, v.Images) {
						changed = true
					} else if len(oldRaw.Specifications) == 0 && len(oldRaw.Options) == 0 {
						changed = true
					} else if typeID == 1 {
						// Проверка актуальности UAEP: если связка активна, но допы еще не выгружены или цены не скорректированы
						uaepActive := s.IsUAEPActiveByExtID(v.ModelID, dealershipID)
						hasUAEPInRaw := strings.Contains(ev.RawJSON, `"additional_equipment_price"`)
						if uaepActive {
							if !hasUAEPInRaw || (oldRaw.AdditionalEquipmentPrice > 0 && ev.Price == v.Price) {
								changed = true
							}
						} else {
							// Связка неактивна, но цена в базе была с вычетом допов
							if ev.Price != v.Price || ev.MinPrice != v.MinPrice {
								changed = true
							}
						}
					}
					// Если в базе кузов или привод пустые/неопределенные, а в сырых данных есть информация
					if !changed {
						if ev.Body == "" || ev.Body == "none" || ev.Drive == "" || ev.Drive == "none" {
							if oldRaw.BodyType != "" || v.BodyType != "" || len(oldRaw.Specifications) > 0 {
								changed = true
							}
						}
					}
				} else {
					changed = true
				}
			}

			if !changed {
				if ev.FromCron {
					// Vehicle is already in cronTable and hasn't changed, no action needed
					needDetailSync = false
					result.OK++
					result.LogEntries = append(result.LogEntries, SyncLogEntry{
						ID:       v.ID,
						VIN:      v.Vin,
						Duration: 0,
						Status:   "ok",
					})
				} else {
					// Vehicle is in prodTable but not in cronTable and hasn't changed. Copy it.
					_, err := s.db.Exec(fmt.Sprintf("INSERT INTO %s SELECT * FROM %s WHERE ext_id = ?", cronTable, prodTable), v.ID)
					if err == nil {
						needDetailSync = false
						result.OK++
						result.LogEntries = append(result.LogEntries, SyncLogEntry{
							ID:       v.ID,
							VIN:      v.Vin,
							Duration: 0,
							Status:   "ok",
						})
					}
				}
			}
		}

		if needDetailSync {
			toSync = append(toSync, v)
		}
	}

	// Ensure vehicles known to have missing models in active alerts are prioritized in toSync for re-check
	var knownMissingVINs []string
	if s.db.Select(&knownMissingVINs, "SELECT REPLACE(alert_key, \x27model:\x27, \x27\x27) FROM yapps_app_cis_alert_history WHERE is_active = 1 AND alert_key LIKE \x27model:%\x27") == nil && len(knownMissingVINs) > 0 {
		missingSet := make(map[string]bool, len(knownMissingVINs))
		for _, vin := range knownMissingVINs {
			missingSet[vin] = true
		}
		inToSync := make(map[int]bool, len(toSync))
		for _, v := range toSync {
			inToSync[v.ID] = true
		}
		for _, v := range items {
			if missingSet[v.Vin] && !inToSync[v.ID] {
				toSync = append(toSync, v)
				inToSync[v.ID] = true
			}
		}
	}

	log.Printf("sync: %d/%d vehicles need detail sync (others copied from %s)", len(toSync), len(items), prodTable)

	if len(toSync) == 0 {
		return result
	}

	for _, v := range toSync {
		if s.isBlocked() {
			if prodTable != "" {
				s.db.Exec(fmt.Sprintf("INSERT IGNORE INTO %s SELECT * FROM %s WHERE ext_id = ?", cronTable, prodTable), v.ID)
			}
			continue
		}

		start := time.Now()
		vin, updImg, alert, missingModel, err := s.SyncVehicleDetail(v.ID, typeID, cronTable)
		duration := time.Since(start)

		entry := SyncLogEntry{
			ID:        v.ID,
			VIN:       vin,
			Duration:  duration,
			Status:    "ok",
			UpdateImg: updImg,
		}

		if alert != nil {
			result.EquipmentAlerts = append(result.EquipmentAlerts, *alert)
		}
		if missingModel != nil {
			result.MissingModelAlerts = append(result.MissingModelAlerts, *missingModel)
		}

		if err != nil {
			isTimeout := duration > 9*time.Second || strings.Contains(strings.ToLower(err.Error()), "timeout") || strings.Contains(strings.ToLower(err.Error()), "deadline")
			if isTimeout {
				result.Timeouts++
				entry.Status = "timeout"
			} else {
				result.Errors++
				entry.Status = "error"
				entry.ErrDetail = err.Error()
			}
			log.Printf("vehicle %d error: %v", v.ID, err)

			// Если произошла ошибка, сохраняем версию из prodTable, чтобы не потерять авто
			if prodTable != "" {
				s.db.Exec(fmt.Sprintf("INSERT IGNORE INTO %s SELECT * FROM %s WHERE ext_id = ?", cronTable, prodTable), v.ID)
			}

			if strings.Contains(err.Error(), "status 403") || strings.Contains(err.Error(), "forbidden") {
				log.Printf("DDoS-Guard block detected. Initiating cooldown...")
				s.setBlock(3 * time.Minute)
			}
		} else {
			result.OK++
			if updImg {
				result.UpdatedImages++
			}
		}

		result.LogEntries = append(result.LogEntries, entry)

		// Пауза между детальными запросами для защиты от WAF ServicePipe
		time.Sleep(detailRequestInterval)
	}

	return result
}

func (s *Service) SyncNewVehicles() (*SyncResult, error) {
	if s.isBlocked() {
		return nil, fmt.Errorf("sync is cooled down due to DDoS-Guard block")
	}
	log.Println("=== CIS new vehicles sync start ===")
	start := time.Now()

	brands, err := s.crm.GetBrands()
	if err != nil {
		return nil, err
	}
	brandCount := len(brands.Items)

	var allVehicles []autocrm.VehicleRaw
	page := 1
	for {
		var resp *autocrm.VehiclesPage
		var err error
		for attempt := 0; attempt < 3; attempt++ {
			if attempt > 0 {
				log.Printf("retry new page %d, attempt %d", page, attempt+1)
				time.Sleep(2 * time.Second)
			}
			resp, err = s.crm.GetVehiclesListTimeout("new", page, 120*time.Second)
			if err == nil {
				break
			}
			log.Printf("new vehicles list error (page %d, attempt %d): %v", page, attempt+1, err)
		}
		if err != nil {
			projectRoot := filepath.Dir(filepath.Dir(s.uploadDir))
			writeSyncLog(projectRoot, "new", start, len(allVehicles), []SyncLogEntry{{
				Status:    "error",
				ErrDetail: fmt.Sprintf("Ошибка получения списка новых авто (стр. %d): %v", page, err),
			}})
			return nil, err
		}
		metaInfo := "no meta"
		if resp.Meta != nil {
			metaInfo = fmt.Sprintf("page %d/%d, total %d", page, resp.Meta.PageCount, resp.Meta.TotalCount)
		}
		log.Printf("fetched page %d: %d items, %s", page, len(resp.Items), metaInfo)
		for _, v := range resp.Items {
			if v.Dealership != nil && v.Dealership.ID == 1514 {
				continue
			}
			// Only include active vehicles (status 1: in stock, status 2: on way)
			if v.Status == nil || (v.Status.ID != statusInStock && v.Status.ID != statusOnWay) {
				continue
			}
			allVehicles = append(allVehicles, v)
		}
		if resp.Meta == nil || page >= resp.Meta.PageCount {
			break
		}
		page++
	}

	log.Printf("new vehicles list done: %d items, %d brands", len(allVehicles), brandCount)

	cronTable := s.vehicleTable()

	result := s.syncVehicles(allVehicles, 1)

	log.Printf("new vehicles done: %d ok, %d err, %d timeout", result.OK, result.Errors, result.Timeouts)

	recipients := []string{
		"yuliya.stolbovaya@yug-avto.ru",
		"natalya.davidova@yug-avto.ru",
		"nataliya.ivanova@yug-avto.ru",
		"vera.golubeva@yug-avto.ru",
		"viktoriya.lopatkina@yug-avto.ru",
		"yuliya.davidyan@yug-avto.ru",
		"darya.ermolaeva@yug-avto.ru",
		"ekaterina.shepetilo@yug-avto.ru",
		"yuliya.martinova@yug-avto.ru",
		"elvina.maksimova@yug-avto.ru",
		"yuliya.kudinova@yug-avto.ru",
		"natalya.kobeleva@yug-avto.ru",
		"anton.boreckiy@yug-avto.ru",
	}

	hasEquipmentAlerts := len(result.EquipmentAlerts) > 0
	hasMissingModels := len(result.MissingModelAlerts) > 0

	// Collect current alert keys to check throttling policy
	currentAlertMap := make(map[string]string)
	for _, m := range result.MissingModelAlerts {
		currentAlertMap[fmt.Sprintf("model:%s", m.VIN)] = "missing_model"
	}
	for _, a := range result.EquipmentAlerts {
		currentAlertMap[fmt.Sprintf("equip:%s|%s|%s", a.Brand, a.Model, a.Equipment)] = "english_equipment"
	}

	shouldSend, sendReason := s.shouldNotifyAlerts(currentAlertMap)

	if (hasEquipmentAlerts || hasMissingModels) && shouldSend {
		var text strings.Builder

		if hasMissingModels {
			uniqueMissing := make(map[int]MissingModelAlert)
			for _, m := range result.MissingModelAlerts {
				uniqueMissing[m.ID] = m
			}

			text.WriteString("<b>Внимание! Обнаружены новые автомобили с не указанной моделью (model_id отсутствует в AutoCRM):</b><br />\n")
			text.WriteString("<i>Возможная причина: у API-ключа нет прав на модель в AutoCRM, либо модель не заведена в CRM-системе. Такие авто не выгружаются на витрину.</i><br /><br />\n")
			text.WriteString(fmt.Sprintf("Количество автомобилей: %d<br />\n", len(uniqueMissing)))
			text.WriteString("<table border=\"1\" cellpadding=\"5\" cellspacing=\"0\" style=\"border-collapse: collapse; font-family: sans-serif; font-size: 13px;\">\n")
			text.WriteString("<tr style=\"background: #f2f2f2;\"><th>ID AutoCRM</th><th>VIN</th><th>ДЦ</th><th>Марка</th><th>Ожидаемая модель (RefModel)</th></tr>\n")
			for _, m := range uniqueMissing {
				text.WriteString(fmt.Sprintf("<tr><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n",
					m.ID, m.VIN, m.Dealership, m.Brand, m.RefModel))
			}
			text.WriteString("</table><br />\n")

			// Формируем сгруппированный шаблон обращения в техподдержку AutoCRM
			type brandModelKey struct {
				Brand      string
				BrandExtID int
				RefModel   string
			}
			groupedModels := make(map[brandModelKey]bool)
			for _, m := range uniqueMissing {
				ref := m.RefModel
				if ref == "" {
					ref = "Не указана"
				}
				key := brandModelKey{
					Brand:      m.Brand,
					BrandExtID: m.BrandExtID,
					RefModel:   ref,
				}
				groupedModels[key] = true
			}

			text.WriteString("<b>Шаблоны писем для отправки в техподдержку AutoCRM (support@autocrm.ru):</b><br /><br />\n")
			apiBaseURL := s.crm.BaseURL()
			maskedToken := ""
			rawToken := s.crm.Token()
			if len(rawToken) > 8 {
				maskedToken = rawToken[:4] + "..." + rawToken[len(rawToken)-4:]
			}

			for bm := range groupedModels {
				fullModelTitle := strings.TrimSpace(bm.Brand + " " + bm.RefModel)
				brandModelsURL := fmt.Sprintf("%s/brands/%d/models", apiBaseURL, bm.BrandExtID)

				text.WriteString("<div style=\"background: #f9f9f9; border-left: 4px solid #007bff; padding: 10px 15px; margin-bottom: 15px; font-family: monospace; font-size: 13px; line-height: 1.5;\">\n")
				text.WriteString(fmt.Sprintf("<b>Тема:</b> Права на модель %s<br /><br />\n", fullModelTitle))
				text.WriteString("Добрый день.<br /><br />\n")
				text.WriteString(fmt.Sprintf("Нам нужны права на модель %s<br /><br />\n", fullModelTitle))
				text.WriteString(fmt.Sprintf("<a href=\"%s\">%s</a><br />\n", brandModelsURL, brandModelsURL))
				if maskedToken != "" {
					text.WriteString(fmt.Sprintf("Наш токен: %s\n", maskedToken))
				}
				text.WriteString("</div>\n")
			}
			text.WriteString("<br />\n")
		}

		if hasEquipmentAlerts {
			uniqueAlerts := make(map[string]EquipmentAlert)
			for _, a := range result.EquipmentAlerts {
				key := fmt.Sprintf("%s|%s|%s", a.Brand, a.Model, a.Equipment)
				uniqueAlerts[key] = a
			}

			if len(uniqueAlerts) > 0 {
				text.WriteString("<b>Внимание! Обнаружены англоязычные комплектации:</b><br /><br />\n")
				text.WriteString(fmt.Sprintf("Комплектаций: %d<br />\n", len(uniqueAlerts)))
				for _, a := range uniqueAlerts {
					text.WriteString(fmt.Sprintf("%s | %s | %s<br />\n", a.Brand, a.Model, a.Equipment))
				}
				text.WriteString("<br />Внести исправления можно здесь: <a href=\"https://apps.yug-avto.ru/cis/equipments/\">https://apps.yug-avto.ru/cis/equipments/</a><br />\n")
			}
		}

		errMail := sendMail("Оповещения Юг-Авто Apps. Витрина.", text.String(), recipients)
		if errMail != nil {
			log.Printf("failed to send alerts mail: %v", errMail)
		} else {
			s.recordAlertsSent(currentAlertMap, sendReason)
			log.Printf("sent alerts email (missing models: %d, equipment: %d) to %d recipients [reason: %s]", len(result.MissingModelAlerts), len(result.EquipmentAlerts), len(recipients), sendReason)
		}
	}

	// Resolve alerts for vehicles that were fixed or removed
	if len(allVehicles) > 0 {
		s.resolveResolvedAlerts(allVehicles)
	}

	// Delete new vehicles that are no longer in the API response (sold or removed)
	if len(allVehicles) > 0 {
		s.deleteSoldVehicles(cronTable, 1, allVehicles)
	}

	projectRoot := filepath.Dir(filepath.Dir(s.uploadDir))
	writeSyncLog(projectRoot, "new", start, len(allVehicles), result.LogEntries)

	return result, nil
}


// shouldNotifyAlerts determines if alerts should be sent according to the policy:
// 1. Immediately upon first detection of any new alert (new missing model or new english equipment).
// 2. Once per day after 09:00 MSK for ongoing/unresolved alerts.
func (s *Service) shouldNotifyAlerts(currentAlerts map[string]string) (bool, string) {
	if len(currentAlerts) == 0 {
		return false, ""
	}

	// 1. Load alert history (all keys seen within the last 24 hours)
	// An alert is truly "new" only if its key has NEVER been seen or not notified in the last 24h.
	var notifiedRows []struct {
		AlertKey       string `db:"alert_key"`
		LastNotifiedAt string `db:"last_notified_at"`
	}
	s.db.Select(&notifiedRows, "SELECT alert_key, last_notified_at FROM yapps_app_cis_alert_history WHERE last_notified_at >= NOW() - INTERVAL 24 HOUR")
	recentlyNotified := make(map[string]bool, len(notifiedRows))
	for _, r := range notifiedRows {
		recentlyNotified[r.AlertKey] = true
	}

	hasNewAlert := false
	for key := range currentAlerts {
		if !recentlyNotified[key] {
			hasNewAlert = true
			break
		}
	}

	if hasNewAlert {
		return true, "first_detection"
	}

	// 2. Check daily reminder condition: after 09:00 MSK, once per calendar day
	loc, err := time.LoadLocation("Europe/Moscow")
	if err != nil {
		loc = time.FixedZone("MSK", 3*3600)
	}
	nowMsk := time.Now().In(loc)

	if nowMsk.Hour() >= 9 {
		var rawTime string
		err := s.db.Get(&rawTime, "SELECT last_notified_at FROM yapps_app_cis_alert_history WHERE alert_key = \x27meta:daily_digest\x27")
		alreadySentToday := false
		if err == nil && rawTime != "" {
			t, errParse := time.ParseInLocation("2006-01-02 15:04:05", rawTime, loc)
			if errParse == nil {
				alreadySentToday = t.Year() == nowMsk.Year() &&
					t.Month() == nowMsk.Month() &&
					t.Day() == nowMsk.Day()
			}
		}

		if !alreadySentToday {
			return true, "daily_digest"
		}
	}

	return false, ""
}

func (s *Service) recordAlertsSent(currentAlerts map[string]string, reason string) {
	// 1. Insert or update notified alerts
	for key, alertType := range currentAlerts {
		s.db.Exec(`
			INSERT INTO yapps_app_cis_alert_history (alert_key, alert_type, first_seen_at, last_notified_at, is_active)
			VALUES (?, ?, NOW(), NOW(), 1)
			ON DUPLICATE KEY UPDATE
				alert_type = VALUES(alert_type),
				last_notified_at = NOW(),
				is_active = 1
		`, key, alertType)
	}

	// 2. If daily digest or after 09:00, update meta:daily_digest
	loc, err := time.LoadLocation("Europe/Moscow")
	if err != nil {
		loc = time.FixedZone("MSK", 3*3600)
	}
	nowMsk := time.Now().In(loc)

	if reason == "daily_digest" || (nowMsk.Hour() >= 9 && reason == "first_detection") {
		s.db.Exec(`
			INSERT INTO yapps_app_cis_alert_history (alert_key, alert_type, first_seen_at, last_notified_at, is_active)
			VALUES (\x27meta:daily_digest\x27, \x27meta\x27, NOW(), NOW(), 1)
			ON DUPLICATE KEY UPDATE last_notified_at = NOW(), is_active = 1
		`)
	}
}

// resolveResolvedAlerts scans currently active alerts and marks resolved ones as is_active = 0
func (s *Service) resolveResolvedAlerts(allVehicles []autocrm.VehicleRaw) {
	var activeRows []struct {
		AlertKey  string `db:"alert_key"`
		AlertType string `db:"alert_type"`
	}
	s.db.Select(&activeRows, "SELECT alert_key, alert_type FROM yapps_app_cis_alert_history WHERE is_active = 1 AND alert_key NOT LIKE \x27meta:%\x27")
	if len(activeRows) == 0 {
		return
	}

	// Map of active vehicles in CRM response by VIN and ModelID
	vehicleByVIN := make(map[string]autocrm.VehicleRaw, len(allVehicles))
	for _, v := range allVehicles {
		vehicleByVIN[v.Vin] = v
	}

	for _, r := range activeRows {
		if r.AlertType == "missing_model" && strings.HasPrefix(r.AlertKey, "model:") {
			vin := strings.TrimPrefix(r.AlertKey, "model:")
			v, exists := vehicleByVIN[vin]
			// Resolved if vehicle is no longer in CRM response, or model_id is now populated > 0
			if !exists || v.ModelID > 0 {
				s.db.Exec("UPDATE yapps_app_cis_alert_history SET is_active = 0 WHERE alert_key = ?", r.AlertKey)
				log.Printf("alert %s resolved (vehicle exists: %v, model_id: %d)", r.AlertKey, exists, v.ModelID)
			}
		} else if r.AlertType == "english_equipment" && strings.HasPrefix(r.AlertKey, "equip:") {
			parts := strings.Split(strings.TrimPrefix(r.AlertKey, "equip:"), "|")
			if len(parts) == 3 {
				brandName, modelName, equipName := parts[0], parts[1], parts[2]
				var count int
				checkErr := s.db.Get(&count, `
					SELECT COUNT(*) FROM yapps_app_cis_equipments e
					JOIN yapps_app_cis_brands b ON b.id = e.brand_id
					JOIN yapps_app_cis_models_new m ON m.id = e.model_id
					WHERE b.name = ? AND m.name = ? AND e.name = ?
				`, brandName, modelName, equipName)
				if checkErr == nil && count > 0 {
					s.db.Exec("UPDATE yapps_app_cis_alert_history SET is_active = 0 WHERE alert_key = ?", r.AlertKey)
					log.Printf("alert %s resolved (translation added to yapps_app_cis_equipments)", r.AlertKey)
				}
			}
		}
	}
}

func (s *Service) deleteSoldVehicles(tableName string, typeID int, activeVehicles []autocrm.VehicleRaw) {
	activeIDs := make(map[int]bool, len(activeVehicles))
	for _, v := range activeVehicles {
		activeIDs[v.ID] = true
	}

	var existingIDs []int
	err := s.db.Select(&existingIDs, fmt.Sprintf("SELECT ext_id FROM %s WHERE type_id = ?", tableName), typeID)
	if err != nil {
		log.Printf("fetch existing vehicles for delete error (type %d): %v", typeID, err)
		return
	}

	var toDelete []int
	for _, id := range existingIDs {
		if !activeIDs[id] {
			toDelete = append(toDelete, id)
		}
	}

	if len(toDelete) == 0 {
		return
	}

	log.Printf("found %d sold vehicles to delete in %s (type %d)", len(toDelete), tableName, typeID)

	const batchSize = 500
	totalDeleted := int64(0)
	for i := 0; i < len(toDelete); i += batchSize {
		end := i + batchSize
		if end > len(toDelete) {
			end = len(toDelete)
		}
		chunk := toDelete[i:end]
		phs := make([]string, len(chunk))
		args := make([]interface{}, len(chunk))
		for j, id := range chunk {
			phs[j] = "?"
			args[j] = id
		}
		q := fmt.Sprintf("DELETE FROM %s WHERE type_id = ? AND ext_id IN (%s)", tableName, strings.Join(phs, ","))
		res, err := s.db.Exec(q, append([]interface{}{typeID}, args...)...)
		if err != nil {
			log.Printf("delete sold vehicles batch error: %v", err)
		} else {
			n, _ := res.RowsAffected()
			totalDeleted += n
		}
	}
	log.Printf("deleted total %d sold vehicles in %s (type %d)", totalDeleted, tableName, typeID)
}

func (s *Service) SyncUsedDealerships() error {
	var dealerships []struct {
		Code int `db:"code"`
	}
	if err := s.db.Select(&dealerships, "SELECT code FROM yapps_app_cis_dealerships WHERE type_id = 2"); err != nil {
		return err
	}
	log.Printf("found %d used dealerships", len(dealerships))
	return nil
}

func (s *Service) SyncUsedVehicles() (*SyncResult, error) {
	log.Println("=== CIS used vehicles sync start ===")
	start := time.Now()

	_, cron := s.TableNames()

	var existing int
	s.db.Get(&existing, "SELECT COUNT(*) FROM "+cron+" WHERE type_id = 1")
	if existing == 0 {
		log.Println("no new vehicles yet, delaying used sync")
		return &SyncResult{}, nil
	}

	var allVehicles []autocrm.VehicleRaw
	page := 1
	var fetchErr error
	for {
		var resp *autocrm.VehiclesPage
		var err error
		for attempt := 0; attempt < 3; attempt++ {
			if attempt > 0 {
				log.Printf("retry used page %d, attempt %d", page, attempt+1)
				time.Sleep(2 * time.Second)
			}
			resp, err = s.crm.GetVehiclesListTimeout("used", page, 120*time.Second)
			if err == nil {
				break
			}
			log.Printf("used vehicles list error (page %d, attempt %d): %v", page, attempt+1, err)
		}
		if err != nil {
			fetchErr = err
			log.Printf("used vehicles list error: giving up page %d: %v", page, err)
			break
		}
		if page == 1 && resp.Filter != nil && len(resp.Filter.Models) > 0 {
			if err := s.SyncUsedModels(resp.Filter.Models); err != nil {
				log.Printf("sync used models error: %v", err)
			}
		}

		metaInfo := "no meta"
		if resp.Meta != nil {
			metaInfo = fmt.Sprintf("page %d/%d, total %d", page, resp.Meta.PageCount, resp.Meta.TotalCount)
		}
		log.Printf("used page %d: %d items, %s", page, len(resp.Items), metaInfo)
		for _, v := range resp.Items {
			if v.ID == 1314264 {
				continue
			}
			if v.Dealership == nil || !allowedUsedDealerships[v.Dealership.ID] {
				continue
			}
			// Only include active vehicles (status 1: in stock, status 2: on way)
			if v.Status != nil && v.Status.ID != statusInStock && v.Status.ID != statusOnWay {
				continue
			}
			allVehicles = append(allVehicles, v)
		}

		if resp.Meta == nil || page >= resp.Meta.PageCount {
			break
		}
		page++
	}

	log.Printf("fetched %d used vehicles", len(allVehicles))

	result := s.syncVehicles(allVehicles, 2)

	// Delete used vehicles that are no longer in the API response
	// Only delete if all pages were fetched successfully
	if len(allVehicles) > 0 && fetchErr == nil {
		s.deleteSoldVehicles(cron, 2, allVehicles)
	}

	log.Printf("used vehicles done: %d total, %d ok, %d err, %d timeout",
		result.Total, result.OK, result.Errors, result.Timeouts)

	projectRoot := filepath.Dir(filepath.Dir(s.uploadDir))
	writeSyncLog(projectRoot, "used", start, len(allVehicles), result.LogEntries)

	return result, nil
}

func (s *Service) FullSync() error {
	start := time.Now()

	if err := s.Init(); err != nil {
		return err
	}

	if err := s.SyncBrands("new"); err != nil {
		return err
	}
	log.Println("brands synced")

	brands, _ := s.crm.GetBrands()
	for _, b := range brands.Items {
		if err := s.SyncModels(b.ID, "new"); err != nil {
			return err
		}
	}
	log.Println("models synced")

	newResult, err := s.SyncNewVehicles()
	if err != nil {
		return err
	}
	log.Printf("new result: %+v", newResult)

	usedResult, err := s.SyncUsedVehicles()
	if err != nil {
		return err
	}
	log.Printf("used result: %+v", usedResult)

	ok, err := s.IsOK()
	if err != nil {
		return err
	}
	if ok {
		if err := s.ToggleTables(); err != nil {
			return err
		}
		log.Println("tables toggled")
	}

	log.Printf("full sync completed in %v", time.Since(start))
	return nil
}

func writeSyncLog(projectRoot string, section string, start time.Time, total int, entries []SyncLogEntry) {
	logDir := filepath.Join(projectRoot, "core", "YApps", "Logs", "Cis", time.Now().Format("2006/01/02"))
	if err := os.MkdirAll(logDir, 0755); err != nil {
		log.Printf("failed to create log dir: %v", err)
		return
	}

	var sb strings.Builder

	sectionTitle := "Новые"
	if section == "used" {
		sectionTitle = "С пробегом"
	}

	sb.WriteString("======================================================================\n")
	sb.WriteString(fmt.Sprintf("СИНХРОНИЗАЦИЯ: %s АВТОМОБИЛИ [%s — %s]\n", strings.ToUpper(sectionTitle), start.Format("02.01.2006 15:04:05"), time.Now().Format("15:04:05")))
	sb.WriteString("======================================================================\n\n")

	var countOk int
	var countError int
	var countTimeout int
	var countPhoto int

	var errorItems []string
	var timeoutItems []string
	var photoItems []string

	brandErrorCounts := make(map[string]int)

	for _, entry := range entries {
		switch entry.Status {
		case "ok":
			countOk++
			if entry.UpdateImg {
				countPhoto++
				photoItems = append(photoItems, fmt.Sprintf("VIN: %s | ID: %d", entry.VIN, entry.ID))
			}
		case "timeout":
			countTimeout++
			timeoutItems = append(timeoutItems, fmt.Sprintf("ID: %d", entry.ID))
		case "error":
			countError++
			errorItems = append(errorItems, fmt.Sprintf("VIN: %s | ID: %d (ошибка: %s)", entry.VIN, entry.ID, entry.ErrDetail))

			brandName := "Неизвестный бренд"
			if strings.HasPrefix(strings.ToUpper(entry.VIN), "XTA") {
				brandName = "LADA"
			} else if strings.Contains(strings.ToLower(entry.ErrDetail), "brand ") {
				brandName = "Несопоставленный бренд"
			}
			brandErrorCounts[brandName]++
		}
	}

	sb.WriteString("[СТАТИСТИКА ИМПОРТА]\n")
	sb.WriteString("----------------------------------------------------------------------\n")
	sb.WriteString(fmt.Sprintf("Всего автомобилей в API:  %d\n", total))
	if total > 0 {
		sb.WriteString(fmt.Sprintf("Успешно импортировано:    %d (%.1f%%)\n", countOk, float64(countOk)/float64(total)*100))
		sb.WriteString(fmt.Sprintf("Пропущено / Ошибки:       %d (%.1f%%)\n", countError, float64(countError)/float64(total)*100))
		sb.WriteString(fmt.Sprintf("Сбои (Таймауты):          %d (%.1f%%)\n", countTimeout, float64(countTimeout)/float64(total)*100))
	} else {
		sb.WriteString("Успешно импортировано:    0\n")
		sb.WriteString("Пропущено / Ошибки:       0\n")
		sb.WriteString("Сбои (Таймауты):          0\n")
	}
	sb.WriteString(fmt.Sprintf("Обновлено фото:           %d\n\n", countPhoto))

	duration := time.Since(start)
	sb.WriteString("[ВРЕМЯ ВЫПОЛНЕНИЯ]\n")
	sb.WriteString("----------------------------------------------------------------------\n")
	sb.WriteString(fmt.Sprintf("Общее время импорта:     %v\n", duration.Round(time.Second)))
	if total > 0 {
		sb.WriteString(fmt.Sprintf("Среднее время на авто:   %.2f сек\n", duration.Seconds()/float64(total)))
	}

	type SortEntry struct {
		ID       int
		Duration time.Duration
	}
	var slowest []SortEntry
	for _, entry := range entries {
		if entry.Status != "timeout" {
			slowest = append(slowest, SortEntry{ID: entry.ID, Duration: entry.Duration})
		}
	}
	for i := 0; i < len(slowest); i++ {
		for j := i + 1; j < len(slowest); j++ {
			if slowest[i].Duration < slowest[j].Duration {
				slowest[i], slowest[j] = slowest[j], slowest[i]
			}
		}
	}

	sb.WriteString("Топ-5 самых медленных запросов к API:\n")
	limitSlow := 5
	if len(slowest) < limitSlow {
		limitSlow = len(slowest)
	}
	for i := 0; i < limitSlow; i++ {
		sb.WriteString(fmt.Sprintf("  - ID #%d: %v\n", slowest[i].ID, slowest[i].Duration.Round(time.Millisecond)))
	}
	sb.WriteString("\n")

	if len(brandErrorCounts) > 0 {
		sb.WriteString("[ГРУППИРОВКА ПРОПУЩЕННЫХ АВТО ПО БРЕНДАМ]\n")
		sb.WriteString("----------------------------------------------------------------------\n")
		sb.WriteString("Не найден бренд/модель в базе CIS:\n")
		for brandName, count := range brandErrorCounts {
			sb.WriteString(fmt.Sprintf("  - %s: %d авто\n", brandName, count))
		}
		sb.WriteString("\n")
	}

	if len(errorItems) > 0 {
		sb.WriteString("[ДЕТАЛЬНЫЙ СПИСОК ПРОПУЩЕННЫХ АВТО]\n")
		sb.WriteString("----------------------------------------------------------------------\n")
		limitErr := 100
		if len(errorItems) < limitErr {
			limitErr = len(errorItems)
		}
		sb.WriteString(fmt.Sprintf("(Показано первых %d из %d)\n", limitErr, len(errorItems)))
		for i := 0; i < limitErr; i++ {
			sb.WriteString(fmt.Sprintf("  - %s\n", errorItems[i]))
		}
		sb.WriteString("\n")
	}

	if len(timeoutItems) > 0 {
		sb.WriteString("[СБРОШЕННЫЕ АВТО ПО ТАЙМАУТУ]\n")
		sb.WriteString("----------------------------------------------------------------------\n")
		limitTo := 100
		if len(timeoutItems) < limitTo {
			limitTo = len(timeoutItems)
		}
		sb.WriteString(fmt.Sprintf("(Показано первых %d из %d)\n", limitTo, len(timeoutItems)))
		for i := 0; i < limitTo; i++ {
			sb.WriteString(fmt.Sprintf("  - %s\n", timeoutItems[i]))
		}
		sb.WriteString("\n")
	}

	if len(photoItems) > 0 {
		sb.WriteString("[СПИСОК НА ОБНОВЛЕНИЕ ФОТО]\n")
		sb.WriteString("----------------------------------------------------------------------\n")
		limitPhoto := 100
		if len(photoItems) < limitPhoto {
			limitPhoto = len(photoItems)
		}
		sb.WriteString(fmt.Sprintf("(Показано первых %d из %d)\n", limitPhoto, len(photoItems)))
		for i := 0; i < limitPhoto; i++ {
			sb.WriteString(fmt.Sprintf("  - %s\n", photoItems[i]))
		}
		sb.WriteString("\n")
	}

	sb.WriteString("======================================================================\n")

	logPath := filepath.Join(logDir, section+".txt")
	if err := os.WriteFile(logPath, []byte(sb.String()), 0644); err != nil {
		log.Printf("failed to write sync log file: %v", err)
	} else {
		log.Printf("sync log written to %s", logPath)
	}
}

func sendMail(subject, body string, recipients []string) error {
	cmd := exec.Command("/usr/sbin/sendmail", "-t")
	stdin, err := cmd.StdinPipe()
	if err != nil {
		return err
	}

	go func() {
		defer stdin.Close()
		fmt.Fprintf(stdin, "To: %s\r\n", strings.Join(recipients, ", "))
		fmt.Fprint(stdin, "From: cis@apps.yug-avto.ru\r\n")
		fmt.Fprintf(stdin, "Subject: =?UTF-8?B?%s?=\r\n", base64.StdEncoding.EncodeToString([]byte(subject)))
		fmt.Fprint(stdin, "MIME-Version: 1.0\r\n")
		fmt.Fprint(stdin, "Content-Type: text/html; charset=utf-8\r\n")
		fmt.Fprint(stdin, "\r\n")
		fmt.Fprint(stdin, body)
	}()

	return cmd.Run()
}
