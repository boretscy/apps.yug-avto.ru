<?php if ( $currentRoute->action == 'delete' && $app->User->isAdminUser($authUser) ) $app->News->delete( $currentRoute->id ); ?>
<?php $arRes = $app->News->getAll(); ?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Новости и релизы<small>Список</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <?php if ($POSTRes) HTML::Error($POSTRes); ?>
  
  <div class="row">
    
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border">
          <h3 class="box-title">Новости</h3>
        </div>
        
        <?php if ( $app->User->isAdminUser($authUser) ) { ?>
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/root/news/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить новость</a>
          </div>
        </div>
        <?php } ?>
        
        <div class="box-body">
          
          <table id="data-table-news" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 15%">Приложение</th>
                <th style="width: 60%">Заголовок</th>
                <th style="width: 10%">Дата</th>
                <th style="width: 10%"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach( $arRes as $item ) { ?>
              <tr>
                <td><?=$item['id']?></td>
                <td><?=$app->Apps->getAppById($item['app_id'])['ru_name']?></td>
                <td><?=$item['title']?></td>
                <td><?=date('Y-m-d H:i', $item['timestamp'])?></td>
                <td class="text-right">
                  <a href="/root/news/edit/<?=$item['id']?>/">
                    <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                  </a>
                  <a href="/root/news/delete/<?=$item['id']?>/" role="delete">
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
