<?php

	//$j = file_get_contents('https://yug-avto.ru/car-filter/new-cars?limit=5000&seoParamUri=%2Fnew-cars');
	$j = file_get_contents(__DIR__.'/data.json');
	$o = json_decode( $j );
	$brands = []; $models = [];
	
	foreach ( $o->filterResult->items as $i ) $items[$i->Alias] = 'https://'.$i->Photo;
	
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
		//$app->setBrand( $i );
	}
	
	
	/**** MODELS ****/
	
	foreach ( $o->filterData->Models as $i ) {
		
		$b = $app->YApps_GetBrandByEnName($i->BrandTitle);
		
		$models[] = [
			'brand_id' => (int)$b['id'],
			'ext_id' => $i->Id,
			'url_key' => $i->Alias,
			'en_name' => $i->Title,
			'ru_name' => $i->Rutitle,
			'photo' => $items[$i->Alias],
			'in_stock' => (int)$i->CarCount
		];
	}
	
	foreach ( $models as $i ) {
		
		if ( $b = $app->YApps_GetModelByEnName($i['en_name']) ) {
			
			$i['id'] = (int)$b['id'];
			if ( !$b['site_url'] ) $i['site_url'] = $b['url_key'];
		}
		//$app->setModel( $i );
	}
	
	
	/***** CLEAR EMPTY MODELS *****/
	/*
	foreach ( $app->YApps_GetModels() as $model ) {
		
		$dc = $app->YApps_GetDCIDsByModel( $model['id'] );
		if ( !$dc ) $app->setModelDCs( $model['id'], [$app->YApps_GetDCIDsBySite( $app->YApps_GetBrandSiteIDs($model['brand_id'])[0] )[0]] );
		
		$mod = $model['url_key'];
		if ( !$o->filterData->Models->$mod ) $app->setModel( ['id'=>$model['id'], 'in_stock'=>0] );
	}
	*/
	
	
	
	
	/***** IN STOCK BRAMDS *****/
	
	$brands = $app->YApps_GetBrands();
	foreach ( $brands as $b ) {
		
		$count = 0;
		foreach ( $app->YApps_GetModelsByBrand($b['id']) as $m ) $count += $m['in_stock'];
		
		$b['in_stock'] = $count;
		//$app->setBrand( $b );
	}
	
	
	//Helper::sp( $o->filterData->Company );
	
	foreach ( $o->filterData->Company as $dc ) {
		
		//$om = json_decode(file_get_contents('https://yug-avto.ru/car-filter/new-cars?Company='.$dc->Brands[0]->Alias));
		$brand_id = $app->YApps_GetDcIDByUrl( $dc->Brands[0]->Alias );
		
		if ( $brand_id == 17 ) {
			
			foreach ( $om->filterResult->items as $i ) {
				
				$model_id = $app->YApps_GetModelByEnName( $i->Title )['id'];
				$suff_dc = ( in_array($model_id, [79,80,81,82,83,85,91]) ) ? '_nfz' : '_pkw';
				
				$dc_id = $app->YApps_GetDcIDByUrl($dc->Alias.$suff_dc);
				
				Helper::sp( $app->YApps_GetDC($dc_id) );
				
				//$app->MySQL->query('UPDATE yapps_models_dcs SET ?u WHERE model_id = ?i AND dc_id = ?i', ['in_stock'=>(int)$i->FilteredInStockCount], $model_id, $dc_id);
			}
			
		} else {
			
			$dc_id = $app->YApps_GetDcIDByUrl($dc->Alias);
			
			Helper::sp( $app->YApps_GetDC($dc_id) );
			
			foreach ( $om->filterResult->items as $i ) {
				
				$model_id = $app->YApps_GetModelByEnName( $i->Title )['id'];
				//$app->MySQL->query('UPDATE yapps_models_dcs SET ?u WHERE model_id = ?i AND dc_id = ?i', ['in_stock'=>(int)$i->FilteredInStockCount], $model_id, $dc_id);
			}
		}
		
		
		//Helper::sp( $dc_id );
	}
	