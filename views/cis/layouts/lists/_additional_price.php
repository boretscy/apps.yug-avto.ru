<?php if ( $currentRoute->action == 'deactivate' ) $app->Cis->yappsActivateModel( $currentRoute->id, $_GET['dealeship'], false ); ?>
<?php if ( $currentRoute->action == 'activate' ) $app->Cis->yappsActivateModel( $currentRoute->id, $_GET['dealeship'], true ); ?>
<?php if ( $currentRoute->action == 'deactivateBrand' ) $app->Cis->yappsActivateBrand( $currentRoute->id, $_GET['dealeship'], false ); ?>
<?php if ( $currentRoute->action == 'activateBrand' ) $app->Cis->yappsActivateBrand( $currentRoute->id, $_GET['dealeship'], true ); ?>
<?php $arRes = $app->Cis->yappsGetBrandsModels(); ?>
<script>let rrr = <?= json_encode($arRes);?></script>
<?php foreach( $arRes as $res ) { ?>
<div class="box box-primary">
    
    <?php foreach ( $res['dealerships'] as $dc ) { ?>
    <div class="box-header with-border">
        <h3 class="box-title">
            <?= $dc['name'];?>
        </h3>
    </div>
    <div class="box-body">
        <div class="col-xs-12">
            <a href="/cis/additional_price/activateBrand/<?=$res['id']?>/?dealeship=<?= $dc['id'];?>" class="btn btn-success btn-flat" role="delete"><i class="fa fa-power-off" aria-hidden="true"></i> Включить все</a>
            <a href="/cis/additional_price/deactivateBrand/<?=$res['id']?>/?dealeship=<?= $dc['id'];?>" class="btn btn-danger btn-flat" role="delete"><i class="fa fa-power-off" aria-hidden="true"></i> Выключить все</a>
        </div>
        <table id="data-table-cis-additional_price" class="table table-hover table-striped table-condensed dataTable">
            <thead>
                <tr>
                    <th style="width: 90%">Название</th>
                    <th style="width: 10%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $res['models'] as $item ) { ?>
                <tr>
                    <td><?= $item['name']?></td>
                    <td class="text-right">
                        <a href="/cis/additional_price/<?=(($app->Cis->isActiveModel($item['id'],$dc['id']))?'de':'')?>activate/<?=$item['id']?>/?dealeship=<?= $dc['id'];?>">
                            <span class="label label-<?=(($app->Cis->isActiveModel($item['id'],$dc['id']))?'success':'warning')?> hint--top" aria-label="<?=(($app->Cis->isActiveModel($item['id'],$dc['id']))?'А':'Неа')?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>
                        </a>
                    </td>
                </tr>
                
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>

</div>
<?php } ?>