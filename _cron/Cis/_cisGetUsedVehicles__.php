<?php
declare(strict_types=1);
#!/usr/bin/php

// ============================================================
// Конфигурация логирования и обработки ошибок для PHP 7.3+
// ============================================================

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$logDir = __DIR__ . '/log';
$errorLogFile = $logDir . '/errors.log';
$usedLogFile = $logDir . '/used.txt';

// Создание директории логов если её нет
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
ini_set('error_log', $errorLogFile);

/**
 * Функция для логирования ошибок в файл
 * @param string $message Сообщение об ошибке
 * @param array $context Контекст ошибки (опционально)
 */
function logError(string $message, array $context = []): void {
    global $errorLogFile;
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    $logMessage = "[$timestamp] ERROR: $message$contextStr" . PHP_EOL;
    file_put_contents($errorLogFile, $logMessage, FILE_APPEND | LOCK_EX);
    error_log($message);
}

/**
 * Функция для логирования информации
 * @param string $message Сообщение
 */
function logInfo(string $message): void {
    global $usedLogFile;
    $logMessage = $message . PHP_EOL;
    file_put_contents($usedLogFile, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Главной блок try-catch для обработки исключений
 */
try {
    $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
    
    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/core/App.php')) {
        throw new Exception('Не найден App.php в ' . $_SERVER['DOCUMENT_ROOT']);
    }
    
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/App.php';

    $startTime = date('Y-m-d H:i:s');
    Helper::sp('Старт: ' . $startTime);
    
    $log = [
        'mess'  => PHP_EOL . PHP_EOL . '-----------------------------------------------' . PHP_EOL . PHP_EOL . 'Б/у авто старт: ' . $startTime . PHP_EOL,
        'count' => 0,
        'ok'    => 0,
        'er'    => ['c' => 0, 'i' => []],
        'to'    => ['c' => 0, 'i' => []],
        'photo' => ['c' => 0, 'i' => []],
        'an'    => ['i' => []]
    ];

    $brands = [];
    $dealershipIds = [1364, 1367, 1370, 1373, 1489, 1492, 1499, 1502, 1533, 1328];
    $excludedVehicleId = 1314264;

    // ============================================================
    // Получение данных из CIS
    // ============================================================
    try {
        $res = $app->Cis->getVehiclesUsed();
        if (empty($res)) {
            throw new Exception('Пустой ответ от getVehiclesUsed()');
        }
    } catch (Exception $e) {
        logError('Ошибка при получении данных б/у автомобилей', ['message' => $e->getMessage()]);
        throw $e;
    }

    // ============================================================
    // Обновление данных по брендам
    // ============================================================
    try {
        foreach ($res['filter']['brands'] ?? [] as $i) {
            try {
                $i['alias'] = $app->Cis->generateModelAlias($i['name'] ?? '');
                $i['vehicles'] = 0;
                $brands[$i['id']] = $i;

                $arIns = [
                    'ext_id' => (int)($i['id'] ?? 0),
                    'name' => $i['name'] ?? '',
                    'ru_name' => $app->Cis->transliterateBrandToRu($i['name'] ?? ''),
                    'code' => $i['alias'],
                ];

                $b_id = $app->Cis->MySQL->getOne('SELECT id FROM yapps_app_cis_brands WHERE ext_id = ?i', $arIns['ext_id']);
                
                if ($b_id) {
                    $app->Cis->MySQL->query('UPDATE yapps_app_cis_brands SET ?u WHERE id = ?i', $arIns, $b_id);
                } else {
                    $app->Cis->MySQL->query('INSERT INTO yapps_app_cis_brands SET ?u', $arIns);
                }
            } catch (Exception $e) {
                logError('Ошибка при обновлении бренда ' . ($i['id'] ?? 'unknown'), ['message' => $e->getMessage()]);
            }
        }
    } catch (Exception $e) {
        logError('Критическая ошибка при обновлении брендов', ['message' => $e->getMessage()]);
        throw $e;
    }

    // ============================================================
    // Обновление данных по моделям
    // ============================================================
    try {
        foreach ($res['filter']['models'] ?? [] as $i) {
            try {
                $i['alias'] = $app->Cis->generateModelAlias($i['name'] ?? '');
                $i['vehicles'] = 0;
                $i['statistics'][1]['counter'] = 0;
                $i['statistics'][2]['counter'] = 0;

                $brandId = $i['brand_id'] ?? null;
                if ($brandId && isset($brands[$brandId])) {
                    $brands[$brandId]['models'][$i['id']] = $i;
                }

                $arIns = [
                    'ext_id' => (int)($i['id'] ?? 0),
                    'brand_id' => $app->Cis->MySQL->getOne('SELECT id FROM yapps_app_cis_brands WHERE ext_id = ?i', (int)($i['brand_id'] ?? 0)),
                    'name' => $i['name'] ?? '',
                    'code' => $i['alias'],
                    'body_id' => $app->Cis->MySQL->getOne('SELECT id FROM yapps_app_cis_bodies WHERE code = ?s', $app->Cis->getBody($i['body_type'] ?? '')['code'] ?? ''),
                ];

                $m_id = $app->Cis->MySQL->getOne('SELECT id FROM yapps_app_cis_models_used WHERE ext_id = ?i', $arIns['ext_id']);
                
                if ($m_id) {
                    $app->Cis->MySQL->query('UPDATE yapps_app_cis_models_used SET ?u WHERE id = ?i', $arIns, $m_id);
                } else {
                    $app->Cis->MySQL->query('INSERT INTO yapps_app_cis_models_used SET ?u', $arIns);
                }
            } catch (Exception $e) {
                logError('Ошибка при обновлении модели ' . ($i['id'] ?? 'unknown'), ['message' => $e->getMessage()]);
            }
        }
    } catch (Exception $e) {
        logError('Критическая ошибка при обновлении моделей', ['message' => $e->getMessage()]);
        throw $e;
    }

    Helper::sp('Vehicles получены ' . date('d-m-Y в H:i:s') . ', кол-во: ' . count($res['items'] ?? []));
    $log['mess'] .= 'Б/у vehicles получены ' . date('d-m-Y в H:i:s') . ', кол-во: ' . count($res['items'] ?? []) . PHP_EOL;

    // ============================================================
    // Обработка каждого автомобиля
    // ============================================================
    try {
        foreach ($res['items'] ?? [] as $k => $r) {
            try {
                $dealershipId = (int)($r['dealership']['id'] ?? 0);
                $vehicleId = (int)($r['id'] ?? 0);

                // Фильтрация по дилершипам
                if (!in_array($dealershipId, $dealershipIds, true) || $vehicleId === $excludedVehicleId) {
                    continue;
                }

                $log['count']++;
                $time = time();
                
                $tmp = $app->Cis->getVehicle($vehicleId, 2);
                $diff = time() - $time;

                // Логирование долгих запросов
                if ($tmp && $diff > 2) {
                    Helper::sp($vehicleId . ' получен за ' . $diff . ' с');
                    $log['mess'] .= $vehicleId . ' получен за ' . $diff . ' с' . PHP_EOL;
                }

                // Обработка таймаутов
                if (!$tmp) {
                    $timeout = $app->Cis->getConf()->cURL_timeout ?? 30;
                    Helper::sp($vehicleId . ' завершен по таймауту более ' . $timeout . ' с');
                    $log['mess'] .= $vehicleId . ' завершен по таймауту более ' . $timeout . ' с' . PHP_EOL;
                    $log['to']['c']++;
                    $log['to']['i'][] = $vehicleId;
                    continue;
                }

                // Анализ результата
                if ($tmp['log'] ?? false) {
                    $log['ok']++;
                    
                    if ($tmp['update_images'] ?? false) {
                        $log['photo']['c']++;
                        $log['photo']['i'][] = [
                            'id' => $tmp['id'] ?? $vehicleId,
                            'vin' => $tmp['vin'] ?? $r['vin'] ?? null
                        ];
                    }

                    // Обновление статистики марок и моделей
                    $brandId = $r['brand_id'] ?? null;
                    $modelId = $r['ref_model_id'] ?? null;
                    $statusId = $r['status']['id'] ?? null;

                    if ($brandId && isset($brands[$brandId])) {
                        $brands[$brandId]['vehicles']++;
                        
                        if ($modelId && isset($brands[$brandId]['models'][$modelId])) {
                            $brands[$brandId]['models'][$modelId]['vehicles']++;
                            
                            if ($statusId) {
                                if (!isset($brands[$brandId]['models'][$modelId]['statistics'][$statusId])) {
                                    $brands[$brandId]['models'][$modelId]['statistics'][$statusId] = ['counter' => 0];
                                }
                                $brands[$brandId]['models'][$modelId]['statistics'][$statusId]['counter']++;
                            }
                        }
                    }
                } else {
                    // Проверка обязательных полей
                    if (($tmp['id'] ?? null) && ($tmp['vin'] ?? null)) {
                        $log['er']['c']++;
                        $log['er']['i'][] = [
                            'id' => $tmp['id'],
                            'vin' => $tmp['vin']
                        ];
                    } else {
                        $tmp['id'] = $vehicleId;
                        $tmp['vin'] = $r['vin'] ?? null;
                        $log['an']['i'][] = $tmp;
                    }
                }
            } catch (Exception $e) {
                logError('Ошибка при обработке б/у автомобиля ' . ($r['id'] ?? 'unknown'), [
                    'message' => $e->getMessage(),
                    'vehicle_id' => $r['id'] ?? null,
                    'dealership_id' => $r['dealership']['id'] ?? null
                ]);
            }
        }
    } catch (Exception $e) {
        logError('Критическая ошибка при обработке автомобилей', ['message' => $e->getMessage()]);
        throw $e;
    }

    Helper::sp('Vehicles обработаны ' . date('d-m-Y в H:i:s') . ', кол-во: ' . $log['count']);
    $log['mess'] .= 'Б/у vehicles обработаны ' . date('d-m-Y в H:i:s') . PHP_EOL;
    $log['mess'] .= 'Кол-во добавленных: ' . $log['ok'] . PHP_EOL;
    $log['mess'] .= 'Кол-во авто без модели или бренда: ' . $log['er']['c'] . PHP_EOL;
    $log['mess'] .= 'Кол-во авто сброшенных по таймауту: ' . $log['to']['c'] . PHP_EOL;
    $log['mess'] .= 'Кол-во авто обновить фото: ' . $log['photo']['c'] . PHP_EOL;

    // ============================================================
    // Обновление изображений
    // ============================================================
    try {
        $app->Cis->setImages();
    } catch (Exception $e) {
        logError('Ошибка при обновлении изображений', ['message' => $e->getMessage()]);
    }

    // ============================================================
    // Генерация финального отчета
    // ============================================================
    try {
        // Ошибочные авто
        if ($log['er']['c'] > 0) {
            $log['mess'] .= PHP_EOL . 'Ошибочные авто (' . $log['er']['c'] . '): ' . PHP_EOL;
            foreach ($log['er']['i'] as $i) {
                $log['mess'] .= ($i['vin'] ?? 'N/A') . ' | ' . ($i['id'] ?? 'N/A') . PHP_EOL;
            }
            $log['mess'] .= PHP_EOL;
        }

        // Сброшенные авто по таймауту
        if ($log['to']['c'] > 0) {
            $log['mess'] .= PHP_EOL . 'Сброшенные авто по таймауту (' . $log['to']['c'] . '): ' . PHP_EOL;
            foreach ($log['to']['i'] as $i) {
                $log['mess'] .= $i . ',';
            }
            $log['mess'] .= PHP_EOL;
        }

        // Авто для обновления фото
        if ($log['photo']['c'] > 0) {
            $log['mess'] .= PHP_EOL . 'Обновить фото (' . $log['photo']['c'] . '): ' . PHP_EOL;
            foreach ($log['photo']['i'] as $i) {
                $log['mess'] .= ($i['vin'] ?? 'N/A') . ' | ' . ($i['id'] ?? 'N/A') . PHP_EOL;
            }
            $log['mess'] .= PHP_EOL;
        }

        // Аномалии
        if (!empty($log['an']['i'])) {
            $log['mess'] .= PHP_EOL . 'Аномалий: ' . count($log['an']['i']) . PHP_EOL;
            foreach ($log['an']['i'] as $i) {
                $log['mess'] .= print_r($i, true) . PHP_EOL;
            }
            $log['mess'] .= PHP_EOL;
        }

        Helper::sp('Финиш: ' . date('Y-m-d H:i:s'));
        $log['mess'] .= 'Б/у авто финиш: ' . date('Y-m-d H:i:s');

        // ============================================================
        // Переключение таблицы и запись логов
        // ============================================================
        try {
            if ($app->Cis->isOk_cron()) {
                $table = $app->Cis->toggleTable();
                Helper::sp('Таблица переключена');
                $log['mess'] .= PHP_EOL . PHP_EOL . '-----------------------------------------------' . PHP_EOL . PHP_EOL . 'Таблица переключена';
                
                if (!file_put_contents($usedLogFile, $log['mess'], FILE_APPEND | LOCK_EX)) {
                    throw new Exception('Не удалось записать лог в ' . $usedLogFile);
                }

                $newLogContent = file_get_contents($logDir . '/new.txt') ?? '';
                $app->Cis->Log($newLogContent . $log['mess'], $table['hash'] ?? '');
                
                logInfo('Успешно завершено. Добавлено: ' . $log['ok'] . ', Ошибок: ' . $log['er']['c'] . ', Таблица переключена');
            } else {
                logInfo('Некритическая ошибка: таблица не переключена');
            }
        } catch (Exception $e) {
            logError('Ошибка при переключении таблицы и записи логов', ['message' => $e->getMessage()]);
        }

    } catch (Exception $e) {
        logError('Ошибка при генерации отчета', ['message' => $e->getMessage()]);
        throw $e;
    }

} catch (Exception $e) {
    $errorMessage = 'Критическая ошибка в скрипте получения б/у автомобилей: ' . $e->getMessage();
    logError($errorMessage, [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    trigger_error($errorMessage, E_USER_ERROR);
}

?>
