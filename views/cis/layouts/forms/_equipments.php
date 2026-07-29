<?php if ( $currentRoute->id ) $arRes = $app->Cis->yappsGetEquipment($currentRoute->id) ?>

<div class="box box-primary">

    <div class="box-header with-border">
        <h3 class="box-title">Комплектация</h3>
    </div>
        
    <div class="box-body">
          
        <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
        <?php
			  
			$formSet = [
				  
				'name' => 'formCisEquipments',
				  
				'fields' => [
				  
					[
                        'type' => 'hidden',
                        'name' => 'id',
                        'value' => $currentRoute->id,
					],
					  
					//////////////////////////////////////////////////////////////////////////////////////////////////////////
					
					[
						'type' => 'select',
						'name' => 'type_id',
						'multiple' => false,
						'placeholder' => 'Тип авто',
						'value' => [$arRes['type_id']],
						'items' => $app->Cis->yappsGetTypes(),
						'select_field' => 'name'
					],
					[
						'type' => 'select',
						'name' => 'brand_id',
						'multiple' => false,
						'placeholder' => 'Бренд',
						'value' => [$arRes['brand_id']],
						'first_empty' => true,
						'items' => $app->Cis->yappsGetBrandsForSeo('name'),
						'select_field' => 'name',
						'data' => 'code'
					],
					[
						'type' => 'select',
						'name' => 'model_id',
						'multiple' => false,
						'placeholder' => 'Модель',
						'value' => [$arRes['model_id']],
						'first_empty' => true,
						'items' => ( $arRes['brand'] ) ? $app->Cis->apiDBGetModels( (($arRes['type_id']==1)?'new':'used'), ['brand'=>$arRes['brand']['code']] ) : [],
						'select_field' => 'name',
						'description' => 'Выберите бренд'
					],
                    [
						'type' => 'text',
						'name' => 'name',
						'placeholder' => 'Наименование',
						'value' => $arRes['name']
					],
                    [
						'type' => 'text',
						'name' => 'ru_name',
						'placeholder' => 'Наименование на русском',
						'value' => $arRes['ru_name']
					],
					  
				],

				'submit' => [
                    'class' => 'primary',
                    'text' => 'Отправить'
				],
			  ];
		?>
          
        <?php HTML::FullForm( $formSet, $arRes['id'] ); ?>
          
    </div>
        
</div>