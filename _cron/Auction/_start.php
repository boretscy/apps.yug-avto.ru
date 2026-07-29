<?php
#!/usr/bin/php7.0
	
	/*
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	*/
	
	include dirname(__DIR__, 2) . '/core/App.php';
	
	$templates = $app->Auction->getTemplates();
	$items = $app->Auction->geItemsToStart();
	
	foreach ( $items as $item ) {

        $cat = json_decode($item['joined_categories'], true);
        $cat[] = $app->Auction->getCategoryIdByItemPrice($item['start_price']);
        
		$traders = $app->Auction->getTradersByIds(
			array_unique(
                array_merge(
                    $app->MySQL->getCol(
                        'SELECT id FROM yapps_app_auction_traders WHERE active = ?i AND id IN (?a)', 1, array_unique(
                            $app->MySQL->getCol(
                                'SELECT trader_id FROM yapps_app_auction_traders_profiles WHERE id IN (?a)',
                                array_unique(
                                    $app->MySQL->getCol('SELECT profile_id FROM yapps_app_auction_traders_profiles_categories WHERE category_id IN (?a)', array_unique($cat))
                                )
                            )
                        )
                    ), (($item['joined_traders']&&$item['joined_traders']!='null')?json_decode($item['joined_traders'], true):[])
                )
            )
		);
		
		foreach ( $traders as $trader ) {
			
			// SMS
			$msg = $templates['sms_start'];
			$msg = str_replace('{brand}', $item['brand'], $msg);
			$msg = str_replace('{model}', $item['model'], $msg);
			$msg = str_replace('{year}', $item['year'], $msg);
            $msg = str_replace('{color}', $item['color'], $msg);
            $msg = str_replace('{type}', $app->Auction->getItemType($item['type_id'])['line_name'], $msg);
			$msg = str_replace('{milleage}', number_format($item['milleage'], 0, '', ' '), $msg);
			$msg = str_replace('{price}', number_format($item['start_price'], 0, '', ' '), $msg);
			$msg = str_replace('{short_link}', $item['short_url'], $msg);
			
			Helper::sendBeelineSMS($trader['phone'], $msg);
			if ( $trader['phone'] != $trader['profile']['contact_phone'] ) Helper::sendBeelineSMS($trader['profile']['contact_phone'], $msg);
			
			//Email
			$msg = $templates['email_start'];
			$msg = str_replace('{brand}', $item['brand'], $msg);
			$msg = str_replace('{model}', $item['model'], $msg);
			$msg = str_replace('{year}', $item['year'], $msg);
            $msg = str_replace('{color}', $item['color'], $msg);
            $msg = str_replace('{type}', $app->Auction->getItemType($item['type_id'])['line_name'], $msg);
			$msg = str_replace('{milleage}', number_format($item['milleage'], 0, '', ' '), $msg);
			$msg = str_replace('{price}', number_format($item['start_price'], 0, '', ' '), $msg);
			$msg = str_replace('{short_link}', $item['short_url'], $msg);
			
			$subj = 'Приглашаем к участию в '.$app->Auction->getItemType($item['type_id'])['line_name'].' на автомобиль '.$item['brand'].' '.$item['model'].', '.$item['year'].' г.в.';
			
			$app->Auction->sendNotify( $subj, $msg, [$trader['email'], $trader['profile']['contact_email']] );
		}
		
		$app->Auction->publicItem( $item['id'] );
	}
	
?>