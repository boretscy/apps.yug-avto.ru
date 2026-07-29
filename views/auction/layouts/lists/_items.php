<?php 

	if ( $currentRoute->action == 'delete' ) $app->Auction->delItem( $currentRoute->id );
	if ( $currentRoute->action == 'activate' ) $app->Auction->publicItem( $currentRoute->id );

	$date1 = ($_GET['date1']) ? $_GET['date1'] : date('Y-m-d', time()-7*24*3600);
	$date2 = ($_GET['date2']) ? $_GET['date2'] : date('Y-m-d', time()+7*24*3600);

	$statuses = ( $_GET['status_ids'] ) ?: [1,2,5];
	
	$arRes = $app->Auction->getItemsByFilter([
		'date1' => $date1,
		'date2' => $date2,
		'params' => [
			'category_id' => $_GET['category_ids'],
			'status_id' => $statuses,
			'user_id' => $_GET['user_ids'],
		]
	]);
	  
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
				'name' => 'Статус аукциона',
				'fields' => [
					[
						'type' => 'select',
						'multiple' => true,
						'name' => 'status_ids[]',
						'placeholder' => 'Статус аукциона',
						'items' => $app->Auction->getStatuses(),
						'value' => $statuses,
						'rows' => 5,
						'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
					],
				]
			]
		],
		'clear' => '/auction/items/',
	];
    
?>

<?php // Helper::sp( $arRes ); ?>

<?php HTML::statAppFilter( $arFilter ); ?>


<div class="box box-primary">
  
  <div class="box-header with-border"><h3 class="box-title">Активные аукционы</h3></div>
  
  <div class="box-body">
    <div class="col-xs-12">
      <a href="/auction/items/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить аукцион</a>
    </div>
  </div>
  
  <div class="box-body">
    <table id="data-table-items" class="table table-hover table-striped table-condensed dataTable">
      <thead>
        <tr>
          <th style="width: 8%">ID</th>
          <th style="width: 8%">Тип</th>
          <th style="width: 14%">Автомобиль</th>
          <th style="width: 14%">VIN</th>
          <th style="width: 18%">Стоимость, ₽</th>
          <th style="width: 18%">Даты проведения</th>
          <th style="width: 8%">Статус</th>
          <th style="width: 10%"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $arRes as $item ) { ?>
        <tr class="<?=$app->Auction->getStatus($item['status_id'])['tr_color']?>">
          <td><a href="/auction/items/view/<?=$item['id']?>/"><?=$item['id']?></a></td>
          <td><?=$app->Auction->getItemType($item['type_id'])['ru_name']?></td>
          <td><a href="/auction/items/view/<?=$item['id']?>/"><?=$item['brand']?> <?=$item['model']?></a></td>
          <td><a href="/auction/items/view/<?=$item['id']?>/"><?=$item['vin']?></a></td>
          <td><?=number_format($item['start_price'], 0, '', ' ')?> &rarr; <?=number_format($item['current_price'], 0, '', ' ')?></td>
          <td><?=date('d.m.Y H:i', strtotime($item['datetime_start']))?> &rarr; <?=date('d.m.Y H:i', strtotime($item['datetime_end']))?></td>
          <td><?=$app->Auction->getStatus($item['status_id'])['ru_name']?></td>
          <td class="text-right">
            <?php if ( $item['status_id'] == 1 || $item['status_id'] == 4 ) { ?>
            <a href="/auction/items/activate/<?=$item['id']?>/" role="delete">
              <span class="label label-success hint--top" aria-label="Опубликовать"><i class="fa fa-play" aria-hidden="true"></i></span>
            </a>
            &nbsp;&nbsp;
            <?php } ?>
            <a href="/auction/items/edit/<?=$item['id']?>/">
              <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
            </a>
            <a href="/auction/items/view/<?=$item['id']?>/">
              <span class="label label-info hint--top" aria-label="Просмотр"><i class="fa fa-eye" aria-hidden="true"></i></span>
            </a>
            <?php if ( $app->User->isAdministrator( $authUser->ssid ) && in_array($authUser->id, $app->Auction->getAdmins()) ) { ?>
            &nbsp;
            <a href="/auction/items/delete/<?=$item['id']?>/" role="delete">
              <span class="label label-danger hint--top" aria-label="Отменить"><i class="fa fa-remove" aria-hidden="true"></i></span>
            </a>
            <?php } ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

</div>