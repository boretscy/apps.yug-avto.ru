<?php
#!/usr/bin/php
	
ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
include $_SERVER['DOCUMENT_ROOT'].'/core/App.php';

$users = $app->Expertbot->getDBActiveUsers();
$dealerships = $app->Expertbot->getDealerships();
$departaments = $app->Expertbot->getDepartaments();

$message = 'Рейтинг за неделю ('.date('d.m.Y', strtotime('- 6 days')).' - '.date('d.m.Y').')'.PHP_EOL.PHP_EOL;
$get = [
    'date_from' => date('Y-m-d', strtotime('- 6 days')),
    'date_to' => date('Y-m-d')
];
foreach ( $dealerships as $dealership ) {
    $get['dealership'] = $dealership['id'];
    foreach ( $departaments as $departament ) {
        $get['departament'] = $departament['id'];
        $items = $app->Expertbot->apiDBGetItems($get, 'timestamp');
        $arRes[] = [
            'name' => $dealership['name'],
            'type' => $departament['name'],
            'count' => count($items)
        ];
        // $message .= $dealership['name'].', '.$departament['name'].' - '.count($items).' '.Helper::getWorld(count($items), 'feedback').PHP_EOL;
    }
}

array_multisort(array_column($arRes, 'count'), SORT_DESC, SORT_NUMERIC, $arRes);
foreach ( $arRes as $i ) $message .= $i['name'].', '.$i['type'].' - '.$i['count'].' '.Helper::getWorld($i['count'], 'feedback').PHP_EOL;

Helper::sp($message);

foreach ( $users as $user ) {
    $app->Expertbot->sendPostMessage( $user['chat_id'], null, $message );
    $app->Expertbot->sendPostMessage( $user['chat_id'], 7 );
}