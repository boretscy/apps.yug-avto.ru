<?php $arRes = $app->getUserDCs($authUser); ?>
<div class="box box-primary">
        
    <div class="box-header with-border"><h3 class="box-title">Дилерский центр</h3></div>
         
    <div class="box-body">
        
        <table id="data-table" class="table table-hover table-striped table-condensed dataTable">
            <thead>
                <tr>
                    <th style="width: 30%">Дилерский центр</th>
                    <th style="width: 30%">Сайт</th>
                    <th style="width: 40%">Файл</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $arRes as $item ) { ?>
                <tr>
                    <td><?=$item['ru_name']?></td>
                    <td><?=$app->getSite($item['site_id'])['ru_name']?></td>
                    <td>
                        <?php if ( file_exists($_SERVER['DOCUMENT_ROOT'].'/upload/Stock/'.$item['site_id'].'/'.$item['id'].'.xlsx') ) { ?>
                        <div class="input-group" style="width: 100%">
                            <div class="input-group-addon" style="background: #eee; cursor: pointer;" role="copy" aria-label="Копировать">
                                <i class="fa fa-copy"></i>
                            </div>
                            <input type="text" class="form-control" readonly value="https://apps.yug-avto.ru/upload/Stock/<?= $item['site_id'].'/'.$item['id'].'.xlsx'?>" />
                        </div>
                        <?php } // if ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
          </table>
          
    </div>
    
</div>
