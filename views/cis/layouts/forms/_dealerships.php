<?php if ( $currentRoute->id ) $arRes = $app->Cis->yappsGetDealership($currentRoute->id) ?>

<div class="box box-primary">

    <div class="box-header with-border">
        <h3 class="box-title">Дилерский центр</h3>
    </div>
        
    <div class="box-body">
          
        <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
        <?php
			  
			$formSet = [
				  
				'name' => 'formCisDealerships',
				  
				'fields' => [

					[
                        'type' => 'hidden',
                        'name' => 'id',
                        'value' => $currentRoute->id,
					],
					  
					//////////////////////////////////////////////////////////////////////////////////////////////////////////
					  
					[
						'type' => 'number',
						'name' => 'code',
						'placeholder' => 'ID в автодилере',
						'value' => $arRes['code']
					],
					[
						'type' => 'select',
						'name' => 'type_id',
						'multiple' => false,
						'placeholder' => 'Тип',
						'value' => [$arRes['type_id']],
						'items' => $app->Cis->yappsGetTypes(),
						'select_field' => 'name'
					],
                    [
						'type' => 'text',
						'name' => 'name',
						'placeholder' => 'Название',
						'value' => $arRes['name']
					],
                    [
						'type' => 'text',
						'name' => 'url',
						'placeholder' => 'Символьный код',
						'value' => $arRes['url']
					],
                    [
						'type' => 'text',
						'name' => 'phone',
						'placeholder' => 'Телефон',
						'value' => $arRes['phone']
					],
                    [
						'type' => 'text',
						'name' => 'email',
						'placeholder' => 'Email',
						'value' => $arRes['email']
					],
                    [
						'type' => 'text',
						'name' => 'address',
						'placeholder' => 'Адрес',
						'value' => $arRes['address']
					],
                    [
						'type' => 'text',
						'name' => 'city',
						'placeholder' => 'Город',
						'value' => $arRes['city']
					],
                    [
						'type' => 'text',
						'name' => 'in_city',
						'placeholder' => 'Город в предложном падеже',
						'value' => $arRes['in_city']
					],
                    [
						'type' => 'text',
						'name' => 'coords_lat',
						'placeholder' => 'Координаты: Широта',
						'value' => $arRes['coords']['lat']
					],
                    [
						'type' => 'text',
						'name' => 'coords_lon',
						'placeholder' => 'Координаты: Долгота',
						'value' => $arRes['coords']['lon']
					],
					[
						'type' => 'select',
						'name' => 'brand_id',
						'multiple' => false,
						'placeholder' => 'Бренд',
						'value' => [$arRes['brand_id']],
						'items' => $app->Cis->yappsGetBrandsForSeo(),
						'select_field' => 'name'
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