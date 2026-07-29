<?php if ( $currentRoute->id ) $arRes = $app->Auction->getItem($currentRoute->id) ?>
<?php $winner = ( $arRes && $w_id = $app->Auction->getItemWinnerId($arRes['id']) ) ? $app->Auction->getTrader($w_id) : false; ?>
<?php $current_winner = $app->Auction->getTrader($app->Auction->getItemWinnersId($arRes['id'])[0]); ?>
<?php $costs = $app->Auction->getBetsByItem($arRes['id']);?>

<?php // Helper::sp( $costs ); ?>

<div class="row">
  <div class="col-md-3 col-sm-6 col-xs-12">
    <div class="info-box">
      <span class="info-box-icon bg-<?=$app->Auction->getStatus($arRes['status_id'])['bg_color']?>"><i class="fa fa-folder-open-o"></i></span>
  
      <div class="info-box-content">
        <span class="info-box-text">Статус</span>
        <span class="info-box-number">
		  <?=$app->Auction->getStatus($arRes['status_id'])['ru_name']?>
          <br /><small>Тип торгов: <?=$app->Auction->getType($arRes['type_id'])['ru_name']?></small>
        </span>
      </div>
      <!-- /.info-box-content -->
    </div>
    <!-- /.info-box -->
  </div>
  <div class="col-md-3 col-sm-6 col-xs-12">
    <div class="info-box">
      <span class="info-box-icon bg-blue"><i class="fa fa-briefcase"></i></span>
  
      <div class="info-box-content">
        <span class="info-box-text">Цена</span>
        <span class="info-box-number">
		  <?=number_format($arRes['current_price'], 0, '', ' ')?> ₽
          <br /><small>Стартовая: <?=number_format($arRes['start_price'], 0, '', ' ')?> ₽</small>
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
        <span class="info-box-text">Ставки</span>
        <span class="info-box-number">
          <?=$app->Auction->getItemCosts($arRes['id']);?>
        </span>
      </div>
      <!-- /.info-box-content -->
    </div>
    <!-- /.info-box -->
  </div>
  <?php if ( $app->User->isAdministrator( $authUser->ssid ) || in_array($authUser->id, $app->Auction->getAdmins()) ) { ?>
  <div class="col-md-3 col-sm-6 col-xs-12">
    <div class="info-box">
      <span class="info-box-icon bg-<?=(($winner)?'green':'gray')?>"><i class="fa fa-dot-circle-o"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Победитель</span>
        <span class="info-box-number"><?=(($winner)?$winner['name']:'Не определен')?></span>
      </div>
      <!-- /.info-box-content -->
    </div>
    <!-- /.info-box -->
  </div>
  <?php } ?>
</div>


<div class="box box-primary">
  
  <div class="box-header with-border">
    <h3 class="box-title"><?=$arRes['brand']?> <?=$arRes['model']?>, <?=$arRes['year']?>, <?=number_format($arRes['milleage'], 0, '', ' ')?> км, <?=$arRes['vin']?></h3>
    <div class="pull-right">
      <a href="/auction/items/edit/<?=$arRes['id']?>/" class="btn btn-info btn-flat">Изменить</a>&nbsp;
      <?php if ( $arRes['status_id'] == 2 ) { ?>
      <a href="/auction/items/delete/<?=$arRes['id']?>/" class="btn btn-danger btn-flat">Снять с публикации</a>
      <?php } else { ?>
      <a href="/auction/items/activate/<?=$arRes['id']?>/" class="btn btn-success btn-flat">Опубликовать</a>
      <?php } ?>
    </div>
    
  </div>
  <div class="box-body">
    <p><strong>Тип</strong>: <?=$app->Auction->getType($arRes['type_id'])['ru_name']?></p>
    <p><strong>Статус</strong>: <?=$app->Auction->getStatus($arRes['status_id'])['ru_name']?></p>
    <?php if ( $app->User->isAdministrator( $authUser->ssid ) || in_array($authUser->id, $app->Auction->getAdmins()) ) { ?>
    <p><strong>Текущий победитель</strong>: <a href="/auction/traders/view/<?=$current_winner['id']?>/"><?=$current_winner['name']?></a></p>
    <?php } ?>
    <p><strong>Старт</strong>: <?=date('d.m.Y H:i', strtotime($arRes['datetime_start']))?></p>
    <p><strong>Окончание</strong>: <?=date('d.m.Y H:i', strtotime($arRes['datetime_end']))?></p>
    <p><strong>Стартовая цена</strong>: <?=number_format($arRes['start_price'], 0, '', ' ')?> ₽</p>
    <p><strong>Текущая цена</strong>: <?=number_format($arRes['current_price'], 0, '', ' ')?> ₽</p>
    <p><strong>Всего ставок</strong>: <?=$app->Auction->getItemCosts($arRes['id']);?></p>
    <hr />
    
    <h4>Автомобиль</h4>
    <p><strong>Марка</strong>: <?=$arRes['brand']?></p>
    <p><strong>Модель</strong>: <?=$arRes['model']?></p>
    <p><strong>VIN</strong>: <?=$arRes['vin']?></p>
    <p><strong>Цвет</strong>: <?=$arRes['color']?></p>
    <p><strong>Год выпуска</strong>: <?=$arRes['year']?></p>
    <p><strong>Пробег</strong>: <?=number_format($arRes['milleage'], 0, '', ' ')?> км</p>
    <p><strong>Объем двигателя</strong>: <?=$arRes['engine_volume']?> см<sup>3</sup></p>
    <p><strong>Тип двигателя</strong>: <?=$app->Auction->getEngineType($arRes['engine_type_id'])['ru_name']?></p>
    <p><strong>Трансмиссия</strong>: <?=$app->Auction->getTransmission($arRes['transmission_id'])['ru_name']?></p>
    <p><strong>Привод</strong>: <?=$app->Auction->getDrive($arRes['drive_id'])['ru_name']?></p>
    <p><strong>Владельцев по ПТС</strong>: <?=$arRes['owners']?></p>
    <hr />
    <h4>Описание</h4>
    <p><?=$arRes['description']?></p>
  </div>
</div>

<?php if ( $costs ) { ?>
<div class="box box-primary">
  
  <div class="box-header with-border"><h3 class="box-title">Ставки</h3></div>
  
  <div class="box-body">
    <table id="data-table-bets" class="table table-hover table-striped table-condensed dataTable">
      <thead>
        <tr>
          <?php if ( $app->User->isAdministrator( $authUser->ssid ) || in_array($authUser->id, $app->Auction->getAdmins()) ) { ?>
          <th style="width: 30%">Трейдер</th>
          <?php } ?>
          <th>Дата / время</th>
          <th>Ставка, ₽</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $costs as $item ) { ?>
        <tr>
          <?php if ( $app->User->isAdministrator( $authUser->ssid ) || in_array($authUser->id, $app->Auction->getAdmins()) ) { ?>
          <th style="width: 18%"><a hre="/auction/traders/view/<?=$app->Auction->getTrader($item['trader_id'])['name']?>/"><?=$app->Auction->getTrader($item['trader_id'])['name']?></a></th>
          <?php } ?>
          <td><?=$item['datetime']?></td>
          <td><?=number_format($item['value'], 0, '', ' ')?> ₽</td>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

</div>
<?php } ?>

  <div class="row">
    <div class="col-md-3">
        <div class="box box-primary">
            <div class="box-header with-border">Фото</div>
            <div class="box-body">
                <div id="carousel-photos" class="carousel slide" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <?php foreach( $app->Auction->getItemPhotos($arRes['id']) as $k => $image ) { ?>
                        <li data-target="#carousel-photos" data-slide-to="0" class="<?=(($k==0)?'active':'')?>"></li>
                        <?php } ?>
                    </ol>
                    <div class="carousel-inner">
                        <?php foreach( $app->Auction->getItemPhotos($arRes['id']) as $k => $image ) { ?>
                        <div class="item <?=(($k==0)?'active':'')?>">
                            <img src="<?=$image?>" style="width: 100%;">
                        </div>
                        <?php } ?>
                    </div>
                    <a class="left carousel-control" href="#carousel-photos" data-slide="prev">
                    <span class="fa fa-angle-left"></span>
                    </a>
                    <a class="right carousel-control" href="#carousel-photos" data-slide="next">
                    <span class="fa fa-angle-right"></span>
                    </a>
                </div>
                <br />
                <?php foreach( $app->Auction->getItemPhotos($arRes['id']) as $image ) { ?>
                <img src="<?=$image?>" style="width: 24.5%;" />
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="box box-primary">
            <div class="box-header with-border">Повреждения</div>
            <div class="box-body">
                <div id="carousel-damages" class="carousel slide" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <?php foreach( $app->Auction->getItemDamages($arRes['id']) as $k => $image ) { ?>
                        <li data-target="#carousel-cards" data-slide-to="0" class="<?=(($k==0)?'active':'')?>"></li>
                        <?php } ?>
                    </ol>
                    <div class="carousel-inner">
                        <?php foreach( $app->Auction->getItemDamages($arRes['id']) as $k => $image ) { ?>
                        <div class="item <?=(($k==0)?'active':'')?>">
                            <img src="<?=$image?>" style="width: 100%;">
                        </div>
                        <?php } ?>
                    </div>
                    <a class="left carousel-control" href="#carousel-damages" data-slide="prev">
                    <span class="fa fa-angle-left"></span>
                    </a>
                    <a class="right carousel-control" href="#carousel-damages" data-slide="next">
                    <span class="fa fa-angle-right"></span>
                    </a>
                </div>
                <br />
                <?php foreach( $app->Auction->getItemDamages($arRes['id']) as $image ) { ?>
                <img src="<?=$image?>" style="width: 24.5%;" />
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="box box-primary">
            <div class="box-header with-border">Диагностическая карта</div>
            <div class="box-body">
                <div id="carousel-cards" class="carousel slide" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <?php foreach( $app->Auction->getItemCards($arRes['id']) as $k => $image ) { ?>
                        <li data-target="#carousel-cards" data-slide-to="0" class="<?=(($k==0)?'active':'')?>"></li>
                        <?php } ?>
                    </ol>
                    <div class="carousel-inner">
                        <?php foreach( $app->Auction->getItemCards($arRes['id']) as $k => $image ) { ?>
                        <div class="item <?=(($k==0)?'active':'')?>">
                            <img src="<?=$image?>" style="width: 100%;">
                        </div>
                        <?php } ?>
                    </div>
                    <a class="left carousel-control" href="#carousel-cards" data-slide="prev">
                    <span class="fa fa-angle-left"></span>
                    </a>
                    <a class="right carousel-control" href="#carousel-cards" data-slide="next">
                    <span class="fa fa-angle-right"></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="box box-primary">
            <div class="box-header with-border">Видео</div>
            <div class="box-body">
            <video controls width="100%">
                <source src="<?=$app->Auction->getItemVideo($arRes['id'])?>" type="video/mp4"><!-- MP4 для Safari, IE9, iPhone, iPad, Android, и Windows Phone 7 -->
            </video>
            </div>
        </div>
    </div>
  </div>

</div>