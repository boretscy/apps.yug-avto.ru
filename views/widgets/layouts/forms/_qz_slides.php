<?php 
	if ( $currentRoute->action == 'edit' && $currentRoute->id ) $arRes = $app->Widgets->getQZSlide($currentRoute->id);
	$widget_id = ( $currentRoute->action == 'new' ) ? (int)$currentRoute->id : $arRes['widget_id'];
	
	$brands = $app->YApps_GetIndBrandsBySiteId($app->YApps_GetBrandsIDsBySiteId($app->MySQL->getOne('SELECT site_id FROM yapps_app_widgets WHERE id = ?i', $widget_id)));
	
	
	foreach ( $brands as $k => $i ) $brands[$k]['models'] = $app->YApps_GetModelsByBrand( $i['id'] );
	$cur_brand = ( $arRes['brand_id'] ) ?: key($brands);
?>
<script>
	var Brands = <?=json_encode( $brands );?>
</script>

<section class="content-header">
  <h1><?=$app->Widgets->getTypeById(7)['ru_name']?> <small>Слайд</small></h1>
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
				  
				  'name' => 'formWidgetQZSlide',
				  
				  'fields' => [
				  
					  [
						  'type' => 'hidden',
						  'name' => 'id',
						  'value' => ( $currentRoute->action == 'edit' ) ? $currentRoute->id : 0,
					  ],
					  [
						  'type' => 'hidden',
						  'name' => 'widget_id',
						  'value' => $widget_id,
					  ],
					  
					  //////////////////////////////////////////////////////////////////////////////////////////////////////////
					  [
						  'type' => 'select',
						  'name' => 'type_id',
						  'multiple' => false,
						  'placeholder' => 'Тип слайда',
						  'value' => [$arRes['type_id']],
						  'items' => $app->Widgets->getQZSlideTypes(),
						  'class' => '',
						  'showto' => true
					  ],
					  [
						  'type' => 'text',
						  'name' => 'ru_name',
						  'placeholder' => 'Загололвок',
						  'value' => $arRes['ru_name']
					  ],
					  [
						  'type' => 'number',
						  'name' => 'sort',
						  'placeholder' => 'Сортировка',
						  'value' => ( $arRes['sort'] ) ?: 500
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
					  [
						  'type' => 'checkbox',
						  'name' => 'required',
						  'placeholder' => 'Обязательность ответа',
						  'value' => (int)$arRes['required'],
						  'items' => [
							  [
								  'text' => 'Обязательность ответа',
								  'value' => (int)$arRes['required']
							  ],
						  ],
					  ],
					  
					  
					  [
						  'type' => 'delimiter',
						  'value' => 'Настройки для типа "Выбор моделей"',
						  'hideable' => true,
						  'hide' => ( $arRes && (int)$arRes['type_id'] != 1 ),
						  'hidename' => 'models'
					  ],
					  [
						  'type' => 'select',
						  'name' => 'brand_id',
						  'multiple' => false,
						  'placeholder' => 'Бренд',
						  'value' => [$cur_brand],
						  'items' => $brands,
						  'params' => [
							  'multisteps' => 'Y',
							  'step' => '1',
							  'target' => 'models_id[]'
						  ],
						  'hideable' => true,
						  'hide' => ( $arRes && (int)$arRes['type_id'] != 1 ),
						  'hidename' => 'models'
					  ],
					  [
						  'type' => 'select',
						  'name' => 'models_id[]',
						  'multiple' => true,
						  'placeholder' => 'Модель',
						  'value' => $arRes['models_id'],
						  'items' => $brands[$cur_brand]['models'],
						  'params' => [
							  'multisteps' => 'Y',
							  'step' => '2'
						  ],
						  'rows' => 10,
						  'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений',
						  'first_empty' => true,
						  'hideable' => true,
						  'hide' => ( $arRes && (int)$arRes['type_id'] != 1 ),
						  'hidename' => 'models'
					  ],
					  
					  [
						  'type' => 'delimiter',
						  'value' => 'Настройки ответа  для типа "Выбор ответа"',
						  'button' => [
						      'text' => 'Добавить ответ',
							  'role' => 'add_linegrouptext',
							  'target' => 'answers'
						  ],
						  'hideable' => true,
						  'hide' => ( (int)$arRes['type_id'] != 2 && (int)$arRes['type_id'] != 3 ),
						  'hidename' => 'answers'
					  ],
					  [
						  'type' => 'linegrouptext',
						  'multiple' => true,
						  'group_name' => 'answers',
						  'value' => $arRes['answers'],
						  'count' => 10,
						  'fields' => [
							  [
								  'name' => 'answers_value',
								  'type' => 'text',
								  'placeholder' => 'Ответ'
							  ]
						  ],
						  'hideable' => true,
						  'hide' => ( (int)$arRes['type_id'] != 2 && (int)$arRes['type_id'] != 3 ),
						  'hidename' => 'answers'
					  ],
					  
					  [
						  'type' => 'delimiter',
						  'value' => 'Настройки ответа для типа "Поля ввода"',
						  'button' => [
						      'text' => 'Добавить',
							  'role' => 'add_linegrouptext',
							  'target' => 'inputs'
						  ],
						  'hideable' => true,
						  'hide' => ( (int)$arRes['type_id'] != 4 ),
						  'hidename' => 'inputs'
					  ],
					  [
						  'type' => 'linegrouptext',
						  'multiple' => true,
						  'group_name' => 'inputs',
						  'value' => $arRes['inputs'],
						  'count' => 10,
						  'fields' => [
							  [
								  'name' => 'inputs_value',
								  'type' => 'text',
								  'placeholder' => 'Вопрос'
							  ],
							  [
								  'name' => 'inputs_description',
								  'type' => 'text',
								  'placeholder' => 'Подсказка'
							  ],
							  [
								  'name' => 'inputs_required',
								  'type' => 'checkbox',
								  'placeholder' => 'Обязательность'
							  ]
						  ],
						  'hideable' => true,
						  'hide' => ( (int)$arRes['type_id'] != 4 ),
						  'hidename' => 'inputs'
					  ],
					  
				  ],
				  'submit' => [
					  'class' => 'primary',
					  'text' => 'Отправить'
				  ],
			  ];
		  ?>
          
          <?php HTML::FullForm( $formSet, $arRes['id'], $POSTRes ); ?>
          
        </div>
        
      </div>
      
      
    </div>
  </div>
  
  
</section>