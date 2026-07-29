<?php $arRes = $app->Lands->getLand( $currentRoute->id ); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?=$app->Lands->AppInfo()->ru_name?> <small>Настройки приложения</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
    <div class="row">
    
        <div class="col-md-12">

        <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
            <?php
                $formSet = [
                    'title' => '',
                    'name' => 'formLandsSettings',
                    'description' => '<a href="/lands/content/edit/'.$arRes['id'].'/" class="btn btn-info btn-flat"><i class="fa fa-edit" aria-hidden="true"></i> Редактировать контент</a><hr />Токен: '.$arRes['token'],
                    'tabs' => [
                        [
                            'title' => 'Основное',
                            'fields' => [
                                [
                                    'type' => 'hidden',
                                    'name' => 'token',
                                    'value' => $arRes['token'],
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'ru_name',
                                    'placeholder' => 'Название',
                                    'value' => $arRes['ru_name'],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'site_id',
                                    'multiple' => false,
                                    'placeholder' => 'Сайт',
                                    'value' => [$arRes['site_id']],
                                    'items' => $userSites['sites'],
                                    'class' => '',
                                    'first_empty' => true
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'dc_id[]',
                                    'multiple' => true,
                                    'placeholder' => 'Дилерский центр',
                                    'value' => $app->Lands->getLandDCsIDs($arRes['id']),
                                    'items' => $app->getUserDCs($authUser),
                                    'class' => '',
                                    'first_empty' => true
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'url',
                                    'placeholder' => 'URL',
                                    'value' => $arRes['url'],
                                    'class' => ''
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
                            ],
                        ],
                        [
                            'title' => 'Виджеты',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Настройки виджетов',
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'use_eh',
                                    'placeholder' => 'Использование Персонального помощника',
                                    'value' => $arRes['use_eh'],
                                    'items' => [
                                        [
                                            'text' => 'Использование Персонального помощника',
                                            'value' => $arRes['use_eh']
                                        ],
                                    ],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'delimiter',
                                    'value' => '',
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'use_lg',
                                    'placeholder' => 'Использование Генератора клиентов',
                                    'value' => $arRes['use_lg'],
                                    'items' => [
                                        [
                                            'text' => 'Использование Генератора клиентов',
                                            'value' => $arRes['use_lg']
                                        ],
                                    ],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'use_cb',
                                    'placeholder' => 'Использование Обратного звонка',
                                    'value' => $arRes['use_cb'],
                                    'items' => [
                                        [
                                            'text' => 'Использование Обратного звонка',
                                            'value' => $arRes['use_cb']
                                        ],
                                    ],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'use_nv',
                                    'placeholder' => 'Использование Построения маршрута',
                                    'value' => $arRes['use_nv'],
                                    'items' => [
                                        [
                                            'text' => 'Использование Построения маршрута',
                                            'value' => $arRes['use_nv']
                                        ],
                                    ],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'use_ch',
                                    'placeholder' => 'Использование Онлайн-консультанта',
                                    'value' => $arRes['use_ch'],
                                    'items' => [
                                        [
                                            'text' => 'Использование Онлайн-консультанта',
                                            'value' => $arRes['use_ch']
                                        ],
                                    ],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'use_qz',
                                    'placeholder' => 'Использование Квиза',
                                    'value' => $arRes['use_qz'],
                                    'items' => [
                                        [
                                            'text' => 'Использование Квиза',
                                            'value' => $arRes['use_qz']
                                        ],
                                    ],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'use_ms',
                                    'placeholder' => 'Использование Мессенджера',
                                    'value' => $arRes['use_ms'],
                                    'items' => [
                                        [
                                            'text' => 'Использование Мессенджера',
                                            'value' => $arRes['use_ms']
                                        ],
                                    ],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'delimiter',
                                    'value' => false,
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'use_av',
                                    'placeholder' => 'Использование АВН',
                                    'value' => $arRes['use_av'],
                                    'items' => [
                                        [
                                            'text' => 'Использование АВН',
                                            'value' => $arRes['use_av']
                                        ],
                                    ],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'use_ht',
                                    'placeholder' => 'Использование Горячих предложений',
                                    'value' => $arRes['use_ht'],
                                    'items' => [
                                        [
                                            'text' => 'Использование Горячих предложений',
                                            'value' => $arRes['use_ht']
                                        ],
                                    ],
                                    'class' => ''
                                ],
                            ],
                        ],
                        [
                            'title' => 'Аналитика',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Аналитика',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'piwik_id',
                                    'placeholder' => 'Matomo ID',
                                    'value' => $arRes['piwik_id'],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'yandex_id',
                                    'placeholder' => 'Yandex ID',
                                    'value' => $arRes['yandex_id'],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'google_id',
                                    'placeholder' => 'Google ID',
                                    'value' => $arRes['google_id'],
                                    'class' => ''
                                ],
                            ],
                        ],
                        [
                            'title' => 'CallTouch',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'CallTouch',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'calltouch_id',
                                    'placeholder' => 'CallTouch ID',
                                    'value' => $arRes['calltouch_id'],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'calltouch_node',
                                    'placeholder' => 'CallTouch NodeID',
                                    'value' => $arRes['calltouch_node'],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'calltouch_sess',
                                    'placeholder' => 'CallTouch Сессия',
                                    'value' => $arRes['calltouch_sess'],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'calltouch_class',
                                    'placeholder' => 'CallTouch Class',
                                    'value' => $arRes['calltouch_class'],
                                    'class' => ''
                                ],
                            ],
                        ],
                        [
                            'title' => 'Обработка заявок',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Обработка заявок',
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'send_email',
                                    'placeholder' => 'Отправлять на почту',
                                    'value' => (int)$arRes['send_email'],
                                    'items' => [
                                        [
                                            'text' => 'Отправлять на почту',
                                            'value' => (int)$arRes['send_email']
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'recipients',
                                    'placeholder' => 'Получатели',
                                    'value' => $arRes['recipients'],
                                    'rows' => 3,
                                ],
                            ],
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
<!-- /.content -->