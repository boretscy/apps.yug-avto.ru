<?php if ( $currentRoute->action == 'delete' ) $app->HumanResourses->delSet( $currentRoute->id ); ?>
<?php $arRes = $app->HumanResourses->getSettings( $userSites['sites'] ); ?>

<section class="content-header">
  <h1><?=$app->HumanResourses->AppInfo()->ru_name?> <small>Установки</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <?php if ($POSTRes) HTML::Error($POSTRes); ?>
  
  <div class="row">
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border"><h3 class="box-title">Дилерские центры</h3></div>
        
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/humanresourses/settings/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
          </div>
        </div>
        
        <div class="box-body">
          <table id="data-table-sets" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 15%">ID</th>
                <th style="width: 75%">Дилерский центр</th>
                <th style="width: 10%"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ( $arRes as $item ) { ?>
              <tr>
                <td><?=$item['id']?></td>
                <td><?=$app->YApps_GetDC($item['dc_id'])['ru_name']?></td>
                <td class="text-right">
                  <span class="label label-<?=($item['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                  <a href="/humanresourses/settings/edit/<?=$item['dc_id']?>/">
                    <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                  </a>
                  <a href="/humanresourses/settings/delete/<?=$item['dc_id']?>/" role="delete">
                    <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
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
        