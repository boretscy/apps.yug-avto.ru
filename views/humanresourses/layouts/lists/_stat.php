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
?>

<section class="content-header">
  <h1><?=$app->HumanResourses->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
</section>

<!-- Main content -->
<section class="content">
  
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
      
      <div class="box box-primary">
        
        <div class="box-body">
        
          <table id="data-table-stats" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 15%">Отправитель</th>
                <th style="width: 8%">Отправлено</th>
                <th style="width: 15%">Соискатель</th>
                <th style="width: 15%">Email</th>
                <th style="width: 15%">Должность</th>
                <th style="width: 10%">Зарплата</th>
                <th style="width: 10%">Начало работы</th>
                <th style="width: 12%"></th
              ></tr>
            </thead>
            <tbody>
              <?php foreach ( $arStat as $item ) { ?>
              <tr>
                <td><a href="/humanresourses/stat/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                <td><?=(($app->User->getById($item['user_id'])->name)?:Helper::getError(18))?></td>
                <td><?=(($item['sent_timestamp'])?date('d.m.Y H:i', $item['sent_timestamp']):'')?></td>
                <td><a href="/humanresourses/stat/edit/<?=$item['id']?>/"><?=$item['name']?></a></td>
                <td><?=$item['email']?></td>
                <td><?=$item['position']?></td>
                <td><?=number_format((int)preg_replace('/[^0-9]/', '', $item['salary']), 0, '', ' ')?> ₽</td>
                <td><?=date('d.m.Y', $item['start_timestamp'])?></td>
                <td style="text-align: right">
                  <a href="/humanresourses/stat/view/<?=$item['id']?>/">
                    <span class="label label-info hint--top" aria-label="Просмотр"><i class="fa fa-eye" aria-hidden="true"></i></span>
                  </a>
                  <a href="/humanresourses/stat/edit/<?=$item['id']?>/">
                    <span class="label label-primary hint--top" aria-label="Изменить"><i class="fa fa-edit" aria-hidden="true"></i></span>
                  </a>
                  <a href="/humanresourses/stat/send/<?=$item['id']?>/">
                    <span class="label label-success hint--top" aria-label="Отправить"><i class="fa fa-paper-plane-o" aria-hidden="true"></i></span>
                  </a>
                </td>
              </tr>
              <?php } // foreach ?>
            </tbody>
          </table>
          
        </div>
      </div> <!-- /.box -->
      
    </div>
  </div>

</section>
