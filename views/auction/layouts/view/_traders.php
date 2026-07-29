<?php if ( $currentRoute->id ) $arRes = $app->Auction->getTrader($currentRoute->id) ?>

<div class="row">
<div class="col-md-3 col-sm-6 col-xs-12">
  <div class="info-box">
    <span class="info-box-icon bg-blue"><i class="fa fa-briefcase"></i></span>

    <div class="info-box-content">
      <span class="info-box-text">Планируемые обороты</span>
      <span class="info-box-number">
	    <?=number_format($arRes['profile']['volume'], 0, '', ' ')?> ₽ / мес<br />
        <?=number_format($arRes['profile']['plan'], 0, '', ' ')?> шт / мес
      </span>
    </div>
    <!-- /.info-box-content -->
  </div>
  <!-- /.info-box -->
</div>
<div class="col-md-3 col-sm-6 col-xs-12">
  <div class="info-box">
    <span class="info-box-icon bg-blue"><i class="fa fa-car"></i></span>

    <div class="info-box-content">
      <span class="info-box-text">Категории</span>
      <span class="info-box-number">
        <small>
          <?=implode(', ', $app->Auction->getCategoriesNamesByProfile($arRes['profile']['id']))?>
        </small>
      </span>
    </div>
    <!-- /.info-box-content -->
  </div>
  <!-- /.info-box -->
</div>
<div class="col-md-3 col-sm-6 col-xs-12">
  <div class="info-box">
    <span class="info-box-icon bg-gray"><i class="fa fa-envelope-o"></i></span>

    <div class="info-box-content">
      <span class="info-box-text">Выиграно аукционов</span>
      <span class="info-box-number">
        <?=$app->Auction->getCountWins($arRes['id']);?> <small>из <?=$app->Auction->getCountTraderItems($arRes['id']);?></small>
      </span>
    </div>
    <!-- /.info-box-content -->
  </div>
  <!-- /.info-box -->
</div>
<div class="col-md-3 col-sm-6 col-xs-12">
  <div class="info-box">
    <span class="info-box-icon bg-gray"><i class="fa fa-dot-circle-o"></i></span>

    <div class="info-box-content">
      <span class="info-box-text">Всего ставок</span>
      <span class="info-box-number"><?=$app->Auction->getCountTraderCosts($arRes['id'])?></span>
    </div>
    <!-- /.info-box-content -->
  </div>
  <!-- /.info-box -->
</div>
</div>

<div class="box box-primary">
  
  <div class="box-header with-border">
    <h3 class="box-title">Информация о трейдере</h3>
    <?php if ( $app->User->isAdministrator( $authUser->ssid ) || in_array($authUser->id, $app->Auction->getAdmins()) ) { ?>
    <a href="/auction/traders/edit/<?=$arRes['id']?>" class="btn btn-info btn-flat pull-right">Изменить</a>
    <?php } ?>
  </div>
  <div class="box-body">
  
    <p><strong>ФИО</strong>: <?=$arRes['name']?></p>
    <p><strong>Телефон</strong>: <?=Helper::formatPhoneOut($arRes['phone'])?></p>
    <p><strong>Email</strong>: <?=$arRes['email']?></p>
    <p>
      <strong>Паспорт, страница с фото</strong>: 
      <?php if ($arRes['passport01_image']) { ?>
      <small>(<a href="<?=$arRes['passport01_image']?>" target="_blank">
        Посмотреть <i class="fa fa-eye" aria-hidden="true"></i>
      </a>)</small><?php } ?>
    </p>
    <p>
      <strong>Паспорт, страница с регистрацией</strong>:
      <?php if ($arRes['passport02_image']) { ?>
      <small>(<a href="<?=$arRes['passport02_image']?>" target="_blank">
        Посмотреть <i class="fa fa-eye" aria-hidden="true"></i>
      </a>)</small><?php } ?>
    </p>
    
    
  	<hr />
    <h4>Организация</h4>
    <p><strong>Название</strong>: <?=(($arRes['profile']['org_name'])?:'---')?></p>
    <p><strong>Телефон</strong>: <?=Helper::formatPhoneOut($arRes['profile']['org_phone'])?></p>
    <p><strong>Email</strong>: <?=(($arRes['profile']['org_email'])?:'---')?></p>
    <p><strong>Адрес</strong>: <?=(($arRes['profile']['org_address'])?:'---')?></p>
    <p>
      <strong>ИНН/КПП</strong>: <?=(($arRes['profile']['org_inn'])?:'---')?> / <?=(($arRes['profile']['org_kpp'])?:'---')?> 
	  <?php if ($arRes['profile']['org_inn_image']) { ?>
      <small>(<a href="<?=$arRes['profile']['org_inn_image']?>" target="_blank">
        Посмотреть <i class="fa fa-eye" aria-hidden="true"></i>
      </a>)</small><?php } ?>
    </p>
    <p>
      <strong>ОГРН (ОГРНИП)</strong>: <?=(($arRes['profile']['org_ogrn'])?:'---')?> 
      <?php if ($arRes['profile']['org_ogrn_image']) { ?>
      <small>(<a href="<?=$arRes['profile']['org_ogrn_image']?>" target="_blank">
        Посмотреть <i class="fa fa-eye" aria-hidden="true"></i>
      </a>)</small><?php } ?>
    </p>
    <br />
    <h4>Руководитель</h4>
    <p><strong>ФИО</strong>: <?=(($arRes['profile']['org_head_name'])?:'---')?></p>
    <p><strong>Должность</strong>: <?=(($arRes['profile']['org_head_position'])?:'---')?></p>
    <p><strong>Действующий на основании</strong>: <?=(($arRes['profile']['org_head_based'])?:'---')?></p>
    
    <br />
    <h4>Контактное лицо</h4>
    <p><strong>ФИО</strong>: <?=(($arRes['profile']['contact_name'])?:'---')?></p>
    <p><strong>Телефон</strong>: <?=Helper::formatPhoneOut($arRes['profile']['contact_phone'])?></p>
    <p><strong>Email</strong>: <?=(($arRes['profile']['contact_email'])?:'---')?></p>
  </div>
  
</div>

<div class="box box-primary">
  
  <div class="box-header with-border"><h3 class="box-title">Участие в аукционах</h3></div>

  <div class="box-body">
    <table id="data-table-items" class="table table-hover table-striped table-condensed dataTable">
    <thead>
        <tr>
          <th style="width: 8%">ID</th>
          <th style="width: 18%">Автомобиль</th>
          <th style="width: 18%">VIN</th>
          <th style="width: 18%">Стоимость, ₽</th>
          <th style="width: 18%">Даты проведения</th>
          <th style="width: 9%">Победитель</th>
          <th style="width: 9%">Ставка</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $app->Auction->getItemsByTrader($arRes['id']) as $item ) { ?>
        <tr>
          <td><a href="/auction/items/view/<?=$item['id']?>/"><?=$item['id']?></a></td>
          <td><a href="/auction/items/view/<?=$item['id']?>/"><?=$item['brand']?> <?=$item['model']?></a></td>
          <td><a href="/auction/items/view/<?=$item['id']?>/"><?=$item['vin']?></a></td>
          <td><?=number_format($item['start_price'], 0, '', ' ')?> &rarr; <?=number_format($item['current_price'], 0, '', ' ')?></td>
          <td><?=date('d.m.Y H:i', strtotime($item['datetime_start']))?> &rarr; <?=date('d.m.Y H:i', strtotime($item['datetime_end']))?></td>
          <td><?=(($item['place'] !== false)?($item['place']+1).' место':'')?></td>
          <td><?=number_format($item['cost'], 0, '', ' ')?> ₽</td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

</div>