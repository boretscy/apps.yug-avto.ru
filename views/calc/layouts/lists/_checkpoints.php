<?php $arRes = $app->Calc->getCheckpoints() ?>

<table id="data-table-checkpoints" class="table table-hover table-striped table-condensed dataTable">
  <thead>
    <tr>
      <th style="width: 2%">ID</th>
      <th style="width: 10%">Пробег, км</th>
      <th style="width: 20%">Возраст, лет</th>
      <th style="width: 20%">Sort</th>
      <th style="width: 15%">&nbsp;</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach( $arRes as $arItem ) { ?>
    <tr>
      <td><?=$arItem['id']?></td>
      <td><?=$arItem['milleage']?></td>
      <td><?=$arItem['age']?></td>
      <td><?=$arItem['sort']?></td>
      <td>
        <a href="/calc/checkpoints/edit/<?=$arItem['id']?>/">
          <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
        </a>
        <?php if ( $app->User->isAdminUser($authUser) ) { ?>
        <a href="/calc/checkpoints/delete/<?=$arItem['id']?>/" role="delete">
          <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
        </a>
        <?php } ?>
      </td>
    </tr>
    <?php } ?>
  </tbody>
</table>