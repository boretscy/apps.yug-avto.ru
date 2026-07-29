package cis

import (
	"encoding/json"
	"fmt"
	"strconv"
)

func (s *Service) rowToVehicleFull(row *VehicleRow, typeID int, images []ImageResp) VehicleFull {
	status := StatusResp{ID: 2, Name: "В пути"}
	if row.InStock {
		status = StatusResp{ID: 1, Name: "В наличии"}
	}

	brand := BrandResp{
		ID:     strconv.Itoa(row.BrandID),
		ExtID:  strconv.Itoa(row.BrandID),
		Code:   row.BrandCode,
		Name:   row.BrandName,
		RuName: row.BrandRuName,
	}

	model := ModelResp{
		ID:      strconv.Itoa(row.ModelID),
		ExtID:   strconv.Itoa(row.ModelID),
		BrandID: strconv.Itoa(row.BrandID),
		Code:    row.ModelCode,
		Name:    row.ModelName,
		RuName:  row.ModelRuName,
		Image:   row.ModelImage,
		BodyID:  "",
	}

	body := RefResp{Code: row.Body}
	for _, b := range s.bodies {
		if b.Code == row.Body {
			body = RefResp{ID: b.ID, Code: b.Code, Name: b.Name}
			break
		}
	}
	// Fallback: row.Body might be "b_N" format (legacy data)
	if body.Name == "" && len(row.Body) > 2 && row.Body[0] == 'b' && row.Body[1] == '_' {
		if id, err := strconv.Atoi(row.Body[2:]); err == nil {
			for _, b := range s.bodies {
				if b.ID == id {
					body = RefResp{ID: b.ID, Code: b.Code, Name: b.Name}
					break
				}
			}
		}
	}

	engine := RefResp{Code: row.Engine}
	for _, e := range s.engines {
		if e.Code == row.Engine {
			engine = RefResp{ID: e.ID, Code: e.Code, Name: e.Name}
			break
		}
	}
	// Fallback: row.Engine might be "e_N" format (legacy data)
	if engine.Name == "" && len(row.Engine) > 2 && row.Engine[0] == 'e' && row.Engine[1] == '_' {
		if id, err := strconv.Atoi(row.Engine[2:]); err == nil {
			for _, e := range s.engines {
				if e.ID == id {
					engine = RefResp{ID: e.ID, Code: e.Code, Name: e.Name}
					break
				}
			}
		}
	}

	transmission := RefWithMeta{Code: row.Transmission}
	for _, t := range s.transmissions {
		if t.Code == row.Transmission {
			transmission = RefWithMeta{ID: t.ID, Code: t.Code, Name: t.Name, Meta: t.Meta}
			break
		}
	}
	// Fallback: row.Transmission might be "t_N" format (legacy data)
	if transmission.Name == "" && len(row.Transmission) > 2 && row.Transmission[0] == 't' && row.Transmission[1] == '_' {
		if id, err := strconv.Atoi(row.Transmission[2:]); err == nil {
			for _, t := range s.transmissions {
				if t.ID == id {
					transmission = RefWithMeta{ID: t.ID, Code: t.Code, Name: t.Name, Meta: t.Meta}
					break
				}
			}
		}
	}

	drive := RefWithMeta{Code: row.Drive}
	for _, d := range s.drives {
		if d.Code == row.Drive {
			drive = RefWithMeta{ID: d.ID, Code: d.Code, Name: d.Name, Meta: d.Meta}
			break
		}
	}
	// Fallback: row.Drive might be "d_N" format (legacy data)
	if drive.Name == "" && len(row.Drive) > 2 && row.Drive[0] == 'd' && row.Drive[1] == '_' {
		if id, err := strconv.Atoi(row.Drive[2:]); err == nil {
			for _, d := range s.drives {
				if d.ID == id {
					drive = RefWithMeta{ID: d.ID, Code: d.Code, Name: d.Name, Meta: d.Meta}
					break
				}
			}
		}
	}

	link := fmt.Sprintf("/cars/new/%s/%s/%d", row.BrandCode, row.ModelCode, row.ExtID)
	if typeID == 2 {
		link = fmt.Sprintf("/cars/used/%s/%s/%d", row.BrandCode, row.ModelCode, row.ExtID)
	}

	imgs := images
	if imgs == nil {
		imgs = []ImageResp{}
	}
	if len(imgs) == 0 && body.Code != "" {
		base := s.imageBaseURL + "/upload/Cis/bodies"
		imgs = append(imgs, ImageResp{
			ID:           "0",
			Detail:       fmt.Sprintf("%s/%s.jpg", base, body.Code),
			Preview:      fmt.Sprintf("%s/%s_sm.jpg", base, body.Code),
			PreviewLarge: fmt.Sprintf("%s/%s.jpg", base, body.Code),
		})
	}

	mainImage := ""
	if len(imgs) > 0 {
		mainImage = imgs[0].Preview
	}

	tags := []TagResp{}
	if row.Discount {
		tags = append(tags, TagResp{ID: "4", Name: "Выгодный кредит", Icon: fmt.Sprintf("%s/upload/Cis/tags/5b6cc18aec49fb58dfcc72a8ef450013.svg", s.imageBaseURL)})
	}
	if row.Drive == "full" {
		tags = append(tags, TagResp{ID: "7", Name: "4х4", Icon: fmt.Sprintf("%s/upload/Cis/tags/1d0a1107e599ab53fe99470df1fc2ac2.svg", s.imageBaseURL)})
	}

	general := []string{}
	if row.Year > 0 {
		general = append(general, fmt.Sprintf("%d год", row.Year))
	}
	if engine.Name != "" {
		general = append(general, engine.Name)
	}
	if row.Volume > 0 {
		general = append(general, fmt.Sprintf("%.1f л", float64(row.Volume)/1000))
	}
	if row.Power > 0 {
		general = append(general, fmt.Sprintf("%d л.с.", row.Power))
	}
	if transmission.Name != "" {
		general = append(general, transmission.Name)
	}
	if drive.Name != "" {
		general = append(general, drive.Name)
	}
	if body.Name != "" {
		general = append(general, body.Name)
	}
	if row.Mileage > 0 {
		general = append(general, fmt.Sprintf("%d км", row.Mileage))
	}

	dealership := s.getDealership(row.DealershipID, row.BrandID)

	entityStr := "new"
	if typeID == 2 {
		entityStr = "used"
	}

	color := ColorResp{
		ID:    "0",
		Code:  row.Color,
		Name:  "",
		Param: "",
	}
	for _, c := range s.colors {
		if c.Code == row.Color {
			color = ColorResp{
				ID:    strconv.Itoa(c.ID),
				Code:  c.Code,
				Name:  c.Name,
				Param: c.Param,
			}
			break
		}
	}

	// Разбираем скидки, характеристики и размеры из сырого JSON
	var specs []SpecResp
	var genStruct []SpecResp
	var discounts []DiscountResp

	if row.Raw != "" {
		var raw struct {
			Specifications []struct {
				Name  string      `json:"name"`
				Value interface{} `json:"value"`
			} `json:"specifications"`
			General []struct {
				Name  string      `json:"name"`
				Value interface{} `json:"value"`
			} `json:"general"`
			Discounts []struct {
				ID          int      `json:"id"`
				Name        string   `json:"name"`
				Sum         float64  `json:"sum"`
				Types       []string `json:"types"`
				Description string   `json:"description"`
				IsDefault   bool     `json:"isDefault"`
			} `json:"discounts"`
		}
		if err := json.Unmarshal([]byte(row.Raw), &raw); err == nil {
			for _, spec := range raw.Specifications {
				specs = append(specs, SpecResp{
					Name:  spec.Name,
					Value: fmt.Sprintf("%v", spec.Value),
				})
			}
			for _, g := range raw.General {
				genStruct = append(genStruct, SpecResp{
					Name:  g.Name,
					Value: fmt.Sprintf("%v", g.Value),
				})
			}
			if len(raw.Discounts) > 0 {
				for _, d := range raw.Discounts {
					discounts = append(discounts, DiscountResp{
						ID:          d.ID,
						Name:        d.Name,
						Sum:         d.Sum,
						Types:       d.Types,
						Description: d.Description,
						IsDefault:   d.IsDefault,
						Active:      true,
					})
				}
			}
		}
	}

	if len(discounts) == 0 && row.Price > row.MinPrice {
		discounts = append(discounts, DiscountResp{
			Name:        "Скидка",
			Description: "Специальное предложение",
			Sum:         row.Price - row.MinPrice,
			Active:      true,
		})
	}

	return VehicleFull{
		ExtID:          row.ExtID,
		ID:             row.ExtID,
		Type:           "vehicle",
		Entity:         entityStr,
		BrandAlias:     row.BrandCode,
		ModelAlias:     row.ModelCode,
		Vin:            row.Vin,
		Brand:          brand,
		Model:          model,
		Name:           row.Name,
		Link:           link,
		Image:          mainImage,
		Price:          row.Price,
		MinPrice:       row.MinPrice,
		Status:         status,
		Body:           body,
		Engine:         engine,
		Transmission:   transmission,
		Drive:          drive,
		Discount:       row.Discount,
		Color:          color,
		Dealership:     dealership,
		Images:         imgs,
		Tags:           tags,
		General:        general,
		Discounts:      discounts,
		Specifications: specs,
		GeneralStruct:  genStruct,
		Volume:         row.Volume,
		Power:          row.Power,
		Year:           row.Year,
		Mileage:        row.Mileage,
		Created:        row.Created,
	}
}
