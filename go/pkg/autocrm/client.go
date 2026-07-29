package autocrm

import (
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"time"
)

var defaultTransport = &http.Transport{
	MaxIdleConns:        100,
	MaxIdleConnsPerHost: 20,
	IdleConnTimeout:     90 * time.Second,
}

type Client struct {
	baseURL   string
	token     string
	timeout   time.Duration
	transport *http.Transport
}

func NewClient(baseURL, token string) *Client {
	return &Client{
		baseURL:   baseURL,
		token:     token,
		timeout:   60 * time.Second,
		transport: defaultTransport,
	}
}

func (c *Client) clientWithTimeout(timeout time.Duration) *http.Client {
	t := c.timeout
	if timeout > 0 {
		t = timeout
	}
	return &http.Client{
		Transport: c.transport,
		Timeout:   t,
	}
}

func (c *Client) request(path string, timeout time.Duration) ([]byte, error) {
	url := c.baseURL + path
	req, err := http.NewRequest("GET", url, nil)
	if err != nil {
		return nil, fmt.Errorf("autocrm request: %w", err)
	}
	req.Header.Set("Authorization", "Bearer "+c.token)
	req.Header.Set("Accept", "application/json")

	cli := c.clientWithTimeout(timeout)
	resp, err := cli.Do(req)
	if err != nil {
		return nil, fmt.Errorf("autocrm do: %w", err)
	}
	defer resp.Body.Close()

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, fmt.Errorf("autocrm read: %w", err)
	}
	if resp.StatusCode != http.StatusOK {
		return nil, fmt.Errorf("autocrm status %d: %s", resp.StatusCode, body)
	}
	return body, nil
}

func (c *Client) getJSON(path string, v interface{}, timeout ...time.Duration) error {
	t := time.Duration(0)
	if len(timeout) > 0 {
		t = timeout[0]
	}
	body, err := c.request(path, t)
	if err != nil {
		return err
	}
	return json.Unmarshal(body, v)
}

func (c *Client) paginate(pathPattern string, page int) (string, error) {
	return fmt.Sprintf("%s?page=%d&per-page=50", pathPattern, page), nil
}

type BrandsResponse struct {
	Items []BrandItem `json:"items"`
}

type BrandItem struct {
	ID   int    `json:"id"`
	Name string `json:"name"`
}

func (c *Client) GetBrands() (*BrandsResponse, error) {
	var resp BrandsResponse
	err := c.getJSON("/brands", &resp)
	if err != nil {
		return nil, err
	}
	return &resp, nil
}

type ModelsResponse struct {
	Items []ModelItem `json:"items"`
}

type ModelItem struct {
	ID        int    `json:"id"`
	Name      string `json:"name"`
	Image     string `json:"image,omitempty"`
	BodyType  string `json:"body_type,omitempty"`
}

func (c *Client) GetModels(brandID int) (*ModelsResponse, error) {
	var resp ModelsResponse
	err := c.getJSON(fmt.Sprintf("/brands/%d/models?expand=statistics", brandID), &resp)
	if err != nil {
		return nil, err
	}
	return &resp, nil
}

type VehiclesPage struct {
	Items []VehicleRaw `json:"items"`
	Meta  *PageMeta    `json:"_meta,omitempty"`
}

type PageMeta struct {
	PageCount  int `json:"pageCount"`
	TotalCount int `json:"totalCount"`
}

type ColorInfo struct {
	ID    string `json:"id"`
	Code  string `json:"code"`
	Name  string `json:"name"`
	Param string `json:"param"`
}

type ImageInfo struct {
	Full         string `json:"full"`
	PreviewLarge string `json:"preview_large,omitempty"`
	PreviewSmall string `json:"preview_small,omitempty"`
}

type GeneralField struct {
	Name  string      `json:"name"`
	Value interface{} `json:"value"`
}

type SpecField struct {
	Name  string      `json:"name"`
	Value interface{} `json:"value"`
}

type VehicleRaw struct {
	ID                       int              `json:"id"`
	Vin                      string           `json:"vin,omitempty"`
	Price                    float64          `json:"price"`
	MinPrice                 float64          `json:"min_price"`
	SpecialPrice             float64          `json:"special_price,omitempty"`
	Status                   *StatusInfo      `json:"status,omitempty"`
	Dealership               *DealershipInfo  `json:"dealership,omitempty"`
	BrandID                  int              `json:"brand_id"`
	BrandName                string           `json:"brand_name"`
	ModelID                  int              `json:"model_id"`
	ModelName                string           `json:"model_name"`
	RefModelID               int              `json:"ref_model_id,omitempty"`
	RefModelName             string           `json:"ref_model_name,omitempty"`
	ModificationName         string           `json:"modification_name,omitempty"`
	EquipmentName            string           `json:"equipment_name,omitempty"`
	Equipment                string           `json:"-"`
	GenerationName           string           `json:"generation_name,omitempty"`
	Color                    *ColorInfo       `json:"-"`
	BodyType                 string           `json:"body_type,omitempty"`
	Images                   []ImageInfo      `json:"images,omitempty"`
	General                  []GeneralField   `json:"general,omitempty"`
	Specifications           []SpecField      `json:"specifications,omitempty"`
	Options                  []string         `json:"-"`
	RawOptions               json.RawMessage  `json:"options,omitempty"`
	Tags                     []string         `json:"tags,omitempty"`
	AdditionalEquipmentPrice float64          `json:"additional_equipment_price,omitempty"`
	VehicleEntryDate         string           `json:"vehicle_entry_date,omitempty"`
	VehicleReceiptDate       string           `json:"vehicle_receipt_date,omitempty"`
	RawEquipment             json.RawMessage  `json:"equipment,omitempty"`
	RawColor                 json.RawMessage  `json:"color,omitempty"`
	Discounts                []DiscountInfo   `json:"discounts,omitempty"`
}

func (v *VehicleRaw) UnmarshalJSON(data []byte) error {
	type alias VehicleRaw
	a := &struct {
		EquipmentRaw interface{} `json:"equipment"`
		ColorRaw     interface{} `json:"color"`
		*alias
	}{
		alias: (*alias)(v),
	}
	if err := json.Unmarshal(data, a); err != nil {
		return err
	}
	switch e := a.EquipmentRaw.(type) {
	case string:
		v.Equipment = e
	case float64:
		v.Equipment = fmt.Sprintf("%.0f", e)
	}
	v.RawEquipment, _ = json.Marshal(a.EquipmentRaw)

	if m, ok := a.ColorRaw.(map[string]interface{}); ok {
		b, _ := json.Marshal(m)
		var c ColorInfo
		if json.Unmarshal(b, &c) == nil {
			v.Color = &c
		}
	}
	v.RawColor, _ = json.Marshal(a.ColorRaw)

	if len(v.RawOptions) > 0 {
		if err := json.Unmarshal(v.RawOptions, &v.Options); err != nil {
			var items []interface{}
			if err2 := json.Unmarshal(v.RawOptions, &items); err2 == nil {
				for _, item := range items {
					switch val := item.(type) {
					case string:
						v.Options = append(v.Options, val)
					case map[string]interface{}:
						if name, ok := val["name"].(string); ok {
							v.Options = append(v.Options, name)
						}
					}
				}
			}
		}
	}
	return nil
}

type DiscountInfo struct {
	ID          int      `json:"id"`
	Name        string   `json:"name"`
	Sum         float64  `json:"sum"`
	Types       []string `json:"types,omitempty"`
	Description string   `json:"description,omitempty"`
	IsDefault   bool     `json:"isDefault"`
}

type StatusInfo struct {
	ID   int    `json:"id"`
	Name string `json:"name"`
}

type DealershipInfo struct {
	ID    int    `json:"id"`
	Name  string `json:"name"`
	Phone string `json:"phone"`
}

func (c *Client) GetVehiclesList(section string, page int) (*VehiclesPage, error) {
	return c.getVehiclesList(section, page, 0)
}

func (c *Client) GetVehiclesListTimeout(section string, page int, timeout time.Duration) (*VehiclesPage, error) {
	return c.getVehiclesList(section, page, timeout)
}

func (c *Client) getVehiclesList(section string, page int, timeout time.Duration) (*VehiclesPage, error) {
	var path string
	switch section {
	case "new":
		path = "/vehicles/all"
	case "used":
		path = "/tradein/vehicles"
	default:
		return nil, fmt.Errorf("unknown section: %s", section)
	}

	fullPath := fmt.Sprintf("%s?page=%d&per-page=50", path, page)
	var resp VehiclesPage
	err := c.getJSON(fullPath, &resp, timeout)
	if err != nil {
		return nil, err
	}
	return &resp, nil
}

func (c *Client) GetVehicleDetail(id int) (*VehicleRaw, error) {
	var resp VehicleRaw
	err := c.getJSON(fmt.Sprintf("/vehicles/%d", id), &resp, 30*time.Second)
	if err != nil {
		return nil, err
	}
	return &resp, nil
}

type ModelInfoResponse struct {
	Modifications []ModificationItem `json:"items,omitempty"`
	Equipments    []string           `json:"items,omitempty"`
	Colors        []ColorInfo        `json:"items,omitempty"`
}

type ModificationItem struct {
	ID   int    `json:"id"`
	Name string `json:"name"`
}

func (c *Client) GetModelModifications(modelID int) ([]ModificationItem, error) {
	var resp struct {
		Items []ModificationItem `json:"items"`
	}
	err := c.getJSON(fmt.Sprintf("/models/%d/modifications", modelID), &resp)
	if err != nil {
		return nil, err
	}
	return resp.Items, nil
}

func (c *Client) GetModelEquipments(modelID int) ([]string, error) {
	var resp struct {
		Items []string `json:"items"`
	}
	err := c.getJSON(fmt.Sprintf("/models/%d/equipments", modelID), &resp)
	if err != nil {
		return nil, err
	}
	return resp.Items, nil
}

func (c *Client) GetModelColors(modelID int) ([]ColorInfo, error) {
	var resp struct {
		Items []ColorInfo `json:"items"`
	}
	err := c.getJSON(fmt.Sprintf("/models/%d/colors", modelID), &resp)
	if err != nil {
		return nil, err
	}
	return resp.Items, nil
}
