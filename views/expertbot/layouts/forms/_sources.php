<?php if ( $currentRoute->id ) $arRes = $app->Expertbot->getSource($currentRoute->id) ?>

<section class="content-header">
  <h1>Источник <small>Редактирование</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
    <div class="row">
        <div class="col-md-12">


            <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
            <?php
                $formSet = [
                    'title' => '',
                    'name' => 'formExpertbotSource',
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
                                    'name' => 'name',
                                    'placeholder' => 'Наименование',
                                    'value' => $arRes['name']
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