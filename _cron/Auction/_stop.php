<?php
#!/usr/bin/php7.0

	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	
	include dirname(__DIR__, 2) . '/core/App.php';
	
	$templates = $app->Auction->getTemplates();
	$items = $app->Auction->getItemsToEnd();
	
	foreach ( $items as $item ) {
		
		$cat = $app->Auction->getCategoryIdByItemPrice($item['start_price']);
		$manager = $app->YApps_GetUserById($item['user_id']);
		
		$winners = $app->Auction->getTradersByIds( $app->Auction->getItemWinnersId($item['id']) );
		foreach ( $winners as $k => $trader ) {
			
			$app->MySQL->query('INSERT INTO yapps_app_auction_wins SET ?u', ['item_id'=>$item['id'], 'trader_id'=>$trader['id'], 'place'=>$k+1]);
			if ( $k == 0 ) {
				$st = [
					'item_id' => $item['id'],
					'category_id' => $cat,
					'trader_id' => $trader['id'],
					'final_price' => $item['current_price'],
					'cost_id' => $app->Auction->getLastCost($item['id'])['id'],
					'costs_count' => $app->Auction->getItemCosts($item['id']),
					'timestamp' => time()
				];
                $app->MySQL->query('INSERT INTO yapps_app_auction_stat SET ?u', $st);
                
                // Email
                
                $msg = $templates['email_winner'];
                $msg = str_replace('{brand}', $item['brand'], $msg);
                $msg = str_replace('{model}', $item['model'], $msg);
                $msg = str_replace('{year}', $item['year'], $msg);
                $msg = str_replace('{place}', $k+1, $msg);
                $msg = str_replace('{color}', $item['color'], $msg);
                $msg = str_replace('{type}', $app->Auction->getItemType($item['type_id'])['line_name'], $msg);
                $msg = str_replace('{milleage}', number_format($item['milleage'], 0, '', ' '), $msg);
                $msg = str_replace('{price}', number_format($item['current_price'], 0, '', ' '), $msg);
                $msg = str_replace('{manager_name}', $manager->name, $msg);
                $msg = str_replace('{manager_phone}', Helper::formatPhoneOut($manager->phone), $msg);
                
                $subj = 'Поздравляем с победой в '.$app->Auction->getItemType($item['type_id'])['line_name'].' на автомобиль '.$item['brand'].' '.$item['model'].', '.$item['year'].' г.в.';
                
                $app->Auction->sendNotify( $subj, $msg, [$trader['email'], $trader['profile']['contact_email'], $trader['profile']['org_email']] );
            
            } else {

                $msg = $templates['email_winners'];
                $msg = str_replace('{brand}', $item['brand'], $msg);
                $msg = str_replace('{model}', $item['model'], $msg);
                $msg = str_replace('{year}', $item['year'], $msg);
                $msg = str_replace('{color}', $item['color'], $msg);
                $msg = str_replace('{place}', $k+1, $msg);
                $msg = str_replace('{type}', $app->Auction->getItemType($item['type_id'])['line_name'], $msg);
                $msg = str_replace('{milleage}', number_format($item['milleage'], 0, '', ' '), $msg);
                $msg = str_replace('{price}', number_format($item['start_price'], 0, '', ' '), $msg);
                $msg = str_replace('{manager_name}', $manager->name, $msg);
                $msg = str_replace('{manager_phone}', Helper::formatPhoneOut($manager->phone), $msg);
                
                $subj = 'Вы заняли '.($k+1).' место в '.$app->Auction->getItemType($item['type_id'])['line_name'].' на автомобиль '.$item['brand'].' '.$item['model'].', '.$item['year'].' г.в.';

                $app->Auction->sendNotify( $subj, $msg, array_unique([$trader['email'], $trader['profile']['contact_email'], $trader['profile']['org_email']]) );
            }
			
		}
		
		// SMS
		$msg = $templates['sms_winner'];
		$msg = str_replace('{brand}', $item['brand'], $msg);
		$msg = str_replace('{model}', $item['model'], $msg);
		$msg = str_replace('{year}', $item['year'], $msg);
        $msg = str_replace('{color}', $item['color'], $msg);
        $msg = str_replace('{price}', number_format($item['current_price'], 0, '', ' '), $msg);
        $msg = str_replace('{type}', $app->Auction->getItemType($item['type_id'])['line_name'], $msg);
		$msg = str_replace('{milleage}', number_format($item['milleage'], 0, '', ' '), $msg);
		$msg = str_replace('{manager_name}', $manager->name, $msg);
		$msg = str_replace('{manager_phone}', Helper::formatPhoneOut($manager->phone), $msg);
		
		Helper::sendBeelineSMS($winners[0]['phone'], $msg);
		if ( $winners[0]['phone'] != $winners[0]['profile']['contact_phone'] ) Helper::sendBeelineSMS($winners[0]['profile']['contact_phone'], $msg);
		
		$app->Auction->closeItem( $item['id'] );
	}

?>