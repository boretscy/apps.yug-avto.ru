<?php if ( $currentRoute->id ) $arRes = $app->YApps_GetShowroom($currentRoute->id) ?>


<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Настройки витрины <small><?=$arRes['ru_name']?></small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border">
          
          <h3 class="box-title">Витрина</h3>
            
          <!-- /.box-tools -->
        </div>
         
        <div class="box-body">
          
		  <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
          <form role="form" method="post">
          	
            <input type="hidden" name="form" value="formAdminShowroom" />
            <?php if ( $currentRoute->id ) { ?>
            <input type="hidden" name="id" value="<?= $currentRoute->id?>" />
            <?php } ?>
            
            <?php
				
				$formSet = [
					'fields' => [
						[
							'type' => 'text',
							'name' => 'url',
							'placeholder' => 'URL',
							'value' => $arRes['url'],
							'class' => ''
						],
						[
							'type' => 'select',
							'name' => 'site_id',
							'multiple' => false,
							'placeholder' => 'Привязка к сайту',
							'value' => [$arRes['site_id']],
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