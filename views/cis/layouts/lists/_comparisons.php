<?php if ( $currentRoute->action == 'delete' ) $app->Cis->yappsDelComparison( $currentRoute->id ); ?>
<?php $arRes = $app->Cis->yappsGetComparisons(); ?>

<div class="box box-primary">
            
    <div class="box-header with-border">
        <h3 class="box-title">Правила сопоставления</h3>
    </div>
        
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/cis/comparisons/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
          </div>
        </div>
            
    <div class="box-body">

        <table id="data-table-cis-comparisons" class="table table-hover table-striped table-condensed dataTable">
            <thead>
                <tr>
                    <th style="width: 10%">ID</th>
                    <th style="width: 20%">Сущность</th>
                    <th style="width: 30%">Искомое</th>
                    <th style="width: 30%">Значение</th>
                    <th style="width: 10%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $arRes as $item ) { ?>
                <tr>
                    <td><a href="/cis/comparisons/edit/<?=$item['id']?>/"><?= $item['id'];?></a></td>
                    <td><a href="/cis/comparisons/edit/<?=$item['id']?>/"><?= $item['entity']?></a></td>
                    <td><a href="/cis/comparisons/edit/<?=$item['id']?>/"><?= $item['desired'];?></a></td>
                    <td><a href="/cis/comparisons/edit/<?=$item['id']?>/"><?= $item['name'];?></a></td>
                    <td class="text-right">
                        <a href="/cis/comparisons/edit/<?=$item['id']?>/">
                            <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                        </a>&nbsp;&nbsp;
                        <a href="/cis/comparisons/delete/<?=$item['id']?>/" role="delete">
                            <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
              
    </div>

</div>