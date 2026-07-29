<?php $arRes = $app->HumanResourses->getManager( $currentRoute->id ); ?>
  
<div class="row">
    
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border"><h3 class="box-title">Специалист отдела кадров</h3></div>

        <div class="box-body">
          
		    <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>

            <?php
				
                $formSet = [
                    
                    'name' => 'formHumanResoursesManager',
                    
                    'fields' => [
                    
                        [
                            'type' => 'hidden',
                            'name' => 'id',
                            'value' => $currentRoute->id,
                        ],
                        
                        //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        
                        [
                            'type' => 'text',
                            'name' => 'ru_name',
                            'placeholder' => 'ФИО',
                            'value' => $arRes['ru_name']
                        ],
                        
                        [
                            'type' => 'number',
                            'name' => 'sort',
                            'placeholder' => 'Сортировка',
                            'value' => $arRes['sort']
                        ],
                        
                    ],
                    'submit' => [
                        'class' => 'primary',
                        'text' => 'Отправить'
                    ],
                ];
            ?>
            
            <?php HTML::FullForm( $formSet, $arRes['id'] ); ?>
            
          
        </div>
        <!-- /.box-body -->
    </div>
      
</div>
    
</div>

</section>
<!-- /.content -->