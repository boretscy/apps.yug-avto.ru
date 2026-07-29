<?php $arRes = $app->Auction->getTraders(); ?>

<div class="box box-primary">
  
  <div class="box-header with-border"><h3 class="box-title">Трейдеры</h3></div>
  
  <div class="box-body">
    <div class="col-xs-12">
      <a href="https://apps.yug-avto.ru/auction/traders/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить трейдера</a>
    </div>
  </div>

  <div class="box-body">
    <table id="data-table-traders" class="table table-hover table-striped table-condensed dataTable">
      <thead>
        <tr>
          <?php if ( $app->User->isAdministrator( $authUser->ssid ) && in_array($authUser->id, $app->Auction->getAdmins()) ) { ?>
          <th style="width: 10%">ID</th>
          <?php } ?>
          <th style="width: 30%">ФИО</th>
          <th style="width: 20%">Телефон</th>
          <th style="width: 20%">Email</th>
          <th style="width: 10%">Статус</th>
          <th style="width: 10%"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $arRes as $item ) { ?>
        <tr>
          <?php if ( $app->User->isAdministrator( $authUser->ssid ) && in_array($authUser->id, $app->Auction->getAdmins()) ) { ?>
          <td><a href="/auction/traders/view/<?=$item['id']?>/"><?=$item['id']?></a></td>
          <?php } ?>
          <td><a href="/auction/traders/view/<?=$item['id']?>/"><?=(($item['name'])?:'---')?></a></td>
          <td><a href="/auction/traders/view/<?=$item['id']?>/"><?=Helper::formatPhoneOut($item['phone'])?></a></td>
          <td><?=(($item['email'])?:'---')?></td>
          <td>
          	<span class="label label-<?=(($item['active'])?'success':'warning')?> hint--top" aria-label="<?=(($item['active'])?'А':'Неа')?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;
            <?php /* <span class="label label-<?=(($item['verified'])?'success':'warning')?> hint--top" aria-label="Подтвержден"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp; */ ?>
            <span class="label label-<?=(($item['profile']['active'])?'success':'warning')?> hint--top" aria-label="<?=(($item['profile']['active'])?'Д':'Не д')?>опущен к торгам"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;
            <span class="label label-<?=(($item['profile_flag'])?'success':'warning')?> hint--top" aria-label="Профиль <?=(($item['profile_flag'])?'':'не')?>заполнен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;
          </td>
          <td class="text-right">
            <?php if ( $app->User->isAdministrator( $authUser->ssid ) || in_array($authUser->id, $app->Auction->getAdmins()) ) { ?>
            <a href="/auction/traders/edit/<?=$item['id']?>/">
              <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
            </a>
            <?php } ?>
            <a href="/auction/traders/view/<?=$item['id']?>/">
              <span class="label label-info hint--top" aria-label="Просмотр"><i class="fa fa-eye" aria-hidden="true"></i></span>
            </a>
            <?php if ( $app->User->isAdministrator( $authUser->ssid ) && in_array($authUser->id, $app->Auction->getAdmins()) ) { ?>
            &nbsp;
            <a href="/auction/traders/delete/<?=$item['id']?>/" role="delete">
              <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
            </a>
            <?php } ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

</div>