<?php
	$date1 = ($_GET['date1']) ? $_GET['date1'] : date('Y-m-d', time()-7*24*3600);
	$date2 = ($_GET['date2']) ? $_GET['date2'] : date('Y-m-d', time()+7*24*3600);
	
	
	$arStat = $app->Stat->AppStatByFilter([
		'app' => $app->Auction->AppInfo()->id,
		'date1' => $date1,
		'date2' => $date2,
		'params' => [
			'category_id' => $_GET['category_ids'],
			'user_id' => $_GET['user_ids'],
		]
	]);
	
	/*
	foreach ( $arStat as $k => $s ) {
		
		$res = $app->Auction->getItem( $s['item_id'] );
		$arStat[$k]['model'] = $app->Hot->getModel($res['model_id'])['ru_name'];
		$arStat[$k]['dc'] = $app->YApps_GetDC($res['dc_id'])['ru_name'];
		$arStat[$k]['complectation'] = $res['complectation'];
		$arStat[$k]['gearbox'] = $res['gearbox'];
		$arStat[$k]['year'] = $res['year'];
		$arStat[$k]['vin'] = $res['vin'];
	}
	*/
	
	/*
	if ( $_GET['hide_doubles'] == 'on' ) {
		
		foreach ( $arStat as $k => $r ) {
			
			if ( $r['site_id']==$arStat[$k+1]['site_id'] && $r['name']==$arStat[$k+1]['name'] && $r['phone']==$arStat[$k+1]['phone'] ) unset( $arStat[$k] );
		}
	}
	*/
	
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Auction->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Auction->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
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
					  'name' => 'Менеджеры',
					  'fields' => [
						  [
							  'type' => 'select',
							  'multiple' => true,
							  'name' => 'user_ids[]',
							  'placeholder' => 'Менеджеры',
							  'items' => $app->YApps_GetUsersByApp(20),
							  'select_field' => 'name',
							  'value' => $_GET['user_ids'],
							  'rows' => 5,
							  'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
						  ],
					  ]
				  ],
				  [
					  'name' => 'Ценовые категории',
					  'fields' => [
						  [
							  'type' => 'select',
							  'multiple' => true,
							  'name' => 'category_ids[]',
							  'placeholder' => 'Ценовые категории',
							  'items' => $app->Auction->getCategories(),
							  'value' => $_GET['category_ids'],
							  'rows' => 5,
							  'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
						  ],
					  ]
				  ],
              ],
              'clear' => '/auction/stat/',
          ];
		  
		  if ( $_GET ) $arFilter['export'] = '/auction/export/?'.http_build_query($_GET);
          
        ?>
        
        <?php // Helper::sp( $arStat ); ?>
        
        <?php HTML::statAppFilter( $arFilter ); ?>
      
         <div class="box box-primary">
        
        	<div class="box-header with-border"><h3 class="box-title">Статистика приложения "<?=$app->Auction->AppInfo()->ru_name?>" <small>(<?=count($arStat)?> <?=Helper::getWorld(count($arStat), 'record')?>)</small></h3></div>
            
            <div class="box-body">
              
              <table id="data-table-stats" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                  <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 25%">Лот</th>
                    <th style="width: 10%">Старт</th>
                    <th style="width: 10%">Окончание</th>
                    <th style="width: 15%">Финальная цена</th>
                    <th style="width: 25%">Победитель</th>
                    <th style="width: 10%">Ставок</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ( $arStat as $item ) { ?>
                  
                  <?php
				  	$lot = $app->Auction->getItem( $item['item_id'] );
					$trader = $app->Auction->getTrader( $item['trader_id'] );
				  ?>
                  
                  <tr>
                    <td><?=$item['id']?></td>
                    <td><a href="/auction/items/view/<?=$lot['id']?>/"><?=$lot['brand']?> <?=$lot['model']?>, <?=$lot['year']?> г.в.</a></td>
                    <td><?=date('d.m.Y H:i', strtotime($lot['datetime_start']))?></td>
                    <td><?=date('d.m.Y H:i', strtotime($lot['datetime_end']))?></td>
                    <td><?=number_format($item['final_price'], 0, '', ' ')?> ₽</td>
                    <td><a href="/auction/traders/view/<?=$trader['id']?>/"><?=$trader['name']?></a></td>
                    <td><?=$item['costs_count']?></td>
                  </tr>
                  <? } ?>
                </tbody>
              </table>
            </div>
            
          </div>
        
      </div>
    </div>
  
  </section>
  <!-- /.content -->
  
</div>