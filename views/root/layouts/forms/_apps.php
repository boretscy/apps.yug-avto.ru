<?php if ( $currentRoute->id ) $arRes = $app->Apps->getAppById( $currentRoute->id ) ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Приложения<small></small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border">
          
          <h3 class="box-title">Приложение</h3>
            
          <!-- /.box-tools -->
        </div>
         
        <div class="box-body">
          
		  <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
          <form role="form" method="post">
          	
            <input type="hidden" name="form" value="formRootApps" />
            <?php if ( $currentRoute->id ) { ?>
            <input type="hidden" name="id" value="<?= $currentRoute->id?>" />
            <?php } ?>
            
            <?php
				
				$formSet = [
					'fields' => [
						[
							'type' => 'text',
							'name' => 'ru_name',
							'placeholder' => 'Название',
							'value' => $arRes['ru_name'],
                        ],
                        [
							'type' => 'text',
							'name' => 'url_key',
							'placeholder' => 'URL',
							'value' => $arRes['url_key'],
                        ],
                        [
							'type' => 'text',
							'name' => 'class',
							'placeholder' => 'Класс',
							'value' => $arRes['class'],
                        ],
                        [
							'type' => 'text',
							'name' => 'fa_icon',
							'placeholder' => 'Иконка',
							'value' => ( $arRes['fa_icon'] ) ?: 'crosshairs',
                        ],
                        [
							'type' => 'number',
							'name' => 'sort',
							'placeholder' => 'Сортировка',
							'value' => ( $arRes['sort'] ) ?: 500,
                        ],
						[
                            'type' => 'checkbox',
                            'name' => 'view_in_menu',
                            'placeholder' => 'Показывать в меню',
                            'value' => (int)$arRes['view_in_menu'],
                            'items' => [
                                [
                                    'text' => 'Показывать в меню',
                                    'value' => (int)$arRes['view_in_menu']
                                ],
                            ],
                        ],
                        [
                            'type' => 'delimiter',
                            'value' => '',
                        ],
						[
                            'type' => 'checkbox',
                            'name' => 'hide_home',
                            'placeholder' => 'Скрыть пункт "Домашняя страница"',
                            'value' => (int)$arRes['hide_home'],
                            'items' => [
                                [
                                    'text' => 'Скрыть пункт "Домашняя страница"',
                                    'value' => (int)$arRes['hide_home']
                                ],
                            ],
                        ],
						[
                            'type' => 'checkbox',
                            'name' => 'hide_stat',
                            'placeholder' => 'Скрыть пункт "Статистика"',
                            'value' => (int)$arRes['hide_stat'],
                            'items' => [
                                [
                                    'text' => 'Скрыть пункт "Статистика"',
                                    'value' => (int)$arRes['hide_stat']
                                ],
                            ],
                        ],
						[
                            'type' => 'checkbox',
                            'name' => 'hide_export',
                            'placeholder' => 'Скрыть пункт "Экспорт"',
                            'value' => (int)$arRes['hide_export'],
                            'items' => [
                                [
                                    'text' => 'Скрыть пункт "Экспорт"',
                                    'value' => (int)$arRes['hide_export']
                                ],
                            ],
                        ],
                        [
                            'type' => 'delimiter',
                            'value' => '',
                        ],
						[
                            'type' => 'checkbox',
                            'name' => 'maintenance',
                            'placeholder' => 'Обслуживание',
                            'value' => (int)$arRes['maintenance'],
                            'items' => [
                                [
                                    'text' => 'Обслуживание',
                                    'value' => (int)$arRes['maintenance']
                                ],
                            ],
                        ],
						[
                            'type' => 'checkbox',
                            'name' => 'activity',
                            'placeholder' => 'Включить',
                            'value' => (int)$arRes['activity'],
                            'items' => [
                                [
                                    'text' => 'Включить',
                                    'value' => (int)$arRes['activity']
                                ],
                            ],
                        ],
                        
					],
					'submit' => [
						'class' => 'primary',
						'text' => 'Отправить'
					]
				];
			?>
            
            <?php HTML::Form( $formSet ); ?>
            
          </form>
          
        </div>
        <!-- /.box-body -->
      </div>
      
    </div>
    
  </div>

</section>
<!-- /.content -->