<?php
#!/usr/bin/php
	
ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
include $_SERVER['DOCUMENT_ROOT'].'/core/App.php';


$timestamp = time()-24*60*60;

// $timestamp = json_decode(file_get_contents(__DIR__.'/data/date.json'), true)['timestamp']+24*60*60;
// if ( $timestamp >= strtotime('26.01.2025') ) die;
// file_put_contents( __DIR__.'/data/date.json', json_encode(['timestamp'=>$timestamp]) );

Helper::sp('Старт: '.date('d-m-Y в H:i:s').'. За '.date('d-m-Y', $timestamp) );

$a = $app->Analytics->getARetailCases(['createdSince' => date('d.m.Y', $timestamp),'createdTill' => date('d.m.Y', $timestamp)]);
$u = $app->Analytics->getAUsedCases(['createdSince' => date('d.m.Y', $timestamp),'createdTill' => date('d.m.Y', $timestamp)]);
$cases = array_merge(
    ( is_array($a) ) ? $a : [],
    ( is_array($u) ) ? $u : []
);
$cases_holding = [];

$projects = json_decode(file_get_contents('https://portal.yug-avto.ru/service/calltracking/ajax/projects.php?action=get'), true);
$project_holding = $projects[102580]; unset( $projects[102580] );

// $d = $app->Analytics->getADealerships();

$calls = []; $bids = []; 
foreach ( $projects as $p ) {
    $tmp = $app->Analytics->getCCalls(
        $p['PROPERTY_CALLTOUCH_API_ID_VALUE'], 
        [
            'clientApiId' => $p['PROPERTY_CALLTOUCH_API_TOKEN_VALUE'],
            'dateFrom' => date('d/m/Y', $timestamp),
            'dateTo' => date('d/m/Y', $timestamp),
        ]
    );
    if ( count($tmp) ) 
        $calls = array_merge(
            ( is_array($calls) ) ? $calls : [],
            ( is_array($tmp) ) ? $tmp : []
        );

    $tmp = $app->Analytics->getCBids(
        [
            'clientApiId' => $p['PROPERTY_CALLTOUCH_API_TOKEN_VALUE'],
            'dateFrom' => date('m/d/Y', $timestamp),
            'dateTo' => date('m/d/Y', $timestamp),
        ]
    );
    if ( count($tmp) ) $bids = array_merge(
        ( is_array($bids) ) ? $bids : [],
        ( is_array($tmp) ) ? $tmp : []
    );
}
$calls_holding = $app->Analytics->getCCalls(
    $project_holding['PROPERTY_CALLTOUCH_API_ID_VALUE'], 
    [
        'clientApiId' => $project_holding['PROPERTY_CALLTOUCH_API_TOKEN_VALUE'],
        'dateFrom' => date('d/m/Y', $timestamp),
        'dateTo' => date('d/m/Y', $timestamp),
    ]
);
$bids_holding = $app->Analytics->getCBids(
    [
        'clientApiId' => $project_holding['PROPERTY_CALLTOUCH_API_TOKEN_VALUE'],
        'dateFrom' => date('m/d/Y', $timestamp),
        'dateTo' => date('m/d/Y', $timestamp),
    ]
);
$calltouch = array_merge(
    ( is_array($calls) ) ? $calls : [],
    ( is_array($bids) ) ? $bids : []
);
$calltouch_holding = array_merge(
    ( is_array($calls_holding) ) ? $calls_holding : [],
    ( is_array($bids_holding) ) ? $bids_holding : []
);
Helper::sp('Данные из calltouch получены '.date('d-m-Y в H:i:s').' - проекты: '.count($calltouch).', холдинг: '.count($calltouch_holding).', звонков: '.count($calls).', заявок: '.count($bids) );

foreach ( $cases as $k => $rl ) {
    $rl_holding = $rl;
    foreach ( $calltouch as $c ) {
        if ( $c['phone'] == $rl['phone'] ) {
            $cases[$k]['channel'] = ( $c['channel'] ) ?: '';
            $cases[$k]['source'] = ( $c['source'] ) ?: '';
            $cases[$k]['utm_campaign'] = ( $c['utm_campaign'] ) ?: '';
            $cases[$k]['utm_content'] = ( $c['utm_content'] ) ?: '';
            $cases[$k]['utm_term'] = ( $c['utm_term'] ) ?: '';
            $cases[$k]['cabinet'] = $c['cabinet'];
            $cases[$k]['type'] = $c['type'];
            break;
        }
    }
    foreach ( $calltouch_holding as $c ) {
        if ( $c['phone'] == $rl['phone'] ) {
            $rl_holding['channel'] = ( $c['channel'] ) ?: '';
            $rl_holding['source'] = ( $c['source'] ) ?: '';
            $rl_holding['utm_campaign'] = ( $c['utm_campaign'] ) ?: '';
            $rl_holding['utm_content'] = ( $c['utm_content'] ) ?: '';
            $rl_holding['utm_term'] = ( $c['utm_term'] ) ?: '';
            $rl_holding['cabinet'] = $c['cabinet'];
            $rl_holding['type'] = $c['type'];
            $rl_holding['dealership_id'] = 61;
            break;
        }
    }
    if ($rl_holding['cabinet']) $cases_holding[] = $rl_holding;
}

$cases = array_merge(
    ( is_array($cases) ) ? $cases : [],
    ( is_array($cases_holding) ) ? $cases_holding : []
);
$calltouch = array_merge(
    ( is_array($calltouch) ) ? $calltouch : [],
    ( is_array($calltouch_holding) ) ? $calltouch_holding : []
);
foreach ( $cases as $item ) $app->Analytics->pushAutodealer( $item );
foreach ( $calltouch as $item ) $app->Analytics->pushCalltouch( $item );

/*  Очистка дублей */
$res = $app->MySQL->getAll("SELECT DISTINCT timestamp,channel,source,utm_campaign,utm_content,utm_term,phone,cabinet,type,unique_flag FROM yapps_app_analytics_calltouch WHERE timestamp >= ?s AND timestamp < ?s", date('Y-m-d H:i:s', $timestamp), date('Y-m-d H:i:s', $timestamp+24*60*60));
$app->MySQL->query('DELETE FROM yapps_app_analytics_calltouch WHERE timestamp >= ?s AND timestamp < ?s', date('Y-m-d H:i:s', $timestamp), date('Y-m-d H:i:s', $timestamp+24*60*60));
foreach ( $res as $r ) $app->MySQL->query('INSERT INTO yapps_app_analytics_calltouch SET ?u', $r);
/********************** */

/* Проверка соотв кабинета и дц */
$dd = $app->Analytics->getYappsDealerships();
foreach ( $dd as $d ) $dcs[$d['id']] = (string)$d['ct_id'];
$rr = $app->MySQL->getAll('SELECT * FROM yapps_app_analytics_autodealer WHERE timestamp >= ?s AND timestamp < ?s', date('Y-m-d H:i:s', $timestamp), date('Y-m-d H:i:s', $timestamp+24*60*60));
foreach ( $rr as $r ) {
    if ( $r['cabinet'] != $dcs[$r['dealership_id']] ) {
        $app->MySQL->query('UPDATE yapps_app_analytics_autodealer SET ?u WHERE ext_id = ?i', ['cabinet'=>$dcs[$r['dealership_id']]], $r['ext_id']);
    }
}
/********************** */

/* Обновление выдач по ранним контрактам */
Helper::sp('Обновление выдач по ранним контрактам: '.date('d-m-Y в H:i:s') );
$iY = $app->Analytics->getAStages(
    [
        'caseTypes' => 4,
        'types' => 12,
        'states' => 3,
        'createdSince' => date('d.m.Y', $timestamp),
        'createdTill' => date('d.m.Y', $timestamp),
    ]
);
foreach ( $iY as $i ) $app->MySQL->query('UPDATE yapps_app_analytics_autodealer SET ?u WHERE ext_id = ?i', ['issuance'=>1, 'issuance_date'=>$r['closedAt']], $i['caseId']); // выдача состоялась
$iY = $app->Analytics->getAStages(
    [
        'caseTypes' => 4,
        'types' => 12,
        'states' => 4,
        'createdSince' => date('d.m.Y', $timestamp),
        'createdTill' => date('d.m.Y', $timestamp),
    ]
);
foreach ( $iY as $i ) $app->MySQL->query('UPDATE yapps_app_analytics_autodealer SET ?u WHERE ext_id = ?i', ['issuance'=>0, 'issuance_date'=>'0000-00-00 00:00:00'], $i['caseId']); // отмена
$iY = $app->Analytics->getAStages(
    [
        'caseTypes' => 4,
        'types' => 12,
        'states' => 5,
        'createdSince' => date('d.m.Y', $timestamp),
        'createdTill' => date('d.m.Y', $timestamp),
    ]
);
foreach ( $iY as $i ) $app->MySQL->query('UPDATE yapps_app_analytics_autodealer SET ?u WHERE ext_id = ?i', ['issuance'=>0, 'issuance_date'=>'0000-00-00 00:00:00'], $i['caseId']); // удаление
/********************** */

Helper::sp('Финиш: '.date('d-m-Y в H:i:s') );

?>
