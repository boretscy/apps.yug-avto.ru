package cis

import "testing"

func TestGenerateModelAlias(t *testing.T) {
	tests := []struct {
		name string
		want string
	}{
		{"Новая Granta седан", "granta-sedan-new"},
		{"Новая Granta лифтбэк", "granta-liftbek-new"},
		{"Новая Granta хетчбэк", "granta-khetchbek-new"},
		{"Новая Granta универсал", "granta-wagon-new"},
		{"Новая Granta Cross", "granta-cross-new"},
		{"Новая Vesta", "vesta-new"},
		{"Новая Vesta SW", "vesta-sw-new"},
		{"Новая Vesta Cross", "vesta-cross-new"},
		{"Новая Vesta SW Cross", "vesta-sw-cross-new"},
		{"Новая Elantra", "elantra-new"},
		{"Новая Creta", "creta-new"},
		{"Новый Largus универсал", "largus-wagon-new"},
		{"Новый Largus фургон", "largus-van-new"},
		{"Новый Largus Cross", "largus-cross-new"},
		{"Новый  Eclipse Cross", "eclipse-cross-new"},
		{"Kodiaq NEW", "kodiaq-new"},
		{"GS8 new", "gs8-new"},
		{"Granta Бортовой", "granta-board"},
		{"SF5 Фургон", "sf5-van"},
		{"Niva Legend Бортовой", "niva-legend-board"},
		{"Niva Legend Фургон", "niva-legend-van"},
		{"БиДжей 60 (BJ60)", "bj60"},
		{"Икс 55 (X55)", "x55"},
		{"Tiggo 8 PRO MAX [рестайлинг]", "tiggo8-pro-max-restyling"},
		{"001 (G11)", "001g11"},
		{"Coolray (SX11-A5)", "coolray-sx11a5"},
		{"C50 2.0", "c50-20"},
		{"NEW STAR TRUCK Борт", "star-truck-board-new"},
		{"NEW STAR TRUCK Фургон", "star-truck-van-new"},
	}

	for _, tc := range tests {
		got := generateModelAlias(tc.name, "new")
		if got != tc.want {
			t.Errorf("generateModelAlias(%q) = %q, want %q", tc.name, got, tc.want)
		}
	}
}
