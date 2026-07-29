<?php
#!/usr/bin/php
	
ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
include $_SERVER['DOCUMENT_ROOT'].'/core/App.php';

$timestamp = $o = json_decode(file_get_contents(__DIR__.'/data/date.json'), true)['timestamp']+24*60*60;
if ( $timestamp >= strtotime('28.01.2025') ) die;
file_put_contents( __DIR__.'/data/date.json', json_encode(['timestamp'=>$timestamp]) );

Helper::sp('Старт: '.date('d-m-Y в H:i:s').'. За '.date('d-m-Y', $o).' - '.date('d-m-Y', $timestamp) );

$dcs = [108, 116];

foreach ( $dcs as $dc ) {

    $rs = [];
    $stages = $app->Analytics->getAStages(
        [
            'salonIds' => $dc,
            'caseTypes' => 4,
            'createdSince' => date('d.m.Y', $timestamp),
            'createdTill' => date('d.m.Y', $timestamp),
        ]
    )['result'];
    foreach ( $stages as $st ) $rs[] = $st['caseId'];
    /* -------- */
    $stages = $app->Analytics->getAStages(
        [
            'salonIds' => $dc,
            'caseTypes' => 4,
            'types' => 12,
            'states' => 3,
            'createdSince' => date('d.m.Y', $timestamp),
            'createdTill' => date('d.m.Y', $timestamp),
        ]
    )['result'];
    foreach ( $stages as $st ) $rs[] = $st['caseId'];
    /* -------- */
    $stages = $app->Analytics->getAStages(
        [
            'salonIds' => $dc,
            'caseTypes' => 4,
            'types' => 12,
            'states' => 4,
            'createdSince' => date('d.m.Y', $timestamp),
            'createdTill' => date('d.m.Y', $timestamp),
        ]
    )['result'];
    foreach ( $stages as $st ) $rs[] = $st['caseId'];
    /* -------- */
    $stages = $app->Analytics->getAStages(
        [
            'salonIds' => $dc,
            'caseTypes' => 4,
            'types' => 12,
            'states' => 5,
            'createdSince' => date('d.m.Y', $timestamp),
            'createdTill' => date('d.m.Y', $timestamp),
        ]
    )['result'];
    foreach ( $stages as $st ) $rs[] = $st['caseId'];
    $res = array_unique($rs);

    foreach ( $res as $item ) {
        $tmp = [];
        $r = $app->Analytics->getACase( $item );
        $tmp['dealership_id'] = $app->Analytics->selectDealership($r);
        $tmp['cabinet'] = 0;
        if ( $tmp['dealership_id'] > 0 ) $tmp['cabinet'] = $app->MySQL->getOne('SELECT ct_id FROM yapps_app_cis_dealerships WHERE id = ?i', $tmp['dealership_id']);
        $tmp['channel'] = $app->Analytics->selectChannel($r);
        $app->MySQL->query('UPDATE yapps_app_analytics_autodealer SET ?u WHERE ext_id = ?i', $tmp, $item);
    }
}



Helper::sp('Финиш: '.date('d-m-Y в H:i:s') );

?>