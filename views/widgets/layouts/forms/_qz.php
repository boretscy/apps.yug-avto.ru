<?php if ( $currentRoute->id ) $arRes = $app->Widgets->getWidgetById($currentRoute->id) ?>
<?php $widget_id = $arRes['id']; ?>
<section class="content-header">
  <h1><?=$app->Widgets->getTypeById(7)['ru_name']?> <small>Настройки</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
    <?php if ( $arRes ) include __DIR__.'/../lists/_qz_slides.php'; ?>
  
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
                                    'value' => 7,
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
                                    'name' => 'qz_url',
                                    'placeholder' => 'Страницы показа виджета ( https://site.yug-avto.ru..... )',
                                    'value' => ( $arRes['lg_url'] ) ?: [$app->Widgets->getConf()->Defaults->LGUrl],
                                    'description' => '"/" - общий виджет для всего сайта.<br /><strong>Каждая страница должна быть в отдельном поле!</strong>',
                                ],
                                [
                                    'type' => 'text',
                                    'multiple' => true,
                                    'name' => 'qz_except_url',
                                    'placeholder' => 'Страницы исключения показа виджета ( https://site.yug-avto.ru..... )',
                                    'value' => $arRes['lg_except_url'],
                                    'description' => 'на этих страницах ЭТОТ виджет показываться не будет (используется для исключения, напр., лендинга из общего виджета).<br /><strong>Каждая страница должна быть в отдельном поле!</strong>',
                                ],
                                
                            ]
                        ],
                        [
                            'title' => 'Помощник',
                            'fields' => [
                                [
                                    'type' => 'delimiter',
                                    'value' => 'Настройки помощника',
                                ],
                                [
                                    'type' => 'checkbox',
                                    'name' => 'qz_hp_use',
                                    'placeholder' => 'Показывать кнопку в помощнике',
                                    'value' => (int)$arRes['qz_hp_use'],
                                    'items' => [
                                        [
                                            'text' => 'Показывать кнопку в помощнике',
                                            'value' => (int)$arRes['qz_hp_use']
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'qz_hp_button',
                                    'placeholder' => 'Текст кнопки в помощнике',
                                    'value' => ( $arRes['qz_hp_button'] ) ?: $app->Widgets->getConf()->Defaults->HPQZButton
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
                                    'name' => 'qz_last_title',
                                    'placeholder' => 'Заголовок последнего слайда',
                                    'value' => ( $arRes['qz_last_title'] ) ?: $app->Widgets->getConf()->Defaults->QZLastTitle
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'qz_last_bigtext',
                                    'placeholder' => 'Подзаголовок последнего слайда',
                                    'value' => ( $arRes['qz_last_bigtext'] ) ?: $app->Widgets->getConf()->Defaults->QZLastBigText
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'qz_last_text',
                                    'placeholder' => 'Текст последнего слайда',
                                    'value' => ( $arRes['qz_last_text'] ) ?: $app->Widgets->getConf()->Defaults->QZLastText,
                                    'rows' => 3,
                                    'class' => 'qz_last_text'
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'qz_form_button',
                                    'placeholder' => 'Текст кнопки',
                                    'value' => ( $arRes['qz_form_button'] ) ?: $app->Widgets->getConf()->Defaults->QZFormButton
                                ],
                            ]
                        ],
                        [
                            'title' => 'Прочее',
                            'fields' => [
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