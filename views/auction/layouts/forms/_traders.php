<?php if ( $currentRoute->id ) $arRes = $app->Auction->getTrader($currentRoute->id) ?>
<div class="box box-primary">
  
  <div class="box-header with-border">
    <h3 class="box-title">Трейдер <?=$arRes['name']?></h3>
  </div>
  
  <div class="box-body">
    
    <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
    
    <?php
		
        $formSet = [
            
            'name' => 'formAuctionTraders',
            
            'fields' => [
				[
					'type' => 'hidden',
					'name' => 'trader_id',
					'value' => $arRes['id'],
				],
				[
					'type' => 'text',
					'name' => 'name',
					'placeholder' => 'ФИО',
					'value' => $arRes['name'],
					'class' => '',
				],
				[
					'type' => 'text',
					'name' => 'phone',
					'placeholder' => 'Телефон',
					'value' => $arRes['phone'],
					'class' => '',
				],
				[
					'type' => 'text',
					'name' => 'email',
					'placeholder' => 'Email',
					'value' => $arRes['email'],
					'class' => '',
                ],
				[
					'type' => 'image',
					'name' => 'passport01_image',
					'placeholder' => 'Паспорт, страница с фото',
					'value' => $arRes['passport01_image'],
				],
				[
					'type' => 'image',
					'name' => 'passport02_image',
					'placeholder' => 'Паспорт, страница с регистрацией',
					'value' => $arRes['passport02_image'],
				],
				[
					'type' => 'checkbox',
					'name' => 'active',
					'placeholder' => 'Активность',
					'value' => (int)$arRes['active'],
					'items' => [
						[
							'text' => 'Активность',
							'value' => (int)$arRes['active']
						],
					],
                ],
                [
					'type' => 'checkbox',
					'name' => 'changepasswd',
					'placeholder' => 'Установить пароль '.$app->Auction->getConf()->DefaultPass,
					'value' => (int)$arRes['changepasswd'],
					'items' => [
						[
							'text' => 'Установить пароль '.$app->Auction->getConf()->DefaultPass,
							'value' => (int)$arRes['changepasswd']
						],
					],
				],
				[
					'type' => 'delimiter',
					'value' => '',
				],
				[
					'type' => 'checkbox',
					'name' => 'accepted',
					'placeholder' => 'Допуск к торгам',
					'value' => (int)$arRes['profile']['active'],
					'items' => [
						[
							'text' => 'Допуск к торгам',
							'value' => (int)$arRes['profile']['active']
						],
					],
				],
				[
					'type' => 'delimiter',
					'value' => 'Организация',
				],
				[
					'type' => 'text',
					'name' => 'org_name',
					'placeholder' => 'Название',
					'value' => $arRes['profile']['contact_name'],
					'class' => '',
				],
				[
					'type' => 'textarea',
					'name' => 'org_address',
					'placeholder' => 'Адрес',
					'value' => $arRes['profile']['org_address'],
					'rows' => 3
				],
				[
					'type' => 'phone',
					'name' => 'org_phone',
					'placeholder' => 'Телефон',
					'value' => $arRes['profile']['org_phone'],
					'class' => '',
				],
				[
					'type' => 'text',
					'name' => 'org_email',
					'placeholder' => 'Email',
					'value' => $arRes['profile']['org_email'],
					'class' => '',
				],
				[
					'type' => 'number',
					'name' => 'org_inn',
					'placeholder' => 'ИНН',
					'value' => $arRes['profile']['org_inn'],
					'class' => '',
				],
				[
					'type' => 'number',
					'name' => 'org_kpp',
					'placeholder' => 'КПП',
					'value' => $arRes['profile']['org_kpp'],
					'class' => '',
				],
				[
					'type' => 'number',
					'name' => 'org_ogrn',
					'placeholder' => 'ОГРН (ОГРНИП)',
					'value' => $arRes['profile']['org_ogrn'],
					'class' => '',
				],
				[
					'type' => 'text',
					'name' => 'org_head_name',
					'placeholder' => 'ФИО руководителя',
					'value' => $arRes['profile']['org_head_name'],
					'class' => '',
				],
				[
					'type' => 'text',
					'name' => 'org_head_position',
					'placeholder' => 'Должность руководителя',
					'value' => $arRes['profile']['org_head_position'],
					'class' => '',
				],
				[
					'type' => 'text',
					'name' => 'org_head_based',
					'placeholder' => 'Действующего на основании',
					'value' => $arRes['profile']['org_head_based'],
					'class' => '',
				],
				[
					'type' => 'image',
					'name' => 'org_inn_image',
					'placeholder' => 'Свидетельство ИНН',
					'value' => $arRes['profile']['org_inn_image'],
				],
				[
					'type' => 'image',
					'name' => 'org_ogrn_image',
					'placeholder' => 'Свидетельство ОГРН (ОГРНИП)',
					'value' => $arRes['profile']['org_ogrn_image'],
				],
				[
					'type' => 'delimiter',
					'value' => 'Контактное лицо',
				],
				[
					'type' => 'text',
					'name' => 'contact_name',
					'placeholder' => 'ФИО',
					'value' => $arRes['profile']['contact_name'],
					'class' => '',
				],
				[
					'type' => 'phone',
					'name' => 'contact_phone',
					'placeholder' => 'Телефон',
					'value' => $arRes['profile']['contact_phone'],
					'class' => '',
				],
				[
					'type' => 'text',
					'name' => 'contact_email',
					'placeholder' => 'Email',
					'value' => $arRes['profile']['contact_email'],
					'class' => '',
				],
				[
					'type' => 'delimiter',
					'value' => 'Оборот',
				],
				[
					'type' => 'number',
					'name' => 'volume',
					'placeholder' => 'Объем перепродаж автомобилей организацией трейдера в месяц, ₽',
					'value' => $arRes['profile']['volume'],
					'step' => 1000
				],
				[
					'type' => 'number',
					'name' => 'plan',
					'placeholder' => 'Планируемое количество покупок автомобилей в месяц, шт',
					'value' => $arRes['profile']['plan'],
					'step' => 10
				],
				[
					'type' => 'select',
					'multiple' => true,
					'name' => 'categories_id[]',
					'placeholder' => 'Интересующие ценовые категории',
					'items' => $app->Auction->getCategories(),
					'value' => $arRes['categories'],
					'rows' => 5,
					'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
				]
            ],
            'submit' => [
                'class' => 'primary',
                'text' => 'Отправить'
            ],
        ];
    ?>
    
    <?php if ( !$app->User->isAdministrator( $authUser->ssid ) && !in_array($authUser->id, $app->Auction->getAdmins()) ) { HTML::Denied(); } else {
    
		HTML::FullForm( $formSet ); } ?>
    
  </div>
  
</div>