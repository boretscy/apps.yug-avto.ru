<?php
	
	ini_set('error_reporting',  E_ALL & ~E_NOTICE);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	
	
	session_start();
	ob_start();	
	
	include $_SERVER['DOCUMENT_ROOT'].'/core/App.php';
    $authUser = $app->User->get( ['ssid' => $_SESSION['SSID']] );


	$v_id = 1622858;
	$pass = '!QAZse4';

	Helper::sp( password_hash($pass, PASSWORD_DEFAULT) );







	// $app->MySQL->query('TRUNCATE ?n', 'yapps_app_cis_equipments');

	// $file = fopen('e.csv', 'r');
	// while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
	// 	if ( !!$row[4] ) {
	// 		$brand_id = $app->MySQL->getOne('SELECT id FROM yapps_app_cis_brands WHERE code = ?s', $row[0]);
	// 		$model_id = $app->MySQL->getOne('SELECT id FROM yapps_app_cis_models_new WHERE code = ?s AND brand_id = ?i', $row[1], $brand_id);
	// 		$arIns = [
	// 			'type_id' => 1,
	// 			'brand_id' => $brand_id,
	// 			'model_id' => $model_id,
	// 			'name' => $row[3],
	// 			'ru_name' => $row[4]
	// 		];
	// 		if ( $arIns['model_id'] ) {
	// 			$app->Cis->yappsSetEquipment($arIns);
	// 		} else {
	// 			$err[] = [
	// 				'brand' => $row[0],
	// 				'model' => $row[1],
	// 			];
	// 		}
	// 	}
	// }
	// fclose($file);

	// if ( $err ) Helper::sp( $err, false, 'Ошибки');

	// $r = $app->MySQL->getAll( 'SELECT * FROM ?n WHERE type_id = ?i ORDER BY ext_id DESC', $app->Cis->getTable()->prod, 1);

	// foreach ( $r as $i ) {
	// 	$e = json_decode($i['raw'], true)['equipment_name'];
	// 	$b = $app->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE id = ?i', $i['brand_id'])['code'];
	// 	$m = $app->MySQL->getRow('SELECT * FROM ?n WHERE id = ?i', 'yapps_app_cis_models_new', $i['model_id'])['code'];
	// 	if ( !in_array( $e, $rrr[$b][$m] ) ) $rrr[$b][$m][] = $e;
	// }

	// foreach ( $rrr as $b => $ib ) {
	// 	foreach ( $ib as $m => $im ) {
	// 		foreach ( $im as $e) {
	// 			$ccc[] = [$b, $m, $e];
	// 		}
	// 	}
	// }

	// Export::PutCSV($ccc);

	Helper::sp( $r );
?>