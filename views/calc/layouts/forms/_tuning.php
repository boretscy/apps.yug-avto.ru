<?php if ( $currentRoute->id ) $arRes = $app->Calc->getColorSettingsById($currentRoute->id) ?>

<?php Helper::sp_h($arRes); ?>
<section class="content-header">
  <h1><?=$app->Calc->AppInfo()->ru_name?> <small>Установки</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border">
          <h3 class="box-title">Стили для сайта <?=$app->getSite( $currentRoute->id )['ru_name']?></h3>
        </div>
        
        <div class="box-body">
          
          <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
          <form role="form" method="post">
          	
            <input type="hidden" name="form" value="formCalcColors" />
            <?php if ( $currentRoute->id ) { ?>
            <input type="hidden" name="id" value="<?=$currentRoute->id?>" />
            <?php } ?>
            
            <?php
				
				$formSet = [
					'fields' => [
						[
							'type' => 'color',
							'name' => 'color',
							'placeholder' => 'Основной цвет',
							'value' => ($arRes) ? $arRes['color'] : '#00aeef',
							'class' => ''
						],
						[
							'type' => 'color',
							'name' => 'black',
							'placeholder' => 'Цвет текста',
							'value' => ($arRes) ? $arRes['black'] : '#2f3538',
							'class' => ''
						],
						[
							'type' => 'color',
							'name' => 'gray',
							'placeholder' => 'Цвет слайдера',
							'value' => ($arRes) ? $arRes['gray'] : '#dedede',
							'class' => ''
						],
						[
							'type' => 'text',
							'name' => 'default_url',
							'placeholder' => 'Основная страница калькулятора',
							'value' => $arRes['default_url'] ,
							'class' => ''
						],
						[
							'type' => 'textarea',
							'name' => 'css',
							'placeholder' => 'Дополнительные стили',
							'value' => ($arRes) ? $arRes['css'] : '',
							'rows' => 10,
							'class' => ''
						],
						[
							'type' => 'textarea',
							'name' => 'recipients',
							'placeholder' => 'Получатели',
							'value' => ($arRes) ? $arRes['recipients'] : '',
							'rows' => 10,
							'class' => ''
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
        
      </div>
    
    </div>
  </div>
  
</section>