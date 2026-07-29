<?php if ( $currentRoute->action == 'delete' ) $app->Sale->delItem( $currentRoute->id ); ?>
<?php $arRes = $app->Sale->getItemsByUser( $authUser ); ?>

<div class="row">
    <div class="col-md-12">

        <div class="box box-primary">

            <div class="box-body">
                <div class="col-xs-12">
                    <a href="/sale/items/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить автомобиль</a>
                </div>
            </div>
            
            <div class="box-body">
            
            <?php if ($POSTRes) HTML::Error($POSTRes); ?>
            
            <table id="data-table-brands" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                    <tr>
                        <th style="width: 5%">ID</th>
                        <th style="width: 20%">Бренд</th>
                        <th style="width: 25%">Название</th>
                        <th style="width: 10%">Фото</th>
                        <th style="width: 10%">Кол-во</th>
                        <th style="width: 10%">Выгода, ₽</th>
                        <th style="width: 10%"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $arRes as $item ) { ?>
                    <tr>
                        <td><a href="/sale/items/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                        <td><?=$app->YApps_GetBrand($item['brand_id'])['ru_name']?></td>
                        <td><a href="/sale/items/edit/<?=$item['id']?>/"><?=$item['en_name']?></a></td>
                        <td><img src="<?=$item['photo']?>" style="width: 30px;" /></td>
                        <td><?=$item['count']?></td>
                        <td><?=number_format((float)$item['discount'], 0, '', ' ')?></td>
                        <td style="text-align: right">
                            <a href="/sale/items/edit/<?=$item['id']?>/">
                                <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                            </a>
                            <a href="/sale/items/delete/<?=$item['id']?>/" role="delete">
                                <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                            </a>
                        </td>
                    </tr>
                    <?php } // foreach ?>
                </tbody>
            </table>
            
            </div>
      
        </div>

        <?php // Helper::sp( $app->YApps_GetBrandsBySiteId($GLOBALS['USER_SITES']['sites_ids']) ); ?>

        <?php // Helper::sp( $GLOBALS['USER_SITES']['sites_ids'] ); ?>
    
    </div>
</div>