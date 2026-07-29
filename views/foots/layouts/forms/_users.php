<?php if ( $currentRoute->id ) $arRes = $app->Foots->getUser($currentRoute->id) ?>
      
<div class="box box-primary">
  
  <div class="box-header with-border">
    <h3 class="box-title"><?=(($arRes->name)?:'Новый')?></h3>
  </div>
  
  <div class="box-body">
    
    <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
    
    <?php
          
        $formSet = [
            
            'name' => 'formFootsUsers',
            
            'fields' => [
				[
					'type' => 'hidden',
					'name' => 'user_id',
					'value' => $currentRoute->id,
				],
				[
                    'type' => 'select',
                    'name' => 'dc_id[]',
                    'multiple' => true,
                    'placeholder' => 'Привязка к ДЦ',
                    'value' => $arRes->dcs,
                    'items' => $app->YApps_GetDCs(),
                    'class' => ''
                ]
                
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