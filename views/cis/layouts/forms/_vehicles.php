<?php if ( $currentRoute->id ) $arRes = $app->Cis->yappsGetVehicle($currentRoute->id); ?>
<?php  $lists = $app->Cis->yappsGetComparisonsLists(); ?>
<?php
    $problems = [];
    if ( !$arRes['vin'] ) $problems[] = 'VIN';
    if ( $arRes['body'] == 'none' ) $problems['bodies'] = 'Кузов';
    if ( $arRes['color'] == 'none' ) $problems['colors'] = 'Цвет';
    if ( $arRes['drive'] == 'none' ) $problems['drives'] = 'Привод';
    if ( $arRes['engine'] == 'none' ) $problems['engines'] = 'Двигатель';
    if ( $arRes['transmission'] == 'none' ) $problems['transmissions'] = 'КПП';
?>

<div class="box box-success">
    <div class="box-body">
        <div class="row">
            <div class="col-md-2">ID:</div>
            <div class="col-md-10"><?= $arRes['ext_id'];?></div>
            <div class="col-md-2">VIN:</div>
            <div class="col-md-10"><?= $arRes['vin'];?></div>
            <div class="col-md-2">Автомобиль:</div>
            <div class="col-md-10"><?= $arRes['name'];?></div>
			<?php if ( count($problems) ) { ?>
			<div class="col-md-12"><hr /></div>
            <div class="col-md-2">Не распознано:</div>
            <div class="col-md-10"><?= implode(', ', $problems);?></div>
				<?php if ( $problems['bodies'] ) { ?>
				<div class="col-md-2">Кузов:</div>
				<div class="col-md-10"><?= ((json_decode($arRes['raw'], true)['body_type'])?:'<span class="text-red">null</span>');?></div>
				<?php } ?>
				<?php if ( $problems['colors'] ) { ?>
				<div class="col-md-2">Цвет:</div>
				<div class="col-md-10"><?= ((json_decode($arRes['raw'], true)['general'][2]['value'])?:'<span class="text-red">null</span>');?></div>
				<?php } ?>
				<?php if ( $problems['drives'] ) { ?>
				<div class="col-md-2">Привод:</div>
				<div class="col-md-10"><?= ((json_decode($arRes['raw'], true)['specifications'][11]['value'])?:'<span class="text-red">null</span>');?></div>
				<?php } ?>
				<?php if ( $problems['engines'] ) { ?>
				<div class="col-md-2">Двигатель:</div>
				<div class="col-md-10"><?= ((json_decode($arRes['raw'], true)['general'][0]['value'])?:'<span class="text-red">null</span>');?></div>
				<?php } ?>
				<?php if ( $problems['transmissions'] ) { ?>
				<div class="col-md-2">КПП:</div>
				<div class="col-md-10"><?= ((json_decode($arRes['raw'], true)['general'][1]['value'])?:'<span class="text-red">null</span>');?></div>
				<?php } ?>
			<?php } ?>
			<?php foreach ( $arRes['images'] as $i ) { ?>
			<div class="col-md-1">
				<a href="<?= $i['detail'];?>" target="_blank"><img src="<?= $i['detail'];?>" style="width: 100%; margin: 10px;" /></a>
			</div>
			<?php } ?>
        </div>
    </div>
</div>

<div class="box box-primary">

    <div class="box-header with-border">
        <h3 class="box-title">Автомобиль</h3>
    </div>
        
    <div class="box-body">
          
        <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
        <?php // Helper::sp( $arRes ); ?>
        <?php
			  
			$formSet = [
				  
				'name' => 'formCisVehicles',
				  
				'fields' => [
				  
					[
                        'type' => 'hidden',
                        'name' => 'ext_id',
                        'value' => $currentRoute->id,
					],
					  
					//////////////////////////////////////////////////////////////////////////////////////////////////////////
					  
				],

				'submit' => [
                    'class' => 'primary',
                    'text' => 'Отправить'
				],
			];
		?>

		<?php

			if ( $arRes['body'] == 'none' ) {
				
				$formSet['fields'][] = [
					'type' => 'select',
					'name' => 'body',
					'multiple' => false,
					'placeholder' => 'Кузов',
					'value' => [$arRes['body']],
					'items' => $lists['bodies'],
					'select_field' => 'name',
					'first_empty' => true
				];
			}

			if ( $arRes['color'] == 'none' ) {
				
				$formSet['fields'][] = [
					'type' => 'select',
					'name' => 'color',
					'multiple' => false,
					'placeholder' => 'Цвет',
					'value' => [$arRes['color']],
					'items' => $lists['colors'],
					'select_field' => 'name',
					'first_empty' => true
				];
			}

			if ( $arRes['drive'] == 'none' ) {
				
				$formSet['fields'][] = [
					'type' => 'select',
					'name' => 'drive',
					'multiple' => false,
					'placeholder' => 'Привод',
					'value' => [$arRes['drive']],
					'items' => $lists['drives'],
					'select_field' => 'name',
					'first_empty' => true
				];
			}

			if ( $arRes['engine'] == 'none' ) {
				
				$formSet['fields'][] = [
					'type' => 'select',
					'name' => 'engine',
					'multiple' => false,
					'placeholder' => 'Двигатель',
					'value' => [$arRes['engine']],
					'items' => $lists['engines'],
					'select_field' => 'name',
					'first_empty' => true
				];
			}

			if ( $arRes['transmission'] == 'none' ) {
				
				$formSet['fields'][] = [
					'type' => 'select',
					'name' => 'transmission',
					'multiple' => false,
					'placeholder' => 'КПП',
					'value' => [$arRes['transmission']],
					'items' => $lists['transmissions'],
					'select_field' => 'name',
					'first_empty' => true
				];
			}

			$formSet['fields'][] = [
				'type' => 'checkbox',
				'name' => 'update_images',
				'placeholder' => 'Обновить фото',
				'value' => (int)$arRes['update_images'],
				'items' => [
					[
						'text' => 'Обновить фото',
						'value' => (int)$arRes['update_images']
					],
				],
			];
		?>

          
        <?php HTML::FullForm( $formSet, $arRes['id'] ); ?>
          
    </div>
        
</div>