<?php if ( $currentRoute->action == 'delete' ) $app->Chat->delSet( $currentRoute->id ); ?>
<?php $arRes = $app->Chat->getSets() ?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?=$app->Chat->AppInfo()->ru_name?> <small>Настройки целей</small></h1>
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
          <div class="col-xs-2">
            <a href="/chat/goals/new/" class="btn btn-block btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить цель</a>
          </div>
        </div>
         
        <div class="box-body">
          
          <table id="data-table-goals" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 40%">Название</th>
                <th style="width: 40%">Идентификатор</th>
                <th style="width: 15%">&nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach( $arRes as $arItem ) { ?>
              <tr>
                <td><?=$arItem['id']?></td>
                <td><?=$arItem['ru_name']?></td>
                <td><?=$arItem['goal']?></td>
                <td>
                  <a href="/chat/goals/edit/<?=$arItem['id']?>/">
                    <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                  </a>
                  <a href="/chat/goals/delete/<?=$arItem['id']?>/" role="delete">
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



