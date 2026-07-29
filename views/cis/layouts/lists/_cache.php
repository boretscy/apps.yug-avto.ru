<?php if ( $_GET['delete'] ) $app->Cis->delQCahce( $_GET['delete'] ); ?>
<?php if ( $_GET['clear'] ) $app->Cis->clearQCaches(); ?>
<?php $arRes = $app->Cis->getQCaches(); ?>

<div class="box box-primary">
            
    <div class="box-header with-border">
        <h3 class="box-title">Кеш - <?= count($arRes);?> <?= Helper::getWorld(count($arRes), 'files');?></h3>
    </div>
    <div class="box-body">
        <div class="col-xs-12">
            <a href="/cis/cache/?clear=Y" class="btn btn-danger btn-flat" role="delete"><i class="fa fa-remove" aria-hidden="true"></i> Очистить</a>
        </div>
    </div>
            
    <div class="box-body">

        <table id="data-table-cis-cache" class="table table-hover table-striped table-condensed dataTable">
            <thead>
                <tr>
                    <th style="width: 15%">Hash</th>
                    <th style="width: 5%">Кол-во</th>
                    <th style="width: 10%">Время жизни</th>
                    <th style="width: 65%">MySQL</th>
                    <th style="width: 5%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $arRes as $item ) { ?>
                <tr>
                    <td><?= $item['hash'];?></td>
                    <td><?= $item['count'];?></td>
                    <td><?= date('Y-m-d H:i:s', $item['expire']);?></td>
                    <td><?= $item['query'];?></td>
                    <td class="text-right">
                        <a href="?delete=<?= $item['hash'];?>" role="delete">
                            <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
              
    </div>

</div>