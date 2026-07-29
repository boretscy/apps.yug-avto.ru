<?php 
    $filter['date1'] = $date1 = ($_GET['date1']) ? $_GET['date1'] : date('Y-m-d', time()-24*3600);
	$filter['date2'] = $date2 = ($_GET['date2']) ? $_GET['date2'] : date('Y-m-d');
	$filter['page'] = $page = ($_GET['page']) ?: 1;
	$clients = $app->Clients->getClients( $authUser, $date1, $date2, $page );
	
	$arStat = $app->Stat->AppStatByFilter([
		'app' => $app->Clients->AppInfo()->id,
		'date1' => $date1,
		'date2' => $date2,
		'params' => [
			'last_site_id' => (($_GET['last_site_id'])?:$GLOBALS['USER_SITES']['sites_ids']),
			'last_app_id' => $_GET['last_app_id']
		]
	]);
	
	foreach ($GLOBALS['USER_APPS'] as $appItem) if ( $appItem['settings']['activity'] ) $uApps[] =  $appItem['settings'];
	
	if ( $_GET ) {
		
		
		foreach ( $arStat as $k => $item ) {
			
			unset( 
				$arStat[$k]['piwik_visitorId'],
				$arStat[$k]['yandex_visitorId'],
				$arStat[$k]['google_visitorId'],
				$arStat[$k]['init_referrer'],
				$arStat[$k]['init_stat_id'],
				$arStat[$k]['init_site_id'],
				$arStat[$k]['timestamp']
			);
			
			$arStat[$k]['last_site_id'] = $GLOBALS['USER_SITES']['sites'][array_search($item['last_site_id'], $GLOBALS['USER_SITES']['sites_ids'])]['ru_name'];
			$arStat[$k]['last_app_id'] = $app->Apps->getAppByID($item['last_app_id'])['ru_name'];
			$arStat[$k]['init_app_id'] = $app->Apps->getAppByID($item['init_app_id'])['ru_name'];
			$arStat[$k]['datetime'] = date( 'd.m.Y H:i', $item['timestamp'] );
		}
		
		$csv = $app->Clients->AppInfo()->class.'--'.date('Y-m-d_H-i').'.csv';
		$POSTRes = Export::SaveCSV( $arStat, $app->Clients->AppInfo()->class.'/'.$csv );
	}
?>
 <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
  
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><?=$app->Clients->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
     
      <?php if ( $app->Clients->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
	  
	  <?php if ($POSTRes) HTML::Error($POSTRes); ?>
		
      <div class="row">
        
        <div class="col-md-12">
          
          <?php
            
			  $arFilter = [
				  
				  'title' => 'Фильтр: c <strong>'.$date1.'</strong> по <strong>'.$date2.'</strong>',
				  'cols' => [
					  
					  [
						  'name' => 'Период',
						  'fields' => [
							  [
								  'type' => 'date',
								  'name' => 'date1',
								  'placeholder' => 'С:',
								  'value' => $date1,
							  ],
							  [
								  'type' => 'date',
								  'name' => 'date2',
								  'placeholder' => 'По:',
								  'value' => $date2,
							  ]
						  ]
					  ],
					  [
						  'name' => 'Сайты',
						  'fields' => [
							  [
								  'type' => 'select',
								  'multiple' => true,
								  'name' => 'last_site_id[]',
								  'placeholder' => 'Cайты',
								  'items' => $GLOBALS['USER_SITES']['sites'],
								  'value' => $_GET['site_ids'],
								  'rows' => 5,
							  	  'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
							  ],
						  ]
					  ],
					  [
						  'name' => 'Приложения',
						  'fields' => [
							  [
								  'type' => 'select',
								  'multiple' => true,
								  'name' => 'last_app_id[]',
								  'placeholder' => 'Приложения',
								  'items' => $uApps,
								  'value' => $_GET['last_app_id'],
								  'rows' => 5,
							  	  'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
							  ]
						  ]
					  ]
				  ],
				  'clear' => '/clients/stat/',
			  ];
			  
			  if ( $_GET ) $arFilter['export'] = '/clients/export/?'.http_build_query($_GET);
			  
			?>
            
            <?php HTML::statAppFilter( $arFilter ); ?>
            <?php // Helper::sp( $GLOBALS['USER_APPS'] ); ?>
          
          
        </div>
        
        
      </div>
    
    </section>
    <!-- /.content -->
    
  </div>