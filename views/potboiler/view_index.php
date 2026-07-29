<?php $page = ($_GET['page']) ? $_GET['page'] : 1 ?>
<?php $Sets = $app->Potboiler->getSettings(); ?>
<?php $arRes = $app->Potboiler->getCompleteItems( $page ); ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Potboiler->AppInfo()->ru_name?> <small>Домашняя страница</small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ($POSTRes) HTML::Error($POSTRes); ?>
    
    <div class="row">
      
      <div class="col-md-12">
        
        <div class="box box-primary">
            
          <div class="box-header with-border">
            <h3 class="box-title">Управление</h3>
          </div>
          
          <div class="col-xs-12">
            
            <div class="info-box bg-<?=(($Sets->status > 0)?'aqua':'gray')?>">
              <span class="info-box-icon"><i class="fa fa-bolt"></i></span>
  
              <div class="info-box-content">
                <span class="info-box-text">Парсинг объявлений <?=(($Sets->status>0)?'активен... Статус: ':'завершен.')?> <?=(($Sets->status==1)?'Накопление объявлений':'')?><?=(($Sets->status==2)?'Парсинг телефонов':'')?></span>
                <span class="info-box-number">Всего <?=number_format((int)$app->Potboiler->getCountItems(), 0, '.', ' ')?> обявлений</span>
  
                <div class="progress">
                  <div class="progress-bar" style="width: <?=number_format((float)$arRes['percent'], 2, '.', ' ')?>%"></div>
                </div>
                    <span class="progress-description">
                      <strong><?=number_format((float)$arRes['percent'], 2, '.', ' ')?>%</strong> объявлений обработано
                    </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            
          </div>
          
          <div class="col-xs-3">
            <?php if ( $Sets->status == 0 ) { ?>
            <a href="/potboiler/settings/start/" class="btn btn-block btn-success btn-flat"><i class="fa fa-play-circle-o" aria-hidden="true"></i> Старт</a>
            <?php } else { ?>
            <a href="/potboiler/settings/stop/" class="btn btn-block btn-danger btn-flat"><i class="fa fa-stop-circle-o" aria-hidden="true"></i> Стоп</a>
            <?php } ?>
          </div>
          <div class="col-xs-3"><a href="/potboiler/settings/reset/" class="btn btn-block btn-info btn-flat"><i class="fa fa-refresh" aria-hidden="true"></i> Сбросить текущую сессию</a></div>
          <div class="col-xs-3"><a href="/potboiler/settings/error/" class="btn btn-block btn-default btn-flat"><i class="fa fa-times" aria-hidden="true"></i> Удалить ошибки</a></div>
          <div class="col-xs-3"><a href="/potboiler/settings/clear/" class="btn btn-block btn-default btn-flat"><i class="fa fa-times" aria-hidden="true"></i> Очистить все объявления</a></div>
        
          <div class="box-body"></div>
          
          <div class="box-body">
            
            <table id="data-table-potboiler" class="table table-hover table-striped table-condensed dataTable">
              <thead>
                <tr>
                  <th style="width: 15%">ID Объявления</th>
                  <th style="width: 30%">Наименование</th>
                  <th style="width: 10%">Цена, <i class="fa fa-rub" aria-hidden="true"></i></th>
                  <th style="width: 15%">Дата размещения</th>
                  <th style="width: 15%">ID Пользователя</th>
                  <th style="width: 15%">Телефон</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach( $arRes['items'] as $arItem ) { ?>
                <tr>
                  <td><?=$arItem['item_id']?></td>
                  <td><a href="<?=$arItem['item_url']?>" target="_blank"><?=$arItem['item_name']?></a></td>
                  <td><?=number_format((float)$arItem['item_price'], 2, '.', ' ')?></td>
                  <td><?=date('d.m.Y H:i', $arItem['item_timestamp'])?></td>
                  <td><a href="<?=$arItem['user_url']?>" target="_blank"><?=$arItem['user_id']?></a></td>
                  <td><?=$arItem['user_phone']?></td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
            
          </div>
          
          <?php $p =  intdiv($app->Potboiler->getCountItems(), 1000) + 1; ?>
          
          <div class="box-body">
            <div class="btn-group">
              <?php for ( $i = 1; $i <= $p; $i++ ) { ?>
              <a href="/potboiler///?page=<?=$i?>" class="btn btn-<?=(((int)$page == $i)?'info':'default')?> btn-sm"><?=$i?></a>
              <? } ?>
            </div>
          </div>
          
        </div>
        
      </div>
      
    </div>
  
  </section>
  <!-- /.content -->
  
</div>