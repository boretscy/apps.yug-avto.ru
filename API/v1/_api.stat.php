<?php
	/*
	ini_set('error_reporting', E_ALL);
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
    */

	$class = ( $_POST['AppName'] ) ?: 'Attack';
				
	$APIres = $app->$class->pushStat( $_POST, $user, $_SERVER['HTTP_X_REAL_IP'] );
    echo (isset($_POST['callback']) ? $_POST['callback'] : '').json_encode($APIres);