<?php $arRes = $app->getUserDCs($authUser); ?>

<div class="box box-primary">
            
    <div class="box-header with-border">
        <h3 class="box-title"></h3>
    </div>
            
    <div class="box-body">

        <table id="data-table" class="table table-hover table-striped table-condensed dataTable">
            <thead>
                <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 15%">Сайт</th>
                    <th style="width: 30%">ДЦ</th>
                    <th style="width: 30%">Телефон</th>
                    <th style="width: 20%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $arRes as $item ) { ?>
                <tr>
                    <td><a href="/feeds/tuning/edit/<?=$item['id']?>/"><?= $item['id'];?></a></td>
                    <td><a href="/feeds/tuning/edit/<?=$item['id']?>/"><?=$app->getSite($item['site_id'])['ru_name']?></a></td>
                    <td><a href="/feeds/tuning/edit/<?=$item['id']?>/"><?= $item['ru_name'];?></a></td>
                    <td><?= (($item['feeds_phone'])?Helper::formatPhoneOut($item['feeds_phone']):Helper::formatPhoneOut($item['phone']));?></td>
                    <td class="text-right">
                        <span class="label label-<?=(($item['feeds_active']==1)?'success':'warning')?> hint--top" aria-label="<?=(($item['feeds_active']==1)?'А':'Неа')?>ктиво"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                        <a href="/feeds/tuning/edit/<?=$item['id']?>/">
                            <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
              
    </div>

</div>