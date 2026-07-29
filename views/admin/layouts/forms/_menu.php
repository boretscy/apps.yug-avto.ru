<?php if ( $currentRoute->id ) $arRes = $app->Apps->getMenuPoint($currentRoute->id) ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Настройки Пункта меню <small><?=$arRes['ru_name']?></small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border">
          
          <h3 class="box-title">Дилерский центр</h3>
            
          <!-- /.box-tools -->
        </div>
         
        <div class="box-body">
          
            <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
            
            <?php
            
                $formSet = [
                    
                    'name' => 'formAdminMenu',
                    
                    'fields' => [
                        [
                            'type' => 'hidden',
                            'name' => 'id',
                            'value' => $currentRoute->id,
                        ],
                        [
							'type' => 'select',
							'name' => 'app_id',
							'multiple' => false,
							'placeholder' => 'Приложение',
							'value' => [$arRes['app_id']],
							'items' => $app->Apps->getApps(),
							'class' => ''
						],
                        [
                            'type' => 'text',
                            'name' => 'name',
                            'placeholder' => 'Наименование',
                            'value' => $arRes['name'],
                            'class' => '',
                        ],
                        [
                            'type' => 'text',
                            'name' => 'url_key',
                            'placeholder' => 'URL ключ',
                            'value' => $arRes['url_key'],
                            'class' => '',
                        ],
                        [
                            'type' => 'text',
                            'name' => 'icon',
                            'placeholder' => 'Иконка',
                            'value' => $arRes['icon'],
                            'class' => '',
                        ],
                        [
							'type' => 'select',
							'name' => 'role_id',
							'multiple' => false,
							'placeholder' => 'Уровень доступа',
							'value' => [$arRes['role_id']],
							'items' => $app->User->getRoles(),
							'class' => ''
						],
                        [
                            'type' => 'number',
                            'name' => 'sort',
                            'placeholder' => 'Сортировка',
                            'value' => $arRes['sort'],
                            'class' => '',
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
        <!-- /.box-body -->
      </div>
      
    </div>
    
  </div>

</section>
<!-- /.content -->