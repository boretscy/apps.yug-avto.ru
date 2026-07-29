<?php
	
	if ( $_GET['code'] ) Yandex::getOAuthtoken( $arConf['App']['Yandex'], $_GET['code'] );
	$app->Route->redirect( '/admin/settings/' );
?>
