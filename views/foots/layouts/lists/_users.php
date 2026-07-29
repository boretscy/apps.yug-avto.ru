<?php $arRes = $app->YApps_GetUsersByApp( $app->Foots->AppInfo()->id ); ?>

<div class="box box-primary">
  
  <div class="box-header with-border"><h3 class="box-title">Пользователи</h3></div>
  
  <div class="box-body">
    <table id="data-table-users" class="table table-hover table-striped table-condensed dataTable">
      <thead>
        <tr>
          <th style="width: 10%">ID</th>
          <th style="width: 40%">ФИО</th>
          <th style="width: 40%">ДЦ</th>
          <th style="width: 10%"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $arRes as $item ) { ?>
        <tr>
          <td><?=$item['id']?></td>
          <td><?=$item['name']?></td>
          <td><?=implode(', ', $app->Foots->getUserDCNames($item['id']))?></td>
          <td class="text-right">
            <a href="/foots/users/edit/<?=$item['id']?>/">
              <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
            </a>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

</div>