<?php
	$date1 = ($_GET['date1']) ? $_GET['date1'] : date('Y-m-d', time()-24*3600);
	$date2 = ($_GET['date2']) ? $_GET['date2'] : date('Y-m-d', time());
	
	$arStat = $app->Stat->AppStatByFilter([
		'app' => $app->Sale->AppInfo()->id,
		'date1' => $date1,
		'date2' => $date2,
		'params' => [
			'brand_id' => $_GET['brand_ids'],
			'form_id' => $_GET['form_ids']
		]
	]);
	
	if ( $_GET['hide_doubles'] == 'on' ) {
		
		foreach ( $arStat as $k => $r ) {
			
			if ( 
				$r['brand_id']==$arStat[$k+1]['brand_id'] && 
				$r['car']==$arStat[$k+1]['car'] && 
				$r['phone']==$arStat[$k+1]['phone'] && 
				$r['name']==$arStat[$k+1]['name'] && 
				$r['form_id']==$arStat[$k+1]['form_id'] 
			) unset( $arStat[$k] );
		}
	}
	
	if ( $_GET ) {
		
		$csv = $app->Sale->AppInfo()->class.'--'.date('Y_m_d-H_i').'.csv';
		$POSTRes = Export::SaveCSV( $arStat, $app->Sale->AppInfo()->class.'/'.$csv );
	}
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Sale->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Sale->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
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
                      'name' => 'Бренд',
                      'fields' => [
                          [
                              'type' => 'select',
                              'multiple' => true,
                              'name' => 'brand_ids[]',
                              'placeholder' => 'Бренд',
                              'items' => $app->YApps_GetBrands(),
                              'value' => $_GET['brand_ids'],
                              'rows' => 5,
							  'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
                          ],
                      ]
                  ],
                  [
                      'name' => 'Формы',
                      'fields' => [
                          [
                              'type' => 'select',
                              'multiple' => true,
                              'name' => 'form_ids[]',
                              'placeholder' => 'Формы',
                              'items' => $app->Sale->getForms(),
                              'value' => $_GET['form_ids'],
                              'rows' => 5,
							  'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
                          ],
                      ]
                  ]
              ],
              'clear' => '/lands/stat/',
          ];
		  
		  if ( $_GET ) $arFilter['export'] = '/sale/export/?'.http_build_query($_GET);
          
        ?>
        
        <?php // Helper::sp( $arStat ); ?>
        
        <?php HTML::statAppFilter( $arFilter ); ?>
          
      </div>
    </div>
  
  </section>
  <!-- /.content -->
  
</div>