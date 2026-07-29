<?php if ( $currentRoute->id ) $arRes = $app->Foots->getManager($currentRoute->id) ?>

<section class="content-header">
  <h1>Менеджеры <small></small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border">
          <h3 class="box-title"><?=(($arRes['ru_name'])?:'Новый')?></h3>
        </div>
        
        <div class="box-body">
          
          <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
          <?php
				
			  $formSet = [
				  
				  'name' => 'formFootsManagers',
				  
				  'fields' => [
					  [
						  'type' => 'hidden',
						  'name' => 'id',
						  'value' => $currentRoute->id,
					  ],
					  
					  
					  [
						  'type' => 'text',
						  'name' => 'ru_name',
						  'placeholder' => 'ФИО',
						  'value' => $arRes['ru_name'],
						  'class' => '',
					  ],
					  [
						  'type' => 'select',
						  'name' => 'dc_id[]',
						  'multiple' => true,
						  'placeholder' => 'Привязка к ДЦ',
						  'value' => $arRes['dcs'],
						  'items' => $app->Foots->getUserDCs( $authUser->id ),
						  'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
					  ],
					  
					  [
						  'type' => 'delimiter',
						  'value' => 'Рассписание работы',
					  ],
					  [
					  	'type' => 'calendar',
						'fields' => [
							[
								'type' => 'select',
								'name' => 'schedule_id[]',
								'value' => $arRes['schedules'],
								'items' => $app->Foots->getSchedules(),
								'first_empty_not_disabled' => true,
								'multiple' => false
							]
						],
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
    
    </div>
  </div>
  
</section>