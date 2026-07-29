package widgets

// WidgetSettings представляет настройки виджета для сайта (yapps_app_widgets_v3_settings)
type WidgetSettings struct {
	ID                       int    `db:"id" json:"id"`
	SiteID                   int    `db:"site_id" json:"site_id"`
	Active                   int    `db:"active" json:"active"`
	UseLibs                  int    `db:"use_libs" json:"use_libs"`
	UseCB                    int    `db:"use_cb" json:"use_cb"`
	UseLG                    int    `db:"use_lg" json:"use_lg"`
	UseNV                    int    `db:"use_nv" json:"use_nv"`
	UseCIS                   int    `db:"use_cis" json:"use_cis"`
	TermChecked              int    `db:"term_checked" json:"term_checked"`
	CBClue                   string `db:"cb_clue" json:"cb_clue"`
	LGClue                   string `db:"lg_clue" json:"lg_clue"`
	NVClue                   string `db:"nv_clue" json:"nv_clue"`
	NVCoordsLat              string `db:"nv_coords_lat" json:"nv_coords_lat"`
	NVCoordsLon              string `db:"nv_coords_lon" json:"nv_coords_lon"`
	CISClue                  string `db:"cis_clue" json:"cis_clue"`
	CISLink                  string `db:"cis_link" json:"cis_link"`
	FormSuccess              string `db:"form_success" json:"form_success"`
	FormError                string `db:"form_error" json:"form_error"`
	TermPersonal             string `db:"term_personal" json:"term_personal"`
	TermCommunications       string `db:"term_communications" json:"term_communications"`
	ColorWidgetBG            string `db:"color_widget_bg" json:"color_widget_bg"`
	ColorWidgetText          string `db:"color_widget_text" json:"color_widget_text"`
	ColorIconLight           string `db:"color_icon_light" json:"color_icon_light"`
	ColorIconDark            string `db:"color_icon_dark" json:"color_icon_dark"`
	ColorIconHoverLight      string `db:"color_icon_hover_light" json:"color_icon_hover_light"`
	ColorIconHoverDark       string `db:"color_icon_hover_dark" json:"color_icon_hover_dark"`
	ColorIconHoverShadow     string `db:"color_icon_hover_shadow" json:"color_icon_hover_shadow"`
	ColorIconButton          string `db:"color_icon_button" json:"color_icon_button"`
	ColorIconHoverButton     string `db:"color_icon_hover_button" json:"color_icon_hover_button"`
	ColorIconHoverButtonShad string `db:"color_icon_hover_button_shadow" json:"color_icon_hover_button_shadow"`
	ColorWidgetFieldBorder   string `db:"color_widget_field_border" json:"color_widget_field_border"`
	ColorWidgetFieldBG       string `db:"color_widget_field_bg" json:"color_widget_field_bg"`
	ColorWidgetButton        string `db:"color_widget_button" json:"color_widget_button"`
	ColorWidgetButtonText    string `db:"color_widget_button_text" json:"color_widget_button_text"`
	ColorWidgetButtonHover   string `db:"color_widget_button_hover" json:"color_widget_button_hover"`
	ColorWidgetButtonHoverTx string `db:"color_widget_button_hover_text" json:"color_widget_button_hover_text"`
	ColorWidgetTerms         string `db:"color_widget_terms" json:"color_widget_terms"`
	ColorWidgetTimerBG       string `db:"color_widget_timer_bg" json:"color_widget_timer_bg"`
	ColorWidgetTimerText     string `db:"color_widget_timer_text" json:"color_widget_timer_text"`
	ColorWidgetError         string `db:"color_widget_error" json:"color_widget_error"`
	MarginBottom             string `db:"margin_bottom" json:"margin_bottom"`
	MarginRight              string `db:"margin_right" json:"margin_right"`
	FormTimeout              string `db:"form_timeout" json:"form_timeout"`
	CBTimeout                string `db:"cb_timeout" json:"cb_timeout"`
	LGTimeout1               string `db:"lg_timeout_1" json:"lg_timeout_1"`
	LGTimeout2               string `db:"lg_timeout_2" json:"lg_timeout_2"`
}

// Widget представляет отдельный виджет (yapps_app_widgets_v3)
type Widget struct {
	ID            int    `db:"id" json:"id"`
	TypeID        int    `db:"type_id" json:"type_id"`
	SiteID        int    `db:"site_id" json:"site_id"`
	Active        int    `db:"active" json:"active"`
	Name          string `db:"name" json:"name"`
	PublicKey     string `db:"public_key" json:"public_key"`
	CBTitle       string `db:"cb_title" json:"cb_title"`
	CBText        string `db:"cb_text" json:"cb_text"`
	CBButtonText  string `db:"cb_button_text" json:"cb_button_text"`
	CBImageBack   string `db:"cb_image_back" json:"cb_image_back"`
	CBImageFront  string `db:"cb_image_front" json:"cb_image_front"`
	TermPersonal  string `db:"term_personal" json:"term_personal"`
	TermPolitic   string `db:"term_politic" json:"term_politic"`
	LGTitle       string `db:"lg_title" json:"lg_title"`
	LGSubtitle    string `db:"lg_subtitle" json:"lg_subtitle"`
	LGText        string `db:"lg_text" json:"lg_text"`
	LGButtonText  string `db:"lg_button_text" json:"lg_button_text"`
	LGMarking     string `db:"lg_marking" json:"lg_marking"`
	LGImageBack   string `db:"lg_image_back" json:"lg_image_back"`
	LGImageFront  string `db:"lg_image_front" json:"lg_image_front"`
	LGTimerUse    int    `db:"lg_timer_use" json:"lg_timer_use"`
	LGTimer       int64  `db:"lg_timer" json:"lg_timer"`
	Recipients    []string
	URL           []string
}

// Site представляет сайт в монолите (yapps_sites)
type Site struct {
	ID            int    `db:"id"`
	URL           string `db:"url"`
	RuName        string `db:"ru_name"`
	YandexID      string `db:"yandex_id"`
	CalltouchID   string `db:"calltouch_id"`
	CalltouchSess string `db:"calltouch_sess"`
}

// Land представляет настройки лендинга (yapps_app_lands)
type Land struct {
	ID            int    `db:"id"`
	URL           string `db:"url"`
	SiteID        int    `db:"site_id"`
	CalltouchID   string `db:"calltouch_id"`
	CalltouchSess string `db:"calltouch_sess"`
	UseCB         int    `db:"use_cb"`
	UseLG         int    `db:"use_lg"`
	UseNV         int    `db:"use_nv"`
	UseCIS        int    `db:"use_cis"`
	UseAV         int    `db:"use_av"`
}

// Showroom представляет настройки шоурума (yapps_showrooms)
type Showroom struct {
	ID     int    `db:"id"`
	URL    string `db:"url"`
	SiteID int    `db:"site_id"`
}

// WidgetShutdown представляет интервал отключения (yapps_app_widgets_shutdowns)
type WidgetShutdown struct {
	ID     int   `db:"id"`
	SiteID int   `db:"site_id"`
	Start  int64 `db:"start"`
	End    int64 `db:"end"`
}

// StatPayload структура входящих данных при отправке формы виджета
type StatPayload struct {
	ID              int    `json:"Id"`
	AppName         string `json:"AppName"`
	EventName       string `json:"EventName"`
	Source          string `json:"Source"`
	SourceTitle     string `json:"source_title"`
	WidgetFormName  string `json:"yapps-widget-form-name"`
	WidgetFormPhone string `json:"yapps-widget-form-phone"`
	WidgetFormTime  string `json:"yapps-widget-form-time"`
	CTSiteID        string `json:"CT_site_id"`
	CTSubject       string `json:"CT_subject"`
	CTSessionID     string `json:"CT_sessionId"`
	CTFio           string `json:"CT_fio"`
	CTPhoneNumber   string `json:"CT_phoneNumber"`
	CTRequestURL    string `json:"CT_requestUrl"`
}
