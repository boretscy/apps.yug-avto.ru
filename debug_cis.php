<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "Session: "; var_dump($_SESSION);
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

$confData = include '/var/www/admin/data/www/apps.avatr-yugavto.ru/core/YApps/Configs/App/Cis.php';
echo "DataDir: " . $confData['DataDir'] . "\n";

$tablePath = '/home/admin/web/apps.yug-avto.ru/public_html' . $confData['DataDir'] . '/table.json';
echo "Table path: " . $tablePath . "\n";
echo "Table exists: " . (file_exists($tablePath) ? 'yes' : 'no') . "\n";

if (file_exists($tablePath)) {
    $table = json_decode(file_get_contents($tablePath));
    echo "Table: "; var_dump($table);
    
    $logFile = $_SERVER['DOCUMENT_ROOT'] . '/core/YApps/Logs/Cis/' . date('Y', $table->time) . '/' . date('m', $table->time) . '/' . date('d', $table->time) . '/' . $table->hash . '.txt';
    echo "Log file: " . $logFile . "\n";
    echo "Log exists: " . (file_exists($logFile) ? 'yes' : 'no') . "\n";
}

echo "OK\n";
