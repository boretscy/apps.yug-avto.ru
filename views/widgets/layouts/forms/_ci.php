<?php if ( $currentRoute->id ) $arRes = $app->Widgets->getWidgetById($currentRoute->id) ?>

<section class="content-header">
  <h1><?=$app->Widgets->getTypeById(9)['ru_name']?> <small>Настройки</small></h1>
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
                                    'value' => 9,
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
                        [
                            'title' => 'Список моделей',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Список моделей',
                                ],
                                [
                                    'type' => 'number',
                                    'name' => 'ci_list_random_min',
                                    'placeholder' => 'Нижняя граница случайного числа',
                                    'value' => ( $arRes['ci_list_random_min'] ) ?: $app->Widgets->getConf()->Defaults->CIRandomMin
                                ],
                                [
                                    'type' => 'number',
                                    'name' => 'ci_list_random_max',
                                    'placeholder' => 'Верхняя граница случайного числа',
                                    'value' => ( $arRes['ci_list_random_max'] ) ?: $app->Widgets->getConf()->Defaults->CIRandomMax
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'ci_list_title',
                                    'placeholder' => 'Заголовок виджета',
                                    'value' => ( $arRes['ci_list_title'] ) ?: $app->Widgets->getConf()->Defaults->CITitle,
                                    'description' => '{{RANDOM}} - случайное число в заданном диапазоне'
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'ci_list_text',
                                    'placeholder' => 'Текст виджета',
                                    'value' => ( $arRes['ci_ci_list_text'] ) ?: $app->Widgets->getConf()->Defaults->CIText,
                                    'description' => '{{RANDOM}} - случайное число в заданном диапазоне'
                                ],
                            ]
                        ],
                        [
                            'title' => 'Модель',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Модель',
                                ],
                                [
                                    'type' => 'number',
                                    'name' => 'ci_model_random_min',
                                    'placeholder' => 'Нижняя граница случайного числа',
                                    'value' => ( $arRes['ci_model_random_min'] ) ?: $app->Widgets->getConf()->Defaults->CIRandomMin
                                ],
                                [
                                    'type' => 'number',
                                    'name' => 'ci_model_random_max',
                                    'placeholder' => 'Верхняя граница случайного числа',
                                    'value' => ( $arRes['ci_model_random_max'] ) ?: $app->Widgets->getConf()->Defaults->CIRandomMax
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'ci_model_title',
                                    'placeholder' => 'Заголовок виджета',
                                    'value' => ( $arRes['ci_model_title'] ) ?: $app->Widgets->getConf()->Defaults->CITitle,
                                    'description' => '{{RANDOM}} - случайное число в заданном диапазоне'
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'ci_model_text',
                                    'placeholder' => 'Текст виджета',
                                    'value' => ( $arRes['ci_model_text'] ) ?: $app->Widgets->getConf()->Defaults->CIText,
                                    'description' => '{{RANDOM}} - случайное число в заданном диапазоне'
                                ],
                            ]
                        ],
                        [
                            'title' => 'Автомобиль',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Автомобиль',
                                ],
                                [
                                    'type' => 'number',
                                    'name' => 'ci_item_random_min',
                                    'placeholder' => 'Нижняя граница случайного числа',
                                    'value' => ( $arRes['ci_item_random_min'] ) ?: $app->Widgets->getConf()->Defaults->CIRandomMin
                                ],
                                [
                                    'type' => 'number',
                                    'name' => 'ci_item_random_max',
                                    'placeholder' => 'Верхняя граница случайного числа',
                                    'value' => ( $arRes['ci_item_random_max'] ) ?: $app->Widgets->getConf()->Defaults->CIRandomMax
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'ci_item_title',
                                    'placeholder' => 'Заголовок виджета',
                                    'value' => ( $arRes['ci_item_title'] ) ?: $app->Widgets->getConf()->Defaults->CITitle,
                                    'description' => '{{RANDOM}} - случайное число в заданном диапазоне'
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'ci_item_text',
                                    'placeholder' => 'Текст виджета',
                                    'value' => ( $arRes['ci_item_text'] ) ?: $app->Widgets->getConf()->Defaults->CIText,
                                    'description' => '{{RANDOM}} - случайное число в заданном диапазоне'
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
                                ]
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