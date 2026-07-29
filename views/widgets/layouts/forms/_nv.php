<?php if ( $currentRoute->id ) $arRes = $app->Widgets->getWidgetById($currentRoute->id) ?>

<section class="content-header">
  <h1><?=$app->Widgets->getTypeById(3)['ru_name']?> <small>Настройки</small></h1>
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
				  
                    'name' => 'formWidget',
                    
                    'fields' => [
                    
                        [
                            'type' => 'hidden',
                            'name' => 'id',
                            'value' => $currentRoute->id,
                        ],
                        [
                            'type' => 'hidden',
                            'name' => 'type_id',
                            'value' => 3,
                        ],
                        [
                            'type' => 'hidden',
                            'name' => 'public_key',
                            'value' => $arRes['public_key'],
                        ],
                        
                        //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        [
                            'type' => 'select',
                            'name' => 'site_id',
                            'multiple' => false,
                            'placeholder' => 'Привязка к сайту',
                            'value' => [$arRes['site_id']],
                            'items' => $userSites['sites'],
                            'class' => '',
                            'first_empty' => true
                        ],
                        [
                            'type' => 'delimiter',
                            'value' => 'Настройки виджета (<strong>для новых виджетов</strong>)',
                        ],
                        [
                            'type' => 'text',
                            'name' => 'nv_title',
                            'placeholder' => 'Заголовок',
                            'value' => ( $arRes['nv_title'] ) ?: $app->Widgets->getConf()->Defaults->NVTitle
                        ],
                        [
                            'type' => 'text',
                            'name' => 'nv_second_title',
                            'placeholder' => 'Заголовок окра выбора приложения',
                            'value' => ( $arRes['nv_second_title'] ) ?: $app->Widgets->getConf()->Defaults->NVSecondTitle
                        ],
                        [
                            'type' => 'text',
                            'name' => 'nv_second_text',
                            'placeholder' => 'Текст окна выбора приложения',
                            'value' => ( $arRes['nv_second_text'] ) ?: $app->Widgets->getConf()->Defaults->NVSecondText
                        ],
                        [
                            'type' => 'delimiter',
                            'value' => 'Настройки',
                        ],

                        [
                            'type' => 'text',
                            'name' => 'ru_name',
                            'placeholder' => 'Название',
                            'value' => $arRes['ru_name']
                        ],
                        [
                            'type' => 'textarea',
                            'name' => 'css',
                            'placeholder' => 'Дополнительные стили',
                            'value' => $arRes['css'],
                            'rows' => 10,
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