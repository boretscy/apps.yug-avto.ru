<?php if ( $currentRoute->id ) $arRes = $app->Cis->yappsGetComparison($currentRoute->id) ?>
<?php 
	$lists = $app->Cis->yappsGetComparisonsLists();
	foreach ( $lists as $k => $i ) $lists['entities'][] = ['id'=>$k, 'name'=>$k];
?>

<div class="box box-primary">

    <div class="box-header with-border">
        <h3 class="box-title">Сопоставление</h3>
    </div>
        
    <div class="box-body">
          
        <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
        <?php
			  
			$formSet = [
				  
				'name' => 'formCisComparisons',
				  
				'fields' => [
				  
					[
                        'type' => 'hidden',
                        'name' => 'id',
                        'value' => $currentRoute->id,
					],
					  
					//////////////////////////////////////////////////////////////////////////////////////////////////////////
					
					[
						'type' => 'select',
						'name' => 'entity',
						'multiple' => false,
						'placeholder' => 'Сущность',
						'value' => [$arRes['entity']],
						'items' => $lists['entities'],
						'select_field' => 'name'
					],
                    [
						'type' => 'text',
						'name' => 'desired',
						'placeholder' => 'Искомое',
						'value' => $arRes['desired']
					],
					[
						'type' => 'select',
						'name' => 'value',
						'multiple' => false,
						'placeholder' => 'Значение',
						'value' => [$arRes['value']],
						'items' => $lists[$arRes['entity']],
						'select_field' => 'name'
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
        
</div>