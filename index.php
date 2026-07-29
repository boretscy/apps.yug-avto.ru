<?php	
	
	ini_set('log_errors', 0);
	
	if ( (int)$_GET['debug'] == 1 ) {
		
		ini_set('error_reporting', E_ALL & ~E_NOTICE);
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
	}
	
	if ( strpos($_SERVER['REQUEST_URI'], '/API/') === 0 ) {
		include __DIR__.'/API/index.php';
		return;
	}

	session_start();
	ob_start();	
	
	include __DIR__.'/core/App.php';
	
	include __DIR__.'/header.php';
	include __DIR__.'/content.php';
	include __DIR__.'/footer.php';
?>