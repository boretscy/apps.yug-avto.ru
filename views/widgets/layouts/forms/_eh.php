<?php
    switch ( $_GET['action'] ) {

        case 'default_steps':
            $app->Widgets->delEHItems($currentRoute->id);
            $app->Widgets->setEHItems(['id'=>$currentRoute->id]);
            header('Location: '.$_SERVER['REDIRECT_URL']);
            break;
        case 'delete_steps': 
            $app->Widgets->delEHItems($currentRoute->id);
            header('Location: '.$_SERVER['REDIRECT_URL']);
            break;
        case 'delete_item': 
            $app->Widgets->delEHItem($_GET['value']); 
            header('Location: '.$_SERVER['REDIRECT_URL']);
            break;
        case 'delete_action': 
            $app->Widgets->delEHItemAction($_GET['value']); 
            header('Location: '.$_SERVER['REDIRECT_URL']);
            break;

        default: break;
    }
?>
<?php if ( $currentRoute->id ) $arRes = $app->Widgets->getWidgetById($currentRoute->id) ?>
<?php if ( $_GET['action'] == 'new_step' ) $arRes['items'][] = []; ?>

<section class="content-header">
  <h1><?=$app->Widgets->getTypeById(10)['ru_name']?> <small>Настройки</small></h1>
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
                                    'value' => 10,
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
                                    'name' => 'eh_title',
                                    'placeholder' => 'Заголовок',
                                    'value' => $arRes['eh_title']
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
                            'title' => 'Социальные сети',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Социальные сети',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'eh_social_text',
                                    'placeholder' => 'Текст блока социальных сетей',
                                    'value' => ( $arRes['eh_social_text'] ) ?: $app->Widgets->getConf()->Defaults->EHSocialText
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'eh_youtube',
                                    'placeholder' => 'Youtube',
                                    'value' => ( $arRes['eh_youtube'] ) ?: $app->Widgets->getConf()->Defaults->EHSocialItems->Youtube
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'eh_instagram',
                                    'placeholder' => 'Instagram',
                                    'value' => ( $arRes['eh_instagram'] ) ?: $app->Widgets->getConf()->Defaults->EHSocialItems->Instagram
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'eh_facebook',
                                    'placeholder' => 'Facebook',
                                    'value' => ( $arRes['eh_facebook'] ) ?: $app->Widgets->getConf()->Defaults->EHSocialItems->Facebook
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'eh_vkontakte',
                                    'placeholder' => 'VK',
                                    'value' => ( $arRes['eh_vkontakte'] ) ?: $app->Widgets->getConf()->Defaults->EHSocialItems->Vkontakte
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
                            ]
                        ]
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
    
    <?php if ( $arRes['items'] ) { ?>
    <div class="row pb-3">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <a href="?action=new_step" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить шаг</a>
                    <a href="?action=delete_steps" class="btn btn-danger btn-flat"><i class="fa fa-times" aria-hidden="true"></i> Очистить шаги</a>
                </div>
            </div>
            
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">

            <?php
                $formSet = [
                    'title' => '',
                    'name' => 'formWidgetEHItems',

                    'submit' => [
                        'class' => 'primary',
                        'text' => 'Отправить'
                    ],
                ];
            ?>

            <?php
                $i = 1;
                foreach ( $arRes['items'] as $item ) {
                    
                    $new_tab = [];
                    $new_tab['title'] = ( $item['type']['ru_name'] ) ? 'Шаг №'.($i).': '.$item['type']['ru_name'] : ($i).': Новый шаг';
                    $new_tab['fields'][] = [
                        'type' => 'hidden',
                        'name' => 'ITEMS['.$i.'][id]',
                        'value' => $item['id'],
                    ];
                    $new_tab['fields'][] = [
                        'type' => 'delimiter',
                        'value' => 'Шаг №'.($i),
                    ];
                    $new_tab['fields'][] = [
                        'type' => 'delete',
                        'value' => $item['id'],
                        'text' => '<i class="fa fa-times" aria-hidden="true"></i> Удалить этот шаг',
                        'get_action' => 'delete_item'
                    ];
                    $new_tab['fields'][] = [
                        'type' => 'select',
                        'name' => 'ITEMS['.$i.'][type]',
                        'multiple' => false,
                        'placeholder' => 'Тип шага',
                        'value' => [$item['type_id']],
                        'items' => $app->Widgets->getEHItemTypes(),
                        'dinamic' => [
                            'name' => 'item'.$i,
                            'parent' => true
                        ]
                    ];
                    $new_tab['fields'][] = [
                        'type' => 'textarea',
                        'name' => 'ITEMS['.$i.'][text]',
                        'placeholder' => 'Текст',
                        'value' => $item['text'],
                        'description' => htmlentities('<br />').' - перенос строки<br />{{RANDOM}} - <strong>для шага "Вовлечение"!</strong> Случайное число между минимальным для "автомобиля" и максимальным для "списка моделей"',
                    ];

                    $new_tab['fields'][] = [
                        'type' => 'delimiter',
                        'value' => '',
                        'dinamic' => [
                            'name' => 'item'.$i,
                            'value' => $item['type_id'],
                            'show_if' => [3]
                        ]
                    ];
                    $new_tab['fields'][] = [
                        'type' => 'select',
                        'name' => 'ITEMS['.$i.'][value]',
                        'multiple' => false,
                        'placeholder' => 'Форма',
                        'value' => [$item['value']],
                        'items' => [
                            [
                                'id' => 'BaseFormPhone',
                                'ru_name' => 'Форма: Телефон'
                            ],
                            [
                                'id' => 'BaseFormNamePhone',
                                'ru_name' => 'Форма: Имя + Телефон'
                            ],
                        ],
                        'first_empty' => true,
                        'dinamic' => [
                            'name' => 'item'.$i,
                            'value' => $item['type_id'],
                            'show_if' => [3]
                        ]
                    ];
                    $new_tab['fields'][] = [
                        'type' => 'delimiter',
                        'value' => '',
                    ];
                    $new_tab['fields'][] = [
                        'type' => 'checkbox',
                        'name' => 'ITEMS['.$i.'][inited_status]',
                        'placeholder' => 'Показывать при старте',
                        'value' => (int)$item['inited_status'],
                        'items' => [
                            [
                                'text' => 'Показывать при старте',
                                'value' => (int)$item['inited_status']
                            ],
                        ],
                    ];
                    $new_tab['fields'][] = [
                        'type' => 'text',
                        'name' => 'ITEMS['.$i.'][inited_delay]',
                        'placeholder' => 'Задержка показа при старте, с',
                        'value' => $item['inited_delay']
                    ];
                    $new_tab['fields'][] = [
                        'type' => 'delimiter',
                        'value' => '',
                    ];
                    $new_tab['fields'][] = [
                        'type' => 'checkbox',
                        'name' => 'ITEMS['.$i.'][cookie_status]',
                        'placeholder' => 'Ограничивать кол-во показов',
                        'value' => (int)$item['cookie_status'],
                        'items' => [
                            [
                                'text' => 'Ограничивать кол-во показов',
                                'value' => (int)$item['cookie_status']
                            ],
                        ],
                    ];

                    $new_tab['fields'][] = [
                        'type' => 'number',
                        'name' => 'ITEMS['.$i.'][cookie_count]',
                        'placeholder' => 'Кол-во показов за сессию',
                        'value' => $item['cookie_count']
                    ];
                    $new_tab['fields'][] = [
                        'type' => 'delimiter',
                        'value' => '',
                    ];
                    $new_tab['fields'][] = [
                        'type' => 'checkbox',
                        'name' => 'ITEMS['.$i.'][blank]',
                        'placeholder' => 'Отрывать в новом пространстве помощника',
                        'value' => (int)$item['blank'],
                        'items' => [
                            [
                                'text' => 'Отрывать в новом пространстве помощника',
                                'value' => (int)$item['blank']
                            ],
                        ],
                    ];
                    $new_tab['fields'][] = [
                        'type' => 'delimiter',
                        'value' => '',
                    ];


                    $new_tab['fields'][] = [
                        'type' => 'group',
                        'name' => 'ITEMS['.$i.'][actions]',
                        'value' => $item,
                        'has_values' => 'actions',
                        'title' => 'Кнопки',
                        'title_one' => 'Кнопка',
                        'count' => 10,
                        'fields' => [
                            [
                                'type' => 'hidden',
                                'name' => 'id',
                                'values' => 'actions',
                                'names' => 'id',
                            ],
                            [
                                'type' => 'delete',
                                'name' => 'id',
                                'get_action' => 'delete_action',
                                'text' => '<i class="fa fa-times" aria-hidden="true"></i> Удалить',
                                'values' => 'actions',
                                'names' => 'id',
                            ],
                            [
                                'type' => 'select',
                                'name' => 'type_id',
                                'multiple' => false,
                                'placeholder' => 'Действие кнопки',
                                'values' => 'actions',
                                'names' => 'type_id',
                                'items' => $app->Widgets->getEHItemActionTypes(),
                                'first_empty' => true,
                                'dinamic' => [
                                    'name' => 'ITEM_'.$i.'_action',
                                    'parent' => true
                                ]
                            ],
                            [
                                'type' => 'text',
                                'name' => 'text',
                                'placeholder' => 'Текст кнопки',
                                'values' => 'actions',
                                'names' => 'text',
                            ],
                            [
                                'type' => 'select',
                                'name' => 'widget_type',
                                'multiple' => false,
                                'placeholder' => 'Виджет',
                                'values' => 'actions',
                                'names' => 'value',
                                'items' => [
                                    [
                                        'id' => 'CB',
                                        'ru_name' => 'Обратный звонок'
                                    ],
                                    [
                                        'id' => 'LG',
                                        'ru_name' => 'Генератор клиентов'
                                    ],
                                    [
                                        'id' => 'MS',
                                        'ru_name' => 'Мессенджеры'
                                    ],
                                    [
                                        'id' => 'NV',
                                        'ru_name' => 'Построение маршрута'
                                    ]
                                ],
                                'first_empty' => true,
                                'dinamic' => [
                                    'name' => 'ITEM_'.$i.'_action',
                                    'values_name' => 'type_id',
                                    'show_if' => [1] 
                                ]
                            ],
                            [
                                'type' => 'text',
                                'name' => 'link',
                                'placeholder' => 'Ссылка',
                                'values' => 'actions',
                                'names' => 'value',
                                'dinamic' => [
                                    'name' => 'ITEM_'.$i.'_action',
                                    'values_name' => 'type_id',
                                    'show_if' => [2]
                                ]
                            ],
                            [
                                'type' => 'select',
                                'name' => 'step',
                                'multiple' => false,
                                'placeholder' => 'Шаг',
                                'values' => 'actions',
                                'names' => 'value',
                                'items' => generateArray( $arRes['items'] ),
                                'first_empty' => true,
                                'dinamic' => [
                                    'name' => 'ITEM_'.$i.'_action',
                                    'values_name' => 'type_id',
                                    'show_if' => [3]
                                ]
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'blank',
                                'names' => 'blank',
                                'values' => 'actions',
                                'placeholder' => 'Отрывать в новом окне внутри помощника',
                                'items' => [
                                    [
                                        'text' => 'Отрывать в новом окне внутри помощника',
                                        'name' => 'blank',
                                        'values' => 'actions',
                                        'names' => 'blank',
                                    ],
                                ],
                                'dinamic' => [
                                    'name' => 'ITEM_'.$i.'_action',
                                    'values_name' => 'type_id',
                                    'show_if' => [3]
                                ]
                            ]
                        ],

                        'dinamic' => [
                            'name' => 'item'.$i,
                            'value' => $item['type_id'],
                            'show_if' => [2,5]
                        ]
                    ];

                    $formSet['tabs'][] = $new_tab;
                    $i++;
                }
            ?>

            <?php HTML::TabsForm( $formSet, $arRes['id'] ); ?>

            <?php // Helper::sp($arRes); ?>

        </div>
    </div>
    <?php } ?>
  
</section>

<?php 
function generateArray( $arr ) {

    $i = 1;
    foreach ( $arr as $item ) {
        $res[] = [
            'id' => $item['eh_key'],
            'ru_name' => ($i).': '.$item['type']['ru_name']
        ];
        $i++;
    }

    return $res;
}
?>