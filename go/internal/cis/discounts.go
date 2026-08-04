package cis

import "strings"

type RawDiscountItem struct {
	ID          int      `json:"id"`
	Name        string   `json:"name"`
	Sum         float64  `json:"sum"`
	Types       []string `json:"types"`
	Description string   `json:"description"`
	IsDefault   bool     `json:"isDefault"`
}

func NormalizeDiscounts(raw []RawDiscountItem) []DiscountResp {
	if len(raw) == 0 {
		return []DiscountResp{}
	}

	grouped := make(map[string]*DiscountResp)
	order := make([]string, 0)

	for _, d := range raw {
		name := strings.TrimSpace(d.Name)
		desc := strings.TrimSpace(d.Description)
		str := strings.ToLower(name + " " + desc)

		var categoryTitle string

		hasTradeIn := strings.Contains(str, "trade") || strings.Contains(str, "трейд") || strings.Contains(str, "обмен") || containsString(d.Types, "trade_in")
		hasCredit := strings.Contains(str, "кредит") || strings.Contains(str, "credit") || strings.Contains(str, "рассрочка") || strings.Contains(str, "финанс") || containsString(d.Types, "credit")

		switch {
		case hasTradeIn && hasCredit:
			categoryTitle = "Трейд-ин + Кредит"
		case hasTradeIn:
			categoryTitle = "Трейд-ин"
		case hasCredit:
			categoryTitle = "Кредит"
		case strings.Contains(str, "семь") || strings.Contains(str, "семейн") || strings.Contains(str, "господдержка") || strings.Contains(str, "первый авто") || strings.Contains(str, "второй авто"):
			categoryTitle = "Семейный автомобиль"
		case strings.Contains(str, "корпоратив") || strings.Contains(str, "лизинг") || strings.Contains(str, "флит"):
			categoryTitle = "Корпоративная скидка"
		case strings.Contains(str, "каско") || strings.Contains(str, "страхов") || strings.Contains(str, "доп") || containsString(d.Types, "insurance"):
			categoryTitle = "КАСКО в подарок"
		case strings.Contains(str, "прямая") || strings.Contains(str, "наличные") || strings.Contains(str, "наличка") || strings.Contains(str, "выгода") || strings.Contains(str, "ррц") || strings.Contains(str, "спеццен"):
			categoryTitle = "Прямая скидка"
		default:
			if desc != "" {
				categoryTitle = desc
			} else if name != "" {
				categoryTitle = name
			} else {
				categoryTitle = "Специальное предложение"
			}
		}

		if existing, ok := grouped[categoryTitle]; ok {
			existing.Sum += d.Sum
		} else {
			grouped[categoryTitle] = &DiscountResp{
				ID:          d.ID,
				Name:        categoryTitle,
				Description: categoryTitle,
				Sum:         d.Sum,
				Types:       d.Types,
				IsDefault:   d.IsDefault,
				Active:      true,
			}
			order = append(order, categoryTitle)
		}
	}

	result := make([]DiscountResp, 0, len(order))
	for _, title := range order {
		result = append(result, *grouped[title])
	}
	return result
}

func containsString(slice []string, val string) bool {
	for _, item := range slice {
		if item == val {
			return true
		}
	}
	return false
}
