<?php
    
    $uApps  = $app->Apps->getAll( $user );
    // Helper::sp($_SERVER);
    $URL = ( $_GET['r'] ) ?: $_SERVER['HTTP_REFERER'];
    $APIRes = $app->Scripts->getAppSrcript( $user, $URL ).PHP_EOL;
	
	foreach ($uApps as $a) {
		
		$class = $a['settings']['class'];
		if ( method_exists($class, 'getScript') ) $APIRes .= $app->$class->getScript( $user, $_SERVER['HTTP_REFERER'] ).PHP_EOL;
    }
    
    $APIRes .= $app->Scripts->getEndScript( $user, $_SERVER['HTTP_REFERER'] );
    
	// echo ( (int)$_GET['dev'] == 1 ) ? $APIRes : JSMin::minify( $APIRes );
	echo $APIRes;