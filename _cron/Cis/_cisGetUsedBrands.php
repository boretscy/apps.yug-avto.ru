<?php
#!/usr/bin/php
	
	ini_set('error_reporting', E_ALL & ~E_NOTICE);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	
	$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
	include $_SERVER['DOCUMENT_ROOT'].'/core/App.php';

	$brands = $app->Cis->getBrandsUsed();
	$app->Cis->save(true, 'brands', $brands);
    
    foreach ( $brands as $k => $brand ) {
		
        $models = $app->Cis->getModels( $brand['id'] );
        $app->Cis->save(true, 'brands/'.$brand['id'], $models);

        foreach ( $models as $model ) {
            
            $model = array_merge( $model, $app->Cis->getModelInfo($model['id']) );
            $app->Cis->save(true, 'models/'.$model['id'], $model);
        }
    }

?>