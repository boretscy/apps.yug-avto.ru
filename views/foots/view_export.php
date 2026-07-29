<?php
	$date1 = ($_GET['date1']) ? $_GET['date1'] : date('Y-m-d', time()-24*3600);
	$date2 = ($_GET['date2']) ? $_GET['date2'] : date('Y-m-d', time());
	
	$arStat = $app->Stat->AppStatByFilter([
		'app' => $app->Foots->AppInfo()->id,
		'date1' => $date1,
		'date2' => $date2,
		'params' => [
			'dc_id' => $_GET['dc_ids'],
			'manager_id' => $_GET['manager_ids'],
			'target_id' => $_GET['target_ids']
		]
	]);
	
	if ( $_GET ) {
		
		foreach ( $arStat as $k => $item ) {
			
			$arStat[$k]['dc_id'] = $app->YApps_GetDC($item['dc_id'])['ru_name'];
			$arStat[$k]['hostess_id'] = $app->Foots->getHostess($item['hostess_id'])['name'];
			$arStat[$k]['manager_id'] = $app->Foots->getManager($item['manager_id'])['ru_name'];
			$arStat[$k]['target_id'] = $app->Foots->getTarget($item['target_id'])['ru_name'];
			unset( $arStat[$k]['date'], $arStat[$k]['timestamp'] );
			$arStat[$k]['datetime'] = date( 'd.m.Y H:i', $item['timestamp'] );
		}
		
		$csv = $app->Foots->AppInfo()->class.'--'.date('Y_m_d-H_i').'.csv';
		$POSTRes = Export::SaveCSV( $arStat, $app->Foots->AppInfo()->class.'/'.$csv );
	}
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <section class="content-header">
    <h1><?=$app->Foots->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Foots->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
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
                      'name' => 'Дилерские центры',
                      'fields' => [
                          [
                              'type' => 'select',
                              'multiple' => true,
                              'name' => 'dc_ids[]',
                              'placeholder' => 'Дилерские центры',
                              'items' => $app->Foots->getUserDCs( $authUser->id ),
                              'value' => $_GET['dc_ids'],
                              'rows' => 5,
							  'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
                          ]
                      ]
                  ],
				  [
                      'name' => 'Менеджер',
                      'fields' => [
                          [
                              'type' => 'select',
                              'multiple' => true,
                              'name' => 'manager_ids[]',
                              'placeholder' => 'Менеджер',
                              'items' => $app->Foots->getManagersByUser( $authUser ),
                              'value' => $_GET['manager_ids'],
                              'rows' => 5,
							  'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
                          ]
                      ]
                  ],
				  [
                      'name' => 'Цель посещения',
                      'fields' => [
                          [
                              'type' => 'select',
                              'multiple' => true,
                              'name' => 'target_ids[]',
                              'placeholder' => 'Цель посещения',
                              'items' => $app->Foots->getAllTargets(),
                              'value' => $_GET['target_ids'],
                              'rows' => 5,
							  'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
                          ]
                      ]
                  ]
              ],
              'clear' => '/foots/stat/',
          ];
		  
		  if ( $_GET ) $arFilter['export'] = '/foots/export/?'.http_build_query($_GET);
          
        ?>
        
        <?php HTML::statAppFilter( $arFilter ); ?>
        
      </div>
    </div>
  
  </section>
  
</div>