<?php if ( $currentRoute->id ) $arRes = $app->getDC($currentRoute->id) ?>
<?php $urls = $app->Feeds->getURLsWithUTM($arRes); ?>

<div class="box box-primary">
            
    <div class="box-header with-border">
        <h3 class="box-title"><?= $arRes['ru_name'];?></h3>
    </div>
            
    <div class="box-body">

        <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
    
        <?php
            
            $formSet = [
                
                'name' => 'formFeedsTuning',
                
                'fields' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id',
                        'value' => $currentRoute->id,
                    ],
                    [
                        'type' => 'text',
                        'name' => 'feeds_name',
                        'placeholder' => 'Отображаемое название',
                        'value' => $arRes['feeds_name']
                    ],
                    [
                        'type' => 'text',
                        'name' => 'phone',
                        'placeholder' => 'Подменный телефон',
                        'value' => Helper::formatPhoneOut($arRes['feeds_phone']),
                    ],
                    [
                        'type' => 'text',
                        'name' => 'feeds_rubric',
                        'placeholder' => 'Идентификатор рубрики',
                        'value' => $arRes['feeds_rubric'],
                        'description' => 'Идентификатор рубрики, к которой относится данный филиал. Если рубрик несколько, они указываются через запятую.'
                    ],
                    [
                        'type' => 'textarea',
                        'name' => 'feeds_address',
                        'placeholder' => 'Адрес ДЦ',
                        'value' => $arRes['feeds_address'],
                    ],
                    [
                        'type' => 'text',
                        'name' => 'feeds_working',
                        'placeholder' => 'Расписание работы',
                        'value' => $arRes['feeds_working']
                    ],
                    [
                        'type' => 'text',
                        'name' => 'coords_lat',
                        'placeholder' => 'Координаты: Широта',
                        'value' => $arRes['coords_lat'],
                        'class' => ''
                    ],
                    [
                        'type' => 'text',
                        'name' => 'coords_lon',
                        'placeholder' => 'Координаты: Долгота',
                        'value' => $arRes['coords_lon'],
                        'class' => ''
                    ],

                    [
                        'type' => 'checkbox',
                        'name' => 'feeds_active',
                        'placeholder' => 'Активность',
                        'value' => (int)$arRes['feeds_active'],
                        'items' => [
                            [
                                'text' => 'Активность',
                                'value' => (int)$arRes['feeds_active']
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
        
        <?php HTML::FullForm( $formSet ); ?>

    </div>
</div>

<?php foreach ( $urls as $item ) { ?>
<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">UTM-метки для <?= $item['name'];?></h3>
    </div>
    <div class="box-body">
        <?php foreach ( $item['items'] as $k => $i ) { ?>
        <?= (($k==0)?'Сайт:':'Витрина:');?>
        <div class="input-group">
            <div class="input-group-addon" style="background: #eee; cursor: pointer;" role="copy">
                <i class="fa fa-copy"></i>
            </div>
            <input type="text" class="form-control" readonly value="<?= $i;?>" />
        </div>
        <?php } ?>
    </div>
</div>
<?php } ?>