<?php
#!/usr/bin/php
	
	// ini_set('error_reporting', E_ALL);
	// ini_set('display_errors', 1);
	// ini_set('display_startup_errors', 1);
	
	
	include dirname(__DIR__, 2) . '/core/App.php';
	
	$j = file_get_contents('https://yug-avto.ru/car-filter/new-cars?limit=5000&seoParamUri=%2Fnew-cars');
	$o = json_decode( $j );
	
	foreach ( $o->filterData->Company as $c ) $comp[] = $c ;
	file_put_contents( __DIR__.'/data/dcs.json', json_encode($comp) );
	
	$brands = []; $models = [];
	
	foreach ( $o->filterResult->items as $i ) $items[$i->Alias] = 'https://'.$i->Photo;
	
	Helper::sp($items);
	
	/**** BRANDS ****/
	
	foreach ( $o->filterData->Brands as $i ) {
		
		$brands[] = [
			'ext_id' => $i->Id,
			'url_key' => $i->Alias,
			'en_name' => $i->Title,
			'ru_name' => $i->Rutitle,
			'logo' => 'https://yug-avto.ru'.str_replace('tradeinscorp', 'holdingyugauto', $i->Logo)
		];
	}
	
	
	
	
	foreach ( $brands as $i ) {
		
		if ( $b = $app->YApps_GetBrandByEnName($i['en_name']) ) $i['id'] = (int)$b['id'];
		$app->setBrand( $i );
	}
	
	
	/**** MODELS ****/
	// Helper::sp($o->filterData->Models);
	foreach ( $o->filterData->Models as $i ) {
		
		$b = $app->YApps_GetBrandByEnName($i->BrandTitle);
		
		$tmp = [
			'brand_id' => (int)$b['id'],
			'ext_id' => $i->Id,
			'url_key' => $i->Alias,
			'en_name' => ( ($i->Prefix) ? $i->Prefix.' ' : '').$i->Title,
			'ru_name' => ( ($i->Prefix) ? $i->Prefix.' ' : '').$i->Rutitle,
			'in_stock' => (int)$i->CarCount
        ];
        if ( $items[$i->Alias] ) $tmp['photo'] = $items[$i->Alias];

        $models[] = $tmp;
        
	}
    
    // Helper::sp($models);

	foreach ( $models as $i ) {
		
		if ( $b = $app->YApps_GetModelByKey($i['url_key']) ) {
			
			$i['id'] = (int)$b['id'];
			if ( !$b['site_url'] ) $i['site_url'] = $b['url_key'];
		}
		$app->setModel( $i );
	}
	
	/***** CLEAR EMPTY MODELS *****/
	foreach ( $app->YApps_GetModels() as $model ) {
		
		$dc = $app->YApps_GetDCIDsByModel( $model['id'] );
		if ( !$dc ) $app->setModelDCs( $model['id'], [$app->YApps_GetDCIDsBySite( $app->YApps_GetBrandSiteIDs($model['brand_id'])[0] )[0]] );
		
		$mod = mb_strtolower($model['en_name']);
		if ( !$o->filterData->Models->$mod ) $app->setModel( ['id'=>$model['id'], 'in_stock'=>0] );
	}
	
	
	
	/***** IN STOCK BRAMDS *****/
	
	$brands = $app->YApps_GetBrands();
	foreach ( $brands as $b ) {
		
		$count = 0;
		foreach ( $app->YApps_GetModelsByBrand($b['id']) as $m ) $count += $m['in_stock'];
		
		$b['in_stock'] = $count;
		$app->setBrand( $b );
	}
	
?>