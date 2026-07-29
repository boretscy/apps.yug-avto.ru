<?php if ( $currentRoute->id ) $arRes = $app->Calc->getCheckworkById( $currentRoute->id ) ?>

<form role="form" method="post">
  
  <input type="hidden" name="form" value="formCalcCheckworks" />
  <?php if ( $currentRoute->id ) { ?>
  <input type="hidden" name="id" value="<?= $currentRoute->id?>" />
  <?php } ?>
  
  <?php
	  
	  $formSet = [
		  'fields' => [
			  [
				  'type' => 'text',
				  'name' => 'ru_name',
				  'placeholder' => 'Наименование работ',
				  'value' => $arRes['ru_name'],
				  'class' => ''
			  ],
			  [
				  'type' => 'text',
				  'name' => 'sort',
				  'placeholder' => 'Порядок сортировки',
				  'value' => ($currentRoute->id)?$arRes['sort']:500,
				  'class' => ''
			  ],
			  [
				  'type' => 'checkbox',
				  'name' => 'additional_flag',
				  'placeholder' => 'Дополнительные работы',
				  'value' => $arRes['additional_flag'],
				  'items' => [
					  [
						  'text' => 'Дополнительные работы',
						  'value' => $arRes['additional_flag']
					  ],
				  ],
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