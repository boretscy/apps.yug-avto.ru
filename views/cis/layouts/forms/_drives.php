<?php if ( $currentRoute->id ) $arRes = $app->Cis->yappsGetDrive($currentRoute->id) ?>

<div class="box box-primary">

    <div class="box-header with-border">
        <h3 class="box-title">Кузов</h3>
    </div>
        
    <div class="box-body">
          
        <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
        <?php
			  
			$formSet = [
				  
				'name' => 'formCisDrives',
				  
				'fields' => [
				  
					[
                        'type' => 'hidden',
                        'name' => 'id',
                        'value' => $currentRoute->id,
					],
					  
					//////////////////////////////////////////////////////////////////////////////////////////////////////////
					  
					[
						'type' => 'text',
						'name' => 'code',
						'placeholder' => 'Ключ',
						'value' => $arRes['code']
					],
                    [
						'type' => 'text',
						'name' => 'name',
						'placeholder' => 'Наименование',
						'value' => $arRes['name']
					],
                    [
						'type' => 'text',
						'name' => 'meta',
						'placeholder' => 'Для мета-тегов',
						'value' => $arRes['meta']
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