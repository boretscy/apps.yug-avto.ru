<?php if ( $currentRoute->id ) $arRes = $app->Widgets->getWidgetById($currentRoute->id) ?>

<section class="content-header">
  <h1><?=$app->Widgets->getTypeById(2)['ru_name']?> <small>Настройки</small></h1>
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
                            'value' => ( $arRes['lg_url'] ) ?: [$app->Widgets->getConf()->Defaults->LGUrl],
                            'description' => '"/" - общий виджет для всего сайта.<br /><strong>Каждая страница должна быть в отдельном поле!</strong>',
                        ],
                        [
                            'type' => 'text',
                            'multiple' => true,
                            'name' => 'lg_except_url',
                            'placeholder' => 'Страницы исключения показа виджета ( https://site.yug-avto.ru..... )',
                            'value' => $arRes['lg_except_url'],
                            'description' => 'на этих страницах ЭТОТ виджет показываться не будет (используется для исключения, напр., лендинга из общего виджета).<br /><strong>Каждая страница должна быть в отдельном поле!</strong>',
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
                            'name' => 'lg_head',
                            'placeholder' => 'Тип предложения',
                            'value' => ( $arRes['lg_head'] ) ?: $app->Widgets->getConf()->Defaults->LGHead
                        ],
                        [
                            'type' => 'checkbox',
                            'name' => 'lg_hide_buttons',
                            'placeholder' => 'Скрыть кнопки АВН и Горячих предложений на картинке',
                            'value' => (int)$arRes['lg_hide_buttons'],
                            'items' => [
                                [
                                    'text' => 'Скрыть кнопки АВН и Горячих предложений на картинке',
                                    'value' => (int)$arRes['lg_hide_buttons']
                                ],
                            ],
                        ],
                        [
                            'type' => 'date',
                            'name' => 'lg_time_start',
                            'placeholder' => 'Дата начала предложения',
                            'value' => ( $arRes['lg_time_start'] ) ? date('d.m.Y', $arRes['lg_time_start']) : ''
                        ],
                        [
                            'type' => 'delimiter',
                            'value' => '',
                        ],
                        [
                            'type' => 'text',
                            'name' => 'lg_title',
                            'placeholder' => 'Заголовок',
                            'value' => $arRes['lg_title']
                        ],
                        [
                            'type' => 'checkbox',
                            'name' => 'lg_hp_use_wname',
                            'placeholder' => 'Подставлять в плашку и кнопку помощника',
                            'value' => ($arRes) ? (int)$arRes['lg_hp_use_wname'] : $app->Widgets->getConf()->Defaults->LGHPUseWName,
                            'items' => [
                                [
                                    'text' => 'Подставлять в плашку и кнопку помощника',
                                    'value' => ($arRes) ? (int)$arRes['lg_hp_use_wname'] : $app->Widgets->getConf()->Defaults->LGHPUseWName
                                ],
                            ],
                            'description' => 'Работает только если соответствующие галочки выключены в установках приложения'
                        ],
                        [
                            'type' => 'delimiter',
                            'value' => '',
                        ],
                        [
                            'type' => 'textarea',
                            'name' => 'lg_text',
                            'placeholder' => 'Текст виджета',
                            'value' => $arRes['lg_text'],
                            'rows' => 3,
                            'class' => 'lg_text',
                        ],
                        [
                            'type' => 'text',
                            'name' => 'lg_link',
                            'placeholder' => 'Ссылка',
                            'value' => $arRes['lg_link']
                        ],
                        [
                            'type' => 'text',
                            'name' => 'lg_link_text',
                            'placeholder' => 'Текст ссылки',
                            'value' => ( $arRes['lg_link_text'] ) ?: $app->Widgets->getConf()->Defaults->LGLinkText
                        ],
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
                            'name' => 'lg_timer_flag',
                            'placeholder' => 'Использование таймера',
                            'value' => (int)$arRes['lg_timer_flag'],
                            'items' => [
                                [
                                    'text' => 'Использование таймера',
                                    'value' => (int)$arRes['lg_timer_flag']
                                ],
                            ],
                        ],
                        [
                            'type' => 'date',
                            'name' => 'lg_timer',
                            'placeholder' => 'Дата и время окончания обратного отсчета',
                            'value' => ( $arRes['lg_timer'] ) ? date('d.m.Y H:i', $arRes['lg_timer']) : ''
                        ],
                        [
                            'type' => 'text',
                            'name' => 'lg_timer_description',
                            'placeholder' => 'Описание таймера',
                            'value' => ( $arRes['lg_timer_description'] ) ?: $app->Widgets->getConf()->Defaults->LGTimerDescription
                        ],
                    ]
                ],
                [
                    'title' => 'Изображение',
                    'fields' => [
                        [
                            'type' => 'delimiter',
                            'value' => 'Изображение виджета',
                        ],
                        [
                            'type' => 'image',
                            'name' => 'lg_image',
                            'placeholder' => 'Изображение (Для старых виджетов - 380х350 px, для новых - 600х600! и название должно быть без пробелов и кириллицы!)',
                            'value' => ( $arRes['lg_image'] ) ?: $app->Widgets->getConf()->Defaults->LGImage,
                        ],
                    ]
                ],
                [
                    'title' => 'Конкурентные запросы',
                    'fields' => [
                        [
                            'type' => 'delimiter',
                            'value' => 'Конкурентные запросы',
                        ],
                        [
                            'type' => 'checkbox',
                            'name' => 'lg_use_competitor',
                            'placeholder' => 'Используется для конкурентных запросов',
                            'value' => (int)$arRes['lg_use_competitor'],
                            'items' => [
                                [
                                    'text' => 'Используется для конкурентных запросов',
                                    'value' => (int)$arRes['lg_use_competitor']
                                ],
                            ],
                        ],
                        [
                            'type' => 'text',
                            'multiple' => true,
                            'name' => 'lg_competitor',
                            'placeholder' => 'Конкурентные запросы ( определяются меткой utm_competitor )',
                            'value' => $arRes['lg_competitor']
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