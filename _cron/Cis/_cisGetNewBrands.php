<?php
#!/usr/bin/php
	
	ini_set('error_reporting', E_ALL & ~E_NOTICE);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	
	$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
	include $_SERVER['DOCUMENT_ROOT'].'/core/App.php';

    Helper::sp( 'Старт: '.date('Y-m-d H:i:s') );
    $log['mess']  = 'Бренды и модели старт: '.date('Y-m-d H:i:s').PHP_EOL;

	$brands = $app->Cis->getBrandsNew();
    foreach ( $brands as $k => $brand ) $models = $app->Cis->getModels( $brand['id'] );

    Helper::sp( 'Финиш: '.date('Y-m-d H:i:s') );
    $log['mess'] .= 'Бренды и модели финиш: '.date('Y-m-d H:i:s');

    file_put_contents(__DIR__.'/log/brands.txt', $log['mess']);
?>