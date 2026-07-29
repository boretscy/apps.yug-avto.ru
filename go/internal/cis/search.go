package cis

import (
	"encoding/json"
	"fmt"
	"math"
	"net/http"
	"regexp"
	"strconv"
	"strings"
)

type SearchResult struct {
	Query        string                   `json:"query"`
	Filter       VehicleFilter            `json:"filter"`
	Parser       map[string][]interface{} `json:"parser"`
	Pseudo       map[string]PseudoEntry   `json:"pseudo"`
	Counts       SearchCounts             `json:"counts"`
	VehiclesNew  []VehicleRow             `json:"vehicles_new,omitempty"`
	VehiclesUsed []VehicleRow             `json:"vehicles_used,omitempty"`
}

type PseudoEntry struct {
	Name  string `json:"name"`
	Value string `json:"value"`
}

type SearchCounts struct {
	New  int `json:"new"`
	Used int `json:"used"`
}

var (
	stopWords = map[string]bool{
		"или": true, "и": true, "на": true, "с": true, "без": true,
		"or": true, "and": true, "the": true, "a": true, "an": true,
	}

	rangePatterns = []struct {
		re   *regexp.Regexp
		proc func([]string, *VehicleFilter)
	}{
		{regexp.MustCompile(`(?i)(?:не\s+)?(?:старше|новее|ранее|позже)\s+(\d{4})`),
			func(m []string, f *VehicleFilter) {
				y, _ := strconv.Atoi(m[1])
				if strings.Contains(strings.ToLower(m[0]), "старше") || strings.Contains(strings.ToLower(m[0]), "ранее") {
					f.YearTo = y
				} else {
					f.YearFrom = y
				}
			}},
		{regexp.MustCompile(`(?i)(?:до|после)\s+(\d{4})\s*(?:г(?:од(?:а)?)?)?`),
			func(m []string, f *VehicleFilter) {
				y, _ := strconv.Atoi(m[1])
				if strings.Contains(strings.ToLower(m[0]), "до") {
					f.YearTo = y
				} else {
					f.YearFrom = y
				}
			}},
		{regexp.MustCompile(`(\d{4})\s*[-–—]\s*(\d{4})`),
			func(m []string, f *VehicleFilter) {
				f.YearFrom, _ = strconv.Atoi(m[1])
				f.YearTo, _ = strconv.Atoi(m[2])
			}},
		{regexp.MustCompile(`(?i)(\d{4})\s*г(?:од(?:а)?)?`),
			func(m []string, f *VehicleFilter) {
				y, _ := strconv.Atoi(m[1])
				if y > 1900 && y < 2100 {
					f.YearTo = y
				}
			}},
		{regexp.MustCompile(`(?i)(?:до|не дороже|не более)\s+([\d\s]+)(?:\s*(?:млн?|тыс?яч?|руб(?:лей)?|₽|\$|€|евро|баксов|тыщ))?`),
			func(m []string, f *VehicleFilter) {
				val := parseHumanNumber(m[1]) * applyMultiplier(m[0])
				if val > 0 {
					f.PriceTo = val
				}
			}},
		{regexp.MustCompile(`(?i)(?:от|не меньше|дороже)\s+([\d\s]+)(?:\s*(?:млн?|тыс?яч?|руб(?:лей)?|₽|\$|€|евро|баксов|тыщ))?`),
			func(m []string, f *VehicleFilter) {
				val := parseHumanNumber(m[1]) * applyMultiplier(m[0])
				if val > 0 {
					f.PriceFrom = val
				}
			}},
		{regexp.MustCompile(`(?i)([\d\s]+)\s*(?:млн?|тыс?|тыщ)?\s*[-–—]\s*([\d\s]+)\s*(?:млн?|тыс?|тыщ)?`),
			func(m []string, f *VehicleFilter) {
				fromVal := parseHumanNumber(m[1]) * detectMultiplier(m[0])
				toVal := parseHumanNumber(m[2]) * detectMultiplier(m[0])
				if fromVal > 0 && toVal > 0 && fromVal < toVal {
					f.PriceFrom = fromVal
					f.PriceTo = toVal
				}
			}},
		{regexp.MustCompile(`(?i)(?:пробег[а]?|км|километраж)\s*(?:до|не более)\s*([\d\s]+)(?:\s*к(?:м)?)?`),
			func(m []string, f *VehicleFilter) {
				val := parseHumanNumber(m[1]) * 1000
				if val > 0 {
					f.MileageTo = int(val)
				}
			}},
		{regexp.MustCompile(`(?i)(?:до|не более)\s+([\d\s]+)(?:\s*к(?:м)?\s*|\s*)(?:км|километров|тыщ|тысяч)`),
			func(m []string, f *VehicleFilter) {
				val := parseHumanNumber(m[1]) * 1000
				if val > 0 {
					f.MileageTo = int(val)
				}
			}},
		{regexp.MustCompile(`(?i)пробег[а]?\s+([\d\s]+)(?:\s*км?|тыс?|тысяч)?`),
			func(m []string, f *VehicleFilter) {
				val := parseHumanNumber(m[1]) * 1000
				if val > 0 {
					f.MileageTo = int(val)
				}
			}},
		{regexp.MustCompile(`(?i)(?:объ[еe]м(?:ом|а|)?)\s*(?:до|от)?\s*([\d,.]+)\s*(?:л|литр)?`),
			func(m []string, f *VehicleFilter) {
				vol := parseFloatNormalized(m[1])
				if vol > 0 {
					f.VolumeTo = int(math.Round(vol * 1000))
					if strings.Contains(strings.ToLower(m[0]), "от") {
						f.VolumeFrom = f.VolumeTo
						f.VolumeTo = 0
					}
				}
			}},
		{regexp.MustCompile(`(?i)(\d{2,4})\s*[-–—]\s*(\d{2,4})\s*(?:сил|л[\.\s]*с[\.]*|лошадиных)`),
			func(m []string, f *VehicleFilter) {
				f.PowerFrom, _ = strconv.Atoi(m[1])
				f.PowerTo, _ = strconv.Atoi(m[2])
			}},
		{regexp.MustCompile(`(?i)(?:до|не более)\s+(\d{2,4})\s*(?:сил|л[\.\s]*с[\.]*|лошадиных)`),
			func(m []string, f *VehicleFilter) {
				f.PowerTo, _ = strconv.Atoi(m[1])
			}},
		{regexp.MustCompile(`(?i)(?:от|не менее)\s+(\d{2,4})\s*(?:сил|л[\.\s]*с[\.]*|лошадиных)`),
			func(m []string, f *VehicleFilter) {
				f.PowerFrom, _ = strconv.Atoi(m[1])
			}},
		{regexp.MustCompile(`(?i)(?:со\s+)?скидк[аи]`),
			func(m []string, f *VehicleFilter) {
				f.Tag = "discount"
			}},
		{regexp.MustCompile(`(?i)(?:в\s+)?наличии`),
			func(m []string, f *VehicleFilter) {
				f.Tag = "instock"
			}},
	}

	bigramBrands = map[string]string{
		"land rover":      "land-rover",
		"mercedes benz":   "mercedes-benz",
		"mercedes-benz":   "mercedes-benz",
		"alfa romeo":      "alfa-romeo",
		"great wall":      "great-wall",
		"haval pro":       "haval-pro",
		"haval city":      "haval-city",
		"range rover":     "land-rover",
		"rolls royce":     "rolls-royce",
		"aston martin":    "aston-martin",
	}

	multiWordDrive = map[string]string{
		"полный привод":    "full",
		"4wd":             "full",
		"4x4":             "full",
		"передний привод": "front",
		"задний привод":   "rear",
	}

	synonymMap = map[string]string{
		"мерс": "mercedes-benz", "мерин": "mercedes-benz", "мерседес": "mercedes-benz",
		"бэха": "bmw", "бумер": "bmw", "бмв": "bmw", "bimmer": "bmw",
		"ауди": "audi",
		"лексус": "lexus",
		"тойота": "toyota", "хонда": "honda",
		"ниссан": "nissan", "мазда": "mazda",
		"субару": "subaru", "митсу": "mitsubishi", "мицу": "mitsubishi",
		"форд": "ford", "шевроле": "chevrolet",
		"шкода": "skoda", "киа": "kia",
		"хёндэ": "hyundai", "хендай": "hyundai", "хендэ": "hyundai",
		"жигуль": "lada", "жигули": "lada", "ваз": "lada", "лада": "lada",
		"вольво": "volvo", "опель": "opel",
		"пежо": "peugeot", "рено": "renault",
		"ситроен": "citroen", "ягуар": "jaguar",
		"джип": "jeep", "черри": "chery",
		"джили": "geely", "хавал": "haval",
		"джетур": "jetour", "эксид": "exeed",
		"омода": "omoda", "лисян": "lixiang",
		"седан": "sedan", "универсал": "wagon",
		"хэтч": "hatchback", "хетч": "hatchback",
		"лифтбек": "liftback", "кабриолет": "cabriolet",
		"купе": "coupe", "кроссовер": "crossover",
		"внедорожник": "suv", "паркетник": "crossover",
		"минивен": "minivan", "минивэн": "minivan",
		"пикап": "pickup", "фургон": "van",
		"автомат": "auto", "механика": "manual", "ручка": "manual",
		"робот": "robot", "вариатор": "cvt",
		"акпп": "auto", "мкпп": "manual",
		"полный": "full", "передний": "front", "задний": "rear",
		"бензин": "petrol", "бензинов": "petrol",
		"дизель": "diesel", "гибрид": "gibrid",
		"электро": "electric", "газ": "gas",
		"белый": "white", "черный": "black", "чёрный": "black",
		"красный": "red", "синий": "blue",
		"зеленый": "green", "зелёный": "green",
		"серый": "gray", "серебрист": "silver",
		"голубой": "lightblue", "желтый": "yellow",
		"оранжевый": "orange", "коричнев": "brown",
		"бежевый": "beige", "фиолетов": "purple",
		"золотой": "gold", "бордов": "burgundy",
	}
)

func (s *Service) SearchVehicles(query string) (*SearchResult, error) {
	query = strings.TrimSpace(query)
	if query == "" {
		return nil, nil
	}

	// Detect if input is entirely Latin characters (possible Russian-keyboard typo)
	isLatin := true
	for _, r := range query {
		if r >= 0x0400 && r <= 0x04FF {
			isLatin = false
			break
		}
	}

	searchQuery := query
	if isLatin {
		searchQuery = fixLayout(query)
	}
	searchQuery = normalizeQuery(searchQuery)

	res := &SearchResult{
		Query:  searchQuery,
		Filter: VehicleFilter{},
		Parser: make(map[string][]interface{}),
		Pseudo: make(map[string]PseudoEntry),
	}

	s.extractRanges(searchQuery, &res.Filter)

	remainder := searchQuery
	for _, rp := range rangePatterns {
		remainder = rp.re.ReplaceAllString(remainder, " ")
	}

	words := tokenizeWords(remainder)
	if len(words) > 0 {
		s.matchWords(words, res)
	}

	s.fillCounts(res)

	// If no matches found with fixLayout, try without it
	if isLatin && res.Counts.New == 0 && res.Counts.Used == 0 {
		altQuery := normalizeQuery(query)
		if altQuery != searchQuery {
			altRes := &SearchResult{
				Query:  altQuery,
				Filter: VehicleFilter{},
				Parser: make(map[string][]interface{}),
				Pseudo: make(map[string]PseudoEntry),
			}
			s.extractRanges(altQuery, &altRes.Filter)
			remainderAlt := altQuery
			for _, rp := range rangePatterns {
				remainderAlt = rp.re.ReplaceAllString(remainderAlt, " ")
			}
			wordsAlt := tokenizeWords(remainderAlt)
			if len(wordsAlt) > 0 {
				s.matchWords(wordsAlt, altRes)
			}
			s.fillCounts(altRes)
			if altRes.Counts.New > 0 || altRes.Counts.Used > 0 {
				res = altRes
			}
		}
	}

	if res.Counts.New > 0 {
		f := res.Filter
		f.TypeID = 1
		f.Limit = 3
		q, args, _ := s.BuildVehicleQuery(f)
		s.db.Select(&res.VehiclesNew, q, args...)
	}
	if res.Counts.Used > 0 {
		f := res.Filter
		f.TypeID = 2
		f.Limit = 3
		q, args, _ := s.BuildVehicleQuery(f)
		s.db.Select(&res.VehiclesUsed, q, args...)
	}

	return res, nil
}

func (s *Service) extractRanges(query string, f *VehicleFilter) {
	for _, rp := range rangePatterns {
		matches := rp.re.FindAllStringSubmatch(query, -1)
		for _, m := range matches {
			rp.proc(m, f)
		}
	}
}

func tokenizeWords(s string) []string {

	raw := strings.Fields(s)
	var words []string
	for _, w := range raw {
		w = strings.TrimSpace(w)
		w = strings.ToLower(w)
		if len(w) <= 1 {
			continue
		}
		if stopWords[w] {
			continue
		}
		if _, err := strconv.Atoi(w); err == nil {
			continue
		}
		words = append(words, w)
	}
	return words
}

func (s *Service) matchWords(words []string, res *SearchResult) {
	used := make(map[int]bool)

	for i := 0; i < len(words)-1; i++ {
		if used[i] || used[i+1] {
			continue
		}
		bigram := strings.ToLower(words[i] + " " + words[i+1])
		if code, ok := bigramBrands[bigram]; ok {
			if b := s.findBrandByCode(code); b != nil {
				res.Parser["brand"] = append(res.Parser["brand"], b)
				res.Filter.Brand = append(res.Filter.Brand, b.Code)
				res.Pseudo["brand"] = PseudoEntry{"Бренд", b.Name}
				used[i] = true
				used[i+1] = true
				continue
			}
		}
		if code, ok := multiWordDrive[bigram]; ok {
			res.Parser["drive"] = append(res.Parser["drive"], map[string]string{"code": code})
			res.Filter.Drive = append(res.Filter.Drive, code)
			res.Pseudo["drive"] = PseudoEntry{"Привод", bigram}
			used[i] = true
			used[i+1] = true
			continue
		}
	}

	matchedBrand := false
	for i, w := range words {
		if used[i] {
			continue
		}
		canonical := synonymMap[w]
		if canonical != "" {
			w = canonical
		}
		if !matchedBrand && s.matchBrand(w, res) {
			used[i] = true
			matchedBrand = true
			continue
		}
		if matchedBrand && s.matchModel(w, res) {
			used[i] = true
			continue
		}
		if s.matchEngineSimple(w, res) {
			used[i] = true
			continue
		}
		if s.matchColorSimple(w, res) {
			used[i] = true
			continue
		}
		if s.matchBodySimple(w, res) {
			used[i] = true
			continue
		}
		if s.matchTransmissionSimple(w, res) {
			used[i] = true
			continue
		}
		if s.matchDriveSimple(w, res) {
			used[i] = true
			continue
		}
	}
}

func (s *Service) matchBrand(w string, res *SearchResult) bool {
	b := s.findBrand(w)
	if b != nil {
		res.Parser["brand"] = append(res.Parser["brand"], b)
		res.Filter.Brand = append(res.Filter.Brand, b.Code)
		res.Pseudo["brand"] = PseudoEntry{"Бренд", b.Name}
		return true
	}
	return false
}

func (s *Service) findBrand(q string) *Brand {
	q = strings.ToLower(q)
	brands := s.loadBrands()
	for i := range brands {
		if strings.Contains(strings.ToLower(brands[i].Name), q) ||
			strings.Contains(strings.ToLower(brands[i].RuName), q) ||
			strings.Contains(strings.ToLower(brands[i].Code), q) {
			return &brands[i]
		}
	}
	return nil
}

func (s *Service) findBrandByCode(code string) *Brand {
	brands := s.loadBrands()
	for i := range brands {
		if strings.EqualFold(brands[i].Code, code) {
			return &brands[i]
		}
	}
	return nil
}

func (s *Service) loadBrands() []Brand {
	var brands []Brand
	s.db.Select(&brands, "SELECT id, ext_id, code, name, ru_name FROM yapps_app_cis_brands")
	return brands
}

func (s *Service) ensureRefs() {
	if len(s.bodies) == 0 {
		s.loadReferenceData()
	}
}

func (s *Service) matchModel(w string, res *SearchResult) bool {
	if len(res.Parser["brand"]) == 0 {
		return false
	}
	w = strings.ToLower(w)
	matched := false
	for _, b := range res.Parser["brand"] {
		brand, ok := b.(*Brand)
		if !ok {
			continue
		}
		for _, table := range []string{"yapps_app_cis_models_new", "yapps_app_cis_models_used"} {
			var models []Model
			s.db.Select(&models, fmt.Sprintf(
				"SELECT id, ext_id, brand_id, code, name, ru_name FROM %s WHERE brand_id = ?", table), brand.ID)
			for _, m := range models {
				if strings.Contains(strings.ToLower(m.Name), w) ||
					strings.Contains(strings.ToLower(m.RuName), w) ||
					strings.Contains(strings.ToLower(m.Code), w) {
					res.Parser["model"] = append(res.Parser["model"], m)
					res.Filter.Model = append(res.Filter.Model, m.Code)
					if !matched {
						res.Pseudo["model"] = PseudoEntry{"Модель", m.Name}
					}
					matched = true
				}
			}
		}
	}
	return matched
}

func (s *Service) matchColorSimple(w string, res *SearchResult) bool {
	s.ensureRefs()
	w = strings.ToLower(w)
	for _, c := range s.colors {
		if strings.Contains(strings.ToLower(c.Name), w) || strings.Contains(strings.ToLower(c.Code), w) {
			res.Parser["color"] = append(res.Parser["color"], c)
			res.Filter.Color = append(res.Filter.Color, c.Code)
			res.Pseudo["color"] = PseudoEntry{"Цвет", c.Name}
			return true
		}
	}
	return false
}

func (s *Service) matchBodySimple(w string, res *SearchResult) bool {
	s.ensureRefs()
	w = strings.ToLower(w)
	for _, b := range s.bodies {
		if strings.Contains(strings.ToLower(b.Name), w) || strings.Contains(strings.ToLower(b.Code), w) {
			res.Parser["body"] = append(res.Parser["body"], b)
			res.Filter.Body = []string{b.Code}
			res.Pseudo["body"] = PseudoEntry{"Кузов", b.Name}
			return true
		}
	}
	return false
}

func (s *Service) matchTransmissionSimple(w string, res *SearchResult) bool {
	s.ensureRefs()
	w = strings.ToLower(w)
	for _, t := range s.transmissions {
		if strings.Contains(strings.ToLower(t.Name), w) || strings.Contains(strings.ToLower(t.Code), w) {
			res.Parser["transmission"] = append(res.Parser["transmission"], t)
			res.Filter.Transmission = append(res.Filter.Transmission, t.Code)
			res.Pseudo["transmission"] = PseudoEntry{"КПП", t.Name}
			return true
		}
	}
	return false
}

func (s *Service) matchDriveSimple(w string, res *SearchResult) bool {
	s.ensureRefs()
	w = strings.ToLower(w)
	for _, d := range s.drives {
		if strings.Contains(strings.ToLower(d.Name), w) || strings.Contains(strings.ToLower(d.Code), w) {
			res.Parser["drive"] = append(res.Parser["drive"], d)
			res.Filter.Drive = append(res.Filter.Drive, d.Code)
			res.Pseudo["drive"] = PseudoEntry{"Привод", d.Name}
			return true
		}
	}
	return false
}

func (s *Service) matchEngineSimple(w string, res *SearchResult) bool {
	s.ensureRefs()
	w = strings.ToLower(w)
	for _, e := range s.engines {
		if strings.Contains(strings.ToLower(e.Name), w) || strings.Contains(strings.ToLower(e.Code), w) {
			res.Parser["engine"] = append(res.Parser["engine"], e)
			res.Filter.Engine = append(res.Filter.Engine, e.Code)
			res.Pseudo["engine"] = PseudoEntry{"Двигатель", e.Name}
			return true
		}
	}
	return false
}

func (s *Service) fillCounts(res *SearchResult) {
	if len(res.Filter.Brand) == 0 && len(res.Filter.Model) == 0 &&
		res.Filter.PriceFrom == 0 && res.Filter.PriceTo == 0 &&
		res.Filter.YearFrom == 0 && res.Filter.YearTo == 0 &&
		res.Filter.PowerFrom == 0 && res.Filter.PowerTo == 0 &&
		res.Filter.MileageTo == 0 && len(res.Filter.Transmission) == 0 &&
		len(res.Filter.Engine) == 0 && len(res.Filter.Drive) == 0 &&
		len(res.Filter.Body) == 0 && len(res.Filter.Color) == 0 {
		res.Counts = SearchCounts{}
		return
	}

	newF := res.Filter
	newF.TypeID = 1
	q, args, _ := s.BuildVehicleQuery(newF)
	s.db.Get(&res.Counts.New, "SELECT COALESCE(COUNT(*), 0) FROM ("+q+") AS cnt", args...)

	usedF := res.Filter
	usedF.TypeID = 2
	q2, args2, _ := s.BuildVehicleQuery(usedF)
	s.db.Get(&res.Counts.Used, "SELECT COALESCE(COUNT(*), 0) FROM ("+q2+") AS cnt", args2...)
}

func parseHumanNumber(s string) float64 {
	s = strings.TrimSpace(s)
	s = strings.ReplaceAll(s, " ", "")
	s = strings.ReplaceAll(s, "\u00a0", "")
	v, err := strconv.ParseFloat(s, 64)
	if err != nil {
		return 0
	}
	return v
}

func applyMultiplier(fullMatch string) float64 {
	lm := strings.ToLower(fullMatch)
	if strings.Contains(lm, "млн") {
		return 1_000_000
	}
	if strings.Contains(lm, "тыс") || strings.Contains(lm, "тыщ") {
		return 1_000
	}
	return 1
}

func detectMultiplier(fullMatch string) float64 {
	return applyMultiplier(fullMatch)
}

func parseFloatNormalized(s string) float64 {
	s = strings.TrimSpace(s)
	s = strings.ReplaceAll(s, ",", ".")
	v, err := strconv.ParseFloat(s, 64)
	if err != nil {
		return 0
	}
	return v
}

func fixLayout(s string) string {
	latToRus := map[rune]rune{
		'q': 'й', 'w': 'ц', 'e': 'у', 'r': 'к', 't': 'е', 'y': 'н', 'u': 'г',
		'i': 'ш', 'o': 'щ', 'p': 'з', '[': 'х', ']': 'ъ',
		'a': 'ф', 's': 'ы', 'd': 'в', 'f': 'а', 'g': 'п', 'h': 'р',
		'j': 'о', 'k': 'л', 'l': 'д', ';': 'ж', '\'': 'э',
		'z': 'я', 'x': 'ч', 'c': 'с', 'v': 'м', 'b': 'и', 'n': 'т',
		'm': 'ь', ',': 'б', '.': 'ю', '/': '.',
	}
	rusToLat := make(map[rune]rune, len(latToRus))
	for k, v := range latToRus {
		rusToLat[v] = k
	}

	latCount := 0
	rusCount := 0
	for _, r := range s {
		if r >= 'a' && r <= 'z' || r >= 'A' && r <= 'Z' {
			latCount++
		}
		if r >= 'а' && r <= 'я' || r >= 'А' && r <= 'Я' {
			rusCount++
		}
	}

	var b strings.Builder
	if latCount > 0 && rusCount == 0 {
		for _, r := range s {
			if r >= 'A' && r <= 'Z' {
				if v, ok := latToRus[rune(r-'A'+'a')]; ok {
					b.WriteRune(v)
				} else {
					b.WriteRune(r)
				}
			} else if v, ok := latToRus[r]; ok {
				b.WriteRune(v)
			} else {
				b.WriteRune(r)
			}
		}
		return b.String()
	}
	if rusCount > 0 && latCount == 0 {
		for _, r := range s {
			if r >= 'А' && r <= 'Я' {
				if v, ok := rusToLat[rune(r-'А'+'а')]; ok {
					b.WriteRune(v)
				} else {
					b.WriteRune(r)
				}
			} else if v, ok := rusToLat[r]; ok {
				b.WriteRune(v)
			} else {
				b.WriteRune(r)
			}
		}
		return b.String()
	}
	return s
}

func (s *Service) handleSearch(w http.ResponseWriter, r *http.Request) {
	query := r.URL.Query().Get("q")
	if query == "" && r.Method == "POST" {
		var body struct {
			Query string `json:"query"`
		}
		if err := json.NewDecoder(r.Body).Decode(&body); err == nil {
			query = body.Query
		}
	}
	if query == "" {
		http.Error(w, `{"error":"missing q param"}`, http.StatusBadRequest)
		return
	}

	res, err := s.SearchVehicles(query)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(res)
}
