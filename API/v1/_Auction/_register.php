<?php
	
	$res = $app->Auction->API_registerTrader( json_decode(file_get_contents('php://input'), true) );
	echo json_encode( $res );
?>