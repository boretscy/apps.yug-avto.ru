<?php
	$user->dcs = $app->Foots->getUserDCNames($user->id);
	
	echo json_encode( $user );