<?php if ( $currentRoute->id ) $arRes = $app->Foots->getTarget($currentRoute->id) ?>
      
<div class="box box-primary">
  
  <div class="box-header with-border">
    <h3 class="box-title"><?=(($arRes['ru_name'])?:'Новая')?></h3>
  </div>
  
  <div class="box-body">
    
    <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
    
    <?php foreach ( range(1, $app->Foots->getConf()->TargetsRoles) as $i ) $roles[] = ['id'=>$i, 'ru_name'=>$i]; ?>
    
    <?php
          
        $formSet = [
            
            'name' => 'formFootsTargets',
            
            'fields' => [
				[
					'type' => 'hidden',
					'name' => 'id',
					'value' => $currentRoute->id,
				],
				[
					'type' => 'text',
					'name' => 'ru_name',
					'placeholder' => 'Наименование',
					'value' => $arRes['ru_name'],
					'class' => '',
				],
				[
                    'type' => 'select',
                    'name' => 'role',
                    'multiple' => false,
                    'placeholder' => 'Важнность цели',
                    'value' => [$arRes['role']],
                    'items' => $roles,
                    'class' => ''
                ],
				[
                    'type' => 'select',
                    'name' => 'next_step',
                    'multiple' => false,
                    'placeholder' => 'Следующий шаг',
                    'value' => [$arRes['next_step']],
                    'items' => $app->Foots->getSteps(),
                    'class' => ''
                ],
				[
					'type' => 'number',
					'name' => 'sort',
					'placeholder' => 'Сортировка',
					'value' => $arRes['sort'],
					'class' => '',
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