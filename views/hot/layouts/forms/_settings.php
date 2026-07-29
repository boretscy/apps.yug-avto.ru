<?php if ( $currentRoute->id ) $arRes = $app->Hot->getSettingsById($currentRoute->id) ?>

<section class="content-header">
  <h1><?=$app->Hot->AppInfo()->ru_name?> <small>Установки для сайта <?=$app->getSite( $currentRoute->id )['ru_name']?></small></h1>
</section>

<!-- Main content -->
<section class="content">
  
    <div class="row">
        <div class="col-md-12">

        <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
            <?php
                $formSet = [
                    'title' => '',
                    'name' => 'formHotSettings',
                    'tabs' => [
                        [
                            'title' => 'Цветовая гамма',
                            'fields' => [
                                [
                                    'type' => 'hidden',
                                    'name' => 'id',
                                    'value' => $currentRoute->id,
                                ],
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Цветовая гамма',
                                ],
                                [
                                    'type' => 'color',
                                    'name' => 'color_dark',
                                    'placeholder' => 'Текст',
                                    'value' => ( $arRes['color_dark'] ) ?: $app->Hot->getConf()->Defaults['ColorDark'],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'color',
                                    'name' => 'color_gray',
                                    'placeholder' => 'Ссылки и подложки',
                                    'value' => ( $arRes['color_gray'] ) ?: $app->Hot->getConf()->Defaults['ColorGray'],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'color',
                                    'name' => 'color_lightgray',
                                    'placeholder' => 'Кнопки',
                                    'value' => ( $arRes['color_lightgray'] ) ?: $app->Hot->getConf()->Defaults['ColorLightgray'],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'color',
                                    'name' => 'color_light',
                                    'placeholder' => 'Светлая подложка',
                                    'value' => ( $arRes['color_light'] ) ?: $app->Hot->getConf()->Defaults['ColorLight'],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'color',
                                    'name' => 'color_error',
                                    'placeholder' => 'Цвет ошибки',
                                    'value' => ( $arRes['color_error'] ) ?: $app->Hot->getConf()->Defaults['ColorError'],
                                    'class' => ''
                                ],
                            ]
                        ],
                        [
                            'title' => 'Слайдер',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Слайдер в шапке модуля',
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'use_slider',
                                    'placeholder' => 'Включить',
                                    'value' => (int)$arRes['use_slider'],
                                    'items' => [
                                        [
                                            'text' => 'Включить',
                                            'value' => (int)$arRes['use_slider']
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'number',
                                    'name' => 'slider_count',
                                    'placeholder' => 'Кол-во одновременно показываемых автомобилей',
                                    'value' => ( $arRes['slider_count'] ) ?: $app->Hot->getConf()->Defaults['SliderCount'],
                                    'params' => [
                                        'min' => 3,
                                        'max' => 4,
                                        'step' => 1
                                    ]
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
                                    'name' => 'title',
                                    'placeholder' => 'Заголовок',
                                    'value' => ($arRes['title']) ?: $app->Hot->getConf()->Defaults['Title'],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'button_shorttext',
                                    'placeholder' => 'Текст кнопки брони в списке',
                                    'value' => ( $arRes['button_shorttext'] ) ?: $app->Hot->getConf()->Defaults['ButtonShorttext'],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'button_longtext',
                                    'placeholder' => 'Текст кнопки брони в карточке автомобиля',
                                    'value' => ( $arRes['button_longtext'] ) ?: $app->Hot->getConf()->Defaults['ButtonLongtext'],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'text',
                                    'placeholder' => 'Призыв к действию',
                                    'value' => ($arRes['text']) ?: $app->Hot->getConf()->Defaults['Text'],
                                    'description' => htmlentities('<br />').' - перенос строки',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'thanks',
                                    'placeholder' => 'Сообщение об успешной отправке формы',
                                    'value' => ($arRes['thanks']) ?: $app->Hot->getConf()->Defaults['Thanks'],
                                    'description' => htmlentities('<br />').' - перенос строки',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'error',
                                    'placeholder' => 'Сообщение об ошибке',
                                    'value' => ($arRes['error']) ?: $app->Hot->getConf()->Defaults['Error'],
                                    'description' => htmlentities('<br />').' - перенос строки',
                                ],
                            ]
                        ],
                        [
                            'title' => 'Баннеры',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Баннеры',
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'banner_list',
                                    'placeholder' => 'Баннер в списке автомобилей - 1170*90px',
                                    'value' => $arRes['banner_list'],
                                    'class' => '',
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'banner_list_m',
                                    'placeholder' => 'Баннер в списке автомобилей (мобильный) - 768*128px',
                                    'value' => $arRes['banner_list_m'],
                                    'class' => '',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'banner_list_link',
                                    'placeholder' => 'Ссылка баннера',
                                    'value' => $arRes['banner_list_link'] ,
                                    'class' => ''
                                ],
                                [
                                    'type' => 'delimiter',
                                    'value' => '',
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'banner_item1',
                                    'placeholder' => 'Баннер 1 в карточке автомобиля - 250*100px',
                                    'value' => $arRes['banner_item1'],
                                    'class' => '',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'banner_item1_link',
                                    'placeholder' => 'Ссылка баннера',
                                    'value' => $arRes['banner_item1_link'] ,
                                    'class' => ''
                                ],
                                [
                                    'type' => 'delimiter',
                                    'value' => '',
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'banner_item2',
                                    'placeholder' => 'Баннер 2 в карточке автомобиля - 250*100px',
                                    'value' => $arRes['banner_item2'],
                                    'class' => '',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'banner_item2_link',
                                    'placeholder' => 'Ссылка баннера',
                                    'value' => $arRes['banner_item2_link'] ,
                                    'class' => ''
                                ],
                                [
                                    'type' => 'delimiter',
                                    'value' => '',
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'banner_item3',
                                    'placeholder' => 'Баннер 3 в карточке автомобиля - 250*100px',
                                    'value' => $arRes['banner_item3'],
                                    'class' => '',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'banner_item3_link',
                                    'placeholder' => 'Ссылка баннера',
                                    'value' => $arRes['banner_item3_link'] ,
                                    'class' => ''
                                ],
                                [
                                    'type' => 'delimiter',
                                    'value' => '',
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'banner_item4',
                                    'placeholder' => 'Баннер 4 в карточке автомобиля - 250*100px',
                                    'value' => $arRes['banner_item4'],
                                    'class' => '',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'banner_item4_link',
                                    'placeholder' => 'Ссылка баннера',
                                    'value' => $arRes['banner_item4_link'] ,
                                    'class' => ''
                                ],
                            ]
                        ],
                        [
                            'title' => 'Персональные данные',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Персональные данные',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'terms_personal',
                                    'placeholder' => 'Ссылка на соглашение о персональных данных',
                                    'value' => $arRes['terms_personal'] ,
                                    'class' => ''
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'term_communications',
                                    'placeholder' => 'Ссылка на соглашение о рекламных коммуникациях',
                                    'value' => $arRes['term_communications'] ,
                                    'class' => ''
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'term_checked',
                                    'placeholder' => 'Предустановленная галочка',
                                    'value' => ( $arRes ) ? $arRes['term_checked'] :  $app->Hot->getConf()->Defaults->TermChecked,
                                    'items' => [
                                        [
                                            'text' => 'Предустановленная галочка',
                                            'value' => ( $arRes ) ? $arRes['term_checked'] :  $app->Hot->getConf()->Defaults->TermChecked,
                                        ],
                                    ],
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
                                    'type' => 'text',
                                    'name' => 'default_url',
                                    'placeholder' => 'Ссылка на основную страницу сервиса на сайте',
                                    'value' => $arRes['default_url'] ,
                                    'class' => '',
                                    'description' => 'Заполняется если на сайте существует выделенный раздел для горячих предложений (как на Хендэ)'
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'css',
                                    'placeholder' => 'Дополнительные стили',
                                    'value' => ($arRes) ? $arRes['css'] : '',
                                    'rows' => 10,
                                    'class' => ''
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'recipients',
                                    'placeholder' => 'Получатели',
                                    'value' => ($arRes) ? $arRes['recipients'] : '',
                                    'rows' => 3,
                                    'class' => ''
                                ],
                                [
                                  'type' => 'checkbox',
                                  'name' => 'active',
                                  'placeholder' => 'Включить',
                                  'value' => (int)$arRes['active'],
                                  'items' => [
                                      [
                                          'text' => 'Включить',
                                          'value' => (int)$arRes['active']
                                      ],
                                  ],
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