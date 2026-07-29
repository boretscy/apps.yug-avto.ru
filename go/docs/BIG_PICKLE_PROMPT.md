# Промпт для Big Pickle — улучшения Go CIS

Ты — senior Go-разработчик. Нужно улучшить production-сервис CIS (каталог авто для Yug-Avto/Avatr).

## Контекст проекта

Путь: `/Users/boretscy/Documents/Work/YA/remoteServers/yapps/apps.avatr-yugavto.ru/go`  
Module: `github.com/yugavto/apps` (Go 1.22)

Два бинарника:

- `cmd/api/main.go` — REST API (каталог, фильтры, поиск, SEO meta)
- `cmd/cron/main.go` — синхронизация из AutoCRM каждые 2 мин + daily sync брендов/моделей в 06:00

Архитектура:

- `internal/cis/*` — вся доменная логика (sync, handlers, search, images, SEO)
- `pkg/autocrm` — клиент AutoCRM API
- `pkg/db` — MySQL (sqlx)
- `config/config.go` — env через envconfig + godotenv

Blue/green sync: две таблицы `yapps_app_cis_vehicles_one/two`. Cron пишет в staging (cron table), API читает prod. После sync вызывается `IsOK()` → `ToggleTables()` → `ProcessImages()`.

## Принципы работы

1. Минимальный diff — не переписывай всё с нуля, не меняй поведение API без необходимости.
2. Сохраняй обратную совместимость API (кроме явно указанных breaking changes).
3. Не добавляй новые зависимости без веской причины (slog — ok, это stdlib).
4. Не коммить `.env`, `*.log`, секреты.
5. После изменений: `go build ./...` и `go test ./...` должны проходить.
6. Комментарии — только для неочевидной логики.
7. Следуй стилю существующего кода.

## Фаза 1 — Quick wins (сделать в первую очередь)

### 1.1 Баг SyncAllBrands()

Файл: `internal/cis/service.go`, строки ~310-324

Сейчас внутри цикла по `brands.Items` вызывается `SyncBrands("new")` — все бренды синкаются N раз.

Исправление:

```go
func (s *Service) SyncAllBrands() error {
    brands, err := s.crm.GetBrands()
    if err != nil {
        return err
    }
    if err := s.SyncBrands("new"); err != nil {
        return err
    }
    for _, b := range brands.Items {
        if err := s.SyncModels(b.ID, "new"); err != nil {
            return err
        }
    }
    return nil
}
```

### 1.2 Баг ToggleTables() — panic

Файл: `internal/cis/service.go`, строка ~168

Заменить `s.db.MustBegin()` на:

```go
tx, err := s.db.Beginx()
if err != nil {
    return fmt.Errorf("begin tx: %w", err)
}
defer tx.Rollback()
// ... existing logic ...
return tx.Commit()
```

### 1.3 Опечатка в JSON API

Файл: `internal/cis/handler.go`, строка ~489

`resp["recomended"]` → `resp["recommended"]`

ВАЖНО: если фронт использует `"recomended"`, добавь оба ключа на переходный период:

```go
resp["recommended"] = recItems
resp["recomended"] = recItems // deprecated, remove after frontend migration
```

### 1.4 Дублирование brandAlias

Файл: `internal/cis/service.go`, строки ~34-71

Удали метод `(s *Service) brandAlias()` — он не используется. Оставь только `defaultBrandAlias()`. Проверь grep'ом, что нигде не сломалось.

### 1.5 .gitignore

Создай `go/.gitignore`:

```
.env
*.log
*.log.bak
/bin/
/upload/
```

## Фаза 2 — Security hardening

### 2.1 Auth: query token → Bearer header

Файл: `cmd/api/main.go`

Сейчас `tokenAuth` проверяет `r.URL.Query().Get("token")`.

Сделай middleware, который принимает:

1. `Authorization: Bearer <token>` (основной способ)
2. `?token=` (deprecated, для обратной совместимости — log warning при использовании)

Content-Type для 401: `application/json` (сейчас `http.Error` без header).

### 2.2 Защита POST /api/v1/cis/sync

Файл: `internal/cis/handler.go` — `RegisterRoutes`, `handleSync`

`/sync` должен требовать auth ВСЕГДА, даже если `API_TOKEN` пуст.

Вариант: добавить `SYNC_TOKEN` в `config/config.go`; если не задан — fallback на `API_TOKEN`; если оба пусты — endpoint возвращает 503 или disabled.

Не запускай `FullSync()` без проверки токена.

### 2.3 Убрать InsecureSkipVerify

Файл: `internal/cis/images.go`, строки ~61-66

Убери `TLSClientConfig` с `InsecureSkipVerify: true`. Используй стандартный `http.Client` с `Timeout`.

Если нужен custom CA — добавь env `IMAGE_DOWNLOAD_CA_FILE`, но по умолчанию — нормальный TLS.

### 2.4 Sanitize 500 errors

Файл: `internal/cis/handler.go` (и другие handlers)

Сейчас: `http.Error(w, err.Error(), 500)` — утекает SQL/внутренние ошибки.

Паттерн:

```go
log.Printf("handleX error: %v", err)
w.Header().Set("Content-Type", "application/json")
w.WriteHeader(http.StatusInternalServerError)
json.NewEncoder(w).Encode(map[string]string{"error": "internal server error"})
```

Детали ошибки — только в log.

### 2.5 Config: пути в env

Файлы: `config/config.go`, `internal/cis/images.go`, `internal/orientation/model.go`

Добавь env-переменные с fallback на текущие hardcoded значения:

- `CWEBP_PATH` (default: `LookPath("cwebp")`, затем текущий fallback path)
- `ONNX_LIB_PATH` (default: `/usr/lib/x86_64-linux-gnu/libonnxruntime.so`)

## Фаза 3 — Dead code cleanup

### 3.1 Удалить неиспользуемый calltouch

- Удали `pkg/calltouch/client.go` (нигде не импортируется)
- Удали из `config/config.go`: `CalltouchBaseURL`, `CalltouchAPIKey`, `TelegramBotToken`

Если удаление ломает что-то — оставь config fields с комментарием `// unused`, но предпочтительно удалить.

## Фаза 4 — Unit tests

Создай тесты без БД и без ONNX:

### internal/cis/search_test.go

Table-driven tests для NL-поиска:

- «до 3 млн» → `PriceTo`
- «не старше 2020» → `YearTo`
- «chery» / «чери» → brand synonym
- keyboard layout fix (если есть публичная функция)

### internal/cis/query_test.go

Тесты `BuildVehicleQuery` / `buildConditions`:

- пустой фильтр
- brand + model
- price range, year range
- проверяй что SQL содержит ожидаемые placeholders (`?`), а не конкатенацию user input

### internal/cis/service_test.go

- `defaultBrandAlias` для chery/tank/wey/unknown
- `expandFilterBrands` — alias expansion только для `typeID=1` без model

Тесты orientation (`orient_test.go`) не трогай.

## Фаза 5 — Observability (если успеваешь)

### cmd/api/main.go

- Заменить `log` на `slog` (`log/slog`)
- Graceful shutdown: `signal.Notify(SIGINT, SIGTERM)` → `srv.Shutdown(ctx)`
- Middleware: log method, path, status, duration_ms
- `GET /healthz` → `{"status":"ok"}` + DB ping (`SELECT 1`)

### cmd/cron/main.go

- `slog` вместо `log` для ключевых событий sync

## НЕ делать в этом PR (out of scope)

- Полный рефакторинг `handler.go` на подпакеты
- Кэширование `handleFilter`
- Retry/backoff для autocrm
- Скрытие VIN
- Integration tests с MySQL
- OpenAPI/Swagger
- Telegram/Calltouch интеграция

## Acceptance criteria

- [ ] `go build ./...` — OK
- [ ] `go test ./...` — OK (новые unit-тесты проходят без env)
- [ ] `SyncAllBrands` вызывает `SyncBrands` один раз
- [ ] `ToggleTables` не использует `MustBegin`
- [ ] Auth работает через Bearer header
- [ ] `/sync` защищён отдельным или общим токеном
- [ ] `InsecureSkipVerify` удалён
- [ ] 500 не отдаёт `err.Error()` клиенту
- [ ] `.gitignore` создан
- [ ] calltouch dead code удалён
- [ ] `README.md` дополнен: env vars, запуск api/cron, зависимости (MySQL, cwebp, ONNX)

## Порядок коммитов (если делаешь несколько)

1. `fix: SyncAllBrands and ToggleTables panic`
2. `fix: API typo recommended + brandAlias dedup`
3. `chore: add .gitignore`
4. `security: Bearer auth, protect sync, remove InsecureSkipVerify, sanitize errors`
5. `test: unit tests for search, query, brand alias`
6. `feat: healthz, slog, graceful shutdown` (optional)
7. `docs: expand README`

Начни с Фазы 1, затем 2. Остановись и покажи diff summary перед Фазой 3+, если scope большой.

Рабочая директория: `/Users/boretscy/Documents/Work/YA/remoteServers/yapps/apps.avatr-yugavto.ru/go`
