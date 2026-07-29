<?php if ( $currentRoute->action == 'delete' ) $app->Chat->delSet( $currentRoute->id ); ?>
<?php if ( $currentRoute->action == 'deactivate' ) $app->Chat->activateSets( $currentRoute->id, false ); ?>
<?php if ( $currentRoute->action == 'activate' ) $app->Chat->activateSets( $currentRoute->id, true ); ?>
<?php $arRes = $app->Chat->getSets() ?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?=$app->Chat->AppInfo()->ru_name?> <small>Настройки приложения</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <?php if ($POSTRes) HTML::Error($POSTRes); ?>
  
  <div class="row">
    
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border">
          
          <h3 class="box-title">Сайты и идентификаторы</h3>
            
          <!-- /.box-tools -->
        </div>
         
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/chat/settings/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить сайт</a>
            <a href="/chat/settings/activate/all/" class="btn btn-success btn-flat" role="delete"><i class="fa fa-power-off" aria-hidden="true"></i> Включить все</a>
            <a href="/chat/settings/deactivate/all/" class="btn btn-danger btn-flat" role="delete"><i class="fa fa-power-off" aria-hidden="true"></i> Выключить все</a>
          </div>
        </div>
         
        <div class="box-body">
          
          <table id="data-table-sites" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 40%">Сайт</th>
                <th style="width: 40%">API Key</th>
                <th style="width: 15%">&nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach( $arRes as $item ) { ?>
              <tr>
                <td><a href="/chat/settings/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                <td><a href="/chat/settings/edit/<?=$item['id']?>/"><?=$item['site']['ru_name']?></a></td>
                <td><?=$item['token']?></td>
                <td class="text-right">
                  <a href="/chat/settings/<?=(($item['active']==1)?'de':'')?>activate/<?=$item['id']?>/" role="delete">
                  	<span class="label label-<?=(($item['active']==1)?'success':'warning')?> hint--top" aria-label="<?=(($item['active']==1)?'А':'Неа')?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>
                  </a>&nbsp;&nbsp;&nbsp;
                  <a href="/chat/stat/?site_ids[]=<?=$item['site_id']?>">
                    <span class="label label-default hint--top" aria-label="Статистика"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
                  </a>&nbsp;&nbsp;&nbsp
                  <a href="/chat/settings/edit/<?=$item['id']?>/">
                    <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                  </a>
                  <a href="/chat/settings/delete/<?=$item['id']?>/" role="delete">
                    <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                  </a>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
          
        </div>
        <!-- /.box-body -->
      </div>
      
    </div>
    
  </div>

</section>
<!-- /.content -->



