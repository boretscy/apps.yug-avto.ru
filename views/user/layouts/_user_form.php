<?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
<form class="form" method="post" enctype="multipart/form-data">
  <input type="hidden" name="form" value="formAdminUser" />
  <input type="hidden" name="id" value="<?=$authUser->id?>" />
  <?php
      $formSet = [
          'fields' => [
              [
                  'type' => 'text',
                  'name' => 'name',
                  'placeholder' => 'Имя',
                  'value' => $authUser->name,
                  'class' => ''
              ],
              [
                  'type' => 'text',
                  'name' => 'email',
                  'placeholder' => 'Email',
                  'value' => $authUser->email,
                  'class' => '',
                  'disabled' => true
              ],
              [
                  'type' => 'text',
                  'name' => 'phone',
                  'placeholder' => 'Телефон',
                  'value' => $authUser->phone,
                  'class' => ''
              ],
              [
                  'type' => 'number',
                  'name' => 'add_phone',
                  'placeholder' => 'Добавочный телефон',
                  'value' => $authUser->add_phone,
                  'class' => ''
              ],
              [
                  'type' => 'avatar',
                  'name' => 'avatar',
                  'placeholder' => 'Аватар',
                  'value' => ($authUser->avatar)?$authUser->avatar:'/assets/img/avatar5.png',
                  'class' => '',
                  'description' => 'Не более 250х250 px.'
              ],
              [
                  'type' => 'checkbox',
                  'name' => 'change_pass',
                  'placeholder' => 'Изменить пароль',
                  'value' => 0,
                  'items' => [
                      [
                          'text' => 'Изменить пароль',
                          'value' => 0
                      ],
                  ],
                  'class' => ''
              ],
              [
                  'type' => 'password',
                  'name' => 'old_passwd',
                  'placeholder' => 'Старый пароль',
                  'class' => '',
                  'hide' => true
              ],
              [
                  'type' => 'password',
                  'name' => 'passwd',
                  'placeholder' => 'Новый пароль',
                  'class' => '',
                  'hide' => true
              ],
              [
                  'type' => 'password',
                  'name' => 'confim_passwd',
                  'placeholder' => 'Подтверждение пароля',
                  'class' => '',
                  'hide' => true
              ],
          ],
          'submit' => [
              'class' => 'primary',
              'text' => 'Отправить'
          ],
          'script' => '$(document).ready(function(e) {
                          $(\'input[name="change_pass"]\').change( function() {
                              $(\'div.form-group[hidden="Y"]\').toggle();
                          });
                      });',
      ];
  ?>
  
  <?php HTML::Form( $formSet ); ?>

</form>