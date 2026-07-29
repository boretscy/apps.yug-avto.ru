<?php if ( $currentRoute->id ) $arRes = $app->Cis->yappsGetSeo($currentRoute->id) ?>

<div class="box box-primary">

    <div class="box-header with-border">
        <h3 class="box-title">Сео настройки</h3>
    </div>
        
    <div class="box-body">
          
        <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
        <?php
			  
			$formSet = [
				  
				'name' => 'formCisSeo',
				  
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
						'placeholder' => 'Сущность',
						'value' => $arRes['entity'],
						'description' => 'new - новые, used - с пробегом, phone - телефон'
					],
					[
						'type' => 'text',
						'name' => 'level',
						'placeholder' => 'Уровень',
						'value' => $arRes['level'],
						'description' => 'brands, brand, model, vehicle'
					],
					[
						'type' => 'delimiter',
						'value' => 'Кастомизация',
					],
					[
						'type' => 'text',
						'name' => 'custom',
						'placeholder' => 'Кастомизация',
						'value' => $arRes['custom'],
						'description' => 'Для конретного бренда, модели или автомобиля. Для бренда или модели - указывать их символьный код (Audi - audi, Q3 - q3), для автомобиля - его id.'
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
					[
						'type' => 'phone',
						'name' => 'phone',
						'placeholder' => 'Телефон',
						'value' => Helper::formatPhoneOut($arRes['phone'])
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