<?php if ( $currentRoute->action == 'delete' ) $app->Widgets3->delWidget( $currentRoute->id ); ?>
<?php $arRes = $app->Widgets3->getWidgetsByType( 2 ); ?>

<section class="content-header">
  <h1><?=$app->Widgets3->AppInfo()->ru_name?></h1>
</section>

<!-- Main content -->
<section class="content">
<?php if ( $app->Widgets3->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
  <div class="row">
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border"><h3 class="box-title"><?=$app->Widgets3->getTypeById(2)['ru_name']?></h3></div>
        
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/widgets_v3/lg/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить виджет</a>
          </div>
        </div>
        
        <div class="box-body">
          
          <?php if ($POSTRes) HTML::Error($POSTRes); ?>
          
          <table id="data-table-lg" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 20%">Название</th>
                <th style="width: 15%">Сайт</th>
                <th style="width: 35%">Страницы</th>
                <th style="width: 10%">Таймер</th>
                <th style="width: 15%"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ( $arRes as $item ) { ?>
              <tr>
                <td><a href="/widgets_v3/lg/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                <td><a href="/widgets_v3/lg/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
                <td><?=$app->getSite($item['site_id'])['ru_name']?></td>
                <td style="overflow: hidden;"><?=implode(", ", $app->Widgets3->getUrls($item['id']))?></td>
                <td>
				  <span class="label label-<?=($item['lg_timer_use'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
				  <?=date('d.m.Y H:i', $item['lg_timer'])?>
                </td>
                <td style="text-align: right">
                  <span class="label label-<?=($item['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                  <a href="/widgets_v3/stat/?widget_ids[]=<?=$item['id']?>">
                    <span class="label label-default hint--top" aria-label="Статистика"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
                  </a>&nbsp;&nbsp;&nbsp
                  <a href="/widgets_v3/lg/edit/<?=$item['id']?>/">
                    <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                  </a>
                  <a href="/widgets_v3/lg/delete/<?=$item['id']?>/" role="delete">
                    <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                  </a>
                </td>
              </tr>
              <?php } // foreach ?>
            </tbody>
          </table>
          
        </div>
      
      </div>
      
    </div>
  </div>

</section>
