<?php

	$res = $app->Foots->getHostessStat( $user->id );
	
	echo json_encode( $res );