<?php if ( $currentRoute->action == 'delete' ) $app->Cis->yappsDelDealership( $currentRoute->id ); ?>
<?php $arRes = $app->Cis->yappsGetDealerships(); ?>

<div class="box box-primary">
            
    <div class="box-header with-border">
        <h3 class="box-title">Дилерские центры</h3>
    </div>
        
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/cis/dealerships/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
          </div>
        </div>
            
    <div class="box-body">

        <table id="data-table-cis-dealerships" class="table table-hover table-striped table-condensed dataTable">
            <thead>
                <tr>
                    <th style="width: 10%">ID</th>
                    <th style="width: 20%">Название</th>
                    <th style="width: 20%">Телефон</th>
                    <th style="width: 20%">Адрес</th>
                    <th style="width: 20%">Город</th>
                    <th style="width: 10%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $arRes as $item ) { ?>
                <tr>
                    <td><a href="/cis/dealerships/edit/<?=$item['code']?>/"><?= $item['code'];?></a></td>
                    <td><a href="/cis/dealerships/edit/<?=$item['code']?>/"><?= $item['name']?></a></td>
                    <td><a href="/cis/dealerships/edit/<?=$item['code']?>/"><?= Helper::formatPhoneOut($item['phone']);?></a></td>
                    <td><?= $item['address'];?></td>
                    <td><?= $item['city'];?></td>
                    <td class="text-right">
                        <a href="/cis/dealerships/edit/<?=$item['code']?>/">
                            <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                        </a>&nbsp;&nbsp;
                        <a href="/cis/dealerships/delete/<?=$item['id']?>/" role="delete">
                            <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
              
    </div>

</div>