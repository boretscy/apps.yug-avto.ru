<?php if ( $currentRoute->id ) $arRes = $app->Widgets->getNavigator($currentRoute->id) ?>
<section class="content-header">
  <h1>Навигаторы <small>Настройки</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        
        <div class="box-body">
          
          <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
          <?php
			  
			  $formSet = [
				  
				  'name' => 'formWidgetsNavigator',
				  
				  'fields' => [
				  
					  [
						  'type' => 'hidden',
						  'name' => 'id',
						  'value' => $currentRoute->id,
					  ],
					  
					  //////////////////////////////////////////////////////////////////////////////////////////////////////////
					  
					  [
						  'type' => 'text',
						  'name' => 'ru_name',
						  'placeholder' => 'Название',
						  'value' => $arRes['ru_name']
					  ],
					  [
						  'type' => 'text',
						  'name' => 'url_scheme',
						  'placeholder' => 'URL-схема обращения',
						  'value' => $arRes['url_scheme'],
						  'description' => '<strong>%%WIDGET.NV.LAT%%</strong> и <strong>%%WIDGET.NV.LON%%</strong> - соответствующие координаты'
					  ],
					  [
						  'type' => 'text',
						  'name' => 'sort',
						  'placeholder' => 'Сортировка',
						  'value' => $arRes['sort']
					  ],
					  [
						  'type' => 'image',
						  'name' => 'image',
						  'placeholder' => 'Иконка .png ( 256x256 px )',
						  'value' => ( $arRes['image'] )
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
    
    </div>
  </div>
  
</section>