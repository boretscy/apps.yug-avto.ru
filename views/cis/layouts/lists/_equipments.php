<?php if ( $currentRoute->action == 'delete' ) $app->Cis->yappsDelEquipment( $currentRoute->id ); ?>
<?php $arRes = $app->Cis->yappsGetEquipments(); ?>

<div class="box box-primary">
            
    <div class="box-header with-border">
        <h3 class="box-title">Комплектации</h3>
    </div>
        
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/cis/equipments/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
          </div>
        </div>
            
    <div class="box-body">

        <table id="data-table-cis-equipments" class="table table-hover table-striped table-condensed dataTable">
            <thead>
                <tr>
                    <th style="width: 10%">ID</th>
                    <th style="width: 15%">Бренд</th>
                    <th style="width: 15%">Модель</th>
                    <th style="width: 25%">Наименование</th>
                    <th style="width: 25%">Наименование на русском</th>
                    <th style="width: 10%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $arRes as $item ) { ?>
                <tr>
                    <td><a href="/cis/equipments/edit/<?=$item['id']?>/"><?= $item['id'];?></a></td>
                    <td><?= $item['brand']['name'];?></td>
                    <td><?= $item['model']['name'];?></td>
                    <td><a href="/cis/equipments/edit/<?=$item['id']?>/"><?= $item['name'];?></a></td>
                    <td><a href="/cis/equipments/edit/<?=$item['id']?>/"><?= $item['ru_name'];?></a></td>
                    <td class="text-right">
                        <a href="/cis/equipments/edit/<?=$item['id']?>/">
                            <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                        </a>&nbsp;&nbsp;
                        <a href="/cis/equipments/delete/<?=$item['id']?>/" role="delete">
                            <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
              
    </div>

</div>