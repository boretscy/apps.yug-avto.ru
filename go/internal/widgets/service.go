package widgets

import (
	"encoding/base64"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"net/url"
	"os"
	"os/exec"
	"path/filepath"
	"strconv"
	"strings"
	"time"

	"github.com/jmoiron/sqlx"
)

type Service struct {
	db          *sqlx.DB
	cache       *ScriptCache
	projectRoot string
	apiToken    string
}

func NewService(db *sqlx.DB, projectRoot string, apiToken string) *Service {
	return &Service{
		db:          db,
		cache:       NewScriptCache(5 * time.Minute),
		projectRoot: projectRoot,
		apiToken:    apiToken,
	}
}

// GetScript генерирует бандл виджетов для запрошенного URL
func (s *Service) GetScript(pageURL string) (string, error) {
	if cached, ok := s.cache.Get(pageURL); ok {
		return cached, nil
	}

	u, err := url.Parse(pageURL)
	if err != nil {
		return "", fmt.Errorf("invalid page url: %w", err)
	}

	host := u.Host
	if host == "" {
		host = pageURL
	}

	// 1. Определение сайта / лендинга
	siteID, site, land, err := s.resolveSite(host, pageURL)
	if err != nil || siteID == 0 {
		return "// yapps widgets: site not found for " + host, nil
	}

	// 2. Получение настроек виджета для сайта
	settings, err := s.getSettingsBySiteID(siteID)
	if err != nil || settings == nil || settings.Active == 0 {
		return "// yapps widgets: inactive for site " + strconv.Itoa(siteID), nil
	}

	// Проверка правила отключения для лендинга
	if land != nil && (land.UseCB == 0 && land.UseLG == 0 && land.UseNV == 0 && land.UseCIS == 0) {
		return "// yapps widgets: land disabled", nil
	}

	// 3. Проверка временного отключения по графику (Shutdown)
	if s.isShutdown(siteID) {
		return "// yapps widgets: shutdown active", nil
	}

	// 4. Подбор активного виджета CallBack (CB)
	var cbWidget *Widget
	if settings.UseCB == 1 && (land == nil || land.UseCB == 1) {
		if s.isCBWorkTime() {
			cbWidget = s.selectWidget(siteID, 1, pageURL)
		}
	}

	// 5. Подбор активного виджета LeadGen (LG)
	var lgWidget *Widget
	if settings.UseLG == 1 && (land == nil || land.UseLG == 1) {
		lgCandidate := s.selectWidget(siteID, 2, pageURL)
		if lgCandidate != nil {
			if lgCandidate.LGTimerUse == 0 || lgCandidate.LGTimer > time.Now().Unix() {
				lgWidget = lgCandidate
			}
		}
	}

	// 6. Подбор NV и CIS
	useNV := settings.UseNV == 1 && (land == nil || land.UseNV == 1)
	useCIS := settings.UseCIS == 1 && (land == nil || land.UseCIS == 1)

	if cbWidget == nil && lgWidget == nil && !useNV && !useCIS {
		return "// yapps widgets: no active widgets", nil
	}

	// Обновление настроек Calltouch из лендинга при наличии
	if land != nil {
		if land.CalltouchID != "" {
			site.CalltouchID = land.CalltouchID
		}
		if land.CalltouchSess != "" {
			site.CalltouchSess = land.CalltouchSess
		}
	}

	// 7. Сборка CSS, HTML и JS
	cssContent := s.buildCSS(settings)
	htmlContent := s.buildHTML(settings, site, cbWidget, lgWidget, useNV, useCIS, land)
	script := s.buildJS(settings, site, cbWidget, lgWidget, htmlContent, cssContent)

	s.cache.Set(pageURL, script)

	return script, nil
}

func (s *Service) resolveSite(host string, rawURL string) (int, *Site, *Land, error) {
	cleanHostURL := "https://" + host + "/"

	// Попытка 1: Проверка таблицы лендингов yapps_app_lands
	var land Land
	err := s.db.Get(&land, "SELECT id, url, site_id, calltouch_id, calltouch_sess, use_cb, use_lg, use_nv, use_cis, use_av FROM yapps_app_lands WHERE url = ? OR url = ? LIMIT 1", cleanHostURL, "http://"+host+"/")
	if err == nil && land.ID > 0 {
		var site Site
		if err := s.db.Get(&site, "SELECT id, url, ru_name, yandex_id, calltouch_id, calltouch_sess FROM yapps_sites WHERE id = ? LIMIT 1", land.SiteID); err == nil {
			return site.ID, &site, &land, nil
		}
	}

	// Попытка 2: Проверка сайтов yapps_sites по полю url (хосту)
	var site Site
	err = s.db.Get(&site, "SELECT id, url, ru_name, yandex_id, calltouch_id, calltouch_sess FROM yapps_sites WHERE url = ? OR url = ? LIMIT 1", host, cleanHostURL)
	if err == nil && site.ID > 0 {
		return site.ID, &site, nil, nil
	}

	// Попытка 3: Проверка шоурумов yapps_showrooms
	var showroom Showroom
	err = s.db.Get(&showroom, "SELECT id, url, site_id FROM yapps_showrooms WHERE url = ? OR url = ? LIMIT 1", cleanHostURL, rawURL)
	if err == nil && showroom.SiteID > 0 {
		if err := s.db.Get(&site, "SELECT id, url, ru_name, yandex_id, calltouch_id, calltouch_sess FROM yapps_sites WHERE id = ? LIMIT 1", showroom.SiteID); err == nil {
			return site.ID, &site, nil, nil
		}
	}

	return 0, nil, nil, fmt.Errorf("site not found")
}

func (s *Service) getSettingsBySiteID(siteID int) (*WidgetSettings, error) {
	var st WidgetSettings
	err := s.db.Get(&st, "SELECT * FROM yapps_app_widgets_v3_settings WHERE site_id = ? LIMIT 1", siteID)
	if err != nil {
		return nil, err
	}
	return &st, nil
}

func (s *Service) isShutdown(siteID int) bool {
	var count int
	now := time.Now().Unix()
	err := s.db.Get(&count, "SELECT COUNT(*) FROM yapps_app_widgets_shutdowns WHERE site_id = ? AND start <= ? AND end >= ?", siteID, now, now)
	return err == nil && count > 0
}

func (s *Service) isCBWorkTime() bool {
	now := time.Now()
	hour := now.Hour()
	return hour >= 8 && hour < 20
}

func (s *Service) selectWidget(siteID int, typeID int, pageURL string) *Widget {
	parsedURL, _ := url.Parse(pageURL)
	reqPath := "/"
	if parsedURL != nil && parsedURL.Path != "" {
		reqPath = parsedURL.Path
	}

	// Поиск совпадающих widget_id по url
	var widgetIDs []int
	err := s.db.Select(&widgetIDs, "SELECT widget_id FROM yapps_app_widgets_v3_urls WHERE value = ? OR value = ?", reqPath, pageURL)
	if err != nil || len(widgetIDs) == 0 {
		_ = s.db.Select(&widgetIDs, "SELECT widget_id FROM yapps_app_widgets_v3_urls WHERE value = '/'")
	}

	var w Widget
	query := "SELECT * FROM yapps_app_widgets_v3 WHERE site_id = ? AND active = 1 AND type_id = ?"
	args := []interface{}{siteID, typeID}

	if len(widgetIDs) > 0 {
		q, inArgs, err := sqlx.In(query+" AND id IN (?) ORDER BY id DESC LIMIT 1", siteID, typeID, widgetIDs)
		if err == nil {
			err = s.db.Get(&w, s.db.Rebind(q), inArgs...)
			if err == nil && w.ID > 0 {
				s.loadWidgetDetails(&w)
				return &w
			}
		}
	}

	// Фоллбек без ограничения по URL
	err = s.db.Get(&w, query+" ORDER BY id DESC LIMIT 1", args...)
	if err == nil && w.ID > 0 {
		s.loadWidgetDetails(&w)
		return &w
	}

	return nil
}

func (s *Service) loadWidgetDetails(w *Widget) {
	_ = s.db.Select(&w.Recipients, "SELECT recipient FROM yapps_app_widgets_v3_recipients WHERE widget_id = ?", w.ID)
	_ = s.db.Select(&w.URL, "SELECT value FROM yapps_app_widgets_v3_urls WHERE widget_id = ?", w.ID)
}

// PushStat обрабатывает отправку формы виджета
func (s *Service) PushStat(p StatPayload, clientIP string) error {
	// 1. Получение получателей почты
	recipients := s.getWidgetRecipients(p.ID)

	// 2. Формирование темы и тела письма
	var siteName string
	var siteID int
	if p.ID > 0 {
		_ = s.db.Get(&siteID, "SELECT site_id FROM yapps_app_widgets_v3 WHERE id = ?", p.ID)
		if siteID > 0 {
			_ = s.db.Get(&siteName, "SELECT ru_name FROM yapps_sites WHERE id = ?", siteID)
		}
	}
	if siteName == "" {
		siteName = "Юг-Авто"
	}

	subject := fmt.Sprintf("Сайт: %s. %s", siteName, p.EventName)
	var body strings.Builder
	body.WriteString(fmt.Sprintf("<h3>Сайт: %s. %s</h3>", siteName, p.EventName))
	if p.WidgetFormName != "" {
		body.WriteString(fmt.Sprintf("Имя: %s<br />", p.WidgetFormName))
	}
	phoneFormatted := formatPhoneOut(p.WidgetFormPhone)
	body.WriteString(fmt.Sprintf("Телефон: %s<br />", phoneFormatted))
	timeStr := "Сейчас"
	if p.WidgetFormTime != "" && p.WidgetFormTime != "now" {
		timeStr = p.WidgetFormTime
	}
	body.WriteString(fmt.Sprintf("Время звонка: %s<br />", timeStr))
	body.WriteString("<br /><br />")
	title := p.SourceTitle
	if title == "" {
		title = p.Source
	}
	body.WriteString(fmt.Sprintf("Страница-источник: <a href=\"%s\" target=\"_blank\">%s</a>", p.Source, title))

	// 3. Отправка письма через /usr/sbin/sendmail (с разделителями CRLF \r\n)
	if len(recipients) > 0 {
		if err := sendMail(subject, body.String(), recipients); err != nil {
			log.Printf("[widgets] sendmail error: %v", err)
		}
	}

	// 4. Запись лога в JSON
	s.logForm(p)

	// 5. Серверная асинхронная регистрация в Calltouch
	if p.CTSiteID != "" && p.WidgetFormPhone != "" {
		go s.sendCalltouch(p)
	}

	return nil
}

func (s *Service) getWidgetRecipients(widgetID int) []string {
	var recs []string
	if widgetID > 0 {
		_ = s.db.Select(&recs, "SELECT recipient FROM yapps_app_widgets_v3_recipients WHERE widget_id = ?", widgetID)
	}
	if len(recs) == 0 {
		recs = []string{"widgets@apps.yug-avto.ru"}
	}
	return recs
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
		fmt.Fprint(stdin, "From: widgets@apps.yug-avto.ru\r\n")
		fmt.Fprintf(stdin, "Subject: =?UTF-8?B?%s?=\r\n", base64.StdEncoding.EncodeToString([]byte(subject)))
		fmt.Fprint(stdin, "MIME-Version: 1.0\r\n")
		fmt.Fprint(stdin, "Content-Type: text/html; charset=utf-8\r\n")
		fmt.Fprint(stdin, "\r\n")
		fmt.Fprint(stdin, body)
	}()

	return cmd.Run()
}

func (s *Service) logForm(p StatPayload) {
	now := time.Now()
	dir := filepath.Join(s.projectRoot, "core", "YApps", "Logs", "Widgets3", now.Format("2006"), now.Format("01"))
	_ = os.MkdirAll(dir, 0755)

	filePath := filepath.Join(dir, now.Format("02")+".json")
	u, _ := url.Parse(p.Source)
	host := "unknown"
	if u != nil && u.Host != "" {
		host = u.Host
	}

	logs := make(map[string]map[string]interface{})
	if data, err := os.ReadFile(filePath); err == nil {
		_ = json.Unmarshal(data, &logs)
	}

	if logs[host] == nil {
		logs[host] = make(map[string]interface{})
	}
	logs[host][now.Format("15:04:05")] = p

	data, err := json.MarshalIndent(logs, "", "  ")
	if err == nil {
		_ = os.WriteFile(filePath, data, 0644)
	}
}

func (s *Service) sendCalltouch(p StatPayload) {
	apiURL := fmt.Sprintf("https://api.calltouch.ru/calls-service/RestAPI/requests/%s/register/", p.CTSiteID)
	data := url.Values{}
	data.Set("fio", p.WidgetFormName)
	data.Set("phoneNumber", formatDigitsOnly(p.WidgetFormPhone))
	data.Set("subject", "Форма виджета "+p.EventName)
	data.Set("requestUrl", p.Source)
	if p.CTSessionID != "" {
		data.Set("sessionId", p.CTSessionID)
	}

	// Использование http.Client с таймаутом 10с для предотвращения утечки горутин
	client := &http.Client{
		Timeout: 10 * time.Second,
	}
	resp, err := client.PostForm(apiURL, data)
	if err == nil && resp != nil {
		_ = resp.Body.Close()
	}
}

func formatPhoneOut(p string) string {
	digits := formatDigitsOnly(p)
	if len(digits) == 11 && (digits[0] == '7' || digits[0] == '8') {
		return fmt.Sprintf("+7 (%s) %s-%s-%s", digits[1:4], digits[4:7], digits[7:9], digits[9:11])
	}
	return p
}

func formatDigitsOnly(s string) string {
	var sb strings.Builder
	for _, r := range s {
		if r >= '0' && r <= '9' {
			sb.WriteRune(r)
		}
	}
	return sb.String()
}

func getWord(q int, flag string) string {
	res := map[string][]string{
		"d":    {"день", "дня", "дней"},
		"h":    {"час", "часа", "часов"},
		"m":    {"минута", "минуты", "минут"},
		"s":    {"секунда", "секунды", "секунд"},
		"a":    {"автомобиль", "автомобиля", "автомобилей"},
		"hot":  {"горячее предложение", "горячих предложения", "горячих предложений"},
		"offer": {"предложение", "предложения", "предложений"},
	}

	words, found := res[flag]
	if !found {
		return ""
	}

	mod10 := q % 10
	mod100 := q % 100

	if mod10 == 1 && mod100 != 11 {
		return words[0]
	}
	if mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20) {
		return words[1]
	}
	return words[2]
}

func hexToRgb(hex string) string {
	hex = strings.TrimPrefix(hex, "#")
	if len(hex) == 3 {
		hex = string([]byte{hex[0], hex[0], hex[1], hex[1], hex[2], hex[2]})
	}
	if len(hex) != 6 {
		return "0,0,0"
	}
	r, _ := strconv.ParseInt(hex[0:2], 16, 32)
	g, _ := strconv.ParseInt(hex[2:4], 16, 32)
	b, _ := strconv.ParseInt(hex[4:6], 16, 32)
	return fmt.Sprintf("%d,%d,%d", r, g, b)
}

func escapeDoubleQuotes(s string) string {
	return strings.ReplaceAll(s, "\"", "\\\"")
}

func (s *Service) readHtmlFile(path string) string {
	data, err := os.ReadFile(filepath.Join(s.projectRoot, "core", "YApps", "html", "Widgets3", path))
	if err != nil {
		return ""
	}
	return string(data)
}

func (s *Service) readMainHtml() string {
	data, err := os.ReadFile(filepath.Join(s.projectRoot, "core", "YApps", "html", "Widgets3.html"))
	if err != nil {
		return ""
	}
	return string(data)
}

func (s *Service) buildCSS(st *WidgetSettings) string {
	cssPath := filepath.Join(s.projectRoot, "core", "YApps", "css", "Widgets3.css")
	data, err := os.ReadFile(cssPath)
	if err != nil {
		return ""
	}
	css := string(data)

	// Замены цветов в CSS
	css = strings.ReplaceAll(css, "%% COLOR_BG %%", st.ColorWidgetBG)
	css = strings.ReplaceAll(css, "%% COLOR_TEXT %%", st.ColorWidgetText)
	css = strings.ReplaceAll(css, "%% COLOR_ICON_LIGHT %%", st.ColorIconLight)
	css = strings.ReplaceAll(css, "%% COLOR_ICON_DARK %%", st.ColorIconDark)
	css = strings.ReplaceAll(css, "%% COLOR_ICON_HOVER_LIGHT %%", st.ColorIconHoverLight)
	css = strings.ReplaceAll(css, "%% COLOR_ICON_HOVER_DARK %%", st.ColorIconHoverDark)
	css = strings.ReplaceAll(css, "%% COLOR_ICON_HOVER_SHADOW %%", hexToRgb(st.ColorIconHoverShadow))
	css = strings.ReplaceAll(css, "%% COLOR_ICON_BUTTON %%", hexToRgb(st.ColorIconButton))
	css = strings.ReplaceAll(css, "%% COLOR_ICON_HOVER_BUTTON %%", st.ColorIconHoverButton)
	css = strings.ReplaceAll(css, "%% COLOR_ICON_HOVER_BUTTON_SHADOW %%", hexToRgb(st.ColorIconHoverButtonShad))
	css = strings.ReplaceAll(css, "%% COLOR_FIELD_BORDER %%", st.ColorWidgetFieldBorder)
	css = strings.ReplaceAll(css, "%% COLOR_FIELD_BG %%", st.ColorWidgetFieldBG)
	css = strings.ReplaceAll(css, "%% COLOR_BUTTON %%", st.ColorWidgetButton)

	btnText := st.ColorWidgetButtonText
	if btnText == "" {
		btnText = "#ffffff"
	}
	css = strings.ReplaceAll(css, "%% COLOR_BUTTON_TEXT %%", btnText)

	btnHover := st.ColorWidgetButtonHover
	css = strings.ReplaceAll(css, "%% COLOR_BUTTON_HOVER %%", btnHover)

	btnHoverText := st.ColorWidgetButtonHoverTx
	if btnHoverText == "" {
		btnHoverText = "#ffffff"
	}
	css = strings.ReplaceAll(css, "%% COLOR_BUTTON_HOVER_TEXT %%", btnHoverText)

	css = strings.ReplaceAll(css, "%% COLOR_TERMS %%", st.ColorWidgetTerms)
	css = strings.ReplaceAll(css, "%% COLOR_TIMER_BG %%", st.ColorWidgetTimerBG)
	css = strings.ReplaceAll(css, "%% COLOR_TIMER_TEXT %%", st.ColorWidgetTimerText)
	css = strings.ReplaceAll(css, "%% COLOR_ERROR %%", st.ColorWidgetError)

	mBottom := st.MarginBottom
	if mBottom == "" {
		mBottom = "20px"
	}
	css = strings.ReplaceAll(css, "%% MARGIN_BOTTOM %%", mBottom)

	mRight := st.MarginRight
	if mRight == "" {
		mRight = "20px"
	}
	css = strings.ReplaceAll(css, "%% MARGIN_RIGHT %%", mRight)

	// Подключение кастомных AddStyles CSS
	addStylesCSSPath := filepath.Join(s.projectRoot, "upload", "Widgets3", "AddStyles", strconv.Itoa(st.SiteID)+".css")
	if data, err := os.ReadFile(addStylesCSSPath); err == nil {
		css += "\n" + string(data)
	}

	return css
}

func (s *Service) buildHTML(st *WidgetSettings, site *Site, cb *Widget, lg *Widget, useNV, useCIS bool, land *Land) string {
	mainHtml := s.readMainHtml()
	if mainHtml == "" {
		return ""
	}

	// 1. Кнопки
	btnCB := ""
	if cb != nil && st.UseCB == 1 && (land == nil || land.UseCB == 1) {
		btnCB = s.readHtmlFile("Buttons/CB.html")
		btnCB = strings.ReplaceAll(btnCB, "%% CLUE %%", st.CBClue)
	}

	btnLG := ""
	if lg != nil && st.UseLG == 1 && (land == nil || land.UseLG == 1) {
		btnLG = s.readHtmlFile("Buttons/LG.html")
		btnLG = strings.ReplaceAll(btnLG, "%% CLUE %%", st.LGClue)
	}

	btnNV := ""
	if useNV && (land == nil || land.UseNV == 1) {
		btnNV = s.readHtmlFile("Buttons/NV.html")
		btnNV = strings.ReplaceAll(btnNV, "%% COORDS_LON %%", st.NVCoordsLon)
		btnNV = strings.ReplaceAll(btnNV, "%% COORDS_LAT %%", st.NVCoordsLat)
		btnNV = strings.ReplaceAll(btnNV, "%% CLUE %%", st.NVClue)
	}

	btnCIS := ""
	useAV := true
	if land != nil && land.UseAV == 0 {
		useAV = false
	}
	if useCIS && useAV {
		btnCIS = s.readHtmlFile("Buttons/CIS.html")
		btnCIS = strings.ReplaceAll(btnCIS, "%% CIS_LINK %%", st.CISLink)
		btnCIS = strings.ReplaceAll(btnCIS, "%% CLUE %%", st.CISClue)
	}

	// 2. Виджеты (формы)
	widgetCB := ""
	if cb != nil && st.UseCB == 1 && (land == nil || land.UseCB == 1) {
		widgetCB = s.readHtmlFile("CB.html")
		widgetCB = strings.ReplaceAll(widgetCB, "%% WIDGET_ID %%", strconv.Itoa(cb.ID))
		widgetCB = strings.ReplaceAll(widgetCB, "%% WIDGET_TITLE %%", escapeDoubleQuotes(cb.CBTitle))
		widgetCB = strings.ReplaceAll(widgetCB, "%% WIDGET_TEXT %%", escapeDoubleQuotes(cb.CBText))
		widgetCB = strings.ReplaceAll(widgetCB, "%% WIDGET_BUTTON %%", cb.CBButtonText)
		widgetCB = strings.ReplaceAll(widgetCB, "%% WIDGET_FORM_SUCCESS %%", st.FormSuccess)
		widgetCB = strings.ReplaceAll(widgetCB, "%% WIDGET_FORM_ERROR %%", st.FormError)

		cbImgBack := cb.CBImageBack
		if cbImgBack == "" {
			cbImgBack = "/upload/Widgets3/cb_back.svg"
		}
		widgetCB = strings.ReplaceAll(widgetCB, "%% WIDGET_IMAGE_BACK %%", cbImgBack)

		cbImgFront := cb.CBImageFront
		if cbImgFront == "" {
			cbImgFront = "/upload/Widgets3/cb_front.png"
		}
		widgetCB = strings.ReplaceAll(widgetCB, "%% WIDGET_IMAGE_FRONT %%", cbImgFront)

		termPers := cb.TermPersonal
		if termPers == "" {
			termPers = st.TermPersonal
		}
		widgetCB = strings.ReplaceAll(widgetCB, "%% WIDGET_TERM_PERSONAL %%", termPers)

		termComm := cb.TermPolitic
		if termComm == "" {
			termComm = st.TermCommunications
		}
		widgetCB = strings.ReplaceAll(widgetCB, "%% WIDGET_TERM_COMMUNICATIONS %%", termComm)

		// Генерация %% WIDGET_HOUR_OPTIONS %%
		hourOpts := strings.Builder{}
		currHour := time.Now().Hour()
		for i := currHour; i < 20; i++ {
			sel := ""
			if i == currHour {
				sel = "selected"
			}
			hourOpts.WriteString(fmt.Sprintf("<option value='%02d' %s>%02d</option>", i, sel, i))
		}
		widgetCB = strings.ReplaceAll(widgetCB, "%% WIDGET_HOUR_OPTIONS %%", hourOpts.String())
	}

	widgetLG := ""
	if lg != nil && st.UseLG == 1 && (land == nil || land.UseLG == 1) {
		widgetLG = s.readHtmlFile("LG.html")
		widgetLG = strings.ReplaceAll(widgetLG, "%% WIDGET_ID %%", strconv.Itoa(lg.ID))
		widgetLG = strings.ReplaceAll(widgetLG, "%% WIDGET_TITLE %%", escapeDoubleQuotes(lg.LGTitle))
		widgetLG = strings.ReplaceAll(widgetLG, "%% WIDGET_SUBTITLE %%", escapeDoubleQuotes(lg.LGSubtitle))
		widgetLG = strings.ReplaceAll(widgetLG, "%% WIDGET_TEXT %%", escapeDoubleQuotes(lg.LGText))
		widgetLG = strings.ReplaceAll(widgetLG, "%% WIDGET_BUTTON %%", lg.LGButtonText)
		widgetLG = strings.ReplaceAll(widgetLG, "%% WIDGET_FORM_SUCCESS %%", st.FormSuccess)
		widgetLG = strings.ReplaceAll(widgetLG, "%% WIDGET_FORM_ERROR %%", st.FormError)
		widgetLG = strings.ReplaceAll(widgetLG, "%% WIDGET_MARKING %%", escapeDoubleQuotes(lg.LGMarking))
		widgetLG = strings.ReplaceAll(widgetLG, "%% WIDGET_IMAGE_BACK %%", lg.LGImageBack)
		widgetLG = strings.ReplaceAll(widgetLG, "%% WIDGET_IMAGE_FRONT %%", lg.LGImageFront)

		termPers := lg.TermPersonal
		if termPers == "" {
			termPers = st.TermPersonal
		}
		widgetLG = strings.ReplaceAll(widgetLG, "%% WIDGET_TERM_PERSONAL %%", termPers)

		termComm := lg.TermPolitic
		if termComm == "" {
			termComm = st.TermCommunications
		}
		widgetLG = strings.ReplaceAll(widgetLG, "%% WIDGET_TERM_COMMUNICATIONS %%", termComm)

		// Таймер %% WIDGET_TIMER %%
		timerHTML := ""
		if lg.LGTimerUse == 1 {
			timerHTML = s.readHtmlFile("Widgets/Timer.html")
			diff := lg.LGTimer - time.Now().Unix()
			if diff < 0 {
				diff = 0
			}

			d := diff / (24 * 60 * 60)
			h := (diff - d*24*60*60) / (60 * 60)
			m := (diff - d*24*60*60 - h*60*60) / 60
			se := diff - d*24*60*60 - h*60*60 - m*60

			timerHTML = strings.ReplaceAll(timerHTML, "%% D %%", strconv.FormatInt(d, 10))
			timerHTML = strings.ReplaceAll(timerHTML, "%% D_D %%", getWord(int(d), "d"))
			timerHTML = strings.ReplaceAll(timerHTML, "%% H %%", strconv.FormatInt(h, 10))
			timerHTML = strings.ReplaceAll(timerHTML, "%% H_D %%", getWord(int(h), "h"))
			timerHTML = strings.ReplaceAll(timerHTML, "%% M %%", strconv.FormatInt(m, 10))
			timerHTML = strings.ReplaceAll(timerHTML, "%% M_D %%", getWord(int(m), "m"))
			timerHTML = strings.ReplaceAll(timerHTML, "%% S %%", strconv.FormatInt(se, 10))
			timerHTML = strings.ReplaceAll(timerHTML, "%% S_D %%", getWord(int(se), "s"))
		}
		widgetLG = strings.ReplaceAll(widgetLG, "%% WIDGET_TIMER %%", timerHTML)
	}

	// 3. Сборка общего HTML-контейнера
	mainHtml = strings.ReplaceAll(mainHtml, "%% BUTTON_LG %%", btnLG)
	mainHtml = strings.ReplaceAll(mainHtml, "%% BUTTON_NV %%", btnNV)
	mainHtml = strings.ReplaceAll(mainHtml, "%% BUTTON_CIS %%", btnCIS)
	mainHtml = strings.ReplaceAll(mainHtml, "%% BUTTON_CB %%", btnCB)
	mainHtml = strings.ReplaceAll(mainHtml, "%% WIDGET_CB %%", widgetCB)
	mainHtml = strings.ReplaceAll(mainHtml, "%% WIDGET_LG %%", widgetLG)

	return mainHtml
}

func (s *Service) buildJS(st *WidgetSettings, site *Site, cb *Widget, lg *Widget, htmlContent, cssContent string) string {
	jsPath := filepath.Join(s.projectRoot, "core", "YApps", "js", "Widgets3.js")
	data, err := os.ReadFile(jsPath)
	if err != nil {
		return ""
	}
	jsStr := string(data)

	// Подмена плейсхолдеров в JS-скрипте
	jsStr = strings.ReplaceAll(jsStr, "%% CSS %%", minifyCSS(cssContent))
	jsStr = strings.ReplaceAll(jsStr, "%% HTML %%", minifyHTML(htmlContent))
	jsStr = strings.ReplaceAll(jsStr, `"%% FORM_TIMEOUT %%"`, st.FormTimeout)
	jsStr = strings.ReplaceAll(jsStr, `"%% CB_TIMEOUT %%"`, st.CBTimeout)
	jsStr = strings.ReplaceAll(jsStr, `"%% LG_TIMEOUT_1 %%"`, st.LGTimeout1)
	jsStr = strings.ReplaceAll(jsStr, `"%% LG_TIMEOUT_2 %%"`, st.LGTimeout2)
	jsStr = strings.ReplaceAll(jsStr, "%% CT_ID %%", site.CalltouchID)
	jsStr = strings.ReplaceAll(jsStr, "%% CT_S %%", site.CalltouchSess)

	yaID := `""`
	if site.YandexID != "" {
		yaID = `"` + site.YandexID + `"`
	}
	jsStr = strings.ReplaceAll(jsStr, `"%% YA_ID %%"`, yaID)

	hasCB := "false"
	if cb != nil && st.UseCB == 1 {
		hasCB = "true"
	}
	jsStr = strings.ReplaceAll(jsStr, `"%% CB %%"`, hasCB)

	hasLG := "false"
	if lg != nil && st.UseLG == 1 {
		hasLG = "true"
	}
	jsStr = strings.ReplaceAll(jsStr, `"%% LG %%"`, hasLG)

	// Замена захардкоженного токена во фронтенде на динамический
	jsStr = strings.ReplaceAll(jsStr, "ef6541490c8bb9d481d37020b6a1953e", s.apiToken)

	// Сборка бандла с библиотеками
	var script strings.Builder
	if st.UseLibs == 1 {
		jqueryPath := filepath.Join(s.projectRoot, "pub", "libs", "jquery", "3.7.1", "jquery.min.js")
		if jqData, err := os.ReadFile(jqueryPath); err == nil {
			script.Write(jqData)
			script.WriteString("\n")
		}

		cookiePath := filepath.Join(s.projectRoot, "pub", "libs", "jquery-cookie", "1.4.1", "jquery.cookie.min.js")
		if cookieData, err := os.ReadFile(cookiePath); err == nil {
			script.Write(cookieData)
			script.WriteString("\n")
		}
	}

	script.WriteString(jsStr)

	// Подключение кастомных AddStyles JS
	addStylesJSPath := filepath.Join(s.projectRoot, "upload", "Widgets3", "AddStyles", strconv.Itoa(st.SiteID)+".js")
	if data, err := os.ReadFile(addStylesJSPath); err == nil {
		script.WriteString("\n" + string(data))
	}

	return script.String()
}

func minifyCSS(css string) string {
	css = strings.ReplaceAll(css, "\n", "")
	css = strings.ReplaceAll(css, "\r", "")
	for strings.Contains(css, "  ") {
		css = strings.ReplaceAll(css, "  ", " ")
	}
	return css
}

func minifyHTML(html string) string {
	html = strings.ReplaceAll(html, "\n", "")
	html = strings.ReplaceAll(html, "\r", "")
	for strings.Contains(html, "  ") {
		html = strings.ReplaceAll(html, "  ", " ")
	}
	return html
}
