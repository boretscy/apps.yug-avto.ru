<?php
#!/usr/bin/php
	
	ini_set('error_reporting', E_ALL & ~E_NOTICE);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	
	$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
	include $_SERVER['DOCUMENT_ROOT'].'/core/App.php';

	// $log['mess'] = file_get_contents(__DIR__.'/log/new.txt');

	Helper::sp( 'Старт: '.date('Y-m-d H:i:s') );
	$log['mess']  = PHP_EOL.PHP_EOL.'-----------------------------------------------'.PHP_EOL.PHP_EOL.'Б/у авто старт: '.date('Y-m-d H:i:s').PHP_EOL;


	$res = $app->Cis->getVehiclesUsed();
	foreach ( $res['filter']['brands'] as $i ) {
		
		$i['alias'] = $app->Cis->generateModelAlias($i['name']);
		$i['vehicles'] = 0;

		$brands[$i['id']] = $i;

		$arIns = [
			'ext_id' => (int)$i['id'],
			'name' => $i['name'],
			'ru_name' => $app->Cis->transliterateBrandToRu($item['name']),
			'code' => $i['alias'],
		];
		if ( $b_id = $app->Cis->MySQL->getOne('SELECT id FROM yapps_app_cis_brands WHERE ext_id = ?i', $arIns['ext_id']) ) {
			$app->Cis->MySQL->query('UPDATE yapps_app_cis_brands SET ?u WHERE id = ?i', $arIns, $b_id);
		} else {
			$app->Cis->MySQL->query('INSERT INTO yapps_app_cis_brands SET ?u', $arIns);
		}
		// $app->Cis->MySQL->query('INSERT INTO yapps_app_cis_brands SET ?u ON DUPLICATE KEY UPDATE ?u', $arIns, $arIns);
	}
	foreach ( $res['filter']['models'] as $i ) {

		$i['alias'] = $app->Cis->generateModelAlias($i['name']);
		// $i['alias'] = $app->Cis->getModelAlias($i['name']);
		$i['vehicles'] = 0;
		$i['statistics'][1]['counter'] = 0;
		$i['statistics'][2]['counter'] = 0;

		$brands[$i['brand_id']]['models'][$i['id']] = $i;

		$arIns = [
			'ext_id' => (int)$i['id'],
			'brand_id' => $app->Cis->MySQL->getOne('SELECT id FROM yapps_app_cis_brands WHERE ext_id = ?i', (int)$i['brand_id']),
			'name' => $i['name'],
			'code' => $i['alias'],
			'body_id' => $app->Cis->MySQL->getOne('SELECT id FROM yapps_app_cis_bodies WHERE code = ?s', $app->Cis->getBody($i['body_type'])['code']),
		];
		// $app->Cis->MySQL->query('INSERT INTO yapps_app_cis_models_used SET ?u ON DUPLICATE KEY UPDATE ?u', $arIns, $arIns);
		if ( $m_id = $app->Cis->MySQL->getOne('SELECT id FROM yapps_app_cis_models_used WHERE ext_id = ?i', $arIns['ext_id']) ) {
			$app->Cis->MySQL->query('UPDATE yapps_app_cis_models_used SET ?u WHERE id = ?i', $arIns, $m_id);
		} else {
			$app->Cis->MySQL->query('INSERT INTO yapps_app_cis_models_used SET ?u', $arIns);
		}
	}
	Helper::sp('Vehicles получены '.date('d-m-Y в H:i:s').', кол-во: '.count($res['items']) );
	$log['mess']  .= 'Б/у vehicles получены '.date('d-m-Y в H:i:s').', кол-во: '.count($res['items']).PHP_EOL;
	$log['count'] = 0; $log['ok'] = 0; $log['er']['c'] = 0; $log['to']['c'] = 0; $log['photo']['c'] = 0;

	foreach ( $res['items'] as $k => $r ) {

		if ( !in_array($r['dealership']['id'], [1364,1367,1370,1373,1489,1492,1499,1502,1533,1328]) || (int)$r['id'] == 1314264 ) continue; // забираем только с эксперта + Genesis Яблоновский

		$log['count']++;
		$time = time();
		$tmp = $app->Cis->getVehicle($r['id'], 2);
		$diff = time() - $time;
		if ( $tmp && $diff > 2 ) {
			Helper::sp($r['id'].' получен за '.$diff.' с' );
			$log['mess'] .= $r['id'].' получен за '.$diff.' с'.PHP_EOL;
		}
		if ( !$tmp ) {
			Helper::sp($r['id'].' завершен по таймауту более '.$app->Cis->getConf()->cURL_timeout.' с' );
			$log['mess'] .= $r['id'].' завершен по таймауту более '.$app->Cis->getConf()->cURL_timeout.' с'.PHP_EOL;
			$log['to']['c']++;
			$log['to']['i'][] = $r['id'];
		}
		if ( $tmp['log'] ) {
			$log['ok']++;
			if ($tmp['update_images']) {
				$log['photo']['c']++;
				$log['photo']['i'][] = ['id'=>$r['id'], 'vin'=>$r['vin']];
			}
			$brands[$r['brand_id']]['vehicles']++;
			$brands[$r['brand_id']]['models'][$r['ref_model_id']]['vehicles']++;
			$brands[$r['brand_id']]['models'][$r['ref_model_id']]['statistics'][$r['status']['id']]['counter']++;
		} else {
			if ($tmp['id'] && $tmp['vin']) { 
				$log['er']['c']++;
				$log['er']['i'][] = ['id'=>$tmp['id'], 'vin'=>$tmp['vin']];
			} else {
				$tmp['id'] = $r['id'];
				$tmp['vin'] = $r['vin'];
				$log['an']['i'][] = $tmp;
			}
		}
	}
	Helper::sp('Vehicles обработаны '.date('d-m-Y в H:i:s').', кол-во: '.$log['count'] );
	$log['mess'] .= 'Б/у vehicles обработаны '.date('d-m-Y в H:i:s').PHP_EOL.'Кол-во добавленных: '.$log['ok'].PHP_EOL.'Кол-во авто без модели или бренда: '.$log['er']['c'].PHP_EOL.'Кол-во авто сброшенных по таймауту: '.$log['to']['c'].PHP_EOL.'Кол-во авто обновить фото: '.$log['photo']['c'].PHP_EOL;

	$app->Cis->setImages();

	if ( $log['er']['c'] ) {
		$log['mess'] .= PHP_EOL.'Ошибочные авто ('.$log['er']['c'].'): '.PHP_EOL;
		foreach ($log['er']['i'] as $i) $log['mess'] .= $i['vin'].' | '.$i['id'].PHP_EOL;
		$log['mess'] .= PHP_EOL;
	}
	if ( $log['to']['c'] ) {
		$log['mess'] .= PHP_EOL.'Cброшенные авто по таймауту ('.$log['to']['c'].'): '.PHP_EOL;
		foreach ($log['to']['i'] as $i) $log['mess'] .= $i.',';
		$log['mess'] .= PHP_EOL;
	}
	if ( $log['photo']['c'] ) {
		$log['mess'] .= PHP_EOL.'Обновить фото ('.$log['photo']['c'].'): '.PHP_EOL;
		foreach ($log['photo']['i'] as $i) $log['mess'] .= $i['vin'].' | '.$i['id'].PHP_EOL;
		$log['mess'] .= PHP_EOL;
	}
	if ( $log['an']['i'] ) {
		$log['mess'] .= PHP_EOL.'Аномалий: '.count($log['an']['i']).PHP_EOL;
		foreach ( $log['an']['i'] as $i ) $log['mess'] .= print_r($i, true).PHP_EOL;
		$log['mess'] .= PHP_EOL;
	}

	Helper::sp( 'Финиш: '.date('Y-m-d H:i:s') );
	$log['mess']  .= 'Б/у авто финиш: '.date('Y-m-d H:i:s');

	if ( $app->Cis->isOk_cron() ) {
		$table = $app->Cis->toggleTable();
		Helper::sp( 'Таблица переключена' );
		$log['mess']  .= PHP_EOL.PHP_EOL.'-----------------------------------------------'.PHP_EOL.PHP_EOL.'Таблица переключена';
		file_put_contents(__DIR__.'/log/used.txt', $log['mess']);
		$app->Cis->Log( file_get_contents(__DIR__.'/log/new.txt').$log['mess'], $table['hash'] );
	}

?>