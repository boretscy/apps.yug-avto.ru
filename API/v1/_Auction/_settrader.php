<?php
	/*
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	*/
	$res = $app->Auction->API_setTrader( $_POST, $_FILES );
	
	echo json_encode( $res );
	
	
    
    // Helper::sp($_FILES );
    // Helper::sp( $_POST );

    // Helper::sd( json_decode(str_replace('&quot;', '"', $_POST['trader']), true) );
    
?>