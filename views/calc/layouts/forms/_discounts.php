<?php if ( $currentRoute->id ) $arRes = $app->Calc->getDiscountById( $currentRoute->id ) ?>

<form role="form" method="post">
          	
  <input type="hidden" name="form" value="formCalcDiscounts" />
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
          ],
          'submit' => [
              'class' => 'primary',
              'text' => 'Отправить'
          ]
      ];
  ?>
  
  <?php HTML::Form( $formSet ); ?>
  
</form>