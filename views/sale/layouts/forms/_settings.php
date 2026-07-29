<?php $arRes = $app->Sale->getSettings() ?>
  
<div class="row">
    <div class="col-md-12">
    
        <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
            <?php
                $formSet = [
                    'title' => '',
                    'name' => 'formSaleSettings',
                    'tabs' => [
                        [
                            'title' => 'Основное',
                            'fields' => [
                                [
                                    'type' => 'hidden',
                                    'name' => 'id',
                                    'value' => 1,
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'title',
                                    'placeholder' => 'Заголовок',
                                    'value' => $arRes['title']
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'description',
                                    'placeholder' => 'Основной текст',
                                    'value' => $arRes['description'],
                                    'rows' => 10,
                                    'class' => 'description'
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'disclamer',
                                    'placeholder' => 'Дисклеймер',
                                    'value' => $arRes['disclamer'],
                                    'rows' => 3
                                ],
                                [
                                    'type' => 'phone',
                                    'name' => 'phone',
                                    'placeholder' => 'Телефон',
                                    'value' => $arRes['phone']
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'email',
                                    'placeholder' => 'Email',
                                    'value' => $arRes['email']
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'maintenance',
                                    'placeholder' => 'Закрыть лендинг',
                                    'value' => (int)$arRes['maintenance'],
                                    'items' => [
                                        [
                                            'text' => 'Закрыть лендинг',
                                            'value' => (int)$arRes['maintenance']
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'number',
                                    'name' => 'maintenance_time',
                                    'placeholder' => 'Время до переадресации, с',
                                    'value' => $arRes['maintenance_time']
                                ],
                            ]
                        ],
                        [
                            'title' => 'Мета-теги',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Мета-теги',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'meta_title',
                                    'placeholder' => 'Title',
                                    'value' => $arRes['meta_title']
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'meta_description',
                                    'placeholder' => 'Description',
                                    'value' => $arRes['meta_description'],
                                    'rows' => 3,
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'meta_keywords',
                                    'placeholder' => 'Keywords',
                                    'value' => $arRes['meta_keywords']
                                ],
                            ]
                        ],
                        [
                            'title' => 'Обратный отсчет',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Обратный отсчет',
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'timer_use',
                                    'placeholder' => 'Использовать',
                                    'value' => (int)$arRes['timer_use'],
                                    'items' => [
                                        [
                                            'text' => 'Использовать',
                                            'value' => (int)$arRes['timer_use']
                                        ],
                                    ],
                                ],
                                /*
                                [
                                    'type' => 'text',
                                    'name' => 'timer_title',
                                    'placeholder' => 'Заголовок',
                                    'value' => $arRes['timer_title']
                                ],
                                [
                                    'type' => 'date',
                                    'name' => 'timer_datetime',
                                    'placeholder' => 'Дата и время окончания обратного отсчета',
                                    'value' => ( $arRes['timer_datetime'] ) ? date('d.m.Y H:i', $arRes['timer_datetime']) : '',
                                    'description' => '<stromg>ИЛИ</stromg>'
                                ],
                                */
                                [
                                    'type' => 'text',
                                    'name' => 'timer_script',
                                    'placeholder' => 'Скрипт встраиваемого таймера обратного отсчета',
                                    'value' => $arRes['timer_script'],
                                    'replace' => ['"', "'"]
                                ],
                            ]
                        ],
                        [
                            'title' => 'Баннер',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Баннер',
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'banner_3840_640',
                                    'placeholder' => '3840*640px',
                                    'value' => $arRes['banner_3840_640'],
                                    'class' => '',
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'banner_1920_640',
                                    'placeholder' => '1920*640px',
                                    'value' => $arRes['banner_1920_640'],
                                    'class' => '',
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'banner_1024_768',
                                    'placeholder' => '1024*768px',
                                    'value' => $arRes['banner_1024_768'],
                                    'class' => '',
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'banner_870_768',
                                    'placeholder' => '870*768px',
                                    'value' => $arRes['banner_870_768'],
                                    'class' => '',
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'banner_768_1024',
                                    'placeholder' => '768*1024px',
                                    'value' => $arRes['banner_768_1024'],
                                    'class' => '',
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'banner_767_575',
                                    'placeholder' => '767*575px',
                                    'value' => $arRes['banner_767_575'],
                                    'class' => '',
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'banner_730_258',
                                    'placeholder' => '730*258px',
                                    'value' => $arRes['banner_730_258'],
                                    'class' => '',
                                ],
                            ]
                        ],
                        [
                            'title' => 'Футер',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Футер',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'footer_title',
                                    'placeholder' => 'Заголовок',
                                    'value' => $arRes['footer_title']
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'footer_description',
                                    'placeholder' => 'Текст',
                                    'value' => $arRes['footer_description'],
                                    'rows' => 10,
                                    'class' => 'footer_description',
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
  