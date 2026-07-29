<?php
	error_reporting(0);
	ini_set('log_errors', 0);

	if (!defined('YAPPS_DOCUMENT_ROOT')) {
		define('YAPPS_DOCUMENT_ROOT', dirname(__DIR__));
	}

	/* Configs */
	//require_once __DIR__.'/Conf.php';
	$arConf = require __DIR__.'/Conf.php';
    $RequestURL = parse_url($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])['path'];

	/* Mode */
	$appMode = ( explode('/', $RequestURL)[1] == 'API' ) ? 'API' : 'Web';
	
	/* Load Classes */
	foreach ( array_merge( $arConf['Core']['Init'], $arConf['Core']['Global'], $arConf['Core'][$appMode] ) as $class ) require __DIR__.'/YApps/'.$class['name'].'.php';
	
	/* Init App */
    $app = new App( $arConf, $RequestURL, $appMode );

	/* GET and POST */
	if ( $appMode == 'Web' ) {
	
		if ( $_GET && $app->User->checkAUth() ) include __DIR__.'/_GET.php';
		
		if ( $_POST && $app->User->checkAUth() ) {
			
			include __DIR__.'/_POST.php';
			
		} elseif ( $_POST ) { 
			
			include __DIR__.'/_POST_login.php';
		}
	}
?>