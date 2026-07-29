<?php
#!/usr/bin/php
	
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	
	$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
	include $_SERVER['DOCUMENT_ROOT'].'/core/application.php';
	
	$app->Parser->getAutoKeySTR('LR');
?>