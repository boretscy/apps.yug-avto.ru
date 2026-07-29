<?php
#!/usr/bin/php
	
	ini_set('error_reporting', E_ALL & ~E_NOTICE);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	
	$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
	include $_SERVER['DOCUMENT_ROOT'].'/core/App.php';
	Helper::sp( 'Старт: '.date('Y-m-d H:i:s') );
	$log['mess']  = 'Новые авто старт: '.date('Y-m-d H:i:s').PHP_EOL;

	$app->MySQL->query('TRUNCATE ?n', $app->Cis->getTable()->cron);

    $res = $app->Cis->getVehicles();
	Helper::sp('Vehicles получены '.date('d-m-Y в H:i:s').', кол-во: '.count($res) );
	$log['mess'] .= 'Новые vehicles получены '.date('d-m-Y в H:i:s').', кол-во: '.count($res).PHP_EOL;
	$log['count'] = count($res); $log['ok'] = 0; $log['er']['c'] = 0; $log['to']['c'] = 0; $log['photo']['c'] = 0;
	
	foreach ( $res as $k => $r ) {
		$time = time();
		$res[$k] = $app->Cis->getVehicle($r['id'], 1);
		$diff = time() - $time;
		if ( $res[$k] && $diff > 2 ) {
			Helper::sp($r['id'].' получен за '.$diff.' с' );
			$log['mess'] .= $r['id'].' получен за '.$diff.' с'.PHP_EOL;
		}
		if ( !$res[$k] ) {
			Helper::sp($r['id'].' завершен по таймауту более '.$app->Cis->getConf()->cURL_timeout.' с' );
			$log['mess'] .= $r['id'].' завершен по таймауту более '.$app->Cis->getConf()->cURL_timeout.' с'.PHP_EOL;
			$log['to']['c']++;
			$log['to']['i'][] = $r['id'];
		}
		if ( $res[$k]['log'] ) {
			$log['ok']++;
			if ($res[$k]['update_images']) {
				$log['photo']['c']++;
				$log['photo']['i'][] = ['id'=>$res[$k]['id'], 'vin'=>$res[$k]['vin']];
			}
		} else {
			if ($res[$k]['id'] && $res[$k]['vin']) { 
				$log['er']['c']++;
				$log['er']['i'][] = ['id'=>$res[$k]['id'], 'vin'=>$res[$k]['vin']];
			} else {
				$res[$k]['id'] = $r['id'];
				$res[$k]['vin'] = $r['vin'];
				$log['an']['i'][] = $res[$k];
			}
		}
		if ( $res[$k]['eq'] ) $log['eq'][] = $res[$k]['eq'];
	}
	Helper::sp('Vehicles обработаны '.date('d-m-Y в H:i:s') );
	$log['mess'] .= 'Новые vehicles обработаны '.date('d-m-Y в H:i:s').PHP_EOL.'Кол-во добавленных: '.$log['ok'].PHP_EOL.'Кол-во авто без модели или бренда: '.$log['er']['c'].PHP_EOL.'Кол-во авто сброшенных по таймауту: '.$log['to']['c'].PHP_EOL.'Кол-во авто обновить фото: '.$log['photo']['c'].PHP_EOL;
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
	if ( $log['eq'] ) {
		$arr_eq = [];
		$mail = [
			'title' => 'Внимание! Обнаружены англоязычные комплектации.<br /><br />',
			'text' => ''
		];
		foreach ( $log['eq'] as $eq ) if ( !$arr_eq[md5($eq['brand'].$eq['model'].$eq['equipment'])] ) $arr_eq[md5($eq['brand'].$eq['model'].$eq['equipment'])] = $eq;
		$log['mess'] .= PHP_EOL.'Англоязычных комплектаций: '.count($arr_eq).PHP_EOL;
		$mail['text'] .= PHP_EOL.'Комплектаций: '.count($arr_eq).'<br />';
		foreach ($arr_eq as $i) {
			$log['mess'] .= $i['brand'].' | '.$i['model'].' | '.$i['equipment'].PHP_EOL;
			$mail['text'] .= $i['brand'].' | '.$i['model'].' | '.$i['equipment'].'<br />';
		}
		$log['mess'] .= PHP_EOL;
		$mail['text'] .= '<br />Внести исправления можно здесь: <a href="https://apps.yug-avto.ru/cis/equipments/">https://apps.yug-avto.ru/cis/equipments/</a>';

		$recipients = ['yuliya.stolbovaya@yug-avto.ru','natalya.davidova@yug-avto.ru','nataliya.ivanova@yug-avto.ru','vera.golubeva@yug-avto.ru','viktoriya.lopatkina@yug-avto.ru','yuliya.davidyan@yug-avto.ru','darya.ermolaeva@yug-avto.ru','ekaterina.shepetilo@yug-avto.ru','yuliya.martinova@yug-avto.ru','elvina.maksimova@yug-avto.ru','yuliya.kudinova@yug-avto.ru','natalya.kobeleva@yug-avto.ru'];

		$app->Cis->send( $mail, $recipients );
	}
	$log['mess'] .= 'Новые авто финиш: '.date('Y-m-d H:i:s');
	file_put_contents(__DIR__.'/log/new.txt', $log['mess']);

	Helper::sp( 'Финиш: '.date('Y-m-d H:i:s') );
?>