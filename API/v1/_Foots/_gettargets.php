<?php
	$res = $app->Foots->getTargets();
	
	echo json_encode( $res );