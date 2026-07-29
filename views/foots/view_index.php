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
                  ]
				  ,
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
        
        <div class="box box-primary">
            
        	<div class="box-header with-border"><h3 class="box-title">Активность приложения "<?=$app->Foots->AppInfo()->ru_name?>" <small>(<?=count($arStat)?> <?=Helper::getWorld(count($arStat), 'record')?>)</small></h3></div>
            
            <div class="box-body">
              
            	
              <table id="data-table-stats" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                  <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 15%;">ДЦ</th>
                    <th style="width: 15%;">Хостес</th>
                    <th style="width: 15%;">Менеджер</th>
                    <th style="width: 15%;">Цель визита</th>
                    <th style="width: 5%;">Предв. согл.</th>
                    <th style="width: 10%;">Рабочий лист</th>
                    <th style="width: 15%;">Контакты</th>
                    <th style="width: 5%;">Лид</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ( $arStat as $item ) { ?>
                  <tr>
                    <td><?=$item['id']?></td>
                    <td><?=$app->YApps_GetDC($item['dc_id'])['ru_name']?></td>
                    <td><?=$app->Foots->getHostess($item['hostess_id'])['name']?></td>
                    <td><?=(($item['manager_id'])?$app->Foots->getManager($item['manager_id'])['ru_name']:'<small>---</small>')?></td>
                    <td><?=$app->Foots->getTarget($item['target_id'])['ru_name']?></td>
                    <td><?=(($item['arrangement'])?'<i class="fa fa-check-square-o" aria-hidden="true"></i>':'<small>---</small>')?></td>
                    <td><?=$item['work_list']?></td>
                    <td>
                      <?=(($item['name'])?$item['name'].'<br />':'')?>
                      <?=(($item['phone'])?:'')?>
					</td>
                    <td class="text-right">
                      <?php if ( $item['client_id'] ) { ?>
                      <?php $client = $app->Clients->getClient( $item['client_id'] ); ?>
                      <a href="#" role="viewPiwik" data-id="<?=$client['piwik_visitorId']?>" data-site="<?=$client['last_site_id']?>" data-date="today"><span class="label label-default hint--top" aria-label="О посетителе"><i class="fa fa-info" aria-hidden="true"></i></span></a>
                      <?php } // if ?>
                    </td>
                  </tr>
                  <?php } // foreach ?>
                </tbody>
              </table>
              
            </div>
            
          </div>
        
      </div>
    </div>
  
  </section>
  
</div>