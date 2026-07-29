<section class="content-header">
  <h1><?=$app->Calc->AppInfo()->ru_name?> <small>Модификации</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    <div class="col-md-12">
      <div class="box box-primary">
            
        <div class="box-header with-border">
          <h3 class="box-title">Модификации</h3>
        </div>
             
        <div class="box-body">

            <?php 
				
				if ( $currentRoute->action == 'edit' && $currentRoute->id ) $arRes = $app->Calc->getModById( $currentRoute->id );
				
				$arUserSites = $app->getUserSites( $authUser );
				$iDs = [];
				foreach ( $arUserSites['sites'] as $site ) {
					
					$arT = $app->Calc->getModelsBySite( $site['id'] )['models'];
					foreach ( $arT as $t ) {
						
						if ( !in_array($t['id'], $iDs) ) {
							
							$iDs[] = $t['id'];
							$arModels[] = $t;
						}
					}
				}
				
				if ( $POSTRes ) HTML::Error( $POSTRes );
			?>

            <form role="form" method="post">
                        
            <input type="hidden" name="form" value="formCalcMod" />
            <?php if ( $currentRoute->action == 'edit' && $currentRoute->id ) { ?>
            <input type="hidden" name="id" value="<?= $currentRoute->id?>" />
            <?php } ?>
            
            <?php
                
                $formSet = [
                    'fields' => [
                        [
                            'type' => 'text',
                            'name' => 'ru_name',
                            'placeholder' => 'Наименование',
                            'value' => $arRes['ru_name'],
                            'class' => ''
                        ],
                        [
                            'type' => 'select',
                            'name' => 'model_id',
                            'multiple' => false,
                            'placeholder' => 'Привязка к модели',
                            'value' => ($arRes) ? [$arRes['model_id']] : [$_GET['model']],
                            'items' => $arModels,
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
<!-- /.content -->