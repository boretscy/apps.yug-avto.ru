<?php $arRes = $app->Auction->getTemplates() ?>
<div class="box box-primary">
  
  <div class="box-header with-border">
    <h3 class="box-title">Шаблоны</h3>
  </div>
  
  <div class="box-body">
    
    <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
    
    <?php
		
        $formSet = [
            
            'name' => 'formAuctionTemplates',
            'description' => false,
			
            'fields' => [
				[
					'type' => 'hidden',
					'name' => 'id',
					'value' => $currentRoute->id,
				],
				
				[
					'type' => 'delimiter',
					'value' => 'СМС',
				],
				[
					'type' => 'textarea',
					'name' => 'sms_start',
					'placeholder' => 'Шаблон смс старта торгов',
					'value' => $arRes['sms_start'],
					'rows' => 3
				],
				[
					'type' => 'textarea',
					'name' => 'sms_winner',
					'placeholder' => 'Шаблон смс победителю торгов',
					'value' => $arRes['sms_winner'],
					'rows' => 3
				],
				
				[
					'type' => 'delimiter',
					'value' => 'Email',
				],
				[
					'type' => 'textarea',
					'name' => 'email_start',
					'placeholder' => 'Шаблон email старта торгов',
					'value' => $arRes['email_start'],
					'class' => 'email_start',
					'rows' => 8
				],
				[
					'type' => 'textarea',
					'name' => 'email_winner',
					'placeholder' => 'Шаблон email победителю торгов',
					'value' => $arRes['email_winner'],
					'class' => 'email_winner',
					'rows' => 8
                ],
				[
					'type' => 'textarea',
					'name' => 'email_winners',
					'placeholder' => 'Шаблон email 2-му и 3-му победителям торгов',
					'value' => $arRes['email_winners'],
					'class' => 'email_winners',
					'rows' => 8
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