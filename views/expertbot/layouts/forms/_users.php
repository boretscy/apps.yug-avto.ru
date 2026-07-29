<?php if ( $currentRoute->id ) $arRes = $app->Expertbot->getDBUser($currentRoute->id) ?>

<section class="content-header">
  <h1>Менеджер <small>Редактирование</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
    <div class="row">
        <div class="col-md-12">

            <div class="box box-success">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-2">ФИО:</div>
                        <div class="col-md-10"><?= $arRes['name'];?></div>
                        <div class="col-md-2">ID портала:</div>
                        <div class="col-md-10"><?= $arRes['ext_id'];?></div>
                        <div class="col-md-2">ID чата:</div>
                        <div class="col-md-10"><?= $arRes['chat_id'];?></div>
                        <div class="col-md-2">Телефон:</div>
                        <div class="col-md-10"><?= (($arRes['phone'])?Helper::formatPhoneOut($arRes['phone']):'');?></div>
                        <div class="col-md-2">Администратор:</div>
                        <div class="col-md-10"><?= (($arRes['is_admin'])?'Да':'Нет');?></div>
                        
                    </div>
                </div>
            </div>

            <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
            <?php
                $formSet = [
                    'title' => '',
                    'name' => 'formExpertbotUser',
                    'tabs' => [
                        [
                            'title' => 'Основное',
                            'fields' => [
                                [
                                    'type' => 'hidden',
                                    'name' => 'id',
                                    'value' => $currentRoute->id,
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'dealership_id',
                                    'multiple' => false,
                                    'placeholder' => 'Дилерский центр',
                                    'value' => [$arRes['dealership_id']],
                                    'items' => $app->Expertbot->getDealerships(),
                                    'class' => '',
                                    'first_empty_not_disabled' => true,
                                    'select_field' => 'name',
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'type_id',
                                    'multiple' => false,
                                    'placeholder' => 'Направление',
                                    'value' => [$arRes['type_id']],
                                    'items' => $app->Expertbot->getTypes(),
                                    'class' => '',
                                    'first_empty_not_disabled' => true,
                                    'select_field' => 'name'
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'departament_id',
                                    'multiple' => false,
                                    'placeholder' => 'Подразделение',
                                    'value' => [$arRes['departament_id']],
                                    'items' => $app->Expertbot->getDepartaments(),
                                    'class' => '',
                                    'first_empty_not_disabled' => true,
                                    'select_field' => 'name'
                                ],
                            ]
                        ],
                    ],

                    'submit' => [
                        'class' => 'primary',
                        'text' => 'Отправить'
                    ],
                ];
            ?>
            <?php HTML::TabsForm( $formSet, $arRes['id'] ); ?>
        
        </div>
    </div>
  
</section>