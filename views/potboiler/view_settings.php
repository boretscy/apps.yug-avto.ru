<?php
    switch ( $currentRoute->action ) {
        
        case 'start':
            $app->Potboiler->setSettings( ['status'=>1] );
			$app->Route->redirect('/potboiler/');
            break;
        
        case 'stop':
            $app->Potboiler->setSettings( ['status'=>0] );
			$app->Route->redirect('/potboiler/');
            break;

        case 'reset':
            $app->Potboiler->setSettings( ['status'=>0, 'next_page'=>1, 'percent'=>0, 'items'=>0, 'total_items'=>0] );
			$app->Route->redirect('/potboiler/');
            break;

        case 'clear':
            $app->Potboiler->clearItems();
			$app->Route->redirect('/potboiler/');
            break;
		
		case 'error':
            $app->Potboiler->clearErrors();
			$app->Route->redirect('/potboiler/');
            break;
    }
?>
  
<?php $arRes = $app->Potboiler->getSettings(); ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Potboiler->AppInfo()->ru_name?> <small>Настройки</small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <div class="row">
      
      <div class="col-md-12">
      
      	 <div class="box box-primary">
        
            <div class="box-header with-border">
              
              <h3 class="box-title">Управление</h3>
                
              <!-- /.box-tools -->
            </div>
             
            <div class="box-body">
              
              <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
              
              <div class="callout callout-warning">
                <h4>Предупреждение!</h4>
                <p>Парсить телефоны возможно ТОЛЬКО с мобильной версии авито!</p>
              </div>
              
              <form role="form" method="post">
                
                <input type="hidden" name="form" value="formPotboilerSettings" />
                
                <?php
                    
                    $formSet = [
                        'fields' => [
                            [
                                'type' => 'text',
                                'name' => 'parse_url',
                                'placeholder' => 'Адрес парсинга',
                                'value' => $arRes->parse_url,
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
  
</div>