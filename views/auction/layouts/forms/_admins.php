<div class="box box-primary">
  
  <div class="box-header with-border">
    <h3 class="box-title">Изменить состав администраторов</h3>
  </div>
  
  <div class="box-body">
    
    <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
    
    <?php
        
		$users = $app->YApps_GetUsersByApp( $app->Auction->AppInfo()->id );
		foreach ( $users as $k => $u ) $users[$k]['ru_name'] = $u['name']; 
		
        $formSet = [
            
            'name' => 'formAuctionAdmins',
            
            'fields' => [
				[
                    'type' => 'select',
                    'name' => 'admins[]',
                    'multiple' => true,
                    'placeholder' => 'Администратоы',
                    'value' => $app->Auction->getAdmins(),
                    'items' => $users,
                    'class' => ''
                ]
                
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