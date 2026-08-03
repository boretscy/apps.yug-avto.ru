package cis

import "database/sql"

type Brand struct {
	ID     int    `db:"id" json:"id"`
	ExtID  int    `db:"ext_id" json:"ext_id"`
	Code   string `db:"code" json:"code"`
	Name   string `db:"name" json:"name"`
	RuName string `db:"ru_name" json:"ru_name"`
}

type Model struct {
	ID        int            `db:"id" json:"id"`
	ExtID     int            `db:"ext_id" json:"ext_id"`
	BrandID   int            `db:"brand_id" json:"brand_id"`
	Code      string         `db:"code" json:"code"`
	Name      string         `db:"name" json:"name"`
	RuName    string         `db:"ru_name" json:"ru_name"`
	Image     sql.NullString `db:"image" json:"image,omitempty"`
	BodyID    int            `db:"body_id" json:"body_id"`
	UseUAEP   bool           `db:"use_additional_equipment_in_price" json:"use_additional_equipment_in_price"`
}

type Vehicle struct {
	ExtID        int     `db:"ext_id" json:"id"`
	TypeID       int     `db:"type_id" json:"type_id"`
	BrandID      int     `db:"brand_id" json:"brand_id"`
	ModelID      int     `db:"model_id" json:"model_id"`
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
	UpdateImages bool    `db:"update_images" json:"update_images"`
	Created      int64   `db:"created" json:"created"`
}

type Body struct {
	ID   int    `db:"id"`
	Code string `db:"code"`
	Name string `db:"name"`
}

type Color struct {
	ID    int    `db:"id"`
	Code  string `db:"code"`
	Name  string `db:"name"`
	Param string `db:"param"`
}

type Transmission struct {
	ID   int    `db:"id"`
	Code string `db:"code"`
	Name string `db:"name"`
	Meta string `db:"meta"`
}

type Engine struct {
	ID   int    `db:"id"`
	Code string `db:"code"`
	Name string `db:"name"`
}

type Drive struct {
	ID   int    `db:"id"`
	Code string `db:"code"`
	Name string `db:"name"`
	Meta string `db:"meta"`
}

type Comparison struct {
	ID      int    `db:"id"`
	Entity  string `db:"entity"`
	Desired string `db:"desired"`
	Value   int    `db:"value"`
}

type Dealership struct {
	ID       int    `db:"id"`
	Code     int    `db:"code"`
	TypeID   int    `db:"type_id"`
	Name     string `db:"name"`
	URL      string `db:"url"`
	Phone    string `db:"phone"`
	Email    string `db:"email"`
	Address  string `db:"address"`
	City     string `db:"city"`
	InCity   string `db:"in_city"`
	CoordsLat string `db:"coords_lat"`
	CoordsLon string `db:"coords_lon"`
	BrandID  int    `db:"brand_id"`
	CtID     int    `db:"ct_id"`
	CtToken  string `db:"ct_token"`
}

type Image struct {
	ID      int    `db:"id"`
	ExtID   int    `db:"ext_id"`
	Detail  string `db:"detail"`
	Preview string `db:"preview"`
}

type StatusResp struct {
	ID   int    `json:"id"`
	Name string `json:"name"`
}

type RefResp struct {
	ID   int    `json:"id"`
	Code string `json:"code"`
	Name string `json:"name"`
}

type RefWithMeta struct {
	ID   int    `json:"id"`
	Code string `json:"code"`
	Name string `json:"name"`
	Meta string `json:"meta,omitempty"`
}

type ImageResp struct {
	ID           string `json:"id"`
	Detail       string `json:"detail,omitempty"`
	Preview      string `json:"preview"`
	PreviewLarge string `json:"preview_large,omitempty"`
	PreviewSmall string `json:"preview_small,omitempty"`
	Big          string `json:"big,omitempty"`
	Thumb        string `json:"thumb,omitempty"`
}

type TagResp struct {
	ID   string `json:"id"`
	Name string `json:"name"`
	Icon string `json:"icon"`
}

type TagEntity struct {
	ID   int    `db:"id" json:"id"`
	Name string `db:"name" json:"name"`
	Icon string `db:"icon" json:"icon"`
}


type BrandResp struct {
	ID     string `json:"id"`
	ExtID  string `json:"ext_id"`
	Code   string `json:"code"`
	Name   string `json:"name"`
	RuName string `json:"ru_name"`
}

type ModelResp struct {
	ID        string `json:"id"`
	ExtID     string `json:"ext_id"`
	BrandID   string `json:"brand_id"`
	Code      string `json:"code"`
	Name      string `json:"name"`
	RuName    string `json:"ru_name"`
	Image     string `json:"image"`
	BodyID    string `json:"body_id"`
	UseUAEP   string `json:"use_additional_equipment_in_price"`
}

type DealershipResp struct {
	ID        int    `json:"id"`
	Name      string `json:"name"`
	Phone     string `json:"phone"`
	Email     string `json:"email"`
	Address   string `json:"address"`
	Site      string `json:"site"`
	PhoneMask string `json:"phone_mask"`
	City      string `json:"city"`
	InCity    string `json:"in_city"`
}

type ColorResp struct {
	ID    string `json:"id"`
	Code  string `json:"code"`
	Name  string `json:"name"`
	Param string `json:"param"`
}

type DiscountResp struct {
	ID          int      `json:"id,omitempty"`
	Name        string   `json:"name"`
	Sum         float64  `json:"sum"`
	Types       []string `json:"types,omitempty"`
	Description string   `json:"description,omitempty"`
	IsDefault   bool     `json:"isDefault"`
	Active      bool     `json:"active"`
}

type SpecResp struct {
	Name  string `json:"name"`
	Value string `json:"value"`
}

type VehicleFull struct {
	ExtID          int            `json:"ext_id"`
	ID             int            `json:"id"`
	Type           string         `json:"type"`
	Entity         string         `json:"entity"`
	BrandAlias     string         `json:"brand_alias"`
	ModelAlias     string         `json:"model_alias"`
	Vin            string         `json:"vin"`
	Brand          BrandResp      `json:"brand"`
	Model          ModelResp      `json:"model"`
	Name           string         `json:"name"`
	Link           string         `json:"link"`
	Image          string         `json:"image"`
	Price          float64        `json:"price"`
	MinPrice       float64        `json:"min_price"`
	Status         StatusResp     `json:"status"`
	Body           RefResp        `json:"body"`
	Engine         RefResp        `json:"engine"`
	Transmission   RefWithMeta    `json:"transmission"`
	Drive          RefWithMeta    `json:"drive"`
	Discount       bool           `json:"discount"`
	Color          ColorResp      `json:"color"`
	Dealership     DealershipResp `json:"dealership"`
	Images         []ImageResp    `json:"images"`
	Tags           []TagResp      `json:"_tags"`
	General        []string       `json:"_general"`
	Equipment      string         `json:"equipment,omitempty"`
	Discounts      []DiscountResp `json:"discounts"`
	Specifications []SpecResp     `json:"specifications,omitempty"`
	GeneralStruct  []SpecResp     `json:"general,omitempty"`
	Volume         int            `json:"volume,omitempty"`
	Power          int            `json:"power,omitempty"`
	Year           int            `json:"year,omitempty"`
	Mileage        int            `json:"mileage,omitempty"`
	Created        int64          `json:"created,omitempty"`
}
