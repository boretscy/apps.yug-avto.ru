<?php
	
	/*
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	*/
	
	$inc_file = ( file_exists(__DIR__.'/_Auction/_'.$route->action.'.php') ) ? __DIR__.'/_Auction/_'.$route->action.'.php' : __DIR__.'/_GET/_get.default.php';
	include $inc_file;
	