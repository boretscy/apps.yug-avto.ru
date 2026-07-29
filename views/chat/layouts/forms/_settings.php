<?php if ( $currentRoute->id ) $arRes = $app->Chat->getSetById($currentRoute->id) ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?=$app->Chat->AppInfo()->ru_name?> <small>Настройки приложения</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border">
          
          <h3 class="box-title">Оператор</h3>
            
          <!-- /.box-tools -->
        </div>
         
        <div class="box-body">
          
		  <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
          <form role="form" method="post">
          	
            <input type="hidden" name="form" value="formChatSettings" />
            <?php if ( $currentRoute->id ) { ?>
            <input type="hidden" name="id" value="<?= $currentRoute->id?>" />
            <?php } ?>
            
            <?php
				
				$formSet = [
					'fields' => [
						[
							'type' => 'select',
							'name' => 'site_id',
							'multiple' => false,
							'placeholder' => 'Сайт',
							'value' => [$arRes['site_id']],
							'items' => $app->getSites(),
							'class' => ''
						],
						[
							'type' => 'text',
							'name' => 'token',
							'placeholder' => 'Token',
							'value' => $arRes['token'],
							'class' => ''
						],
						
						[
							'type' => 'checkbox',
							'name' => 'active',
							'placeholder' => 'Активность',
							'value' => $arRes['active'],
							'items' => [
								[
									'text' => 'Включить',
									'value' => $arRes['active']
								],
							],
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
        <!-- /.box-body -->
      </div>
      
    </div>
    
  </div>

</section>
<!-- /.content -->