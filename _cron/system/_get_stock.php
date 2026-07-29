<?php
#!/usr/bin/php7.0
	
    ini_set('error_reporting', E_ALL & ~E_NOTICE);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
	
	include dirname(__DIR__, 2) . '/core/App.php';
	
	$j = file_get_contents(__DIR__.'/data/dcs.json');
	$o = (array)json_decode( $j );
	
	$index = (int)file_get_contents(__DIR__.'/data/index.json');
	
	Helper::sp( $index, false, 'index' );
	Helper::sp( $o[$index]->Alias );
//	Helper::sp( $o );
	
	$om = json_decode(file_get_contents('https://yug-avto.ru/car-filter/new-cars?Company='.$o[$index]->Alias.'&limit=5000'));
	Helper::sp( 'https://yug-avto.ru/car-filter/new-cars?Company='.$o[$index]->Alias );
	
	$brand = $app->YApps_GetBrandByEnName( $om->filterResult->items[0]->Brand->Title );
	Helper::sp( $brand, false, 'Brand' );
	
	if ( $brand['id'] == 17 ) {
		
		foreach ( $om->filterResult->items as $i ) {
				
			$ids[] = $model_id = $app->YApps_GetModelByExtId( $i->Id )['id'];
			
			Helper::sp( $app->YApps_GetModelByEnName($i->Title), false, 'Model' );
			Helper::sp((int)$i->FilteredCount, false, 'Instock');
			
			$suff_dc = ( in_array($model_id, [79,80,81,82,83,85,91]) ) ? '_nfz' : '_pkw';
			$dc_id = $app->YApps_GetDcIDByUrl($o[$index]->Alias.$suff_dc);
			
			Helper::sp($o[$index]->Alias.$suff_dc.'<br />'.$dc_id );
            
            if ( !!$dc_id ) {
            
                $app->MySQL->query('UPDATE yapps_models_dcs SET ?u WHERE model_id = ?i AND dc_id = ?i', ['in_stock'=>(int)$i->FilteredCount], $model_id, $dc_id);
                $app->MySQL->query('UPDATE yapps_models_dcs SET ?u WHERE model_id NOT IN (?a) AND dc_id = ?i', ['in_stock'=>0], $ids, $dc_id);
            }
		}
		
	} else {
		
		$dc_id = $app->YApps_GetDcIDByUrl($o[$index]->Alias);
		if ( $dc_id ) $app->MySQL->query('UPDATE yapps_models_dcs SET ?u WHERE dc_id = ?i', ['in_stock'=>0], $dc_id);
		
		foreach ( $om->filterResult->items as $i ) {
			
			$model = $app->YApps_GetModelByExtId( $i->Id );
			Helper::sp( $model );
			Helper::sp( $i->FilteredCount );
			
			$ids[] = $model_id = $model['id'];
			
			if ($dc_id) $app->MySQL->query('UPDATE yapps_models_dcs SET ?u WHERE model_id = ?i AND dc_id = ?i', ['in_stock'=>(int)$i->FilteredCount], $model_id, $dc_id);
		}
	}
	
	
	$om = json_decode(file_get_contents('https://yug-avto.ru/car-filter/new-cars?Company='.$o[$index]->Alias).'');
	
	
	$index++;
	if ( $index >= count($o) ) $index = 0;
	file_put_contents( __DIR__.'/data/index.json', $index );
	
?>