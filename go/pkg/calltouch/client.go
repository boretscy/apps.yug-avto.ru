package calltouch

import (
	"encoding/json"
	"fmt"
	"net/http"
	"time"
)

type Client struct {
	baseURL string
	apiKey  string
	http    *http.Client
}

type Call struct {
	ID        int    `json:"id"`
	Phone     string `json:"phone"`
	Duration  int    `json:"duration"`
	Date      string `json:"date"`
	SiteID    string `json:"site_id"`
}

type CallsResponse struct {
	Calls []Call `json:"calls"`
}

func NewClient(baseURL, apiKey string) *Client {
	return &Client{
		baseURL: baseURL,
		apiKey:  apiKey,
		http:    &http.Client{Timeout: 30 * time.Second},
	}
}

func (c *Client) GetCalls(dateFrom, dateTo string) (*CallsResponse, error) {
	url := fmt.Sprintf("%s/calls?dateFrom=%s&dateTo=%s&key=%s",
		c.baseURL, dateFrom, dateTo, c.apiKey)

	resp, err := c.http.Get(url)
	if err != nil {
		return nil, fmt.Errorf("calltouch get calls: %w", err)
	}
	defer resp.Body.Close()

	var result CallsResponse
	if err := json.NewDecoder(resp.Body).Decode(&result); err != nil {
		return nil, fmt.Errorf("calltouch decode: %w", err)
	}
	return &result, nil
}
