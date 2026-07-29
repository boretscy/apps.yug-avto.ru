<?php if ( $currentRoute->id ) $arRes = $app->Widgets->getWidgetById($currentRoute->id) ?>

<section class="content-header">
  <h1><?=$app->Widgets->getTypeById(1)['ru_name']?> <small>Настройки</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
    <div class="row">
        <div class="col-md-12">

            <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
            <?php
                $formSet = [
                    'title' => '',
                    'name' => 'formWidget',
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
                                    'value' => 1,
                                ],
                                [
                                    'type' => 'hidden',
                                    'name' => 'public_key',
                                    'value' => $arRes['public_key'],
                                ],
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Основное',
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'site_id',
                                    'multiple' => false,
                                    'placeholder' => 'Привязка к сайту',
                                    'value' => [$arRes['site_id']],
                                    'items' => $userSites['sites'],
                                    'class' => '',
                                    'first_empty' => true
                                ],
                                
                                [
                                    'type' => 'text',
                                    'name' => 'ru_name',
                                    'placeholder' => 'Название',
                                    'value' => $arRes['ru_name']
                                ],
                                
                                [
                                    'type' => 'text',
                                    'name' => 'cb_title_prologue',
                                    'placeholder' => 'Первая строка заголовка',
                                    'value' => ( $arRes['cb_title_prologue'] ) ?: $app->Widgets->getConf()->Defaults->CBTitlePrologue
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'cb_text',
                                    'placeholder' => 'Текст виджета',
                                    'value' => ( $arRes['cb_text'] ) ?: $app->Widgets->getConf()->Defaults->CBText,
                                    'description' => htmlentities('<br />').' - перенос строки',
                                ],
                            ]
                        ],
                        [
                            'title' => 'Немедленный звонок',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Настройки немедленного звонка',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'cb_description_now',
                                    'placeholder' => 'Текст переключающей ссылки',
                                    'value' => ( $arRes['cb_description_now'] ) ?: $app->Widgets->getConf()->Defaults->CBDescriptionNow,
                                ],
                            ]
                        ],
                        [
                            'title' => 'Отложенный звонок',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Настройки отложенного звонка',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'cb_title_span_proroque',
                                    'placeholder' => 'Вторая строка заголовка',
                                    'value' => ( $arRes['cb_title_span_proroque'] ) ?: $app->Widgets->getConf()->Defaults->CBTitleSpanProroque,
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'cb_description_later',
                                    'placeholder' => 'Текст переключающей ссылки',
                                    'value' => ( $arRes['cb_description_later'] ) ?: $app->Widgets->getConf()->Defaults->CBDescriptionLater,
                                ],
                            ]
                        ],
                        [
                            'title' => 'Прочее',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Прочее',
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'css',
                                    'placeholder' => 'Дополнительные стили',
                                    'value' => $arRes['css'],
                                    'rows' => 10,
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'recipients',
                                    'placeholder' => 'Получатели',
                                    'value' => implode(", ", $arRes['recipients']),
                                    'rows' => 3,
                                    'description' => 'Эти получатели будут добавлены к основному списку из установок'
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'active',
                                    'placeholder' => 'Активность',
                                    'value' => (int)$arRes['active'],
                                    'items' => [
                                        [
                                            'text' => 'Активность',
                                            'value' => (int)$arRes['active']
                                        ],
                                    ],
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