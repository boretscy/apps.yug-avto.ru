<?php if ( $currentRoute->id ) $arRes = $app->Widgets->getWidgetById($currentRoute->id) ?>

<section class="content-header">
  <h1><?=$app->Widgets->getTypeById(8)['ru_name']?> <small>Настройки</small></h1>
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
                                    'value' => 8,
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
                                    'name' => 'ms_title',
                                    'placeholder' => 'Заголовок',
                                    'value' => ( $arRes['ms_title'] ) ?: $app->Widgets->getConf()->Defaults->MSTitle
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'ms_text',
                                    'placeholder' => 'Текст виджета',
                                    'value' => ( $arRes['ms_text'] ) ?: $app->Widgets->getConf()->Defaults->MSText,
                                    'description' => htmlentities('<br />').' - перенос строки',
                                ],
                                [
                                    'type' => 'number',
                                    'name' => 'ms_idle_timeout',
                                    'placeholder' => 'Таймаут бездействия пользователя, сек',
                                    'value' => ( $arRes['ms_idle_timeout'] ) ?: $app->Widgets->getConf()->Defaults->MSIdleTimeout,
                                    'description' => 'Через какое время бездействия пользователя при открытой форме "онлайн-оплаты" будет показан виджет',
                                ],
                            ]
                        ],
                        [
                            'title' => 'Мессенджеры',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Мессенджеры',
                                ],
                                [
                                    'type' => 'phone',
                                    'name' => 'ms_whatsapp',
                                    'placeholder' => 'Номер WhatsApp',
                                    'value' => $app->Widgets->getValueByWidgetAndType(1, $arRes['id']),
                                ],
                                [
                                    'type' => 'phone',
                                    'name' => 'ms_viber',
                                    'placeholder' => 'Номер Viber',
                                    'value' => $app->Widgets->getValueByWidgetAndType(5, $arRes['id']),
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'ms_telegram',
                                    'placeholder' => 'Имя пользователя Telegram',
                                    'value' => $app->Widgets->getValueByWidgetAndType(2, $arRes['id']),
                                    'description' => 'Обратите внимание: не НОМЕР, не ИМЯ, а <strong>ИМЯ ПОЛЬЗОВАТЕЛЯ</strong>',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'ms_skype',
                                    'placeholder' => 'Логин в Skype',
                                    'value' => $app->Widgets->getValueByWidgetAndType(4, $arRes['id']),
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