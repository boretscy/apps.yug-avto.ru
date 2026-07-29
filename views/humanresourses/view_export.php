<?php
	$date1 = ($_GET['date1']) ? $_GET['date1'] : date('Y-m-d', time()-24*3600);
	$date2 = ($_GET['date2']) ? $_GET['date2'] : date('Y-m-d', time());
	
	$arStat = $app->Stat->AppStatByFilter([
		'app' => $app->HumanResourses->AppInfo()->id,
		'date1' => $date1,
		'date2' => $date2,
		'params' => [
			'widget_id' => $_GET['dc_ids']
		]
	]);
	
	if ( $_GET ) {
		
		foreach ( $arStat as $k => $item ) {
			
			unset( 
				$arStat[$k]['gender_id'],
				$arStat[$k]['html']
			);
		}
		
		$csv = $app->HumanResourses->AppInfo()->class.'--'.date('Y_m_d-H_i').'.csv';
		$POSTRes = Export::SaveCSV( $arStat, $app->HumanResourses->AppInfo()->class.'/'.$csv );
	}
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <section class="content-header">
    <h1><?=$app->HumanResourses->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->HumanResourses->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
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
                              'items' => $app->HumanResourses->getDCs(),
                              'value' => $_GET['dc_ids'],
                              'rows' => 5,
							  'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
                          ]
                      ]
                  ]
              ],
              'clear' => '/humanresourses/stat/',
          ];
		  
		  if ( $_GET ) $arFilter['export'] = '/humanresourses/export/?'.http_build_query($_GET);
          
        ?>
        
        <?php HTML::statAppFilter( $arFilter ); ?>
		        
      </div>
    </div>
  
  </section>
  
</div>