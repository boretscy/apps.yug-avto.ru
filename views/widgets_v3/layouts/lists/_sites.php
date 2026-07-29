<?php if ( $currentRoute->action == 'delete' ) $app->Widgets3->delSettings( $currentRoute->id ); ?>
<?php if ( $currentRoute->action == 'deactivate' ) $app->Widgets3->activateSets( $currentRoute->id, false ); ?>
<?php if ( $currentRoute->action == 'activate' ) $app->Widgets3->activateSets( $currentRoute->id, true ); ?>
<?php $arRes = $app->Widgets3->getSettings( $userSites['sites'] ); ?>

<div class="row">
  <div class="col-md-12">

    <div class="box box-primary">
      
      <div class="box-header with-border"><h3 class="box-title">Сайты и стили</h3></div>
      
      <?php if ( $app->User->isAdminUser($authUser) ) { ?>
      <div class="box-body">
        <div class="col-xs-12">
          <a href="/widgets_v3/tuning/activate/all/" class="btn btn-success btn-flat" role="delete"><i class="fa fa-power-off" aria-hidden="true"></i> Включить все</a>
          <a href="/widgets_v3/tuning/deactivate/all/" class="btn btn-danger btn-flat" role="delete"><i class="fa fa-power-off" aria-hidden="true"></i> Выключить все</a>
        </div>
      </div>
      <? } // is Admin ?>
      
      <div class="box-body">
        <table id="data-table-sites" class="table table-hover table-striped table-condensed dataTable">
          <thead>
            <tr>
              <th style="width: 10%">Сайт</th>
              <th style="width: 10%"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $arRes as $item ) { ?>
            <tr>
              <td><a href="/widgets_v3/sites/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
              <td class="text-right">
                <a href="/widgets_v3/tuning/<?=(($item['settings']['active']==1)?'de':'')?>activate/<?=$item['id']?>/" role="delete">
                  <span class="label label-<?=(($item['settings']['active']==1)?'success':'warning')?> hint--top" aria-label="<?=(($item['settings']['active']==1)?'А':'Неа')?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>
                </a>&nbsp;&nbsp;&nbsp;
                <a href="/widgets_v3/stat/?site_ids[]=<?=$item['id']?>">
                  <span class="label label-default hint--top" aria-label="Статистика"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
                </a>&nbsp;&nbsp;&nbsp
                <a href="/widgets_v3/sites/edit/<?=$item['id']?>/">
                  <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                </a>
                <a href="/widgets_v3/tuning/delete/<?=$item['id']?>/" role="delete">
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