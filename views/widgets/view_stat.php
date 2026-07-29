<?php
  $date1 = ($_GET['date1']) ? $_GET['date1'] : date('Y-m-d', time()-24*3600);
  $date2 = ($_GET['date2']) ? $_GET['date2'] : date('Y-m-d', time());
  $userWidgets = $app->Widgets->getUserWidgets($authUser);
  $arStat = $app->Stat->AppStatByFilter([
        'app' => $app->Widgets->AppInfo()->id,
        'date1' => $date1,
        'date2' => $date2,
        'params' => [
            'site_id' => (($_GET['site_ids'])?:$GLOBALS['USER_SITES']['sites_ids']),
            'type_id' => (($_GET['type_ids'])?:$app->Widgets->getTypesIDs()),
            'widget_id' => $_GET['widget_ids']
        ]
  ]);
  
  if ( $_GET['hide_doubles'] == 'on' ) {
    
    foreach ( $arStat as $k => $r ) {
      
      if ( $r['widget_id']==$arStat[$k+1]['widget_id'] && $r['phone']==$arStat[$k+1]['phone'] ) unset( $arStat[$k] );
    }
  }
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Widgets->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Widgets->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
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
                      'name' => 'Типы виджетов',
                      'fields' => [
                          [
                              'type' => 'select',
                              'multiple' => true,
                              'name' => 'type_ids[]',
                              'placeholder' => 'Типы виджетов',
                              'items' => $app->Widgets->getTypes(),
                              'value' => $_GET['type_ids'],
                              'rows' => 5,
                              'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
                          ],
                      ]
                  ],
                  [
                      'name' => 'Виджеты',
                      'fields' => [
                          [
                              'type' => 'select',
                              'multiple' => true,
                              'name' => 'widget_ids[]',
                              'placeholder' => 'Виджеты',
                              'items' => $userWidgets,
                              'value' => $_GET['widget_ids'],
                              'rows' => 5,
                'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
                          ]
                      ]
                  ]
              ],
              'clear' => '/widgets/stat/',
          ];
      
      if ( $_GET ) $arFilter['export'] = '/widgets/export/?'.http_build_query($_GET);
          
        ?>
        
        <?php // Helper::sp( $arStat ); ?>
        
        <?php HTML::statAppFilter( $arFilter ); ?>
      
         <div class="box box-primary">
            
          <div class="box-header with-border"><h3 class="box-title">Активность пользователей приложения "<?=$app->Widgets->AppInfo()->ru_name?>"<small>(<?=count($arStat)?> <?=Helper::getWorld(count($arStat), 'record')?>)</small></h3></div>
            
            <div class="box-body">
              
              
              <table id="data-table-stats" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                  <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 10%">Сайт</th>
                    <th style="width: 30%">Активность</th>
                    <th style="width: 15%">Имя</th>
                    <th style="width: 15%">Телефон</th>
                    <th style="width: 15%">Дата</th>
                    <th style="width: 10%"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ( $arStat as $item ) { ?>
                  <?php $client = $app->Clients->getClientByStatId(3, $item['id']); ?>
                  <tr>
                    <td><?=$item['id']?></td>
                    <td><?=$GLOBALS['USER_SITES']['sites'][$item['site_id']]['ru_name']?></td>
                    <td>
                      <?php if ( $item['type_id'] == 0 ) { ?><span class="label label-default hint--top" aria-label="Старые виджеты"><i class="fa fa-clock-o" aria-hidden="true"></i></span> <?php } ?>
            <?=$item['event_name']?>
                    </td>
                    <td><?=(($item['name'])?:(($client['name'])?:'<small>---</small>'))?></td>
                    <td><?=(($item['phone'])?Helper::formatPhoneOut($item['phone']):(($client['phone'])?Helper::formatPhoneOut($client['phone']):'<small>---</small>'))?></td>
                    <td><?=date('Y-m-d H:i', $item['timestamp'])?></td>
                    <td class="text-right">
                      <a href="/clients/profile/view/<?=$item['piwik_visitorId']?>/">
                        <span class="label label-info hint--top" aria-label="Просмотр"><i class="fa fa-eye" aria-hidden="true"></i></span>
                      </a>
                      <?php if ( $app->MySQL->getOne('SELECT type_id FROM yapps_app_widgets WHERE id = ?i', $item['widget_id']) == 7 ) { ?>
                      <a href="/widgets/qz/view/<?=$item['id']?>/">
                        <span class="label label-success hint--top" aria-label="Просмотр"><i class="fa fa-info" aria-hidden="true"></i></span>
                      </a>
                      <?php } ?>
                      <a href="<?=$item['source_url']?>" target="_blank" class="span-label hint--top" aria-label="Страница-источник"><span class="label label-default"><i class="fa fa-external-link" aria-hidden="true"></i></span></a>
                    </td>
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