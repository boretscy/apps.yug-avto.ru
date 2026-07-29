<?php
#!/usr/bin/php

	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);

	$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
	include $_SERVER['DOCUMENT_ROOT'].'/core/application.php';
	
	$arAds = $app->Parser->getAdsAuto('J');
	
	$newAds = [];
	$changeAds = [];
	
	foreach ($arAds as $Ad) {
		
		$curAd = $app->db->getRow('SELECT * FROM parser_ads WHERE ad_id = ?s', $Ad['ad_id']);
		
		if (!$curAd) {
			
			$app->db->query('INSERT INTO parser_ads SET ?u', $Ad);
			$newAds[] = $Ad;
			
		} else {
			
			if ($curAd['price'] != $Ad['price']) {
				
				$app->db->query('UPDATE parser_ads SET ?u WHERE ad_id = ?s', $Ad, $Ad['ad_id']);
				$arIns = [
					'ad_id' => $Ad['ad_id'],
					'price' => $Ad['price'],
					'old_price' => $curAd['price'],
				];
				$app->db->query('INSERT INTO parser_history SET ?u', $arIns);
				
				$Ad['old_price'] = $curAd['price'];
				
				$changeAds[] = $Ad;
			}
		}
	}
	
	
	if (count($newAds)>0 || count($changeAds)>0) $app->sendParserNotify($newAds, $changeAds, 'AUTO.RU', 'Jaguar');
?>