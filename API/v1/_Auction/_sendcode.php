<?php
	
	$res = $app->Auction->API_sendVCode( json_decode(file_get_contents('php://input'), true)['ssid'] );
	
	echo json_encode( $res );
?>