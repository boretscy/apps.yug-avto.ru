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
$appLogFile = $logDir . '/new.txt';

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
    global $appLogFile;
    $logMessage = $message . PHP_EOL;
    file_put_contents($appLogFile, $logMessage, FILE_APPEND | LOCK_EX);
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
        'mess'  => 'Новые авто старт: ' . $startTime . PHP_EOL,
        'count' => 0,
        'ok'    => 0,
        'er'    => ['c' => 0, 'i' => []],
        'to'    => ['c' => 0, 'i' => []],
        'photo' => ['c' => 0, 'i' => []],
        'eq'    => ['c' => 0, 'i' => []],
        'an'    => ['i' => []]
    ];

    // ============================================================
    // Очистка таблицы
    // ============================================================
    try {
        $app->MySQL->query('TRUNCATE ?n', $app->Cis->getTable()->cron);
    } catch (Exception $e) {
        logError('Ошибка при очистке таблицы', ['message' => $e->getMessage()]);
        throw $e;
    }

    // ============================================================
    // Получение списка новых автомобилей
    // ============================================================
    try {
        $res = $app->Cis->getVehicles();
        Helper::sp('Vehicles получены ' . date('d-m-Y в H:i:s') . ', кол-во: ' . count($res));
        $log['mess'] .= 'Новые vehicles получены ' . date('d-m-Y в H:i:s') . ', кол-во: ' . count($res) . PHP_EOL;
        $log['count'] = count($res);
    } catch (Exception $e) {
        logError('Ошибка при получении списка vehicles', ['message' => $e->getMessage()]);
        throw $e;
    }

    // ============================================================
    // Обработка каждого автомобиля
    // ============================================================
    foreach ($res as $k => $r) {
        try {
            $vehicleId = $r['id'] ?? null;
            
            if ($vehicleId === null) {
                throw new Exception('Отсутствует ID автомобиля');
            }

            $time = time();
            $res[$k] = $app->Cis->getVehicle($vehicleId, 1);
            $diff = time() - $time;

            // Логирование долгих запросов
            if ($res[$k] && $diff > 2) {
                Helper::sp($vehicleId . ' получен за ' . $diff . ' с');
                $log['mess'] .= $vehicleId . ' получен за ' . $diff . ' с' . PHP_EOL;
            }

            // Обработка таймаутов
            if (!$res[$k]) {
                $timeout = $app->Cis->getConf()->cURL_timeout ?? 30;
                Helper::sp($vehicleId . ' завершен по таймауту более ' . $timeout . ' с');
                $log['mess'] .= $vehicleId . ' завершен по таймауту более ' . $timeout . ' с' . PHP_EOL;
                $log['to']['c']++;
                $log['to']['i'][] = $vehicleId;
                continue;
            }

            // Анализ результата
            if ($res[$k]['log'] ?? false) {
                $log['ok']++;
                
                if ($res[$k]['update_images'] ?? false) {
                    $log['photo']['c']++;
                    $log['photo']['i'][] = [
                        'id' => $res[$k]['id'] ?? null,
                        'vin' => $res[$k]['vin'] ?? null
                    ];
                }
            } else {
                // Проверка обязательных полей
                if (($res[$k]['id'] ?? null) && ($res[$k]['vin'] ?? null)) {
                    $log['er']['c']++;
                    $log['er']['i'][] = [
                        'id' => $res[$k]['id'],
                        'vin' => $res[$k]['vin']
                    ];
                } else {
                    $res[$k]['id'] = $r['id'] ?? null;
                    $res[$k]['vin'] = $r['vin'] ?? null;
                    $log['an']['i'][] = $res[$k];
                }
            }

            // Обработка комплектаций
            if ($res[$k]['eq'] ?? null) {
                $log['eq']['i'][] = $res[$k]['eq'];
            }
        } catch (Exception $e) {
            logError('Ошибка при обработке автомобиля ' . ($r['id'] ?? 'unknown'), [
                'message' => $e->getMessage(),
                'vehicle_id' => $r['id'] ?? null,
                'vin' => $r['vin'] ?? null
            ]);
        }
    }

    // ============================================================
    // Генерация финального отчета
    // ============================================================
    try {
        Helper::sp('Vehicles обработаны ' . date('d-m-Y в H:i:s'));
        $log['mess'] .= 'Новые vehicles обработаны ' . date('d-m-Y в H:i:s') . PHP_EOL;
        $log['mess'] .= 'Кол-во добавленных: ' . $log['ok'] . PHP_EOL;
        $log['mess'] .= 'Кол-во авто без модели или бренда: ' . $log['er']['c'] . PHP_EOL;
        $log['mess'] .= 'Кол-во авто сброшенных по таймауту: ' . $log['to']['c'] . PHP_EOL;
        $log['mess'] .= 'Кол-во авто обновить фото: ' . $log['photo']['c'] . PHP_EOL;

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

        // Комплектации
        if ($log['eq']['c'] > 0) {
            $arr_eq = [];
            foreach ($log['eq']['i'] as $eq) {
                $key = ($eq['brand'] ?? 'unknown') . '_' . ($eq['model'] ?? 'unknown');
                if (!isset($arr_eq[$key])) {
                    $arr_eq[$key] = [];
                }
                $arr_eq[$key][] = $eq;
            }
            $log['mess'] .= PHP_EOL . 'Комплектаций: ' . count($arr_eq) . PHP_EOL;
            foreach ($arr_eq as $i) {
                if (is_array($i) && isset($i[0])) {
                    $log['mess'] .= ($i[0]['brand'] ?? 'N/A') . ' | ' . ($i[0]['model'] ?? 'N/A') . PHP_EOL;
                }
            }
            $log['mess'] .= PHP_EOL;
        }

        $log['mess'] .= 'Новые авто финиш: ' . date('Y-m-d H:i:s');

        // Запись логов
        if (!file_put_contents($appLogFile, $log['mess'], FILE_APPEND | LOCK_EX)) {
            throw new Exception('Не удалось записать лог в ' . $appLogFile);
        }

        Helper::sp('Финиш: ' . date('Y-m-d H:i:s'));
        logInfo('Успешно завершено. Добавлено: ' . $log['ok'] . ', Ошибок: ' . $log['er']['c']);

    } catch (Exception $e) {
        logError('Ошибка при записи логов', ['message' => $e->getMessage()]);
        throw $e;
    }

} catch (Exception $e) {
    $errorMessage = 'Критическая ошибка в скрипте получения новых автомобилей: ' . $e->getMessage();
    logError($errorMessage, [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    trigger_error($errorMessage, E_USER_ERROR);
}

?>
