<?php if ( $currentRoute->id ) $arRes = $app->Widgets3->getSettingsById($currentRoute->id) ?>

<section class="content-header">
    <h1> 
        <?=$app->Widgets3->AppInfo()->ru_name?> 
        <small>Установки для сайта <?= $app->getSite( $currentRoute->id )['ru_name'];?></small> 
        <?=(($app->Widgets3->getConf()->Version)?'<span class="pull-right"><small>V'.$app->Widgets3->getConf()->Version.'</small></span>':'')?>
    </h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    <div class="col-md-12">

        <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>

        <?php
            $formSet = [
                'title' => '',
                'name' => 'formWidgets3Sites',
                'tabs' => [
                    [
                        'title' => 'Основное',
                        'fields' => [
                            [
                                'type' => 'delimiter',
                                'value' => 'Основные настройки',
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
                                'type' => 'checkbox',
                                'name' => 'use_libs',
                                'placeholder' => 'Подгрузка библиотек',
                                'value' => (int)$arRes['use_libs'],
                                'items' => [
                                    [
                                        'text' => 'Подгрузка библиотек',
                                        'value' => (int)$arRes['use_libs']
                                    ],
                                ],
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => 'Обратный звонок',
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'use_cb',
                                'placeholder' => 'Использовать Обратный звонок',
                                'value' => (int)$arRes['use_cb'],
                                'items' => [
                                    [
                                        'text' => 'Использовать Обратный звонок',
                                        'value' => (int)$arRes['use_cb']
                                    ],
                                ],
                            ],
                            [
                                'type' => 'text',
                                'name' => 'cb_clue',
                                'placeholder' => 'Всплывающая подсказка',
                                'value' => ( $arRes['cb_clue'] ) ?: $app->Widgets3->getConf()->Defaults['Buttons']['CBClue'],
                            ],
                            [
                                'type' => 'number',
                                'name' => 'cb_timeout',
                                'placeholder' => 'Время до показа, сек',
                                'value' => ( $arRes['cb_timeout'] ) ?: $app->Widgets3->getConf()->Defaults['CB']['timeout'],
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => 'Генератор клиентов',
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'use_lg',
                                'placeholder' => 'Использовать Генератор клиентов',
                                'value' => (int)$arRes['use_lg'],
                                'items' => [
                                    [
                                        'text' => 'Использовать Генератор клиентов',
                                        'value' => (int)$arRes['use_lg']
                                    ],
                                ],
                            ],
                            [
                                'type' => 'text',
                                'name' => 'lg_clue',
                                'placeholder' => 'Всплывающая подсказка',
                                'value' => ( $arRes['lg_clue'] ) ?: $app->Widgets3->getConf()->Defaults['Buttons']['LGClue'],
                            ],
                            [
                                'type' => 'number',
                                'name' => 'lg_timeout_1',
                                'placeholder' => 'Время до первого показа, сек',
                                'value' => ( $arRes['lg_timeout_1'] ) ?: $app->Widgets3->getConf()->Defaults['LG']['timeout_1'],
                            ],
                            [
                                'type' => 'number',
                                'name' => 'lg_timeout_2',
                                'placeholder' => 'Время до второго показа, мин',
                                'value' => ( $arRes['lg_timeout_2'] ) ?: $app->Widgets3->getConf()->Defaults['LG']['timeout_2'],
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => 'Построение маршрута',
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'use_nv',
                                'placeholder' => 'Использовать Построение маршрута',
                                'value' => (int)$arRes['use_nv'],
                                'items' => [
                                    [
                                        'text' => 'Использовать Построение маршрута',
                                        'value' => (int)$arRes['use_nv']
                                    ],
                                ],
                            ],
                            [
                                'type' => 'text',
                                'name' => 'nv_clue',
                                'placeholder' => 'Всплывающая подсказка',
                                'value' => ( $arRes['nv_clue'] ) ?: $app->Widgets3->getConf()->Defaults['Buttons']['NVClue'],
                            ],
                            [
                                'type' => 'text',
                                'name' => 'nv_coords_lat',
                                'placeholder' => 'Координаты: широта',
                                'value' => $arRes['nv_coords_lat']
                            ],
                            [
                                'type' => 'text',
                                'name' => 'nv_coords_lon',
                                'placeholder' => 'Координаты: долгота',
                                'value' => $arRes['nv_coords_lon']
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => 'Автомобили в наличии',
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'use_cis',
                                'placeholder' => 'Использовать АНВ',
                                'value' => (int)$arRes['use_cis'],
                                'items' => [
                                    [
                                        'text' => 'Использовать АВН',
                                        'value' => (int)$arRes['use_cis']
                                    ],
                                ],
                            ],
                            [
                                'type' => 'text',
                                'name' => 'cis_clue',
                                'placeholder' => 'Всплывающая подсказка',
                                'value' => ( $arRes['cis_clue'] ) ?: $app->Widgets3->getConf()->Defaults['Buttons']['CISClue'],
                            ],
                            [
                                'type' => 'text',
                                'name' => 'cis_link',
                                'placeholder' => 'Ссылка на витрину автомобилей в наличии',
                                'value' => $arRes['cis_link']
                            ],
                        ]
                    ],
                    [
                        'title' => 'Цветовая гамма',
                        'fields' => [
                            [
                                'type' => 'hidden',
                                'name' => 'site_id',
                                'value' => $currentRoute->id,
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => 'Иконка',
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_icon_dark',
                                'placeholder' => 'Темный цвет',
                                'value' => ( $arRes['color_icon_dark'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['IconDark'],
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_icon_light',
                                'placeholder' => 'Светлый цвет',
                                'value' => ( $arRes['color_icon_light'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['IconLight'],
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_icon_button',
                                'placeholder' => 'Цвет подложки',
                                'value' => ( $arRes['color_icon_button'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['IconButton'],
                                'class' => ''
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => 'Иконка при наведении мышкой',
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_icon_hover_dark',
                                'placeholder' => 'Темный цвет',
                                'value' => ( $arRes['color_icon_hover_dark'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['IconHoverDark'],
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_icon_hover_light',
                                'placeholder' => 'Светлый цвет',
                                'value' => ( $arRes['color_icon_hover_light'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['IconHoverLight'],
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_icon_hover_shadow',
                                'placeholder' => 'Цвет тени иконки',
                                'value' => ( $arRes['color_icon_hover_shadow'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['IconHoverShadow'],
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_icon_hover_button',
                                'placeholder' => 'Цвет подложки',
                                'value' => ( $arRes['color_icon_hover_button'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['IconHoverButton'],
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_icon_hover_button_shadow',
                                'placeholder' => 'Цвет тени подложки',
                                'value' => ( $arRes['color_icon_hover_button_shadow'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['IconHoverButtonShadow'],
                                'class' => ''
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => 'Форма',
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_widget_field_border',
                                'placeholder' => 'Цвет рамки поля',
                                'value' => ( $arRes['color_widget_field_border'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['WidgetFieldBorder'],
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_widget_field_bg',
                                'placeholder' => 'Цвет поля',
                                'value' => ( $arRes['color_widget_field_bg'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['WidgetFieldBg'],
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_widget_button',
                                'placeholder' => 'Цвет кнопки',
                                'value' => ( $arRes['color_widget_button'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['WidgetButton'],
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_widget_button_text',
                                'placeholder' => 'Цвет текста кнопки',
                                'value' => ( $arRes['color_widget_button_text'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['WidgetButtonText'],
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_widget_button_hover',
                                'placeholder' => 'Цвет кнопки при наведении мышкой',
                                'value' => ( $arRes['color_widget_button_hover'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['WidgetButtonHover'],
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_widget_button_hover_text',
                                'placeholder' => 'Цвет текста кнопки при наведении мышкой',
                                'value' => ( $arRes['color_widget_button_hover_text'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['WidgetButtonHoverText'],
                                'class' => ''
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => 'Прочее',
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_widget_bg',
                                'placeholder' => 'Цвет подложки виджета',
                                'value' => ( $arRes['color_widget_bg'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['WidgetBg'],
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_widget_text',
                                'placeholder' => 'Цвет текста виджета',
                                'value' => ( $arRes['color_widget_text'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['WidgetText'],
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_widget_terms',
                                'placeholder' => 'Цвет текста согласий',
                                'value' => ( $arRes['color_widget_terms'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['WidgetTerms'],
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_widget_timer_bg',
                                'placeholder' => 'Цвет подложки таймера',
                                'value' => ( $arRes['color_widget_timer_bg'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['WidgetTimerBg'],
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_widget_timer_text',
                                'placeholder' => 'Цвет текста таймера',
                                'value' => ( $arRes['color_widget_timer_text'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['WidgetTimerText'],
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_widget_error',
                                'placeholder' => 'Цвет ошибки',
                                'value' => ( $arRes['color_widget_error'] ) ?: $app->Widgets3->getConf()->Defaults['Colors']['WidgetError'],
                                'class' => ''
                            ],
                        ]
                    ],
                    [
                        'title' => 'Форма',
                        'fields' => [
                            [
                                'type' => 'delimiter',
                                'value' => 'Форма',
                            ],
                            [
                                'type' => 'number',
                                'name' => 'form_timeout',
                                'placeholder' => 'Время показа результата отправки формы, секунд',
                                'value' => ( $arRes['form_timeout'] ) ?: $app->Widgets3->getConf()->Defaults['Form']['Timeout'],
                            ],
                            [
                                'type' => 'text',
                                'name' => 'form_success',
                                'placeholder' => 'Сообщение об успешной отправке формы',
                                'value' => ( $arRes['form_success'] ) ?: $app->Widgets3->getConf()->Defaults['Form']['Success'],
                                'description' => htmlentities('<br />').' - перенос строки',
                            ],
                            [
                                'type' => 'text',
                                'name' => 'form_error',
                                'placeholder' => 'Сообщение об ошибке',
                                'value' => ( $arRes['form_error'] ) ?: $app->Widgets3->getConf()->Defaults['Form']['Error'],
                                'description' => htmlentities('<br />').' - перенос строки',
                            ],
                            [
                                'type' => 'text',
                                'name' => 'term_personal',
                                'placeholder' => 'Ссылка на политику обработки персональных данных',
                                'value' => $arRes['term_personal']
                            ],
                            // [
                            //     'type' => 'text',
                            //     'name' => 'term_communications',
                            //     'placeholder' => 'Ссылка на политику обработки персональных данных',
                            //     'value' => $arRes['term_communications'],
                            // ],
                            [
                                'type' => 'checkbox',
                                'name' => 'term_checked',
                                'placeholder' => 'Предустановленная галочка',
                                'value' => ( $arRes ) ? $arRes['term_checked'] :  $app->Widgets3->getConf()->Defaults->TermChecked,
                                'items' => [
                                    [
                                        'text' => 'Предустановленная галочка',
                                        'value' => ( $arRes ) ? $arRes['term_checked'] :  $app->Widgets3->getConf()->Defaults->TermChecked,
                                    ],
                                ],
                            ],
                        ]
                    ],
                    [
                        'title' => 'Положение кнопок',
                        'fields' => [
                            [
                                'type' => 'delimiter',
                                'value' => 'Положение кнопок',
                                'button' => [
                                    'text' => 'Добавить интервал',
                                    'role' => 'add_datetimerange'
                                ]
                            ],
                            [
                                'type' => 'text',
                                'name' => 'margin_right',
                                'placeholder' => 'Отступ справа',
                                'value' => ( $arRes['margin_right'] ) ?: $app->Widgets3->getConf()->Defaults['Margins']['right'],
                                'description' => '60px - 60 пикселей',
                            ],
                            [
                                'type' => 'text',
                                'name' => 'margin_bottom',
                                'placeholder' => 'Отступ снизу',
                                'value' => ( $arRes['margin_bottom'] ) ?: $app->Widgets3->getConf()->Defaults['Margins']['bottom'],
                                'description' => '90px - 90 пикселей',
                            ],
                        ]
                    ],
                    [
                        'title' => 'Отключение',
                        'fields' => [
                            [
                                'type' => 'delimiter',
                                'value' => 'Отключение в праздничные дни',
                                'button' => [
                                    'text' => 'Добавить интервал',
                                    'role' => 'add_datetimerange'
                                ]
                            ],
                            [
                                'type' => 'datetimerange',
                                'multiple' => true,
                                'name' => 'shutdown',
                                'placeholder' => 'Интервал отключения',
                                'value' => $arRes['shutdown'],
                                'count' => 5
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