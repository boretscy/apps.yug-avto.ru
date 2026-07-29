<?php 
    $filter['date1'] = $date1 = ($_GET['date1']) ? $_GET['date1'] : date('Y-m-d', time()-24*3600);
	$filter['date2'] = $date2 = ($_GET['date2']) ? $_GET['date2'] : date('Y-m-d');
	$filter['page'] = $page = ($_GET['page']) ?: 1;
	
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
	
	
	$allClients = $app->Clients->getAllCount();
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
                                'value' => $_GET['last_site_id'],
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
          <?php //  Helper::sp( $GLOBALS['USER_SITES'] ); ?>
        
        
        <div class="box box-primary">
          
          <div class="box-header with-border">
            <h3 class="box-title">Лиды <span class="badge bg-green"><?=$allClients?></span> <small>(<?=count($arStat)?> <?=Helper::getWorld(count($arStat), 'record')?>)</small></h3>
          </div>
          
          <div class="box-body">
              
              <table id="data-table-stats" class="table table-hover table-striped table-condensed dataTable">
                  <thead>
                      <tr>
                          <th style="width: 5%;">ID</th>
                          <th style="width: 12%">Имя</th>
                          <th style="width: 12%">Телефон</th>
                          <th style="width: 12%">Сайт</th>
                          <th style="width: 12%">Приложение</th>
                          <th style="width: 27%">Действие</th>
                          <th style="width: 10%">Дата</th>
                          <th style="width: 10%"></th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach ( $arStat as $item ) { ?>
                      <?php $application = $app->Apps->getAppByID($item['last_app_id']); ?>
                      <tr>
                          <td><?=$item['id']?></td>
                          <td><?=(($item['name'])?:'<small>---</small>')?></td>
                          <td><?=(($item['phone'])?Helper::formatPhoneOut($item['phone']):'<small>---</small>')?></td>
                          <td><?=$GLOBALS['USER_SITES']['sites'][$item['last_site_id']]['ru_name']?></td>
                          <td><a href="/<?=$application['url_key']?>/"><?=$application['ru_name']?></a></td>
                          <td><?=$item['last_event']?></td>
                          <td><?=date('d.m.Y H:i:s', $item['timestamp'])?></td>
                          <td class="text-right">
                              <a href="/clients/profile/view/<?=$item['piwik_visitorId']?>/">
                                <span class="label label-info hint--top" aria-label="Просмотр"><i class="fa fa-eye" aria-hidden="true"></i></span>
                              </a>
                              <a href="<?=$item['last_url']?>" target="_blank" class="span-label hint--top" aria-label="Страница-источник"><span class="label label-default"><i class="fa fa-external-link" aria-hidden="true"></i></span></a>
                          </td>
                      </tr>
                      <?php } ?>
                  </tbody>
              </table>
          
          </div>
          
        </div>
        
      </div>
      
      
    </div>
  
  </section>
  <!-- /.content -->
  
</div>