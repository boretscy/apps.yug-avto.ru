<?php if ( $currentRoute->id ) $arRes = $app->Widgets->getABTest($currentRoute->id) ?>
<?php  $type = ( $_GET['widget'] ) ? $app->Widgets->getTypeByKey($_GET['widget']) : $app->Widgets->getTypeById($arRes['type_id']); ?>
<section class="content-header">
  <h1>A/B Тест <small>Настройки</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        
        <div class="box-body">
          
          <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
          <?php
			  
			  if ( $_GET['widget'] ) $arWidgets = $app->Widgets->getWidgetsABTest( $type['id'], $authUser );
			  
			  $formSet = [
				  
				  'name' => 'formWidgetsABTest',
				  
				  'fields' => [
				  
					  [
						  'type' => 'hidden',
						  'name' => 'id',
						  'value' => $currentRoute->id,
					  ],
					  [
						  'type' => 'hidden',
						  'name' => 'type_id',
						  'value' => $type['id'],
					  ],
					  
					  //////////////////////////////////////////////////////////////////////////////////////////////////////////
					  
					  [
						  'type' => 'text',
						  'name' => 'ru_name',
						  'placeholder' => 'Название',
						  'value' => $arRes['ru_name']
					  ],
					  [
						  'type' => 'select',
						  'name' => 'a_widget_id',
						  'multiple' => false,
						  'placeholder' => 'Виджет А',
						  'value' => [$arRes['a_widget_id']],
						  'items' => ( $_GET['widget'] ) ? $arWidgets : [$app->Widgets->getWidgetById($arRes['a_widget_id'])],
						  'class' => '',
						  'first_empty' => true
					  ],
					  [
						  'type' => 'select',
						  'name' => 'b_widget_id',
						  'multiple' => false,
						  'placeholder' => 'Виджет B',
						  'value' => [$arRes['b_widget_id']],
						  'items' => ( $_GET['widget'] ) ? $arWidgets : [$app->Widgets->getWidgetById($arRes['b_widget_id'])],
						  'class' => '',
						  'first_empty' => true
					  ],
					  [
						  'type' => 'checkbox',
						  'name' => 'active',
						  'placeholder' => 'Активность',
						  'value' => (int)$arRes['active'],
						  'items' => [
							  [
								  'text' => 'Активность',
								  'value' => (int)$arRes['active']
							  ],
						  ],
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