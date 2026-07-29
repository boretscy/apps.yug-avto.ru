<?php if ( $currentRoute->action == 'delete' ) $app->Cis->yappsDelSeoFilter( $currentRoute->id ); ?>
<?php $arRes = $app->Cis->yappsGetSeoFilters(); ?>

<div class="box box-primary">
            
    <div class="box-header with-border">
        <h3 class="box-title">Сео настройки фильтра</h3>
    </div>
        
    <div class="box-body">
        <div class="col-xs-12">
            <a href="/cis/seo-filters/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
        </div>
    </div>
            
    <div class="box-body">

        <table id="data-table-cis-seo-filters" class="table table-hover table-striped table-condensed dataTable">
            <thead>
                <tr>
                    <th style="width: 10%">ID</th>
                    <th style="width: 20%">Сайт</th>
                    <th style="width: 10%">Тип</th>
                    <th style="width: 50%">Фильтр</th>
                    <th style="width: 10%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $arRes as $item ) { ?>
                <tr>
                    <td><a href="/cis/seo-filters/edit/<?=$item['id']?>/"><?= $item['id'];?></a></td>
                    <td><a href="/cis/seo-filters/edit/<?=$item['id']?>/"><?= $item['site']?></a></td>
                    <td><a href="/cis/seo-filters/edit/<?=$item['id']?>/"><?= $item['entity'];?></a></td>
                    <td>
                        <?= (($item['brand'])?'brand: '.$item['brand'].'<br />':'');?>
                        <?= (($item['model'])?'model: '.$item['model'].'<br />':'');?>
                        <?= (($item['price'])?'price: '.$item['price'].'<br />':'');?>
                        <?= (($item['transmission'])?'transmission: '.$item['transmission'].'<br />':'');?>
                        <?= (($item['engine'])?'engine: '.$item['engine'].'<br />':'');?>
                        <?= (($item['drive'])?'drive: '.$item['drive'].'<br />':'');?>
                        <?= (($item['body'])?'body: '.$item['body'].'<br />':'');?>
                        <?= (($item['color'])?'color: '.$item['color'].'<br />':'');?>
                        <?= (($item['dealership'])?'dealership: '.$item['dealership'].'<br />':'');?>
                        <?= (($item['volume'])?'volume: '.$item['volume'].'<br />':'');?>
                        <?= (($item['power'])?'power: '.$item['power'].'<br />':'');?>
                    </td>
                    <td class="text-right">
                        <a href="/cis/seo-filters/edit/<?=$item['id']?>/">
                            <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                        </a>&nbsp;&nbsp;
                        <a href="/cis/seo-filters/delete/<?=$item['id']?>/" role="delete">
                            <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
              
    </div>

</div>