<?php if ( $currentRoute->id ) $arRes = $app->Goals->getById($currentRoute->id) ?>

<section class="content-header">
  <h1>Настройки <small>Цели</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border">
          <h3 class="box-title">Установки для цели <?=$arRes['goal_name']?></h3>
        </div>
        
        <div class="box-body">
          
          <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
          <?php
				
			  $formSet = [
				  
				  'name' => 'formHotGoals',
				  
				  'fields' => [
					  [
						  'type' => 'hidden',
						  'name' => 'app_id',
						  'value' => ( $arRes ) ? $arRes['app_id'] : $app->Hot->AppInfo()->id,
					  ],
					  [
						  'type' => 'hidden',
						  'name' => 'goal_id',
						  'value' => $arRes['goal_id'],
					  ],
					  [
						  'type' => 'hidden',
						  'name' => 'goal_js',
						  'value' => 'YApps_Goals-Hot_Send',
					  ],
					  [
						  'type' => 'select',
						  'name' => 'site_id',
						  'multiple' => false,
						  'placeholder' => 'Привязка к сайту',
						  'value' => [$arRes['site_id']],
						  'items' => $userSites['sites'],
						  'class' => ''
					  ],
					  [
						  'type' => 'text',
						  'name' => 'goal_name',
						  'placeholder' => 'Название цели',
						  'value' => $arRes['goal_name'],
						  'class' => '',
					  ],
					  [
						  'type' => 'text',
						  'name' => 'goal_url',
						  'placeholder' => (( $arRes )?'С':'Полная с').'сылка на страницу с целью',
						  'value' => $arRes['goal_url'],
						  'class' => '',
						  'description' => ( $arRes ) ? '' : 'Например: http://kia.yug-avto.ru/special/purchase/rio/item24790603.php' 
					  ],
					  
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