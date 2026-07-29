<?php $arRes = $app->Calc->getCheckworks() ?>

<table id="data-table-checkworks" class="table table-hover table-striped table-condensed dataTable">
  <thead>
    <tr>
      <th style="width: 2%">ID</th>
      <th style="width: 73%">Наименование</th>
      <th style="width: 5%">Доп</th>
      <th style="width: 10%">Sort</th>
      <th style="width: 10%">&nbsp;</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach( $arRes as $arItem ) { ?>
    <tr>
      <td><?=$arItem['id']?></td>
      <td><?=$arItem['ru_name']?></td>
      <td>
        <?php if ( $arItem['additional_flag'] ) { ?>
        <span class="label label-info hint--top" aria-label="Дополнительная работа"><i class="fa check-square-o" aria-hidden="true"></i></span>
        <?php } ?>
      </td>
      <td><?=$arItem['sort']?></td>
      <td>
        <a href="/calc/checkworks/edit/<?=$arItem['id']?>/">
          <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
        </a>
        <?php if ( $app->User->isAdminUser($authUser) ) { ?>
        <a href="/calc/checkworks/delete/<?=$arItem['id']?>/" role="delete">
          <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
        </a>
        <?php } ?>
      </td>
    </tr>
    <?php } ?>
  </tbody>
</table>