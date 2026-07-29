<?php if ( $currentRoute->action == 'delete' ) $app->Lands->delLand( $currentRoute->id ); ?>
<?php $arRes = $app->Lands->getUserLands( $userSites['sites_ids'] ) ?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?=$app->Lands->AppInfo()->ru_name?> <small>Настройки приложения</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <?php if ($POSTRes) HTML::Error($POSTRes); ?>
  
  <div class="row">
    
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border">
          
          <h3 class="box-title">Посадочные страницы</h3>
            
          <!-- /.box-tools -->
        </div>
         
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/lands/settings/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
          </div>
        </div>
         
        <div class="box-body">
          
          <table id="data-table-lands" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 20%">Название</th>
                <th style="width: 15%">Сайт</th>
                <th style="width: 25%">URL</th>
                <th style="width: 20%">Виджеты</th>
                <th style="width: 15%">&nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach( $arRes as $item ) { ?>
              <tr>
                <td><a href="/lands/settings/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                <td><a href="/lands/settings/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
                <td><?=$app->getSite($item['site_id'])['ru_name']?></td>
                <td><?=$item['url']?></td>
                <td>
                  <span class="label label-<?=(($item['use_lg']==1)?'success':'warning')?> hint--top" aria-label="Генератор клиентов"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;
                  <span class="label label-<?=(($item['use_cb']==1)?'success':'warning')?> hint--top" aria-label="Обратный звонок"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;
                  <span class="label label-<?=(($item['use_nv']==1)?'success':'warning')?> hint--top" aria-label="Маршруты в ДЦ"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;
                  <span class="label label-<?=(($item['use_ch']==1)?'success':'warning')?> hint--top" aria-label="Онлайн-контультант"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;
                  <span class="label label-<?=(($item['use_av']==1)?'success':'warning')?> hint--top" aria-label="АВН"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;
                  <span class="label label-<?=(($item['use_ht']==1)?'success':'warning')?> hint--top" aria-label="Горячие предложения"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;
                  <span class="label label-<?=(($item['use_qz']==1)?'success':'warning')?> hint--top" aria-label="Квиз"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;
                </td>
                <td class="text-right">
                  <span class="label label-<?=(($item['active']==1)?'success':'warning')?> hint--top" aria-label="<?=(($item['active']==1)?'А':'Неа')?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                  <a href="/lands/stat/?land_ids[]=<?=$item['id']?>">
                    <span class="label label-default hint--top" aria-label="Статистика"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
                  </a>&nbsp;&nbsp;&nbsp
                  <a href="/lands/settings/edit/<?=$item['id']?>/">
                    <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                  </a>
                  <a href="/lands/settings/delete/<?=$item['id']?>/" role="delete">
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



