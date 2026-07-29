<?php

	$res = $app->Foots->getManagersAPI( $user );
	$curcount = $app->Foots->getCurCount( $app->Foots->getUser($user->id)->dcs[0] );
	
	foreach ( $res as $k => $r ) {
		
		$res[$k]['results']['total'] = $curcount; 
		$res[$k]['results']['current'] = $app->Foots->getManagerCurCount( $r['id'] );
		$res[$k]['results']['percent_raw'] = ( !$res[$k]['results']['total'] ) ? 0 : $res[$k]['results']['current'] / $res[$k]['results']['total'] * 100;
		$res[$k]['results']['percent'] = $res[$k]['results']['percent_raw'].'%';
		$res[$k]['arrangement'] = false;
	}
	
	echo json_encode( $res );