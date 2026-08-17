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
	"sync"
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

type SyncResult struct {
	Total            int
	OK               int
	Errors           int
	Timeouts         int
	UpdatedImages    int
	LogEntries       []SyncLogEntry
	EquipmentAlerts  []EquipmentAlert
}

const syncWorkers = 2

func (s *Service) syncVehicles(items []autocrm.VehicleRaw, typeID int) *SyncResult {
	result := &SyncResult{Total: len(items)}
	result.LogEntries = make([]SyncLogEntry, 0, len(items))
	
	cronTable := s.vehicleTable()
	prodTable := s.apiTable()

	// Load existing vehicles basic fields from prodTable and cronTable
	type existingVehicle struct {
		ExtID             int     `db:"ext_id"`
		Price             float64 `db:"price"`
		MinPrice          float64 `db:"min_price"`
		InStock           bool    `db:"instock"`
		OnWay             bool    `db:"onway"`
		Mileage           int     `db:"mileage"`
		DealershipID      int     `db:"dealership_id"`
		RawJSON           string  `db:"raw"`
		FromCron          bool
	}

	var existing []existingVehicle
	existingMap := make(map[int]existingVehicle)
	if prodTable != "" {
		err := s.db.Select(&existing, fmt.Sprintf("SELECT ext_id, price, min_price, instock, onway, mileage, dealership_id, raw FROM %s", prodTable))
		if err == nil {
			for _, ev := range existing {
				ev.FromCron = false
				existingMap[ev.ExtID] = ev
			}
		}
	}
	if cronTable != "" {
		var existingCron []existingVehicle
		err := s.db.Select(&existingCron, fmt.Sprintf("SELECT ext_id, price, min_price, instock, onway, mileage, dealership_id, raw FROM %s", cronTable))
		if err == nil {
			for _, ev := range existingCron {
				ev.FromCron = true
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

			// Also compare first image if available
			if !changed && len(v.Images) > 0 {
				var oldRaw autocrm.VehicleRaw
				if json.Unmarshal([]byte(ev.RawJSON), &oldRaw) == nil && len(oldRaw.Images) > 0 {
					if oldRaw.Images[0].Full != v.Images[0].Full {
						changed = true
					}
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

	log.Printf("sync: %d/%d vehicles need detail sync (others copied from %s)", len(toSync), len(items), prodTable)

	if len(toSync) == 0 {
		return result
	}

	toSyncCh := make(chan autocrm.VehicleRaw, len(toSync))
	for _, v := range toSync {
		toSyncCh <- v
	}
	close(toSyncCh)

	var mu sync.Mutex
	var wg sync.WaitGroup

	for range syncWorkers {
		wg.Add(1)
		go func() {
			defer wg.Done()
			for v := range toSyncCh {
				if s.isBlocked() {
					// Drain the queue to stop immediately
					for len(toSyncCh) > 0 {
						<-toSyncCh
					}
					break
				}
				time.Sleep(1 * time.Second) // Add a delay to prevent DDoS-Guard blocking
				start := time.Now()
				vin, updImg, alert, err := s.SyncVehicleDetail(v.ID, typeID, cronTable)
				duration := time.Since(start)

				entry := SyncLogEntry{
					ID:        v.ID,
					VIN:       vin,
					Duration:  duration,
					Status:    "ok",
					UpdateImg: updImg,
				}

				mu.Lock()
				if alert != nil {
					result.EquipmentAlerts = append(result.EquipmentAlerts, *alert)
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

					if strings.Contains(err.Error(), "status 403") || strings.Contains(err.Error(), "forbidden") {
						log.Printf("DDoS-Guard block detected. Initiating cooldown...")
						s.setBlock(3 * time.Minute)
						// Drain the queue to stop immediately
						for len(toSyncCh) > 0 {
							<-toSyncCh
						}
					}
				} else {
					result.OK++
					if updImg {
						result.UpdatedImages++
					}
				}
				result.LogEntries = append(result.LogEntries, entry)
				mu.Unlock()
			}
		}()
	}

	wg.Wait()
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
		time.Sleep(500 * time.Millisecond)
	}

	log.Printf("new vehicles list done: %d items, %d brands", len(allVehicles), brandCount)

	cronTable := s.vehicleTable()

	result := s.syncVehicles(allVehicles, 1)

	log.Printf("new vehicles done: %d ok, %d err, %d timeout", result.OK, result.Errors, result.Timeouts)

	if len(result.EquipmentAlerts) > 0 {
		uniqueAlerts := make(map[string]EquipmentAlert)
		for _, a := range result.EquipmentAlerts {
			key := fmt.Sprintf("%s|%s|%s", a.Brand, a.Model, a.Equipment)
			uniqueAlerts[key] = a
		}

		if len(uniqueAlerts) > 0 {
			var text strings.Builder
			text.WriteString("Внимание! Обнаружены англоязычные комплектации.<br /><br />\n")
			text.WriteString(fmt.Sprintf("Комплектаций: %d<br />\n", len(uniqueAlerts)))
			for _, a := range uniqueAlerts {
				text.WriteString(fmt.Sprintf("%s | %s | %s<br />\n", a.Brand, a.Model, a.Equipment))
			}
			text.WriteString("<br />Внести исправления можно здесь: <a href=\"https://apps.yug-avto.ru/cis/equipments/\">https://apps.yug-avto.ru/cis/equipments/</a>\n")

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

			errMail := sendMail("Оповещения Юг-Авто Apps. Витрина.", text.String(), recipients)
			if errMail != nil {
				log.Printf("failed to send equipment alerts mail: %v", errMail)
			} else {
				log.Printf("sent %d equipment alerts email to %d recipients", len(uniqueAlerts), len(recipients))
			}
		}
	}

	// Delete new vehicles that are no longer in the API response (sold or removed)
	if len(allVehicles) > 0 {
		s.deleteSoldVehicles(cronTable, 1, allVehicles)
	}

	projectRoot := filepath.Dir(filepath.Dir(s.uploadDir))
	writeSyncLog(projectRoot, "new", start, len(allVehicles), result.LogEntries)

	return result, nil
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
		metaInfo := "no meta"
		if resp.Meta != nil {
			metaInfo = fmt.Sprintf("page %d/%d, total %d", page, resp.Meta.PageCount, resp.Meta.TotalCount)
		}
		log.Printf("used page %d: %d items, %s", page, len(resp.Items), metaInfo)
		for _, v := range resp.Items {
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
		time.Sleep(300 * time.Millisecond)
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
