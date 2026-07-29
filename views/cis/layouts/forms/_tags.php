<?php if ( $currentRoute->id ) $arRes = $app->Cis->yappsGetTag($currentRoute->id) ?>

<div class="box box-primary">

    <div class="box-header with-border">
        <h3 class="box-title">Тег</h3>
    </div>
        
    <div class="box-body">
          
        <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
        <?php
			  
			$formSet = [
				  
				'name' => 'formCisTags',
				  
				'fields' => [
				  
					[
                        'type' => 'hidden',
                        'name' => 'id',
                        'value' => $currentRoute->id,
					],
					  
					//////////////////////////////////////////////////////////////////////////////////////////////////////////
					  
                    [
						'type' => 'text',
						'name' => 'name',
						'placeholder' => 'Наименование',
						'value' => $arRes['name']
					],
					[
						'type' => 'image',
						'name' => 'icon',
						'placeholder' => 'SVG 36*36px',
						'value' => $arRes['icon'],
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