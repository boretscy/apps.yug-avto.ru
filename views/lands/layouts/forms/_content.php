<?php if ( $currentRoute->id ) $arRes = $app->Lands->getContent( $currentRoute->id ); ?>
<?php  $brands = $app->YApps_GetBrandsByIds( $app->YApps_GetBrandsIDsByUser($authUser) ); ?>

<div class="row">
    <div class="col-md-12">

        <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
            <?php
                $formSet = [
                    'title' => '',
                    'name' => 'formLandsContent',
                    'tabs' => [
                        [
                            'title' => 'Бренд',
                            'fields' => [
                                [
                                    'type' => 'hidden',
                                    'name' => 'land_id',
                                    'value' => $currentRoute->id,
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'brand_id',
                                    'multiple' => false,
                                    'placeholder' => 'Бренд',
                                    'value' => [$arRes['brand_id']],
                                    'items' => $brands,
                                    'first_empty' => true
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
                                    'type' => 'text',
                                    'name' => 'meta_description',
                                    'placeholder' => 'Description',
                                    'value' => $arRes['meta_description']
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
                            'title' => 'Баннер',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Баннер',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'banner_title_1',
                                    'placeholder' => 'Основной заголовок',
                                    'value' => $arRes['banner_title_1']
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'banner_title_2',
                                    'placeholder' => 'Подзаголовок 1',
                                    'value' => $arRes['banner_title_2']
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'banner_title_3',
                                    'placeholder' => 'Подзаголовок 2',
                                    'value' => $arRes['banner_title_3']
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'banner_button_text',
                                    'placeholder' => 'Текст кнопки',
                                    'value' => $arRes['banner_button_text']
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'banner_image',
                                    'placeholder' => 'Картинка (1920*500px)',
                                    'value' => $arRes['banner_image']
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
                                [
                                    'type' => 'text',
                                    'name' => 'timer_title',
                                    'placeholder' => 'Заголовок',
                                    'value' => $arRes['timer_title']
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'timer_text',
                                    'placeholder' => 'Текст',
                                    'value' => $arRes['timer_text'],
                                    'class' => 'timer_text'
                                ],
                                [
                                    'type' => 'date',
                                    'name' => 'timer_datetime',
                                    'placeholder' => 'Дата и время окончания обратного отсчета',
                                    'value' => ( $arRes['timer_datetime'] ) ? date('d.m.Y H:i', $arRes['timer_datetime']) : '',
                                    'description' => '<stromg>ИЛИ</stromg>'
                                ],
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
                            'title' => 'Предложение дня',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Предложение дня',
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'dayoffer_use',
                                    'placeholder' => 'Использовать',
                                    'value' => (int)$arRes['dayoffer_use'],
                                    'items' => [
                                        [
                                            'text' => 'Использовать',
                                            'value' => (int)$arRes['dayoffer_use']
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'dayoffer_title',
                                    'placeholder' => 'Заголовок',
                                    'value' => $arRes['dayoffer_title']
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'dayoffer_text',
                                    'placeholder' => 'Текст',
                                    'value' => $arRes['dayoffer_text'],
                                    'rows' => 5,
                                    'class' => 'dayoffer_text'
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'dayoffer_image',
                                    'placeholder' => 'Картинка',
                                    'value' => $arRes['dayoffer_image']
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'dayoffer_model',
                                    'placeholder' => 'Модель',
                                    'value' => $arRes['dayoffer_model']
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'dayoffer_complectation',
                                    'placeholder' => 'Комплектация',
                                    'value' => $arRes['dayoffer_complectation']
                                ],
                                [
                                    'type' => 'number',
                                    'name' => 'dayoffer_oldprice',
                                    'placeholder' => 'Старая цена',
                                    'value' => $arRes['dayoffer_oldprice']
                                ],
                                [
                                    'type' => 'number',
                                    'name' => 'dayoffer_newprice',
                                    'placeholder' => 'Новая цена',
                                    'value' => $arRes['dayoffer_newprice']
                                ],
                            ]
                        ],

                        [
                            'title' => 'Кредит',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Блок кредита',
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'credit_use',
                                    'placeholder' => 'Использовать',
                                    'value' => (int)$arRes['credit_use'],
                                    'items' => [
                                        [
                                            'text' => 'Использовать',
                                            'value' => (int)$arRes['credit_use']
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'credit_title',
                                    'placeholder' => 'Заголовок',
                                    'value' => $arRes['credit_title']
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'credit_text',
                                    'placeholder' => 'Текст',
                                    'value' => $arRes['credit_text'],
                                    'rows' => 5,
                                    'class' => 'credit_text'
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'credit_image',
                                    'placeholder' => 'Картинка',
                                    'value' => $arRes['credit_image']
                                ],
                            ]
                        ],
                        [
                            'title' => 'Трейд-ин',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Блок Трейд-ин',
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'tradein_use',
                                    'placeholder' => 'Использовать',
                                    'value' => (int)$arRes['tradein_use'],
                                    'items' => [
                                        [
                                            'text' => 'Использовать',
                                            'value' => (int)$arRes['tradein_use']
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'tradein_title',
                                    'placeholder' => 'Заголовок',
                                    'value' => $arRes['tradein_title']
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'tradein_text',
                                    'placeholder' => 'Текст',
                                    'value' => $arRes['tradein_text'],
                                    'rows' => 5,
                                    'class' => 'tradein_text'
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'tradein_image',
                                    'placeholder' => 'Картинка',
                                    'value' => $arRes['tradein_image']
                                ],
                            ]
                        ],
                        [
                            'title' => 'Сервис',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Блок Сервиса',
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'service_use',
                                    'placeholder' => 'Использовать',
                                    'value' => (int)$arRes['service_use'],
                                    'items' => [
                                        [
                                            'text' => 'Использовать',
                                            'value' => (int)$arRes['service_use']
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'service_title',
                                    'placeholder' => 'Заголовок',
                                    'value' => $arRes['service_title']
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'service_text',
                                    'placeholder' => 'Текст',
                                    'value' => $arRes['service_text'],
                                    'rows' => 5,
                                    'class' => 'service_text'
                                ],
                                [
                                    'type' => 'image',
                                    'name' => 'service_image',
                                    'placeholder' => 'Картинка',
                                    'value' => $arRes['service_image']
                                ],
                            ]
                        ],
                        [
                            'title' => 'Контакты',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Блок Контактов',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'contacts_title',
                                    'placeholder' => 'Заголовок',
                                    'value' => $arRes['contacts_title']
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'contacts_text',
                                    'placeholder' => 'Текст',
                                    'value' => $arRes['contacts_text'],
                                    'rows' => 5,
                                    'class' => 'contacts_text'
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'contacts_map',
                                    'placeholder' => 'Карта',
                                    'value' => $arRes['contacts_map'],
                                    'replace' => ['"', "'"]
                                ],
                            ]
                        ],
                        [
                            'title' => 'Дисклеймер',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Дисклеймер',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'disclamer_title',
                                    'placeholder' => 'Заголовок',
                                    'value' => $arRes['disclamer_title']
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'disclamer_text',
                                    'placeholder' => 'Текст',
                                    'value' => $arRes['disclamer_text'],
                                    'rows' => 15,
                                    'class' => 'disclamer_text'
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