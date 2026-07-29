<?php if ( $currentRoute->action == 'delete' ) $app->Widgets->delWidget( $currentRoute->id ); ?>
<?php if ( $currentRoute->action == 'copy' ) $app->Widgets->copyWidget( $currentRoute->id ); ?>
<?php $arRes = $app->Widgets->getWidgetsByType( 8 ); ?>
<?php $wType = (object)$app->Widgets->getTypeById( 8 ); ?>

<section class="content-header">
  <h1><?=$app->Widgets->AppInfo()->ru_name?></h1>
</section>

<!-- Main content -->
<section class="content">
<?php if ( $app->Widgets->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
  <div class="row">
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-<?=$wType->icon?>"></i> <?=$wType->ru_name?></h3></div>
        
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/widgets/ms/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить виджет</a>
          </div>
        </div>
        
        <div class="box-body">
          
          <?php if ($POSTRes) HTML::Error($POSTRes); ?>
          
          <table id="data-table-ms" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 20%">Название</th>
                <th style="width: 15%">Сайт</th>
                <?php foreach ( $messengers = $app->Widgets->getMessengers() as $messenger ) { ?>
                <th style="width: <?php echo 45 / count($messengers);?>%"><?=$messenger['ru_name']?></th>
                <?php } ?>
                <th style="width: 15%"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ( $arRes as $item ) { ?>
              <?php $mess = $app->Widgets->getMessengersByWidget( $item['id'] ); ?>
              <tr>
                <td><a href="/widgets/ms/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                <td><a href="/widgets/ms/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
                <td><?=$app->getSite($item['site_id'])['ru_name']?></td>
                <?php foreach ( $messengers as $messenger ) { ?>
                <td><?=(($messenger['id']==1)?Helper::formatPhoneOut($mess[$messenger['id']]['value']):$mess[$messenger['id']]['value'])?></th>
                <?php } ?>
                <td style="text-align: right">
                  <span class="label label-<?=($item['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                  <a href="/widgets/stat/?widget_ids[]=<?=$item['id']?>">
                    <span class="label label-default hint--top" aria-label="Статистика"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
                  </a>&nbsp;&nbsp;&nbsp;
                  <a href="/widgets/ms/edit/<?=$item['id']?>/">
                    <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                  </a>
                  <a href="/widgets/ms/copy/<?=$item['id']?>/">
                    <span class="label label-info hint--top" aria-label="Скопировать"><i class="fa fa-clone" aria-hidden="true"></i></span>
                  </a>
                  <a href="/widgets/ms/delete/<?=$item['id']?>/" role="delete">
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
