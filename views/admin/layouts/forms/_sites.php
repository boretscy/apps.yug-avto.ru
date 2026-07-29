<?php if ( $currentRoute->id ) $arRes = $app->getSite($currentRoute->id) ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Настройки сайта <small><?=$arRes['ru_name']?></small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border">
          
          <h3 class="box-title">Сайт</h3>
            
          <!-- /.box-tools -->
        </div>
         
        <div class="box-body">
          
		  <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
          <form role="form" method="post">
          	
            <input type="hidden" name="form" value="formAdminSite" />
            <?php if ( $currentRoute->id ) { ?>
            <input type="hidden" name="id" value="<?= $currentRoute->id?>" />
            <?php } ?>
            
            <?php
				
				$formSet = [
					'fields' => [
						[
							'type' => 'text',
							'name' => 'url',
							'placeholder' => 'Хост (без http://)',
							'value' => $arRes['url'],
							'class' => ''
						],
						[
							'type' => 'text',
							'name' => 'raw_url',
							'placeholder' => 'Полный URL (с http://)',
							'value' => $arRes['raw_url'],
							'class' => ''
						],
						[
							'type' => 'text',
							'name' => 'en_name',
							'placeholder' => 'Ключевое обозначение',
							'value' => $arRes['en_name'],
							'class' => ''
						],
						[
							'type' => 'text',
							'name' => 'ru_name',
							'placeholder' => 'Название',
							'value' => $arRes['ru_name'],
							'class' => ''
						],
						[
							'type' => 'text',
							'name' => 'brand_name',
							'placeholder' => 'Наименование бренда',
							'value' => $arRes['brand_name'],
							'class' => ''
						],
						[
							'type' => 'text',
							'name' => 'sort',
							'placeholder' => 'Сортировка',
							'value' => $arRes['sort'],
							'class' => ''
						],
						
						//////////////////////////////////
						[
							'type' => 'delimiter',
							'value' => 'Аналитика',
						],
						[
							'type' => 'text',
							'name' => 'piwik_id',
							'placeholder' => 'Matomo ID',
							'value' => $arRes['piwik_id'],
							'class' => ''
						],
						[
							'type' => 'text',
							'name' => 'yandex_id',
							'placeholder' => 'Yandex ID',
							'value' => $arRes['yandex_id'],
							'class' => ''
						],
						[
							'type' => 'text',
							'name' => 'google_id',
							'placeholder' => 'Google ID',
							'value' => $arRes['google_id'],
							'class' => ''
						],
						
						//////////////////////////////////
						[
							'type' => 'delimiter',
							'value' => 'CallTouch',
						],
						[
							'type' => 'text',
							'name' => 'calltouch_id',
							'placeholder' => 'CollTouch ID',
							'value' => $arRes['calltouch_id'],
							'class' => ''
						],
						[
							'type' => 'text',
							'name' => 'calltouch_api',
							'placeholder' => 'CollTouch API Token',
							'value' => $arRes['calltouch_api'],
							'class' => ''
						],
						[
							'type' => 'text',
							'name' => 'calltouch_node',
							'placeholder' => 'CallTouch NodeID',
							'value' => $arRes['calltouch_node'],
							'class' => ''
						],
						[
							'type' => 'text',
							'name' => 'calltouch_sess',
							'placeholder' => 'CallTouch Сессия',
							'value' => $arRes['calltouch_sess'],
							'class' => ''
						],
						[
							'type' => 'text',
							'name' => 'calltouch_class',
							'placeholder' => 'CallTouch Class',
							'value' => $arRes['calltouch_class'],
							'class' => ''
						],
						[
							'type' => 'textarea',
							'name' => 'start_script',
							'placeholder' => 'Дополнительный js перед методами',
							'value' => $arRes['start_script'],
							'rows' => 10,
						],
						[
							'type' => 'textarea',
							'name' => 'end_script',
							'placeholder' => 'Дополнительный js перед init()',
							'value' => $arRes['end_script'],
							'rows' => 10,
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