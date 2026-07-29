<?php
	
	$uApps = $app->Apps->getAll( $user );
	$APIRes= $app->Scripts->getAppCSS( $user, $_SERVER['HTTP_REFERER'] ).PHP_EOL;
	
	foreach ($uApps as $a) {
		
		$class = $a['settings']['class'];
		if ( method_exists($class, 'getCSS') ) $APIRes .= $app->$class->getCSS( $user, $_SERVER['HTTP_REFERER'] ).PHP_EOL;
	}
	
	echo ( (int)$_GET['dev'] == 1 ) ? $APIRes : JSMin::minifyCSS( $APIRes );