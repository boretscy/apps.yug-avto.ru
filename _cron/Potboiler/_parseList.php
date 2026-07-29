<?php
#!/usr/bin/php
	/*
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    */
    include __DIR__.'/../../core/App.php';
	
	$Sets = $app->Potboiler->getSettings();
	
	if ( $Sets->status == 1 ) {
		
		$items = $app->Potboiler->getList( (int)$Sets->next_page );
		
		foreach ( $items as $i ) {
			
			if ( $iid = $app->Potboiler->checkItem($i['item_id']) ) {
				
				$app->Potboiler->updateItem( $i, $iid );
			
			} else {
				
				$app->Potboiler->newItem( $i );
			}
		}
		
		$arIns = (array)$Sets;
		unset($arIns['id'], $arIns['status']);
		
		$arIns['items'] += count($items);
		$arIns['total_items'] = (int)$app->Potboiler->getCount();
		$arIns['next_page'] = (int)$Sets->next_page + 1;
		if ( $arIns['items'] >= $arIns['total_items'] ) $arIns['status'] = 2;
		$app->Potboiler->setSettings( $arIns );
	}
	