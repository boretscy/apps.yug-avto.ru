<?php if ( $POSTRes->id ) Route::redirect( '/auction/items/view/'.$POSTRes->id ); ?>
<?php if ( $currentRoute->id ) $arRes = $app->Auction->getItem($currentRoute->id) ?>
<div class="box box-primary">
  
  <div class="box-header with-border">
    <h3 class="box-title">Лот <?php if ($arRes) echo '№ '.$arRes['id'];?></h3>
  </div>
  
  <div class="box-body">
    
    <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
    
    <?php
		
        $formSet = [
            
            'name' => 'formAuctionItems',
            
            'fields' => [
				[
					'type' => 'hidden',
					'name' => 'id',
					'value' => $currentRoute->id,
				],
				[
					'type' => 'hidden',
					'name' => 'user_id',
					'value' => $authUser->id,
				],
				[
					'type' => 'select',
					'multiple' => false,
					'name' => 'type_id',
					'placeholder' => 'Тип торгов',
					'items' => $app->Auction->getItemsTypes(),
					'value' => [$arRes['type_id']]
				],
				[
					'type' => 'number',
					'name' => 'start_price',
					'placeholder' => 'Стартовая цена, ₽',
					'value' => $arRes['start_price'],
				],
				[
					'type' => 'date',
					'name' => 'datetime_start',
					'placeholder' => 'Начало (дата и время)',
					'value' => ( $arRes ) ? date('d.m.Y H:i', strtotime($arRes['datetime_start'])) : date('d.m.Y H:i')
				],
				[
					'type' => 'date',
					'name' => 'datetime_end',
					'placeholder' => 'Окончание (дата и время)',
					'value' => ( $arRes ) ? date('d.m.Y H:i', strtotime($arRes['datetime_end'])) : date('d.m.Y H:i', time()+8*3600)
				],
				[
					'type' => 'checkbox',
					'name' => 'auto_start',
					'placeholder' => 'Автостарт',
					'value' => (int)$arRes['auto_start'],
					'items' => [
						[
							'text' => 'Автостарт',
							'value' => (int)$arRes['auto_start']
						],
					],
				],
				
				[
					'type' => 'delimiter',
					'value' => 'Автомобиль',
				],
				[
					'type' => 'text',
					'name' => 'vin',
					'placeholder' => 'VIN',
					'value' => $arRes['vin'],
				],
				[
					'type' => 'delimiter',
					'value' => '',
				],
				[
					'type' => 'text',
					'name' => 'brand',
					'placeholder' => 'Марка',
					'value' => $arRes['brand'],
				],
				[
					'type' => 'text',
					'name' => 'model',
					'placeholder' => 'Модель',
					'value' => $arRes['model'],
				],
				[
					'type' => 'text',
					'name' => 'color',
					'placeholder' => 'Цвет',
					'value' => $arRes['color'],
				],
				[
					'type' => 'number',
					'name' => 'year',
					'placeholder' => 'Год выпуска',
					'value' => $arRes['year'],
				],
				[
					'type' => 'number',
					'name' => 'milleage',
					'placeholder' => 'Пробег',
					'value' => $arRes['milleage'],
				],
				[
					'type' => 'delimiter',
					'value' => '',
				],
				[
					'type' => 'number',
					'name' => 'engine_volume',
					'placeholder' => 'Объем двигателя (см3)',
					'value' => $arRes['engine_volume'],
				],
				[
					'type' => 'select',
					'multiple' => false,
					'name' => 'engine_type_id',
					'placeholder' => 'Тип двигателя',
					'items' => $app->Auction->getEngineTypes(),
					'value' => [$arRes['engine_type_id']]
				],
				[
					'type' => 'delimiter',
					'value' => '',
				],
				[
					'type' => 'select',
					'multiple' => false,
					'name' => 'transmission_id',
					'placeholder' => 'Трансмиссия',
					'items' => $app->Auction->getTransmissions(),
					'value' => [$arRes['transmission_id']]
				],
				[
					'type' => 'select',
					'multiple' => false,
					'name' => 'drive_id',
					'placeholder' => 'Привод',
					'items' => $app->Auction->getDrives(),
					'value' => [$arRes['drive_id']]
				],
				[
					'type' => 'delimiter',
					'value' => '',
				],
				[
					'type' => 'select',
					'multiple' => false,
					'name' => 'owners',
					'select_field' => 'id',
					'placeholder' => 'Владельцев по ПТС',
					'items' => [
						['id'=>1],
						['id'=>2],
						['id'=>3],
						['id'=>4],
						['id'=>5],
						['id'=>6],
						['id'=>7],
						['id'=>8],
						['id'=>9],
						['id'=>10],
					],
					'value' => [$arRes['owners']],
					'rows' => 5
				],
				[
					'type' => 'textarea',
					'name' => 'description',
					'placeholder' => 'Описание',
					'value' => $arRes['description'],
					'rows' => 8,
					'cols' => 80,
					'ckeditor' => true,
				],
				
				[
					'type' => 'delimiter',
					'value' => 'Медиа',
				],
				[
					'type' => 'image',
					'multiple' => true,
					'name' => 'photo[]',
					'placeholder' => 'Фото',
					'value' => $app->Auction->getItemPhotos($arRes['id']),
				],
				[
					'type' => 'image',
					'multiple' => true,
					'name' => 'damage[]',
					'placeholder' => 'Повреждения',
					'value' => $app->Auction->getItemDamages($arRes['id']),
				],
				[
					'type' => 'image',
					'multiple' => true,
					'name' => 'card[]',
					'placeholder' => 'Диагностическая карта',
					'value' => $app->Auction->getItemCards($arRes['id']),
                ],
				[
					'type' => 'image',
					'multiple' => true,
					'name' => 'video[]',
					'placeholder' => 'Видео',
					'value' => $app->Auction->getItemVideo($arRes['id']),
				],
				
				[
					'type' => 'delimiter',
					'value' => 'Дополнительно оповещать',
				],
				[
					'type' => 'select',
					'multiple' => true,
					'name' => 'category_ids[]',
					'placeholder' => 'Ценовые категории',
					'items' => $app->Auction->getCategories(),
					'value' => json_decode($arRes['joined_categories']),
					'rows' => 5
				],
				[
					'type' => 'select',
					'multiple' => true,
					'name' => 'trader_ids[]',
					'select_field' => 'name',
					'placeholder' => 'Трейдеры',
					'items' => $app->Auction->getTraders(),
					'value' => json_decode($arRes['joined_traders']),
					'params' => [
						'select2' => true
					]
				],
				
            ],
            'submit' => [
                'class' => 'primary',
                'text' => 'Предпросмотр'
            ],
        ];
    ?>
    
    <?php HTML::FullForm( $formSet ); ?>
    
  </div>
  
</div>