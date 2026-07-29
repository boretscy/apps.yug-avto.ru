<?php
#!/usr/bin/php7.0

	/*
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	*/
	
	include dirname(__DIR__, 2) . '/core/App.php';
	
	$res = Yandex::refreshOAuthToken( $arConf['App']['Yandex'] );
	file_put_contents( __DIR__.'/data/yandex.json', json_encode($res) );
?>