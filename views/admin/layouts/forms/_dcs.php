<?php if ( $currentRoute->id ) $arRes = $app->getDC($currentRoute->id) ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Настройки Дилерского центра <small><?=$arRes['ru_name']?></small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border">
          
          <h3 class="box-title">Дилерский центр</h3>
            
          <!-- /.box-tools -->
        </div>
         
        <div class="box-body">
          
            <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
          
            <form role="form" method="post">
                
                <input type="hidden" name="form" value="formAdminDC" />
                <?php if ( $currentRoute->id ) { ?>
                <input type="hidden" name="id" value="<?= $currentRoute->id?>" />
                <?php } ?>
                
                <?php
                    
                    $formSet = [
                        'fields' => [
                            [
                                'type' => 'select',
                                'name' => 'site_id',
                                'multiple' => false,
                                'placeholder' => 'Сайт',
                                'value' => [$arRes['site_id']],
                                'items' => $app->getSites(),
                                'class' => ''
                            ],
                            [
                                'type' => 'select',
                                'name' => 'brand_id',
                                'multiple' => false,
                                'placeholder' => 'Бренд',
                                'value' => [$arRes['brand_id']],
                                'items' => $app->YApps_GetBrands(),
                                'class' => ''
                            ],
                            [
                                'type' => 'text',
                                'name' => 'url_key',
                                'placeholder' => 'URL',
                                'value' => $arRes['url_key'],
                                'class' => ''
                            ],
                            [
                                'type' => 'text',
                                'name' => 'ru_name',
                                'placeholder' => 'Название',
                                'value' => $arRes['ru_name'],
                                'class' => ''
                            ],
                            [
                                'type' => 'text',
                                'name' => 'display_name',
                                'placeholder' => 'Название в приложениях',
                                'value' => $arRes['display_name'],
                                'class' => ''
                            ],
                            [
                                'type' => 'text',
                                'name' => 'link',
                                'placeholder' => 'Ссылка на карточку ДЦ',
                                'value' => $arRes['link'],
                                'class' => ''
                            ],
                            [
                                'type' => 'text',
                                'name' => 'phone',
                                'placeholder' => 'Телефон',
                                'value' => $arRes['phone'],
                                'class' => ''
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
                                'type' => 'number',
                                'name' => 'sort',
                                'placeholder' => 'Сортировка',
                                'value' => $arRes['sort'],
                                'class' => ''
                            ],
                            [
                                'type' => 'textarea',
                                'name' => 'address',
                                'placeholder' => 'Адрес ДЦ',
                                'value' => $arRes['address'],
                                'class' => ''
                            ],
                            [
                                'type' => 'textarea',
                                'name' => 'recipients',
                                'placeholder' => 'Получатели форм',
                                'value' => $arRes['recipients'],
                                'class' => ''
                            ],
                        ],
                        'submit' => [
                            'class' => 'primary',
                            'text' => 'Отправить'
                        ]
                    ];
                ?>
                
                <?php HTML::Form( $formSet ); ?>
                
            </form>
          
        </div>
        <!-- /.box-body -->
      </div>
      
    </div>
    
  </div>

</section>
<!-- /.content -->