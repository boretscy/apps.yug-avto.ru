<?php $arRes = $app->Parts->getSettings( $userSites['sites'] ); ?>

<section class="content-header">
  <h1><?=$app->Parts->AppInfo()->ru_name?> <small>Установки</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <?php if ($POSTRes) HTML::Error($POSTRes); ?>
  
  <div class="row">
    <div class="col-md-12">
      
      <div class="box box-info box-solid">
          <div class="box-header with-border">
            <h3 class="box-title">Установка модуля на сайт</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            <p>Тег для подключения модуля:
            <pre>&lt;div id="YApps_Parts"&gt;&lt;/div&gt;</pre>
            Этот тег нужно вставить в нужное место в html-код сайта.</p>
          </div><!-- /.box-body -->
        </div>
      
      <div class="box box-primary">
        
        <div class="box-header with-border"><h3 class="box-title">Сайты и стили</h3></div>
        
        <div class="box-body">
          <table id="data-table-settings" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 15%">Сайт</th>
                <th style="width: 20%">Получатели запросов</th>
                <th style="width: 10%">Основной цвет</th>
                <th style="width: 10%">Текст</th>
                <th style="width: 10%">Серый</th>
                <th style="width: 20%">Основной URL</th>
                <th style="width: 5%">CSS</th>
                <th style="width: 5%"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ( $arRes as $item ) { ?>
              <tr>
                <td><?=$item['id']?></td>
                <td><?=$item['ru_name']?></td>
                <td><?=$item['css']['recipients']?></td>
                <td>
                  <div class="color-palette-set">
                    <div class="bg-green color-palette" style="background-color: <?=(($item['css']['color'])?$item['css']['color']:'#00aeef')?> !important;"><span><?=(($item['css']['color'])?$item['css']['color']:'#00aeef')?></span></div>
                  </div>
                </td>
                <td>
                  <div class="color-palette-set">
                    <div class="bg-green color-palette" style="background-color: <?=(($item['css']['black'])?$item['css']['black']:'#2f3538')?> !important;"><span><?=(($item['css']['black'])?$item['css']['black']:'#2f3538')?></span></div>
                  </div>
                </td>
                <td>
                  <div class="color-palette-set">
                    <div class="bg-green color-palette" style="background-color: <?=(($item['css']['gray'])?$item['css']['gray']:'#d3d3d3')?> !important;"><span><?=(($item['css']['gray'])?$item['css']['gray']:'#d3d3d3')?></span></div>
                  </div>
                </td>
                <td><a href="<?=$item['css']['default_url']?>" target="_blank"><?=$item['css']['default_url']?></a></td>
                <td>
                  <?php if ( $item['css']['css'] ) { ?>
                  <span class="label label-info hint--top" aria-label="Дополнительные стили"><i class="fa check-square-o" aria-hidden="true"></i></span>
                  <?php } ?>
                </td>
                <td class="text-right">
                  <span class="label label-<?=($item['settings']['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['settings']['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                  <a href="/parts/settings/edit/<?=$item['id']?>/">
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
        