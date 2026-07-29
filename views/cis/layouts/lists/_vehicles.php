<?php $arRes = $app->Cis->yappsGetVehicles('new'); ?>

<div class="box box-primary">
            
    <div class="box-header with-border">
        <h3 class="box-title">Новые</h3>
    </div>
            
    <div class="box-body">

        <table id="data-table-cis-vehicles-new" class="table table-hover table-striped table-condensed dataTable">
            <thead>
                <tr>
                    <th style="width: 10%">ID</th>
                    <th style="width: 10%">VIN</th>
                    <th style="width: 20%">Авто</th>
                    <th style="width: 20%">Проблема</th>
                    <th style="width: 7%">Кузов</th>
                    <th style="width: 7%">Цвет</th>
                    <th style="width: 7%">Привод</th>
                    <th style="width: 7%">Двигатель</th>
                    <th style="width: 7%">КПП</th>
                    <th style="width: 5%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php $err_count = 0; ?>
                <?php foreach( $arRes as $item ) { ?>
                <?php
                    $problems = [];

                    if ( !$item['vin'] ) $problems[] = 'VIN';
                    if ( $item['body'] == 'none' ) $problems[] = 'Кузов';
                    if ( $item['color'] == 'none' ) $problems[] = 'Цвет';
                    if ( $item['drive'] == 'none' ) $problems[] = 'Привод';
                    if ( $item['engine'] == 'none' ) $problems[] = 'Двигатель';
                    if ( $item['transmission'] == 'none' ) $problems[] = 'КПП';

                    if (count($problems)) $err_count++;
                ?>
                <tr class="<?= ((count($problems))?'bg-gray':'');?>">
                    <td><a href="/cis/vehicles/edit/<?=$item['ext_id']?>/"><?= $item['ext_id'];?></a></td>
                    <td><a href="/cis/vehicles/edit/<?=$item['ext_id']?>/"><?= $item['vin'];?></a></td>
                    <td><a href="/cis/vehicles/edit/<?=$item['ext_id']?>/"><?= $item['name'];?></a></td>
                    <td><?= implode(', ', $problems);?></td>
                    <td><?= (($item['body']!='none')?$item['body']:'<span class="text-red">'.json_decode($item['raw'], true)['body_type'].'</span>');?></td>
                    <td><?= (($item['color']!='none')?$item['color']:'<span class="text-red">'.json_decode($item['raw'], true)['general'][2]['value'].'</span>');?></td>
                    <td><?= (($item['drive']!='none')?$item['drive']:'<span class="text-red">'.json_decode($item['raw'], true)['specifications'][11]['value'].'</span>');?></td>
                    <td><?= (($item['engine']!='none')?$item['engine']:'<span class="text-red">'.json_decode($item['raw'], true)['general'][0]['value'].'</span>');?></td>
                    <td><?= (($item['transmission']!='none')?$item['transmission']:'<span class="text-red">'.json_decode($item['raw'], true)['general'][1]['value'].'</span>');?></td>
                    <td class="text-right">
                        <a href="/cis/vehicles/edit/<?=$item['ext_id']?>/">
                            <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                        </a>&nbsp;
                        <a href="/cis/vehicles/edit/<?=$item['ext_id']?>/">
                            <span class="label label-<?= (($item['update_images'])?'warning':'default');?> hint--top" aria-label="Обновить фото"><i class="fa fa-refresh" aria-hidden="true"></i></span>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <p>Ошибок: <?= $err_count;?></p>
    </div>

</div>

<?php $arRes = $app->Cis->yappsGetVehicles('used'); ?>

<div class="box box-primary">
            
    <div class="box-header with-border">
        <h3 class="box-title">С пробегом</h3>
    </div>
            
    <div class="box-body">

        <table id="data-table-cis-vehicles-used" class="table table-hover table-striped table-condensed dataTable">
            <thead>
                <tr>
                    <th style="width: 10%">ID</th>
                    <th style="width: 10%">VIN</th>
                    <th style="width: 20%">Авто</th>
                    <th style="width: 20%">Проблема</th>
                    <th style="width: 7%">Кузов</th>
                    <th style="width: 7%">Цвет</th>
                    <th style="width: 7%">Привод</th>
                    <th style="width: 7%">Двигатель</th>
                    <th style="width: 7%">КПП</th>
                    <th style="width: 5%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $arRes as $item ) { ?>
                <?php
                    $problems = [];

                    if ( !$item['vin'] ) $problems[] = 'VIN';
                    if ( $item['body'] == 'none' ) $problems[] = 'Кузов';
                    if ( $item['color'] == 'none' ) $problems[] = 'Цвет';
                    if ( $item['drive'] == 'none' ) $problems[] = 'Привод';
                    if ( $item['engine'] == 'none' ) $problems[] = 'Двигатель';
                    if ( $item['transmission'] == 'none' ) $problems[] = 'КПП';

                    if (count($problems)) $err_count++;
                ?>
                <tr class="<?= ((count($problems))?'bg-gray':'');?>">
                    <td><a href="/cis/vehicles/edit/<?=$item['ext_id']?>/"><?= $item['ext_id'];?></a></td>
                    <td><a href="/cis/vehicles/edit/<?=$item['ext_id']?>/"><?= $item['vin'];?></a></td>
                    <td><a href="/cis/vehicles/edit/<?=$item['ext_id']?>/"><?= $item['name'];?></a></td>
                    <td><?= implode(', ', $problems);?></td>
                    <td><?= (($item['body']!='none')?$item['body']:'<span class="text-red">'.json_decode($item['raw'], true)['body_type'].'</span>');?></td>
                    <td><?= (($item['color']!='none')?$item['color']:'<span class="text-red">'.json_decode($item['raw'], true)['general'][2]['value'].'</span>');?></td>
                    <td><?= (($item['drive']!='none')?$item['drive']:'<span class="text-red">'.json_decode($item['raw'], true)['specifications'][11]['value'].'</span>');?></td>
                    <td><?= (($item['engine']!='none')?$item['engine']:'<span class="text-red">'.json_decode($item['raw'], true)['general'][0]['value'].'</span>');?></td>
                    <td><?= (($item['transmission']!='none')?$item['transmission']:'<span class="text-red">'.json_decode($item['raw'], true)['general'][1]['value'].'</span>');?></td>
                    <td class="text-right">
                        <a href="/cis/vehicles/edit/<?=$item['ext_id']?>/">
                            <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                        </a>&nbsp;
                        <a href="/cis/vehicles/edit/<?=$item['ext_id']?>/">
                            <span class="label label-<?= (($item['update_images'])?'warning':'default');?> hint--top" aria-label="Обновить фото"><i class="fa fa-refresh" aria-hidden="true"></i></span>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <p>Ошибок: <?= $err_count;?></p>
    </div>

</div>

