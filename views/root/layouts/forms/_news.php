<?php if ( $currentRoute->id ) $arRes = $app->News->getById( $currentRoute->id ) ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Новости и релизы<small></small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border">
          
          <h3 class="box-title">Новость</h3>
            
          <!-- /.box-tools -->
        </div>
         
        <div class="box-body">
          
		  <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
          <form role="form" method="post">
          	
            <input type="hidden" name="form" value="formRootNews" />
            <?php if ( $currentRoute->id ) { ?>
            <input type="hidden" name="id" value="<?= $currentRoute->id?>" />
            <?php } ?>
            
            <?php
				
				$formSet = [
					'fields' => [
						[
							'type' => 'text',
							'name' => 'title',
							'placeholder' => 'Заголовок',
							'value' => $arRes['title'],
                        ],
                        [
                            'type' => 'textarea',
                            'name' => 'text',
                            'placeholder' => 'Текст',
                            'value' => $arRes['text'],
                            'class' => '',
                            'rows' => 10,
                            'cols' => 80,
                            'ckeditor' => true,
                        ],
						[
                            'type' => 'select',
                            'name' => 'app_id',
                            'multiple' => false,
                            'placeholder' => 'Приложение',
                            'value' => [$arRes['app_id']],
                            'items' => array_merge([], $app->Apps->getApps()),
                            'class' => '',
                            'first_empty' => true
                        ],
						[
							'type' => 'date',
							'name' => 'date',
							'placeholder' => 'Дата',
							'value' => ($arRes['timestamp']) ? date('d.m.Y H:i', $arRes['timestamp']) : '',
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