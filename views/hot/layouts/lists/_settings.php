<?php $arRes = $app->Hot->getSettings( $userSites['sites'] ); ?>

<section class="content-header">
  <h1><?=$app->Hot->AppInfo()->ru_name?> <small>Установки</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <?php if ($POSTRes) HTML::Error($POSTRes); ?>
  
  <div class="row">
    <div class="col-md-12">
      
      <div class="box box-info box-solid collapsed-box">
        <div class="box-header with-border">
          <h3 class="box-title">Установка модуля на сайт</h3>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
          </div>
        </div><!-- /.box-header -->
        <div class="box-body">
          <p>Тег для подключения модуля:
          <pre>&lt;div id="YApps_Hot"&gt;&lt;/div&gt;</pre>
          Этот тег нужно вставить в нужное место в html-код сайта.</p>
          <p>Отображаемые модели или дц можно комбинировать спомощью атрибутов тега, например:</p>
          <pre>&lt;div id="YApps_Hot" <strong>data-dc="25"</strong>&gt;&lt;/div&gt;</pre>
          <pre>&lt;div id="YApps_Hot" <strong>data-dc="24,25"</strong>&gt;&lt;/div&gt;</pre>
          <pre>&lt;div id="YApps_Hot" <strong>data-model="9"</strong>&gt;&lt;/div&gt;</pre>
          <pre>&lt;div id="YApps_Hot" <strong>data-model="9,11,15"</strong>&gt;&lt;/div&gt;</pre>
          <pre>&lt;div id="YApps_Hot" <strong>data-dc="25" data-model="9,11,15"</strong>&gt;&lt;/div&gt;</pre>
          <p>Так же возможно использование вместо тега div, тегов <strong>span, p, input(hidden)</strong> и т.п.</p>
          
          <p>Есть возможность автоматически скрывать ненужные автомобили по ссылке (комбинировать нельзя):</p>
          <pre>https://site/hot/#/model/Polo</pre>
          <pre>https://site/hot/#/dc/volkswagen-yug-avto-krasnodar_pkw</pre>
        </div><!-- /.box-body -->
      </div>
      
      <div class="box box-primary">
        
        <div class="box-header with-border"><h3 class="box-title">Сайты и стили</h3></div>
        
        <div class="box-body">
          <table id="data-table-sets" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 15%">Сайт</th>
                <th style="width: 15%">Цветовая гамма</th>
                <th style="width: 60%">Текст кнопок</th>
                <th style="width: 10%"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ( $arRes as $item ) { ?>
              <tr>
                <td><a href="/hot/settings/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
                <td>
                  <span 
                  	class="label label-default hint--top" 
                    style="background-color: <?=($item['settings']['color_dark'])?:$app->Hot->getConf()->Defaults['ColorDark']?> !important;" 
                    aria-label="Текст">&nbsp;</span>
                  &nbsp;
                  <span 
                  	class="label label-default hint--top"
                    style="background-color: <?=($item['settings']['color_gray'])?:$app->Hot->getConf()->Defaults['ColorGray']?> !important;" 
                    aria-label="Ссылки и подложки">&nbsp;</span>
                  &nbsp;
                  <span 
                  	class="label label-default hint--top" 
                    style="background-color:<?=($item['settings']['color_lightgray'])?:$app->Hot->getConf()->Defaults['ColorLightgray']?> !important;" 
                    aria-label="Кнопки">&nbsp;</span>
                  &nbsp;
                  <span 
                  	class="label label-default hint--top" 
                    style="background-color: <?=($item['settings']['color_light'])?:$app->Hot->getConf()->Defaults['ColorLight']?> !important;" 
                    aria-label="Светлая подложка">&nbsp;</span>
                  &nbsp;
                  <span 
                  	class="label label-default hint--top" 
                    style="background-color: <?=($item['settings']['color_error'])?:$app->Hot->getConf()->Defaults['ColorError']?> !important;" 
                    aria-label="Цвет ошибки">&nbsp;</span>
                </td>
                <td>
                	<?=($item['settings']['button_shorttext'])?$item['settings']['button_shorttext']:$app->Hot->getConf()->Defaults['ButtonShorttext']?> | 
                    <?=($item['settings']['button_longtext'])?$item['settings']['button_longtext']:$app->Hot->getConf()->Defaults['ButtonLongtext']?>
                </td>
                <td class="text-right">
                    <span class="label label-<?=($item['settings']['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['settings']['active'])?'А':'Не а'?>ктивен">
                        <i class="fa fa-power-off" aria-hidden="true"></i>
                    </span>&nbsp;&nbsp;
                    <span class="label label-<?=($item['settings']['use_slider'])?'success':'default'?> hint--top" aria-label="Слайдер">
                        <i class="fa fa-files-o" aria-hidden="true"></i>
                    </span>
                    <a href="/hot/stat/?site_ids[]=<?=$item['id']?>">
                        <span class="label label-default hint--top" aria-label="Статистика"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
                    </a>
                    <a href="/hot/settings/edit/<?=$item['id']?>/">
                        <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
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
</section>
        