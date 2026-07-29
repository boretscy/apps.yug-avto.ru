<?php
    
    $uApps  = $app->Apps->getAll( $user );
    // Helper::sp($_SERVER);
    $URL = ( $_GET['r'] ) ?: $_SERVER['HTTP_REFERER'];
    $APIRes .= $app->Widgets3->getScript( $user, $URL ).PHP_EOL;
	echo $APIRes;