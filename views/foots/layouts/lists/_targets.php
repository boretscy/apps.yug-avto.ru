<?php if ( $currentRoute->action == 'delete' ) $app->Foots->delTarget( $currentRoute->id ); ?>
<?php $arRes = $app->Foots->getTargets(); ?>

<div class="box box-primary">
  
  <div class="box-header with-border"><h3 class="box-title">Цели визита</h3></div>
  
  <div class="box-body">
    <div class="col-xs-12">
      <a href="/foots/targets/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить цель</a>
    </div>
  </div>
  
  <div class="box-body">
    <table id="data-table-targets" class="table table-hover table-striped table-condensed dataTable">
      <thead>
        <tr>
          <th style="width: 10%">ID</th>
          <th style="width: 35%">Наименование</th>
          <th style="width: 15%">Важность</th>
          <th style="width: 15%">Следующий шаг</th>
          <th style="width: 15%">Sort</th>
          <th style="width: 10%"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $arRes as $role ) { ?>
		  <?php foreach ( $role as $item ) { ?>
          <tr>
            <td><?=$item['id']?></td>
            <td><?=$item['ru_name']?></td>
            <td><?=$item['role']?></td>
            <td><?=$app->Foots->getStep($item['next_step'])['ru_name']?></td>
            <td><?=$item['sort']?></td>
            <td class="text-right">
              <a href="/foots/targets/edit/<?=$item['id']?>/">
                <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
              </a>
              <a href="/foots/targets/delete/<?=$item['id']?>/" role="delete">
                <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
              </a>
            </td>
          </tr>
          <?php } ?>
        <?php } ?>
      </tbody>
    </table>
  </div>

</div>