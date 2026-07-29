<?php
#!/usr/bin/php
	
	ini_set('error_reporting', E_ALL & ~E_NOTICE);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	
	$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
	include $_SERVER['DOCUMENT_ROOT'].'/core/App.php';
    
    for ( $i = 1; $i<=5; $i++ ) $app->Cis->makeImages();

?>