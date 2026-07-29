<?php
	$date1 = ($_GET['date1']) ? $_GET['date1'] : date('Y-m-d', time()-24*3600);
	$date2 = ($_GET['date2']) ? $_GET['date2'] : date('Y-m-d', time());
	
	$arStat = $app->Stat->AppStatByFilter([
		'app' => $app->Lands->AppInfo()->id,
		'date1' => $date1,
		'date2' => $date2,
		'params' => [
			'site_id' => (($_GET['site_ids'])?:$GLOBALS['USER_SITES']['sites_ids']),
			'land_id' => $_GET['land_ids']
		]
	]);
	
	if ( $_GET ) {
		
		foreach ( $arStat as $k => $item ) {
			
			unset( $arStat[$k]['timestamp'] );
			
			$arStat[$k]['site_id'] = $GLOBALS['USER_SITES']['sites'][array_search($item['site_id'], $GLOBALS['USER_SITES']['sites_ids'])]['ru_name'];
			$arStat[$k]['land_id'] = $app->Lands->getLand($item['land_id'])['ru_name'];
			$arStat[$k]['datetime'] = date( 'd.m.Y H:i', $item['timestamp'] );
		}
		
		$csv = $app->Lands->AppInfo()->class.'--'.date('Y_m_d-H_i').'.csv';
		$POSTRes = Export::SaveCSV( $arStat, $app->Lands->AppInfo()->class.'/'.$csv );
	}
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Lands->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Lands->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
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
                          ],
						  [
							  'type' => 'checkbox',
							  'name' => 'hide_doubles',
							  'placeholder' => 'Скрыть дубли',
							  'value' => (($_GET['hide_doubles'])?1:0),
							  'items' => [
								  [
									  'text' => 'Скрыть дубли',
									  'value' => (($_GET['hide_doubles']=='on')?1:0)
								  ],
							  ],
						  ]
                      ]
                  ],
                  [
                      'name' => 'Сайты',
                      'fields' => [
                          [
                              'type' => 'select',
                              'multiple' => true,
                              'name' => 'site_ids[]',
                              'placeholder' => 'Cайты',
                              'items' => $GLOBALS['USER_SITES']['sites'],
                              'value' => $_GET['site_ids'],
                              'rows' => 5,
							  'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'

                          ],
                      ]
                  ],
                  [
                      'name' => 'Лендинги',
                      'fields' => [
                          [
                              'type' => 'select',
                              'multiple' => true,
                              'name' => 'land_ids[]',
                              'placeholder' => 'Посадочные страницы',
                              'items' => $app->Lands->getUserLands($GLOBALS['USER_SITES']['sites_ids']),
                              'value' => $_GET['land_ids'],
                              'rows' => 5,
							  'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
                          ],
                      ]
                  ]
              ],
              'clear' => '/lands/stat/',
          ];
		  
		  if ( $_GET ) $arFilter['export'] = '/lands/export/?'.http_build_query($_GET);
          
        ?>
        
        <?php // Helper::sp( $arStat ); ?>
        
        <?php HTML::statAppFilter( $arFilter ); ?>
        
      </div>
    </div>
  
  </section>
  <!-- /.content -->
  
</div>