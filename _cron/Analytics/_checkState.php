<?php
#!/usr/bin/php
	
ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
include $_SERVER['DOCUMENT_ROOT'].'/core/App.php';

$res = $app->Analytics->getADealerships()['result'];
$t = json_decode(file_get_contents(__DIR__.'/data/dealerships.json'), true);

if ($res !== $t) {

    file_put_contents( __DIR__.'/data/dealerships.json', json_encode($res) );

    $s = json_decode(file_get_contents(__DIR__.'/data/state.json'), true);
    $s['state']['attention'] = true;
    $s['event'] = 'updateDC';
    file_put_contents( __DIR__.'/data/state.json', json_encode($s) );
}



// $s = [
//     'state' => [
//         'timestamp' => time(),
//         'attention' => false,
//     ],
//     'event' => false,
//     'events' => [
//         'updateDC' => ['Внимание! Изменения в списке ДЦ. Нажмите кнопку "исправлено" после доработки метода selectDealership().']
//     ]
// ];
// file_put_contents( __DIR__.'/data/state.json', json_encode($s) );
?>