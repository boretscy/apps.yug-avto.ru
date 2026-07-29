<?php if ( $currentRoute->id ) $arRes = $app->Cis->yappsGetSeoFilter($currentRoute->id) ?>

<div class="box box-primary">

    <div class="box-header with-border">
        <h3 class="box-title">Сео настройки фильтра</h3>
    </div>
        
    <div class="box-body">
		
        <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
        <?php
			  
			$formSet = [
				  
				'name' => 'formCisSeoFilter',
				  
				'fields' => [
					[
						'type' => 'hidden',
						'name' => 'id',
						'value' => $currentRoute->id,
					],
					//////////////////////////////////////////////////////////////////////////////////////////////////////////
					[
						'type' => 'delimiter',
						'value' => 'Основное',
					],
					[
						'type' => 'text',
						'name' => 'site',
						'placeholder' => 'Сайт',
						'value' => $arRes['site'],
						'description' => 'хост, например yug-avto.ru'
					],
					[
						'type' => 'text',
						'name' => 'entity',
						'placeholder' => 'Тип',
						'value' => $arRes['entity'],
						'description' => 'new - новые, used - с пробегом, phone - телефон'
					],

					[
						'type' => 'delimiter',
						'value' => 'Фильтр',
					],
					[
						'type' => 'select',
						'name' => 'brand[]',
						'multiple' => true,
						'placeholder' => 'Бренд',
						'value' => explode(',', $arRes['brand']),
						'items' => $app->Cis->yappsGetBrandsForSeo(),
						'select_id' => 'code',
						'select_field' => 'name',
						'rows' => 5
					],
					[
						'type' => 'select',
						'name' => 'model[]',
						'multiple' => true,
						'placeholder' => 'Модель',
						'value' => explode(',', $arRes['model']),
						'items' => $app->Cis->yappsGetModelsForSeo( ($arRes['entity']) ?: 'new' ),
						'select_id' => 'code',
						'select_field' => 'name',
						'rows' => 10
					],
					[
						'type' => 'number',
						'name' => 'price[]',
						'placeholder' => 'Прайс от',
						'value' => explode(',', $arRes['price'])[0],
					],
					[
						'type' => 'number',
						'name' => 'price[]',
						'placeholder' => 'Прайс до',
						'value' => explode(',', $arRes['price'])[1],
					],
					[
						'type' => 'select',
						'name' => 'transmission[]',
						'multiple' => true,
						'placeholder' => 'КПП',
						'value' => explode(',', $arRes['transmission']),
						'items' => $app->Cis->yappsGetTransmissions(),
						'select_id' => 'code',
						'select_field' => 'name',
						'rows' => 5
					],
					[
						'type' => 'select',
						'name' => 'engine[]',
						'multiple' => true,
						'placeholder' => 'Двигатель',
						'value' => explode(',', $arRes['engine']),
						'items' => $app->Cis->yappsGetEngines(),
						'select_id' => 'code',
						'select_field' => 'name',
						'rows' => 5
					],
					[
						'type' => 'select',
						'name' => 'drive[]',
						'multiple' => true,
						'placeholder' => 'Привод',
						'value' => explode(',', $arRes['drive']),
						'items' => $app->Cis->yappsGetDrives(),
						'select_id' => 'code',
						'select_field' => 'name',
						'rows' => 5
					],
					[
						'type' => 'select',
						'name' => 'body[]',
						'multiple' => true,
						'placeholder' => 'Кузов',
						'value' => explode(',', $arRes['body']),
						'items' => $app->Cis->yappsGetBodies(),
						'select_id' => 'code',
						'select_field' => 'name',
						'rows' => 5
					],
					[
						'type' => 'select',
						'name' => 'color[]',
						'multiple' => true,
						'placeholder' => 'Цвет',
						'value' => explode(',', $arRes['color']),
						'items' => $app->Cis->yappsGetColors(),
						'select_id' => 'code',
						'select_field' => 'name',
						'rows' => 5
					],
					[
						'type' => 'number',
						'name' => 'volume[]',
						'placeholder' => 'Объем от',
						'value' => explode(',', $arRes['volume'])[0],
					],
					[
						'type' => 'number',
						'name' => 'volume[]',
						'placeholder' => 'Объем до',
						'value' =>explode(',', $arRes['volume'])[1],
					],
					[
						'type' => 'number',
						'name' => 'power[]',
						'placeholder' => 'Мощность от',
						'value' => explode(',', $arRes['power'])[0],
					],
					[
						'type' => 'number',
						'name' => 'power[]',
						'placeholder' => 'Мощность до',
						'value' => explode(',', $arRes['power'])[1],
					],
					[
						'type' => 'number',
						'name' => 'year[]',
						'placeholder' => 'Год от',
						'value' => explode(',', $arRes['year'])[0],
					],
					[
						'type' => 'number',
						'name' => 'year[]',
						'placeholder' => 'Год до',
						'value' => explode(',', $arRes['year'])[1],
					],
					[
						'type' => 'select',
						'name' => 'dealership[]',
						'multiple' => true,
						'placeholder' => 'Дилерский центр',
						'value' => explode(',', $arRes['dealership']),
						'items' => $app->Cis->yappsGetDealerships(),
						'select_id' => 'url',
						'select_field' => 'name',
						'rows' => 5
					],
					
					
					[
						'type' => 'delimiter',
						'value' => 'Значения',
					],
					[
						'type' => 'textarea',
						'name' => 'meta_h1',
						'placeholder' => 'H1',
						'value' => $arRes['meta_h1'],
						'class' => ''
					],
					[
						'type' => 'textarea',
						'name' => 'meta_title',
						'placeholder' => 'Title',
						'value' => $arRes['meta_title'],
						'class' => ''
					],
					[
						'type' => 'textarea',
						'name' => 'meta_description',
						'placeholder' => 'Description',
						'value' => $arRes['meta_description'],
						'class' => ''
					],
					[
						'type' => 'textarea',
						'name' => 'seo_title',
						'placeholder' => 'SEO Title',
						'value' => $arRes['seo_title'],
						'class' => ''
					],
					[
						'type' => 'textarea',
						'name' => 'seo_text',
						'placeholder' => 'SEO Text',
						'value' => $arRes['seo_text'],
						'class' => ''
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

<div class="box box-primary">

	<div class="box-footer">
        <div class="col-xs-12">
			<p>Допустимые переменные:</p>
            <p>
				{%count%} - количество автомобилей<br/>
				{%cars%} - автомобиль / автомобиля / автомобилей <br/>
				<br/>
				{%brand%} - бренд<br/>
				{%brand_rus%} - бренд на русском<br/>
				<br/>
				{%model%} - модель<br/>
				{%model_rus%} - модель на русском<br/>
				<br/>
				{%tth%} - 2.0 d AT AWD (190 л.с.)<br/>
				<br/>
				{%year%} - год выпуска<br/>
				{%complectation%} - комлектация<br/>
				{%color%} - сырой цвет (напр "Пантера (672)")<br/>
				{%color_processed%} - распознанный цвет (напр "Серый")<br/>
				{%mileage%} - пробег<br/>
				{%transmission%} - автоматической<br/>
				{%transmission_meta%} - Механика<br/>
				{%engine%} - двигатель<br/>
				{%drive%} - привод<br/>
				{%volume%} - объем<br/>
				{%power%} - мощность<br/>
				{%ext_id%} - ID<br/>
				<br/>
				{%price%} - цена<br/>
				<br/>
				{%filter%} - цвет: белый, двигатель: бензин, привод: передний, КПП: механика<br />
				<br />
				{%date%} - месяц и год<br/>
				{%tel%} - телефон<br/>
				{%city%} - в Краснодаре
			</p>
        </div>
    </div>
        
</div>