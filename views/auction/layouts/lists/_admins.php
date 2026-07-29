<?php $arRes = $app->YApps_GetUsersByIDs( $app->Auction->getAdmins() ); ?>

<div class="box box-primary">
  
  <div class="box-header with-border"><h3 class="box-title">Администраторы</h3></div>
  
  <div class="box-body">
    <div class="col-xs-12">
      <a href="/auction/admins/edit/" class="btn btn-info btn-flat">Изменить</a>
    </div>
  </div>
  
  <div class="box-body">
    <table id="data-table-admins" class="table table-hover table-striped table-condensed dataTable">
      <thead>
        <tr>
          <th style="width: 10%">ID</th>
          <th style="width: 90%">ФИО</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $arRes as $item ) { ?>
        <tr>
          <td><?=$item['id']?></td>
          <td><?=$item['name']?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

</div>