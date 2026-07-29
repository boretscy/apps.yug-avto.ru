<?php if ( $currentRoute->id ) $arRes = $app->Expertbot->getMessage($currentRoute->id) ?>

<section class="content-header">
  <h1>Сообщение в группе <strong><?= $app->Expertbot->getMessageType( (($_GET['type_id'])?:6) )['ru_name'];?></strong><small>Редактирование</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
    <div class="row">
        <div class="col-md-12">


            <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
            <?php
                $formSet = [
                    'title' => '',
                    'name' => 'formExpertbotMessage',
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
                                    'type' => 'hidden',
                                    'name' => 'type_id',
                                    'value' => ( $_GET['type_id'] ) ?: 6
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'text',
                                    'placeholder' => 'Текст',
                                    'value' => $arRes['text']
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