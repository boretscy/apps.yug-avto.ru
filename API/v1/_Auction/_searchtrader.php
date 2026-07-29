<?php
	
	$res = $app->Auction->API_seachTrader( $POST );
	
	echo json_encode( $res );
?>