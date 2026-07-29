<?php if ( $currentRoute->id ) $arRes = $app->Sale->getItem( $currentRoute->id ) ?>
<?php 
	
	$brands = $app->YApps_GetBrandsByIds( $app->YApps_GetBrandsIDsByUser($authUser) );
	foreach ( $brands as $k => $i ) $brands[$k]['models'] = $app->YApps_GetModelsByBrand( $i['id'] );
?>
<script>
	var Brands = <?=json_encode( $brands );?>
</script>
  
<div class="row">
    <div class="col-md-12">
      
        <div class="box box-primary">
            <div class="box-body">
            
            <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>

            <?php
                    
                $formSet = [
                    
                    'name' => 'formSaleItems',
                    
                    'fields' => [
                        [
                            'type' => 'hidden',
                            'name' => 'id',
                            'value' => $currentRoute->id,
                        ],
                        
                        //////////////////////////////////////////////////////////////////////////////////////////////////////////
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
                            'name' => 'en_name',
                            'placeholder' => 'Выводимое название модели',
                            'value' => $arRes['en_name'],
                            'description' => 'Если не запоненно, будет выведено англоязычное название из карточки модели'
                        ],
                        [
                            'type' => 'number',
                            'name' => 'count',
                            'placeholder' => 'Количество автомобилей',
                            'value' => $arRes['count']
                        ],
                        [
                            'type' => 'checkbox',
                            'name' => 'is_price',
                            'placeholder' => 'Цена',
                            'value' => $arRes['is_price'],
                            'items' => [
                                [
                                    'text' => 'Цена',
                                    'value' => $arRes['is_price']
                                ],
                            ],
                            'class' => ''
                        ],
                        [
                            'type' => 'number',
                            'name' => 'discount',
                            'placeholder' => 'Максимальная выгода или минимальная цена',
                            'value' => $arRes['discount']
                        ],
                        [
                            'type' => 'text',
                            'name' => 'photo',
                            'placeholder' => 'Ссылка на фото',
                            'value' => $arRes['photo'],
                            'description' => 'Если не указано, будет выведено фото из карточки модели'
                        ],

                        
                    ],
                    'submit' => [
                        'class' => 'primary',
                        'text' => 'Отправить'
                    ],
                ];
            ?>
            
            <?php HTML::FullForm( $formSet, $arRes['id'] ); ?>
            
            </div>
            
        </div>
    
    </div>
</div>
  