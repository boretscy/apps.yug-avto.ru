<?php
    
    $uApps  = $app->Apps->getAll( $user );
    // Helper::sp($_SERVER);
    $URL = ( $_GET['r'] ) ?: $_SERVER['HTTP_REFERER'];
	
	foreach ($uApps as $a) {
		
		$class = $a['settings']['class'];
		if ( method_exists($class, 'getVueScript') ) $APIRes .= $app->$class->getVueScript( $user, $URL ).PHP_EOL;
    }
    
	echo $APIRes;