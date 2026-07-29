<?php $arRes = $app->Expertbot->getDepartaments(); ?>

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Expertbot->AppInfo()->ru_name?> <small>Подразделения</small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Expertbot->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
    <?php if ($POSTRes) HTML::Error($POSTRes); ?>
    
    <div class="row">
      <div class="col-md-12">

      <div class="box box-primary">

        <div class="box-body">
          <div class="col-xs-12">
            <a href="/expertbot/departaments/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить направление</a>
          </div>
        </div>

        <div class="box-body">
          
          <?php if ($POSTRes) HTML::Error($POSTRes); ?>

          <table class="table table-striped table-bordered table-sm" id="data-table-expertbot-departaments">
            <thead>
              <tr>
                <th style="width: 10%">ID</th>
                <th style="width: 80%">Наименование</th>
                <th style="width: 10%"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ( $arRes as $item ) { ?>
              <tr>
                <td><a href="/expertbot/departaments/edit/<?=$item['id']?>/"><?= $item['id'];?></a></td>
                <td><a href="/expertbot/departaments/edit/<?=$item['id']?>/"><?= $item['name'];?></a></td>
                <td class="text-right">
                  <a href="/expertbot/departaments/edit/<?=$item['id']?>/">
                    <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
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
  <!-- /.content -->
  