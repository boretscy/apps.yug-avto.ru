<?php if ( $currentRoute->id ) $arRes = $app->Calc->getworkvalueById( $currentRoute->id ) ?>

<form role="form" method="post">
          	
  <input type="hidden" name="form" value="formCalcWorkvalues" />
  <?php if ( $currentRoute->id ) { ?>
  <input type="hidden" name="id" value="<?= $currentRoute->id?>" />
  <?php } ?>
  
  <?php
      
      $formSet = [
          'fields' => [
              [
                  'type' => 'text',
                  'name' => 'value',
                  'placeholder' => 'Значение',
                  'value' => $arRes['ru_name'],
                  'class' => ''
              ],
			  [
                  'type' => 'text',
                  'name' => 'ru_name',
                  'placeholder' => 'Описание',
                  'value' => $arRes['ru_name'],
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