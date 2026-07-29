<?php if ( $currentRoute->action == 'delete' ) $app->Widgets->delSettings( $currentRoute->id ); ?>
<?php if ( $currentRoute->action == 'deactivate' ) $app->Widgets->activateSets( $currentRoute->id, false ); ?>
<?php if ( $currentRoute->action == 'activate' ) $app->Widgets->activateSets( $currentRoute->id, true ); ?>
<?php $arRes = $app->Widgets->getSettings( $userSites['sites'] ); ?>

<div class="row">
  <div class="col-md-12">

    <div class="box box-primary">
      
      <div class="box-header with-border"><h3 class="box-title">Сайты и стили</h3></div>
      
      <?php if ( $app->User->isAdminUser($authUser) ) { ?>
      <div class="box-body">
        <div class="col-xs-12">
          <a href="/widgets/tuning/activate/all/" class="btn btn-success btn-flat" role="delete"><i class="fa fa-power-off" aria-hidden="true"></i> Включить все</a>
          <a href="/widgets/tuning/deactivate/all/" class="btn btn-danger btn-flat" role="delete"><i class="fa fa-power-off" aria-hidden="true"></i> Выключить все</a>
        </div>
      </div>
      <? } // is Admin ?>
      
      <div class="box-body">
        <table id="data-table-sites" class="table table-hover table-striped table-condensed dataTable">
          <thead>
            <tr>
              <th style="width: 10%">Сайт</th>
              <th style="width: 20%">Цветовая гамма</th>
              <th style="width: 20%">Поведение. Помощник<br /><small>Горячие предложения / АВН / Показ / Скрытие</small></th>
              <th style="width: 20%">Поведение. Обратный звонок<br /><small>Бездействие / Обратный отсчет / Ресет</small></th>
              <th style="width: 20%">Поведение. Генератор клиентов<br /><small>Первый показ (всего за сессию) / Второй показ (всего за сессию)</small></th>
              <th style="width: 10%"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $arRes as $item ) { ?>
            <tr>
              <td><a href="/widgets/sites/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
              <td>
                <span 
                  class="label label-default hint--top" 
                  style="background-color: <?=($item['settings']['color_bg'])?:$app->Widgets->getConf()->Defaults->ColorBg?> !important;" 
                  aria-label="Цвет бэкграунда">&nbsp;</span>
                &nbsp;
                <span 
                  class="label label-default hint--top"
                  style="background-color: <?=($item['settings']['color_fill'])?:$app->Widgets->getConf()->Defaults->ColorFill?> !important;" 
                  aria-label="Цвет иконок, заливок, ссылок">&nbsp;</span>
                &nbsp;
                <span 
                  class="label label-default hint--top" 
                  style="background-color:<?=($item['settings']['color_text'])?:$app->Widgets->getConf()->Defaults->ColorText?> !important;" 
                  aria-label="Цвет текста">&nbsp;</span>
                &nbsp;
                <span 
                  class="label label-default hint--top" 
                  style="background-color: <?=($item['settings']['color_button'])?:$app->Widgets->getConf()->Defaults->ColorButton?> !important;" 
                  aria-label="Цвет кнопки">&nbsp;</span>
                &nbsp;
                <span 
                  class="label label-default hint--top" 
                  style="background-color: <?=($item['settings']['color_button_text'])?:$app->Widgets->getConf()->Defaults->ColorButtonText?> !important;" 
                  aria-label="Цвет текста кнопки">&nbsp;</span>
                &nbsp;
                <span 
                  class="label label-default hint--top" 
                  style="background-color: <?=($item['settings']['color_error'])?:$app->Widgets->getConf()->Defaults->ColorError?> !important;" 
                  aria-label="Цвет ошибки">&nbsp;</span>
                &nbsp;
                <span 
                  class="label label-default hint--top" 
                  style="background-color: <?=($item['settings']['color_lightgray'])?:$app->Widgets->getConf()->Defaults->ColorLightgray?> !important;" 
                  aria-label="Светло-серый">&nbsp;</span>
                &nbsp;
                <span 
                  class="label label-default hint--top" 
                  style="background-color: <?=($item['settings']['color_middlegray'])?:$app->Widgets->getConf()->Defaults->ColorMiddlegray?> !important;" 
                  aria-label="Умеренно-серый">&nbsp;</span>
                &nbsp;
                <span 
                  class="label label-default hint--top" 
                  style="background-color: <?=($item['settings']['color_darkgray'])?:$app->Widgets->getConf()->Defaults->ColorDarkgray?> !important;" 
                  aria-label="Темно-серый">&nbsp;</span>
              </td>
              <td>
                <span class="label label-<?=($item['settings']['hp_use_hot'])?'success':'warning'?> hint--top" aria-label="Кнопка Горячие предложения: <?=($item['settings']['hp_use_hot'])?'Да':'Нет'?>">
                  <i class="fa fa-power-off" aria-hidden="true"></i>
                </span>
                 / 
                <span class="label label-<?=($item['settings']['hp_use_avail'])?'success':'warning'?> hint--top" aria-label="Кнопка АВН: <?=($item['settings']['hp_use_avail'])?'Да':'Нет'?>">
                <i class="fa fa-power-off" aria-hidden="true"></i>
                </span>
                 / 
                <?=($item['settings']['hp_show_interval'])?:$app->Widgets->getConf()->Defaults->HPShowTimeout?> мин / 
                <?=($item['settings']['hp_close_timeout'])?:$app->Widgets->getConf()->Defaults->HPCloseTimeout?> сек
              </td>
              <td>
                <?=($item['settings']['cb_idle_timeout'])?:$app->Widgets->getConf()->Defaults->CBIdleTimeout?> мин / 
                <?=($item['settings']['cb_timer_await'])?:$app->Widgets->getConf()->Defaults->CBTimerAwait?> сек / 
                <?=($item['settings']['result_timeout'])?:$app->Widgets->getConf()->Defaults->ResultTimeout?> сек
              </td>
              <td>
                <?=($item['settings']['lg_show_timeout'])?:$app->Widgets->getConf()->Defaults->LGShowTimeout?> сек (
                <?=($item['settings']['lg_show_count'])?:$app->Widgets->getConf()->Defaults->LGShowCount?> раз) / 
                <?=($item['settings']['lg_second_timeout'])?:$app->Widgets->getConf()->Defaults->LGShowSecond?> мин (
                <?=($item['settings']['lg_second_count'])?:$app->Widgets->getConf()->Defaults->LGShowCount2?> раз)
              </td>
              <td class="text-right">
                <a href="/widgets/tuning/<?=(($item['settings']['active']==1)?'de':'')?>activate/<?=$item['id']?>/" role="delete">
                  <span class="label label-<?=(($item['settings']['active']==1)?'success':'warning')?> hint--top" aria-label="<?=(($item['settings']['active']==1)?'А':'Неа')?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>
                </a>&nbsp;&nbsp;&nbsp;
                <a href="/widgets/stat/?site_ids[]=<?=$item['id']?>">
                  <span class="label label-default hint--top" aria-label="Статистика"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
                </a>&nbsp;&nbsp;&nbsp
                <a href="/widgets/sites/edit/<?=$item['id']?>/">
                  <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                </a>
                <a href="/widgets/tuning/delete/<?=$item['id']?>/" role="delete">
                  <span class="label label-danger hint--top" aria-label="Сбросить на умолчания"><i class="fa fa-remove" aria-hidden="true"></i></span>
                </a>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    
    </div>
    
  </div>
</div>