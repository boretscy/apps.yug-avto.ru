<?php if ( $currentRoute->id ) $arRes = $app->Calc->getCheckpointById( $currentRoute->id ) ?>

<form role="form" method="post">
          	
  <input type="hidden" name="form" value="formCalcCheckpoints" />
  <?php if ( $currentRoute->id ) { ?>
  <input type="hidden" name="id" value="<?= $currentRoute->id?>" />
  <?php } ?>
  
  <?php
      
      $formSet = [
          'fields' => [
              [
                  'type' => 'text',
                  'name' => 'milleage',
                  'placeholder' => 'Пробег, км',
                  'value' => $arRes['milleage'],
                  'class' => ''
              ],
              [
                  'type' => 'text',
                  'name' => 'age',
                  'placeholder' => 'Возраст, лет',
                  'value' => $arRes['age'],
                  'class' => ''
              ],
              [
                  'type' => 'text',
                  'name' => 'sort',
                  'placeholder' => 'Sort',
                  'value' => ($currentRoute->id)?$arRes['sort']:500,
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