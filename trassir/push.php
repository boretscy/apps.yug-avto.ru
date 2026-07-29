<?php
	
	// ini_set('error_reporting',  E_ALL & ~E_NOTICE);
	// ini_set('display_errors', 1);
	// ini_set('display_startup_errors', 1);
	
	
	session_start();
	ob_start();	
	
    include $_SERVER['DOCUMENT_ROOT'].'/core/App.php';
    
    file_put_contents('_request.txt', file_get_contents('php://input'));
    file_put_contents('_get.txt', json_encode($_GET));
    file_put_contents('_post.txt', json_encode($_POST));