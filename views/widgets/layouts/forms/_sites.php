<?php if ( $currentRoute->id ) $arRes = $app->Widgets->getSettingsById($currentRoute->id) ?>

<section class="content-header">
    <h1> 
        <?=$app->Widgets->AppInfo()->ru_name?> 
        <small>Установки для сайта <?= $app->getSite( $currentRoute->id )['ru_name'];?></small> 
        <?=(($app->Widgets->getConf()->Version)?'<span class="pull-right"><small>V'.$app->Widgets->getConf()->Version.'</small></span>':'')?>
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
                'name' => 'formWidgetsSites',
                'tabs' => [
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
                                'value' => 'Цветовая гамма',
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_bg',
                                'placeholder' => 'Цвет бэкграунда',
                                'value' => ( $arRes['color_bg'] ) ?: $app->Widgets->getConf()->Defaults->ColorBg,
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_darkbg',
                                'placeholder' => 'Цвет темного бэкграунда',
                                'value' => ( $arRes['color_darkbg'] ) ?: $app->Widgets->getConf()->Defaults->ColorDarkBg,
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_fill',
                                'placeholder' => 'Цвет иконок, заливок, ссылок',
                                'value' => ( $arRes['color_fill'] ) ?: $app->Widgets->getConf()->Defaults->ColorFill,
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_text',
                                'placeholder' => 'Цвет текста',
                                'value' => ( $arRes['color_text'] ) ?: $app->Widgets->getConf()->Defaults->ColorText,
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_button',
                                'placeholder' => 'Цвет кнопки',
                                'value' => ( $arRes['color_button'] ) ?: $app->Widgets->getConf()->Defaults->ColorButton,
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_button_text',
                                'placeholder' => 'Цвет текста кнопки',
                                'value' => ( $arRes['color_button_text'] ) ?: $app->Widgets->getConf()->Defaults->ColorButtonText,
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_error',
                                'placeholder' => 'Цвет ошибки',
                                'value' => ( $arRes['color_error'] ) ?: $app->Widgets->getConf()->Defaults->ColorError,
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_lightgray',
                                'placeholder' => 'Светло-серый',
                                'value' => ( $arRes['color_lightgray'] ) ?: $app->Widgets->getConf()->Defaults->ColorLightgray,
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_middlegray',
                                'placeholder' => 'Умеренно-серый',
                                'value' => ( $arRes['color_middlegray'] ) ?: $app->Widgets->getConf()->Defaults->ColorMiddletgray,
                                'class' => ''
                            ],
                            [
                                'type' => 'color',
                                'name' => 'color_darkgray',
                                'placeholder' => 'Темно-серый',
                                'value' => ( $arRes['color_darkgray'] ) ?: $app->Widgets->getConf()->Defaults->ColorDarkgray,
                                'class' => ''
                            ],
                        ]
                    ],
                    [
                        'title' => 'Настройки Помощника',
                        'fields' => [
                            [
                                'type' => 'delimiter',
                                'value' => 'Настройки Персонального помощника (<strong>для новых виджетов</strong>)',
                            ],
                            [
                                'type' => 'number',
                                'name' => 'hp_active_interval',
                                'placeholder' => 'Интервал активации кнопок',
                                'value' => ( $arRes['hp_active_interval'] ) ?: $app->Widgets->getConf()->Defaults->Vue->Helper->ActiveInterval,
                            ],
                            [
                                'type' => 'number',
                                'name' => 'hp_eh_content_delay',
                                'placeholder' => 'Задержка иконки имитации активности',
                                'value' => ( $arRes['hp_eh_content_delay'] ) ?: $app->Widgets->getConf()->Defaults->Vue->Helper->EHContentDelay,
                            ],
                            [
                                'type' => 'text',
                                'name' => 'hp_eh_button',
                                'placeholder' => 'Подпись кнопки помощника',
                                'value' => ( $arRes['hp_eh_button'] ) ?: $app->Widgets->getConf()->Defaults->Vue->Helper->EHText,
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'hp_eh_use_startstop',
                                'placeholder' => 'Использовать иконку СТАРТ/СТОП',
                                'value' => (int)$arRes['hp_eh_use_startstop'],
                                'items' => [
                                    [
                                        'text' => 'Использовать иконку СТАРТ/СТОП',
                                        'value' => (int)$arRes['hp_eh_use_startstop']
                                    ],
                                ],
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => '',
                            ],
                            [
                                'type' => 'text',
                                'name' => 'hp_av_button',
                                'placeholder' => 'Подпись кнопки АВН',
                                'value' => ( $arRes['hp_av_button'] ) ?: $app->Widgets->getConf()->Defaults->Vue->Helper->AVText,
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => '',
                            ],
                            [
                                'type' => 'text',
                                'name' => 'hp_nv_button',
                                'placeholder' => 'Подпись кнопки Маршрута',
                                'value' => ( $arRes['hp_nv_button'] ) ?: $app->Widgets->getConf()->Defaults->Vue->Helper->NVText,
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => '',
                            ],
                            [
                                'type' => 'text',
                                'name' => 'hp_cb_out_button',
                                'placeholder' => 'Подпись кнопки Звонка на мобильных',
                                'value' => ( $arRes['hp_cb_out_button'] ) ?: $app->Widgets->getConf()->Defaults->Vue->Helper->CallOutText,
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => 'Настройки помощника',
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'hp_start_open',
                                'placeholder' => 'Открыть кнопки виджетов и скрыть стартовую СТАРТ/СТОП',
                                'value' => (int)$arRes['hp_start_open'],
                                'items' => [
                                    [
                                        'text' => 'Открыть кнопки виджетов и скрыть стартовую СТАРТ/СТОП',
                                        'value' => (int)$arRes['hp_start_open']
                                    ],
                                ],
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'hp_bind_widgets',
                                'placeholder' => 'Отображать виджеты привязанными к кнопкам помощника',
                                'value' => (int)$arRes['hp_bind_widgets'],
                                'items' => [
                                    [
                                        'text' => 'Отображать виджеты привязанными к кнопкам помощника',
                                        'value' => (int)$arRes['hp_bind_widgets']
                                    ],
                                ],
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => '',
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'hp_use_hot',
                                'placeholder' => 'Показ количества "Горячих предложений"',
                                'value' => (int)$arRes['hp_use_hot'],
                                'items' => [
                                    [
                                        'text' => 'Показ количества "Горячих предложений"',
                                        'value' => (int)$arRes['hp_use_hot']
                                    ],
                                ],
                                'description' => 'Автоматическое определение бренда'
                            ],
                            [
                                'type' => 'text',
                                'name' => 'link_hot',
                                'placeholder' => 'Ссылка на витрину "Горячие предложения"',
                                'value' => ($arRes['link_hot'] ),
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'hp_use_avail',
                                'placeholder' => 'Показ кнопки "АВН"',
                                'value' => (int)$arRes['hp_use_avail'],
                                'items' => [
                                    [
                                        'text' => 'Показ кнопки "АВН"',
                                        'value' => (int)$arRes['hp_use_avail']
                                    ],
                                ],
                                'description' => 'Автоматическое определение бренда'
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'hp_use_avail_count',
                                'placeholder' => 'Показ количества "АВН"',
                                'value' => ($arRes) ? (int)$arRes['hp_use_avail_count'] : 1,
                                'items' => [
                                    [
                                        'text' => 'Показ количества "АВН"',
                                        'value' => ($arRes) ? (int)$arRes['hp_use_avail_count'] : 1
                                    ],
                                ],
                                'description' => 'Автоматическое определение бренда'
                            ],
                            [
                                'type' => 'text',
                                'name' => 'link_avail',
                                'placeholder' => 'Ссылка на витрину АВН',
                                'value' => $arRes['link_avail'],
                            ],
                            [
                                'type' => 'number',
                                'name' => 'hp_show_interval',
                                'placeholder' => 'Таймаут показа содержимого помощника, минут',
                                'value' => ( $arRes['hp_show_interval'] ) ?: $app->Widgets->getConf()->Defaults->HPShowTimeout,
                            ],
                            [
                                'type' => 'number',
                                'name' => 'hp_close_timeout',
                                'placeholder' => 'Таймаут скрытия содержимого помощника, секунд',
                                'value' => ( $arRes['hp_close_timeout'] ) ?: $app->Widgets->getConf()->Defaults->HPCloseTimeout,
                            ],
                            [
                                'type' => 'number',
                                'name' => 'hp_icons_interval',
                                'placeholder' => 'Интервал смены иконок на главной кнопке, секунд',
                                'value' => ( $arRes['hp_icons_interval'] ) ?: $app->Widgets->getConf()->Defaults->HPIconsInterval,
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => '',
                            ],
                            [
                                'type' => 'text',
                                'name' => 'hp_lg_plate',
                                'placeholder' => 'Текст плашки "Генератора клиентов"',
                                'value' => ( $arRes['hp_lg_plate'] ) ?: $app->Widgets->getConf()->Defaults->HPLGPlateText,
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'hp_lg_plate_use_wname',
                                'placeholder' => 'Использовать заголовок виджета',
                                'value' => ($arRes) ? (int)$arRes['hp_lg_plate_use_wname'] : $app->Widgets->getConf()->Defaults->HPLGPlateUseWName,
                                'items' => [
                                    [
                                        'text' => 'Использовать название виджета',
                                        'value' => ($arRes) ? (int)$arRes['hp_lg_plate_use_wname'] : $app->Widgets->getConf()->Defaults->HPLGPlateUseWName
                                    ],
                                ],
                                'description' => 'Подставлять заголовок виджета вместо текста выше'
                            ],
                            [
                                'type' => 'select',
                                'name' => 'hp_lg_plate_position_id',
                                'multiple' => false,
                                'placeholder' => 'Положение плашки',
                                'value' => [(($arRes['hp_lg_plate_position_id'])?:$app->Widgets->getConf()->Defaults->HPLGPlatePositionID)],
                                'items' => $app->Widgets->getPositions()
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'hp_lg_plate_draggable',
                                'placeholder' => 'Разрешить пользователю двигать плашку "Генератора клиентов"',
                                'value' => ($arRes) ? (int)$arRes['hp_lg_plate_draggable'] : $app->Widgets->getConf()->Defaults->HPLGPlateDraggable,
                                'items' => [
                                    [
                                        'text' => 'Разрешить пользователю двигать плашку "Генератора клиентов"',
                                        'value' => ($arRes) ? (int)$arRes['hp_lg_plate_draggable'] : $app->Widgets->getConf()->Defaults->HPLGPlateDraggable
                                    ],
                                ]
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => '',
                            ],
                            [
                                'type' => 'text',
                                'name' => 'hp_lg_button',
                                'placeholder' => 'Текст кнопки "Генератора клиентов"',
                                'value' => ( $arRes['hp_lg_button'] ) ?: $app->Widgets->getConf()->Defaults->HPLGButtonText,
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'hp_lg_button_use_wname',
                                'placeholder' => 'Использовать заголовок виджета',
                                'value' => ($arRes) ? (int)$arRes['hp_lg_button_use_wname'] : $app->Widgets->getConf()->Defaults->HPLGButtonUseWName,
                                'items' => [
                                    [
                                        'text' => 'Использовать название виджета',
                                        'value' => ($arRes) ? (int)$arRes['hp_lg_button_use_wname'] : $app->Widgets->getConf()->Defaults->HPLGButtonUseWName
                                    ],
                                ],
                                'description' => 'Подставлять заголовок виджета вместо текста выше'
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => '',
                            ],
                            [
                                'type' => 'text',
                                'name' => 'hp_ch_button',
                                'placeholder' => 'Текст кнопки "Онлайн-консультанта"',
                                'value' => ( $arRes['hp_ch_button'] ) ?: $app->Widgets->getConf()->Defaults->HPCHButton,
                            ],
                            [
                                'type' => 'text',
                                'name' => 'hp_fb_button',
                                'placeholder' => 'Текст кнопки "Отзывы наших клиентов"',
                                'value' => ( $arRes['hp_fb_button'] ) ?: $app->Widgets->getConf()->Defaults->HPFBButton,
                            ],
                            [
                                'type' => 'text',
                                'name' => 'hp_cb_button',
                                'placeholder' => 'Текст кнопки "Обратного звонка"',
                                'value' => ( $arRes['hp_cb_button'] ) ?: $app->Widgets->getConf()->Defaults->HPCBButton,
                                'description' => 'Этот текст показывается в нерабочее время, когда нет возможности заказать немедленный звонок. В рабочее время - "Перезвоним за ... секунд"',
                            ],
                            [
                                'type' => 'text',
                                'name' => 'hp_nv_button',
                                'placeholder' => 'Текст кнопки "Маршрута в ДЦ"',
                                'value' => ( $arRes['hp_nv_button'] ) ?: $app->Widgets->getConf()->Defaults->HPNVButton,
                            ],
                            [
                                'type' => 'text',
                                'name' => 'hp_qz_button',
                                'placeholder' => 'Текст кнопки "Квиз"',
                                'value' => ( $arRes['hp_qz_button'] ) ?: $app->Widgets->getConf()->Defaults->HPQZButton,
                            ],
                        ]
                    ],
                    [
                        'title' => 'Обратный звонок',
                        'fields' => [
                            [
                                'type' => 'delimiter',
                                'value' => 'Настройки виджета "Обратный звонок"(<strong>для новых виджетов</strong>)',
                            ],
                            [
                                'type' => 'number',
                                'name' => 'cb_timer_await',
                                'placeholder' => 'Обратный отсчет ожидания звонка, секунд',
                                'value' => ( $arRes['cb_timer_await'] ) ?: $app->Widgets->getConf()->Defaults->CBTimerAwait,
                            ],
                            [
                                'type' => 'number',
                                'name' => 'cb_timer_timeout',
                                'placeholder' => 'Таймаут после окончания таймера, секунд',
                                'value' => ( $arRes['cb_timer_timeout'] ) ?: $app->Widgets->getConf()->Defaults->CBTimerTimeout,
                                'description' => 'После окончания таймера и указанного времени - скрытие таймера и показ формы',
                            ],
                            [
                                'type' => 'time',
                                'name' => 'cb_time_start',
                                'placeholder' => 'Время начала работы виджета',
                                'value' => ( $arRes['cb_time_start'] ) ?: $app->Widgets->getConf()->Defaults->CBTimeStart,
                            ],
                            [
                                'type' => 'time',
                                'name' => 'cb_time_end',
                                'placeholder' => 'Время окончания работы виджета',
                                'value' => ( $arRes['cb_time_end'] ) ?: $app->Widgets->getConf()->Defaults->CBTimeEnd,
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => 'Настройки виджета "Обратный звонок"',
                            ],
                            [
                                'type' => 'number',
                                'name' => 'cb_idle_timeout',
                                'placeholder' => 'Отслеживаение бездействия пользователя, минут',
                                'value' => ( $arRes['cb_idle_timeout'] ) ?: $app->Widgets->getConf()->Defaults->CBIdleTimeout,
                                'description' => 'Через указанное время бездействия пользователя - показ виджета',
                            ],
                            [
                                'type' => 'number',
                                'name' => 'cb_await_days',
                                'placeholder' => 'Максимальный отложенный звонок, рабочих дней',
                                'value' => ( $arRes['cb_await_days'] ) ?: $app->Widgets->getConf()->Defaults->CBAwaitDays,
                            ],
                            [
                                'type' => 'text',
                                'name' => 'cb_form_button_now',
                                'placeholder' => 'Текст кнопки заказа немедленного звонка',
                                'value' => ( $arRes['cb_form_button_now'] ) ?: $app->Widgets->getConf()->Defaults->CBFormButtomNow,
                            ],
                            [
                                'type' => 'text',
                                'name' => 'cb_form_button_later',
                                'placeholder' => 'Текст кнопки заказа отложенного звонка',
                                'value' => ( $arRes['cb_form_button_later'] ) ?: $app->Widgets->getConf()->Defaults->CBFormButtomLater,
                            ],
                        ]
                    ],
                    [
                        'title' => 'Генератор клиентов',
                        'fields' => [
                            [
                                'type' => 'delimiter',
                                'value' => 'Настройки виджета "Генератор клиентов"(<strong>для новых виджетов</strong>)',
                            ],
                            [
                                'type' => 'number',
                                'name' => 'lg_show_timeout',
                                'placeholder' => 'Таймаут 1-го показа, секунд',
                                'value' => ( $arRes['lg_show_timeout'] ) ?: $app->Widgets->getConf()->Defaults->LGShowTimeout,
                                'description' => 'Через указанное время - показ виджета',
                            ],
                            [
                                'type' => 'number',
                                'name' => 'lg_second_timeout',
                                'placeholder' => 'Таймаут 2-го показа, минут',
                                'value' => ( $arRes['lg_second_timeout'] ) ?: $app->Widgets->getConf()->Defaults->LGShowSecond,
                                'description' => 'Через указанное время - показ виджета',
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => 'Настройки виджета "Генератор клиентов"',
                            ],
                            [
                                'type' => 'number',
                                'name' => 'lg_show_count',
                                'placeholder' => 'Количество 1-х показов за сессию, раз',
                                'value' => ( $arRes['lg_show_count'] ) ?: $app->Widgets->getConf()->Defaults->LGShowCount,
                                'description' => 'Сессия - до закрытия браузера',
                            ],
                            [
                                'type' => 'number',
                                'name' => 'lg_second_count',
                                'placeholder' => 'Количество 2-х показов за сессию, раз',
                                'value' => ( $arRes['lg_second_count'] ) ?: $app->Widgets->getConf()->Defaults->LGShowCount2,
                                'description' => 'Сессия - до закрытия браузера',
                            ],
                            [
                                'type' => 'text',
                                'name' => 'lg_form_button',
                                'placeholder' => 'Текст кнопки',
                                'value' => ( $arRes['lg_form_button'] ) ?: $app->Widgets->getConf()->Defaults->LGFormButton,
                            ],
                        ]
                    ],
                    [
                        'title' => 'Онлайн-консультант',
                        'fields' => [
                            [
                                'type' => 'delimiter',
                                'value' => 'Настроки онлайн-консультанта',
                            ],
                            [
                                'type' => 'number',
                                'name' => 'ch_timeout',
                                'placeholder' => 'Таймаут до показа чата с консультантом, секунд',
                                'value' => ( $arRes['ch_timeout'] ) ?: $app->Widgets->getConf()->Defaults->CHTimeout
                            ]
                        ]
                    ],
                    [
                        'title' => 'Построения маршрута',
                        'fields' => [
                            [
                                'type' => 'delimiter',
                                'value' => 'Настроки виджета "Построения маршрута"',
                            ],
                            [
                                'type' => 'text',
                                'name' => 'sn_text',
                                'placeholder' => 'Текст выбора приложения навигации',
                                'value' => ( $arRes['sn_text'] ) ?: $app->Widgets->getConf()->Defaults->NVSelectNaviText
                            ],
                            [
                                'type' => 'select',
                                'name' => 'navi_ids[]',
                                'multiple' => true,
                                'placeholder' => 'Используемые приложения навигации',
                                'value' => $app->Widgets->getNavigatorIDsBySite($arRes['site_id']),
                                'items' => $app->Widgets->getNavigators(),
                                'rows' => 5,
                                'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
                            ],
                        ]
                    ],
                    [
                        'title' => 'Вовлечение',
                        'fields' => [
                            [
                                'type' => 'delimiter',
                                'value' => 'Настроки виджета "Вовлечение"',
                            ],
                            [
                                'type' => 'number',
                                'name' => 'ci_timeout_1',
                                'placeholder' => 'Таймаут до первого показа виджета, секунд',
                                'value' => ( $arRes['ci_timeout_1'] ) ?: $app->Widgets->getConf()->Defaults->CIFTimeout
                            ],
                            [
                                'type' => 'number',
                                'name' => 'ci_timeout_2',
                                'placeholder' => 'Таймаут до второго показа виджета, минут',
                                'value' => ( $arRes['ci_timeout'] ) ?: $app->Widgets->getConf()->Defaults->CISTimeout
                            ],
                            [
                                'type' => 'text',
                                'name' => 'ci_form_button',
                                'placeholder' => 'Текст кнопки',
                                'value' => ( $arRes['ci_form_button'] ) ?: $app->Widgets->getConf()->Defaults->CIFormButton,
                            ],
                            [
                                'type' => 'number',
                                'name' => 'ci_level_list',
                                'placeholder' => 'Уровень вложенности для списка моделей',
                                'value' => ( $arRes['ci_level_list'] ) ?: $app->Widgets->getConf()->Defaults->CILevelList
                            ],
                            [
                                'type' => 'number',
                                'name' => 'ci_level_model',
                                'placeholder' => 'Уровень вложенности для списка автомобиля',
                                'value' => ( $arRes['ci_level_model'] ) ?: $app->Widgets->getConf()->Defaults->CILevelModel
                            ],
                            [
                                'type' => 'number',
                                'name' => 'ci_level_item',
                                'placeholder' => 'Уровень вложенности для автомобиля',
                                'value' => ( $arRes['ci_level_item'] ) ?: $app->Widgets->getConf()->Defaults->CILevelItem
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
                                'name' => 'term_personal',
                                'placeholder' => 'Ссылка на соглашение о персональных данных',
                                'value' => $arRes['term_personal']
                            ],
                            [
                                'type' => 'text',
                                'name' => 'term_communications',
                                'placeholder' => 'Ссылка на соглашение о рекламных коммуникациях',
                                'value' => $arRes['term_communications'],
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'term_checked',
                                'placeholder' => 'Предустановленная галочка',
                                'value' => ( $arRes ) ? $arRes['term_checked'] :  $app->Widgets->getConf()->Defaults->TermChecked,
                                'items' => [
                                    [
                                        'text' => 'Предустановленная галочка',
                                        'value' => ( $arRes ) ? $arRes['term_checked'] :  $app->Widgets->getConf()->Defaults->TermChecked,
                                    ],
                                ],
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
                    [
                        'title' => 'Прочее',
                        'fields' => [
                            [
                                'type' => 'delimiter',
                                'value' => '<strong>Для новых виджетов</strong>',
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'use_new',
                                'placeholder' => 'Не подгружать старые виджеты',
                                'value' => (int)$arRes['use_new'],
                                'items' => [
                                    [
                                        'text' => 'Не подгружать старые виджеты',
                                        'value' => (int)$arRes['use_new']
                                    ],
                                ],
                            ],
                            [
                                'type' => 'delimiter',
                                'value' => 'Прочее',
                            ],
                            [
                                'type' => 'number',
                                'name' => 'init_timeout',
                                'placeholder' => 'Таймаут инициализации виджетов, МИЛИсекунд',
                                'value' => ( $arRes['init_timeout'] ) ?: $app->Widgets->getConf()->Defaults->InitTimeout,
                            ],
                            [
                                'type' => 'number',
                                'name' => 'result_timeout',
                                'placeholder' => 'Время показа результата отправки формы, секунд',
                                'value' => ( $arRes['result_timeout'] ) ?: $app->Widgets->getConf()->Defaults->ResultTimeout,
                            ],
                            [
                                'type' => 'text',
                                'name' => 'form_success',
                                'placeholder' => 'Сообщение об успешной отправке формы',
                                'value' => ( $arRes['form_success'] ) ?: $app->Widgets->getConf()->Defaults->FormSuccess,
                                'description' => htmlentities('<br />').' - перенос строки',
                            ],
                            [
                                'type' => 'text',
                                'name' => 'form_error',
                                'placeholder' => 'Сообщение об ошибке',
                                'value' => ( $arRes['form_error'] ) ?: $app->Widgets->getConf()->Defaults->FormError,
                                'description' => htmlentities('<br />').' - перенос строки',
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
                                'value' => (implode(", ", $arRes['recipients']))?:$app->Widgets->getConf()->Defaults->Recipients,
                                'rows' => 3,
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