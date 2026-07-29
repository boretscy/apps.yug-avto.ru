<?php $arRes = $app->Calc->getWorkvalues() ?>

<table id="data-table-workvalues" class="table table-hover table-striped table-condensed dataTable">
  <thead>
    <tr>
      <th style="width: 10%">ID</th>
      <th style="width: 20%">Значение</th>
      <th style="width: 55%">Описание</th>
      <th style="width: 15%">&nbsp;</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach( $arRes as $arItem ) { ?>
    <tr>
      <td><?=$arItem['id']?></td>
      <td><?=$arItem['value']?></td>
      <td><?=$arItem['ru_name']?></td>
      <td>
        <a href="/calc/workvalues/edit/<?=$arItem['id']?>/">
          <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
        </a>
        <?php if ( $app->User->isAdminUser($authUser) ) { ?>
        <a href="/calc/workvalues/delete/<?=$arItem['id']?>/" role="delete">
          <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
        </a>
        <?php } ?>
      </td>
    </tr>
    <?php } ?>
  </tbody>
</table>