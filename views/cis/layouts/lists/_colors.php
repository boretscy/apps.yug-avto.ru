<?php if ( $currentRoute->action == 'delete' ) $app->Cis->yappsDelColor( $currentRoute->id ); ?>
<?php $arRes = $app->Cis->yappsGetColors(); ?>

<div class="box box-primary">
            
    <div class="box-header with-border">
        <h3 class="box-title">Цвет</h3>
    </div>
        
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/cis/colors/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
          </div>
        </div>
            
    <div class="box-body">

        <table id="data-table-cis-colors" class="table table-hover table-striped table-condensed dataTable">
            <thead>
                <tr>
                    <th style="width: 10%">ID</th>
                    <th style="width: 20%">Ключ</th>
                    <th style="width: 30%">Название</th>
                    <th style="width: 30%">Meta</th>
                    <th style="width: 10%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $arRes as $item ) { ?>
                <tr>
                    <td><a href="/cis/colors/edit/<?=$item['id']?>/"><?= $item['id'];?></a></td>
                    <td><a href="/cis/colors/edit/<?=$item['id']?>/"><?= $item['code']?></a></td>
                    <td><a href="/cis/colors/edit/<?=$item['id']?>/"><?= $item['name'];?></a></td>
                    <td><a href="/cis/colors/edit/<?=$item['id']?>/"><?= $item['meta'];?></a></td>
                    <td class="text-right">
                        <a href="/cis/colors/edit/<?=$item['id']?>/">
                            <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                        </a>&nbsp;&nbsp;
                        <a href="/cis/colors/delete/<?=$item['id']?>/" role="delete">
                            <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
              
    </div>

</div>