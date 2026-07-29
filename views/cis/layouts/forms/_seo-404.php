<?php if ( $currentRoute->id ) $arRes = $app->Cis->yappsGetSeo404($currentRoute->id) ?>

<div class="box box-primary">

    <div class="box-header with-border">
        <h3 class="box-title">Страница 404</h3>
    </div>
        
    <div class="box-body">
          
        <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
        <?php
			  
			$formSet = [
				  
				'name' => 'formCisSeo404',
				  
				'fields' => [
				  
					[
                        'type' => 'hidden',
                        'name' => 'id',
                        'value' => $currentRoute->id,
					],
					  
					//////////////////////////////////////////////////////////////////////////////////////////////////////////
					  
					[
						'type' => 'text',
						'name' => 'site',
						'placeholder' => 'Сайт',
						'value' => $arRes['site'],
					],
                    [
						'type' => 'text',
						'name' => 'uri',
						'placeholder' => 'Путь',
						'value' => $arRes['uri']
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