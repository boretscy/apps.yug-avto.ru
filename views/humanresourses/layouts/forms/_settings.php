<?php if ( $currentRoute->id ) $arRes = $app->HumanResourses->getSet($currentRoute->id) ?>


<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?=$app->HumanResourses->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    <div class="col-md-12">
    
      <div class="box box-primary">
        
        <div class="box-header with-border">
          
          <h3 class="box-title">Настройка дилерского центра </h3>
            
          <!-- /.box-tools -->
        </div>
        
        <div class="box-body">
        
          <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
          <form role="form" method="post">
            
            <input type="hidden" name="form" value="formHumanResoursesSettings" />
            
            <?php
              
                $formSet = [
                    'fields' => [
                        
                        [
                            'type' => 'select',
                            'name' => 'dc_id',
                            'multiple' => false,
                            'placeholder' => 'Дилерский центр',
                            'value' => [$arRes['dc_id']],
                            'items' => $app->YApps_GetDCs()
                        ],
                        [
                            'type' => 'checkbox',
                            'name' => 'active',
                            'placeholder' => 'Включить',
                            'value' => (int)$arRes['active'],
                            'items' => [
                                  [
                                      'text' => 'Включить',
                                      'value' => (int)$arRes['active']
                                  ],
                             ]
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
