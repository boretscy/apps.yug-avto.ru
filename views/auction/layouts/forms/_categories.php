<?php if ( $currentRoute->id ) $arRes = $app->Auction->getCategory($currentRoute->id) ?>

<div class="box box-primary">
  
  <div class="box-header with-border">
    <h3 class="box-title"><?=(($arRes['ru_name'])?:'Новая')?></h3>
  </div>
  
  <div class="box-body">
    
    <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
    
    <?php
		
        $formSet = [
            
            'name' => 'formAuctionCategories',
            
            'fields' => [
				[
					'type' => 'hidden',
					'name' => 'id',
					'value' => $currentRoute->id,
				],
				[
					'type' => 'text',
					'name' => 'ru_name',
					'placeholder' => 'Наименование',
					'value' => $arRes['ru_name'],
					'class' => '',
				],
				[
					'type' => 'number',
					'name' => 'min',
					'placeholder' => 'Минимальное значение',
					'value' => $arRes['min'],
					'class' => '',
				],
				[
					'type' => 'number',
					'name' => 'max',
					'placeholder' => 'Максимальное значение',
					'value' => $arRes['max'],
					'class' => '',
				],
				[
					'type' => 'number',
					'name' => 'cost_step',
					'placeholder' => 'Минимальный шаг торгов',
					'value' => $arRes['cost_step'],
					'class' => '',
				],
                [
                    'type' => 'text',
                    'multiple' => true,
                    'name' => 'default_costs',
                    'placeholder' => 'Предлагаемые ставки',
                    'value' => ( $arRes['default_costs'] ) ? json_decode($arRes['default_costs'], true) : $app->Auction->getConf()->DefaultCosts,
                ],
                
            ],
            'submit' => [
                'class' => 'primary',
                'text' => 'Отправить'
            ],
        ];
    ?>
    
    <?php HTML::FullForm( $formSet ); ?>
    
  </div>
  
</div>