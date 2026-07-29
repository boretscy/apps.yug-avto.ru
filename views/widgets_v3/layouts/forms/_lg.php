<?php if ( $currentRoute->id ) $arRes = $app->Widgets3->getWidgetById($currentRoute->id) ?>

<section class="content-header">
  <h1><?=$app->Widgets3->getTypeById(2)['ru_name']?> <small>Настройки</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    <div class="col-md-12">

    <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
    <?php
        $formSet = [
            'title' => '',
            'name' => 'formWidget3',
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
                            'value' => 2,
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
                            'multiple' => true,
                            'name' => 'lg_url',
                            'placeholder' => 'Страницы показа виджета ( https://site.yug-avto.ru..... )',
                            'value' => ( $arRes['url'] ) ?: [$app->Widgets3->getConf()->Defaults['LG']['url']],
                            'description' => '"/" - общий виджет для всего сайта.<br /><strong>Каждая страница должна быть в отдельном поле!</strong>',
                        ],
                    ]
                ],
                [
                    'title' => 'Содержимое',
                    'fields' => [
                        [
                            'type' => 'delimiter',
                            'value' => 'Содержимое',
                        ],
                        [
                            'type' => 'text',
                            'name' => 'lg_title',
                            'placeholder' => 'Заголовок',
                            'value' => $arRes['lg_title']
                        ],
                        [
                            'type' => 'text',
                            'name' => 'lg_subtitle',
                            'placeholder' => 'Подзаголовок',
                            'value' => $arRes['lg_subtitle']
                        ],
                        [
                            'type' => 'text',
                            'name' => 'lg_text',
                            'placeholder' => 'Текст виджета',
                            'value' => ( $arRes['lg_text'] ) ?: $app->Widgets3->getConf()->Defaults['LG']['text'],
                            'description' => htmlentities('<br />').' - перенос строки',
                        ],
                        [
                            'type' => 'text',
                            'name' => 'lg_button_text',
                            'placeholder' => 'Текст кнопки',
                            'value' => ( $arRes['lg_button_text'] ) ?: $app->Widgets3->getConf()->Defaults['LG']['button'],
                        ],
                        [
                            'type' => 'text',
                            'name' => 'lg_marking',
                            'placeholder' => 'Рекламная маркировка',
                            'value' => ( $arRes['lg_marking'] ) ?: $app->Widgets3->getConf()->Defaults['LG']['marking'],
                        ],
                        [
                            'type' => 'text',
                            'name' => 'term_personal',
                            'placeholder' => 'Ссылка на политику обработки персональных данных (если не заполнено, используется ссылка из общих настроек)',
                            'value' => $arRes['term_personal']
                        ],
                        // [
                        //     'type' => 'text',
                        //     'name' => 'term_politic',
                        //     'placeholder' => 'Ссылка на политику обработки персональных данных (если не заполнено, используется ссылка из общих настроек)',
                        //     'value' => $arRes['term_politic'],
                        // ],
                    ]
                ],
                [
                    'title' => 'Обратный отсчет',
                    'fields' => [
                        [
                            'type' => 'delimiter',
                            'value' => 'Таймер обратного отсчета',
                        ],
                        [
                            'type' => 'checkbox',
                            'name' => 'lg_timer_use',
                            'placeholder' => 'Использование таймера',
                            'value' => (int)$arRes['lg_timer_use'],
                            'items' => [
                                [
                                    'text' => 'Использование таймера',
                                    'value' => (int)$arRes['lg_timer_use']
                                ],
                            ],
                        ],
                        [
                            'type' => 'date',
                            'name' => 'lg_timer',
                            'placeholder' => 'Дата и время окончания обратного отсчета',
                            'value' => ( $arRes['lg_timer'] ) ? date('d.m.Y H:i', $arRes['lg_timer']) : ''
                        ],
                    ]
                ],
                [
                    'title' => 'Изображения',
                    'fields' => [
                        [
                            'type' => 'delimiter',
                            'value' => 'Изображения виджета',
                        ],
                        [
                            'type' => 'image',
                            'name' => 'lg_image_back',
                            'placeholder' => 'Изображение на заднем фоне (.svg 370*390)',
                            'value' => ( $arRes['lg_image_back'] ) ?: $app->Widgets3->getConf()->Defaults['LG']['image_back'],
                        ],
                        [
                            'type' => 'image',
                            'name' => 'lg_image_front',
                            'placeholder' => 'Изображение на переднем фоне (.png 370*250)',
                            'value' => ( $arRes['lg_image_front'] ) ?: $app->Widgets3->getConf()->Defaults['LG']['image_front'],
                        ],
                    ]
                ],
                [
                    'title' => 'Получатели',
                    'fields' => [
                        [
                            'type' => 'delimiter',
                            'value' => 'Получатели',
                        ],
                        [
                            'type' => 'textarea',
                            'name' => 'recipients',
                            'placeholder' => 'Получатели',
                            'value' => implode(", ", $arRes['recipients']),
                            'rows' => 3,
                            'description' => 'Необходимо указывать ВСЕХ получателей, включая коллцентр'
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