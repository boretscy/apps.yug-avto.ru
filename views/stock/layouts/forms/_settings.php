<div class="box box-primary">
        
    <div class="box-header with-border"><h3 class="box-title">Дилерский центр</h3></div>
         
    <div class="box-body">
          
        <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
        <?php
            
            $formSet = [
                    
                'name' => 'formStock',
                
                'fields' => [
                    [
						'type' => 'select',
						'name' => 'id',
						'multiple' => false,
						'placeholder' => 'Дилерский центр',
						'value' => [],
						'items' => $app->getUserDCs($authUser),
                    ],
                    [
                        'type' => 'image',
                        'name' => 'stock',
                        'placeholder' => 'Файл .xlsx',
                        'value' => ''
                    ],
                        
                ],
                'submit' => [
                    'class' => 'primary',
                    'text' => 'Отправить'
                ],
            ];
        ?>
        <?php HTML::FullForm( $formSet ); ?>
          
    </div>
    
</div>
