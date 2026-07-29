package cis

import (
	"fmt"
	"strings"
	"time"
)

type VehicleFilter struct {
	ID            []int    `json:"id,omitempty"`
	TypeID        int      `json:"type_id,omitempty"`
	Brand         []string `json:"brand,omitempty"`
	NotBrand      []string `json:"not_brand,omitempty"`
	Model         []string `json:"model,omitempty"`
	NotModel      []string `json:"not_model,omitempty"`
	Transmission  []string `json:"transmission,omitempty"`
	Engine        []string `json:"engine,omitempty"`
	Drive         []string `json:"drive,omitempty"`
	Body          []string `json:"body,omitempty"`
	Color         []string `json:"color,omitempty"`
	Dealership    []int    `json:"dealership,omitempty"`
	NotDealership []int    `json:"not_dealership,omitempty"`
	PriceFrom     float64  `json:"price_from,omitempty"`
	PriceTo       float64  `json:"price_to,omitempty"`
	YearFrom      int      `json:"year_from,omitempty"`
	YearTo        int      `json:"year_to,omitempty"`
	VolumeFrom    int      `json:"volume_from,omitempty"`
	VolumeTo      int      `json:"volume_to,omitempty"`
	PowerFrom     int      `json:"power_from,omitempty"`
	PowerTo       int      `json:"power_to,omitempty"`
	MileageTo     int      `json:"mileage_to,omitempty"`
	Tag           string   `json:"tag,omitempty"`
	Sort          string   `json:"sort,omitempty"`
	Page          int      `json:"page,omitempty"`
	PerPage       int      `json:"per_page,omitempty"`
	Limit         int      `json:"limit,omitempty"`
}

type VehicleRow struct {
	ExtID        int     `db:"ext_id" json:"id"`
	TypeID       int     `db:"type_id" json:"type_id"`
	BrandID      int     `db:"brand_id" json:"brand_id"`
	BrandCode    string  `db:"brand_code" json:"brand_code,omitempty"`
	BrandName    string  `db:"brand_name" json:"brand_name,omitempty"`
	BrandRuName  string  `db:"brand_ru_name" json:"brand_ru_name,omitempty"`
	ModelID      int     `db:"model_id" json:"model_id"`
	ModelCode    string  `db:"model_code" json:"model_code,omitempty"`
	ModelName    string  `db:"model_name" json:"model_name,omitempty"`
	ModelRuName  string  `db:"model_ru_name" json:"model_ru_name,omitempty"`
	ModelImage   string  `db:"model_image" json:"model_image,omitempty"`
	Vin          string  `db:"vin" json:"vin,omitempty"`
	Name         string  `db:"name" json:"name"`
	Price        float64 `db:"price" json:"price"`
	MinPrice     float64 `db:"min_price" json:"min_price"`
	Transmission string  `db:"transmission" json:"transmission"`
	Engine       string  `db:"engine" json:"engine"`
	Drive        string  `db:"drive" json:"drive"`
	Body         string  `db:"body" json:"body"`
	Color        string  `db:"color" json:"color"`
	DealershipID int     `db:"dealership_id" json:"dealership_id"`
	Volume       int     `db:"volume" json:"volume"`
	Power        int     `db:"power" json:"power"`
	Year         int     `db:"year" json:"year"`
	Mileage      int     `db:"mileage" json:"mileage"`
	InStock      bool    `db:"instock" json:"instock"`
	OnWay        bool    `db:"onway" json:"onway"`
	Discount     bool    `db:"discount" json:"discount"`
	UpdateImages bool    `db:"update_images" json:"-"`
	UseInternalImages bool `db:"use_internal_images" json:"-"`
	Created      int64  `db:"created" json:"-"`
	Raw          string  `db:"raw" json:"-"`
	SyncedAt     *time.Time `db:"synced_at" json:"-"`
}

func (s *Service) buildConditions(f VehicleFilter) (string, []interface{}) {
	var wheres []string
	var args []interface{}

	if len(f.ID) > 0 {
		placeholders := make([]string, len(f.ID))
		for i, idVal := range f.ID {
			placeholders[i] = "?"
			args = append(args, idVal)
		}
		wheres = append(wheres, fmt.Sprintf("v.ext_id IN (%s)", strings.Join(placeholders, ",")))
	}

	if f.TypeID > 0 {
		wheres = append(wheres, "v.type_id = ?")
		args = append(args, f.TypeID)
	}

	if len(f.Brand) > 0 {
		placeholders := make([]string, len(f.Brand))
		for i, b := range f.Brand {
			placeholders[i] = "?"
			args = append(args, b)
		}
		wheres = append(wheres, fmt.Sprintf("b.code IN (%s)", strings.Join(placeholders, ",")))
	}

	if len(f.NotBrand) > 0 {
		placeholders := make([]string, len(f.NotBrand))
		for i, b := range f.NotBrand {
			placeholders[i] = "?"
			args = append(args, b)
		}
		wheres = append(wheres, fmt.Sprintf("(b.code NOT IN (%s) OR b.code IS NULL)", strings.Join(placeholders, ",")))
	}

	if len(f.Model) > 0 {
		placeholders := make([]string, len(f.Model))
		for i, m := range f.Model {
			placeholders[i] = "?"
			args = append(args, m)
		}
		wheres = append(wheres, fmt.Sprintf("COALESCE(mn.code, mu.code) IN (%s)", strings.Join(placeholders, ",")))
	}

	if len(f.Dealership) > 0 {
		placeholders := make([]string, len(f.Dealership))
		for i, d := range f.Dealership {
			placeholders[i] = "?"
			args = append(args, d)
		}
		wheres = append(wheres, fmt.Sprintf("v.dealership_id IN (%s)", strings.Join(placeholders, ",")))
	}

	if len(f.Transmission) > 0 {
		var vals []string
		for _, tr := range f.Transmission {
			vals = append(vals, tr)
			for _, t := range s.transmissions {
				if t.Code == tr {
					vals = append(vals, fmt.Sprintf("t_%d", t.ID))
					break
				}
			}
		}
		placeholders := make([]string, len(vals))
		for i, v := range vals {
			placeholders[i] = "?"
			args = append(args, v)
		}
		wheres = append(wheres, fmt.Sprintf("v.transmission IN (%s)", strings.Join(placeholders, ",")))
	}

	if len(f.Engine) > 0 {
		var vals []string
		for _, eng := range f.Engine {
			vals = append(vals, eng)
			for _, e := range s.engines {
				if e.Code == eng {
					vals = append(vals, fmt.Sprintf("e_%d", e.ID))
					break
				}
			}
		}
		placeholders := make([]string, len(vals))
		for i, v := range vals {
			placeholders[i] = "?"
			args = append(args, v)
		}
		wheres = append(wheres, fmt.Sprintf("v.engine IN (%s)", strings.Join(placeholders, ",")))
	}

	if len(f.Drive) > 0 {
		var vals []string
		for _, drv := range f.Drive {
			vals = append(vals, drv)
			for _, d := range s.drives {
				if d.Code == drv {
					vals = append(vals, fmt.Sprintf("d_%d", d.ID))
					break
				}
			}
		}
		placeholders := make([]string, len(vals))
		for i, v := range vals {
			placeholders[i] = "?"
			args = append(args, v)
		}
		wheres = append(wheres, fmt.Sprintf("v.drive IN (%s)", strings.Join(placeholders, ",")))
	}

	if len(f.Body) > 0 {
		var vals []string
		for _, b := range f.Body {
			vals = append(vals, b)
			for _, body := range s.bodies {
				if body.Code == b {
					vals = append(vals, fmt.Sprintf("b_%d", body.ID))
					break
				}
			}
		}
		placeholders := make([]string, len(vals))
		for i, v := range vals {
			placeholders[i] = "?"
			args = append(args, v)
		}
		wheres = append(wheres, fmt.Sprintf("v.body IN (%s)", strings.Join(placeholders, ",")))
	}

	if len(f.Color) > 0 {
		var vals []string
		for _, col := range f.Color {
			vals = append(vals, col)
			for _, c := range s.colors {
				if c.Code == col {
					vals = append(vals, fmt.Sprintf("c_%d", c.ID))
					break
				}
			}
		}
		placeholders := make([]string, len(vals))
		for i, v := range vals {
			placeholders[i] = "?"
			args = append(args, v)
		}
		wheres = append(wheres, fmt.Sprintf("v.color IN (%s)", strings.Join(placeholders, ",")))
	}

	if f.PriceFrom > 0 {
		wheres = append(wheres, "v.price >= ?")
		args = append(args, f.PriceFrom)
	}
	if f.PriceTo > 0 {
		wheres = append(wheres, "v.price <= ?")
		args = append(args, f.PriceTo)
	}

	if f.YearFrom > 0 {
		wheres = append(wheres, "v.year >= ?")
		args = append(args, f.YearFrom)
	}
	if f.YearTo > 0 {
		wheres = append(wheres, "v.year <= ?")
		args = append(args, f.YearTo)
	}

	if f.VolumeFrom > 0 {
		wheres = append(wheres, "v.volume >= ?")
		args = append(args, f.VolumeFrom)
	}
	if f.VolumeTo > 0 {
		wheres = append(wheres, "v.volume <= ?")
		args = append(args, f.VolumeTo)
	}

	if f.PowerFrom > 0 {
		wheres = append(wheres, "v.power >= ?")
		args = append(args, f.PowerFrom)
	}
	if f.PowerTo > 0 {
		wheres = append(wheres, "v.power <= ?")
		args = append(args, f.PowerTo)
	}

	if f.MileageTo > 0 {
		wheres = append(wheres, "v.mileage <= ?")
		args = append(args, f.MileageTo)
	}

	switch f.Tag {
	case "instock":
		wheres = append(wheres, "v.instock = 1")
	case "onway":
		wheres = append(wheres, "v.onway = 1")
	case "discount":
		wheres = append(wheres, "v.discount = 1")
	}

	if len(wheres) > 0 {
		return "WHERE " + strings.Join(wheres, " AND "), args
	}
	return "", args
}

func (s *Service) BuildVehicleQuery(f VehicleFilter) (string, []interface{}, error) {
	table := s.apiTable()
	where, args := s.buildConditions(f)

	orderBy := "v.ext_id DESC"
	switch f.Sort {
	case "price_up":
		orderBy = "v.price ASC"
	case "price_down":
		orderBy = "v.price DESC"
	case "year_up":
		orderBy = "v.year ASC"
	case "year_down":
		orderBy = "v.year DESC"
	case "mileage_up":
		orderBy = "v.mileage ASC"
	case "mileage_down":
		orderBy = "v.mileage DESC"
	case "datetime_up":
		orderBy = "v.created ASC"
	case "datetime_down":
		orderBy = "v.ext_id DESC"
	case "random":
		orderBy = "RAND()"
	}

	perPage := f.PerPage
	if perPage == 0 {
		perPage = 32
	}

	limit := perPage
	offset := 0

	if f.Limit > 0 {
		limit = f.Limit
		if f.Page > 0 {
			offset = (f.Page - 1) * limit
		}
	} else {
		cA := perPage / 16
		limit = perPage - cA
		if f.Page > 0 {
			offset = (f.Page - 1) * limit
		}
	}

	sqlSelect := fmt.Sprintf(`
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
		%s
		ORDER BY %s
		LIMIT %d OFFSET %d
	`, table, where, orderBy, limit, offset)

	return sqlSelect, args, nil
}

func (s *Service) CountVehicles(f VehicleFilter) (int, error) {
	table := s.apiTable()
	where, args := s.buildConditions(f)

	var count int
	err := s.db.Get(&count, fmt.Sprintf(`
		SELECT COUNT(*) FROM %s v
		LEFT JOIN yapps_app_cis_brands b ON b.id = v.brand_id
		LEFT JOIN yapps_app_cis_models_new mn ON mn.id = v.model_id AND v.type_id = 1
		LEFT JOIN yapps_app_cis_models_used mu ON mu.id = v.model_id AND v.type_id = 2
		%s
	`, table, where), args...)
	return count, err
}
