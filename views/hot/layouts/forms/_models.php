<?php if ( $currentRoute->id ) $arRes = $app->Hot->getModel($currentRoute->id) ?>
<?php 
	
	$brands = $app->YApps_GetBrands();
	foreach ( $brands as $k => $i ) $brands[$k]['models'] = $app->YApps_GetModelsByBrand( $i['id'] );
?>
<script>
	var Brands = <?=json_encode( $brands );?>
</script>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Настройки Модели <small><?=$arRes['ru_name']?></small></h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        
        <div class="col-md-12">
        
            <div class="box box-primary">
                
                <div class="box-header with-border">
                
                <h3 class="box-title">Модель</h3>
                    
                <!-- /.box-tools -->
                </div>
                
                <div class="box-body">
                
                    <?php // Helper::sp( $brands ); ?>
                    
                    <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
                    
                    <form role="form" method="post" enctype="multipart/form-data">
                        
                        <input type="hidden" name="form" value="formHotModel" />
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
                                        'placeholder' => 'Привязка к сайту',
                                        'value' => [$arRes['site_id']],
                                        'items' => $userSites['sites'],
                                        'class' => '',
                                        'first_empty' => true
                                    ],
                                    [
                                        'type' => 'select',
                                        'name' => 'brand_id',
                                        'multiple' => false,
                                        'placeholder' => 'Бренд',
                                        'value' => [$arRes['brand_id']],
                                        'items' => $brands,
                                        'params' => [
                                            'multisteps' => 'Y',
                                            'step' => '1',
                                            'target' => 'model_id'
                                        ],
                                        'first_empty' => true
                                    ],
                                    [
                                        'type' => 'select',
                                        'name' => 'model_id',
                                        'multiple' => false,
                                        'placeholder' => 'Модель',
                                        'value' => [$arRes['model_id']],
                                        'items' => $brands[$arRes['brand_id']]['models'],
                                        'params' => [
                                            'multisteps' => 'Y',
                                            'step' => '2'
                                        ],
                                        'first_empty' => true
                                    ],
                                    [
                                        'type' => 'text',
                                        'name' => 'sort',
                                        'placeholder' => 'Сортировка',
                                        'value' => ( $arRes['sort'] ) ?: 500,
                                    ],
                                    [
                                    'type' => 'image',
                                    'name' => 'image',
                                    'placeholder' => 'Изображение модели - 250*120px',
                                    'value' => $arRes['image'],
                                    'description' => 'Если картинка не загружена, подгрузится автоматичести с сайта холдинга',
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