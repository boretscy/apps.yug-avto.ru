<?php
#!/usr/bin/php
	/*
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    */
    include __DIR__.'/../../core/App.php';
	
	$Sets = $app->Potboiler->getSettings();
	
	if ( $Sets->status == 2 ) {
		
		$items = $app->Potboiler->getEmptyPhoneItems();
		
		if ( count($items) == 0 ) {
			
			$app->Potboiler->setSettings( ['status'=>0, 'next_page'=>1, 'percent'=>0, 'items'=>0, 'total_items'=>0] );
			die;
		}
		
		foreach ( $items as $i ) {
			
			$arIns = $app->Potboiler->getPhone( $i['item_url'] );
			$app->Potboiler->updateItem( $arIns, $i['id'] );
		}
		
		$arS['percent'] = (int)$app->Potboiler->getFullPhoneItems()/(int)$Sets->total_items*100;
		$app->Potboiler->setSettings( $arS );
	}
	