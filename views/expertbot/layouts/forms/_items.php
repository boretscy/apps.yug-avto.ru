<?php if ( $currentRoute->id ) $arRes = $app->Expertbot->apiDBGetItem($currentRoute->id) ?>

<section class="content-header">
  <h1>Отзыв <small>Редактирование</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
    <div class="row">
        <div class="col-md-12">

            <div class="box box-success">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-11">
                            <div class="row">
                                <div class="col-md-2">Дата и время:</div>
                                <div class="col-md-10"><?= date('Y-m-d', $arRes['timestamp']);?></div>
                                <div class="col-md-2">ФИО:</div>
                                <div class="col-md-10"><?= $arRes['user'];?></div>
                                <div class="col-md-2">Дилерский центр:</div>
                                <div class="col-md-10"><?= $arRes['dealership'];?></div>
                                <div class="col-md-2">Направление:</div>
                                <div class="col-md-10"><?= $arRes['type'];?></div>
                                <div class="col-md-2">Подразделение:</div>
                                <div class="col-md-10"><?= $arRes['departament'];?></div>
                                <div class="col-md-2">Дата отзыва:</div>
                                <div class="col-md-10"><?= $arRes['date_feedback'];?></div>
                            </div>
                        </div>
                        <div class="col-md-1 text-right"><a href="<?= $arRes['screenshot'];?>" target="_blank"><img style="width: 50%" src="<?= $arRes['screenshot'];?>" /></a></div>
                    </div>
                </div>
            </div>

            <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
            <?php
                $formSet = [
                    'title' => '',
                    'name' => 'formExpertbotItem',
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
                                    'type' => 'text',
                                    'name' => 'checker_name',
                                    'placeholder' => 'Маркетолог',
                                    'value' => $authUser->name,
                                    'disabled' => true
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'status_id',
                                    'multiple' => false,
                                    'placeholder' => 'Статус',
                                    'value' => [$arRes['status_id']],
                                    'items' => $app->Expertbot->getStatuses(),
                                    'class' => '',
                                    'first_empty' => true,
                                    'select_field' => 'name'
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'checker_comment',
                                    'placeholder' => 'Комментарий',
                                    'value' => $arRes['checker_comment']
                                ],
                                [
                                    'type' => 'date',
                                    'name' => 'date_response',
                                    'placeholder' => 'Дата ответа на отзыв',
                                    'value' => ( $arRes['date_response'] != '0000-00-00' ) ? $arRes['date_response'] : ''
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