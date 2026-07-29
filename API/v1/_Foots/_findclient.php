<?php

	$res = $app->Clients->findClientByPhone( json_decode(file_get_contents('php://input'), true)['phone'] );
	if ( $res->status != 'error' ) $res->status = 'success';
	if ( $res->status == 'error' ) $res->name = 'Не найдено';
	
	echo json_encode( $res );