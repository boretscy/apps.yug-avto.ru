<?php

	$uApps = $app->Apps->getAll( $user );
	$APIRes= $app->Scripts->getAppSVG( $user, $_SERVER['HTTP_REFERER'] ).PHP_EOL;
	
	foreach ($uApps as $a) {
		
		$class = $a['settings']['class'];
		if ( method_exists($class, 'getSVG') ) $APIRes .= $app->$class->getSVG( $user, $_SERVER['HTTP_REFERER'] ).PHP_EOL;
	}
	
	echo ( (int)$_GET['dev'] == 1 ) ? $APIRes : JSMin::minifyHTML( $APIRes );