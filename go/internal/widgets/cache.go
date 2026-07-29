package widgets

import (
	"sync"
	"time"
)

type cacheItem struct {
	script    string
	expiresAt time.Time
}

type ScriptCache struct {
	mu    sync.RWMutex
	items map[string]cacheItem
	ttl   time.Duration
}

func NewScriptCache(ttl time.Duration) *ScriptCache {
	return &ScriptCache{
		items: make(map[string]cacheItem),
		ttl:   ttl,
	}
}

func (c *ScriptCache) Get(key string) (string, bool) {
	c.mu.RLock()
	defer c.mu.RUnlock()

	item, found := c.items[key]
	if !found {
		return "", false
	}

	if time.Now().After(item.expiresAt) {
		return "", false
	}

	return item.script, true
}

func (c *ScriptCache) Set(key string, script string) {
	c.mu.Lock()
	defer c.mu.Unlock()

	c.items[key] = cacheItem{
		script:    script,
		expiresAt: time.Now().Add(c.ttl),
	}
}

func (c *ScriptCache) Clear() {
	c.mu.Lock()
	defer c.mu.Unlock()
	c.items = make(map[string]cacheItem)
}
