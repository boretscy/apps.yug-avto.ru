<?php if ( $currentRoute->id ) $arRes = $app->YApps_GetBrand($currentRoute->id) ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Настройки бренда <small><?=$arRes['ru_name']?></small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border">
          
          <h3 class="box-title">Бренд</h3>
            
          <!-- /.box-tools -->
        </div>
         
        <div class="box-body">
          
		  <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
          <form role="form" method="post">
          	
            <input type="hidden" name="form" value="formAdminBrand" />
            <?php if ( $currentRoute->id ) { ?>
            <input type="hidden" name="id" value="<?= $currentRoute->id?>" />
            <?php } ?>
            
            <?php
				
				$formSet = [
					'fields' => [
						[
							'type' => 'text',
							'name' => 'url_key',
							'placeholder' => 'URL',
							'value' => $arRes['url_key'],
							'class' => ''
						],
						
						[
							'type' => 'text',
							'name' => 'en_name',
							'placeholder' => 'Название',
							'value' => $arRes['en_name'],
							'class' => ''
						],
						
						[
							'type' => 'text',
							'name' => 'ru_name',
							'placeholder' => 'Название на русском',
							'value' => $arRes['ru_name'],
							'class' => ''
						],
						[
							'type' => 'select',
							'name' => 'site_ids[]',
							'multiple' => true,
							'placeholder' => 'Привязка к сайтам',
							'value' => $arRes['site_ids'],
							'items' => $app->getSites(),
							'class' => ''
						]
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