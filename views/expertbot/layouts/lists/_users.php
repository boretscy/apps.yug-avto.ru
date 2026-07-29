<?php $arRes = $app->Expertbot->getDBUsers(); ?>

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Expertbot->AppInfo()->ru_name?> <small>Менеджеры</small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Expertbot->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
    <?php if ($POSTRes) HTML::Error($POSTRes); ?>
    
    <div class="row">
      <div class="col-md-12">

      <div class="box box-primary">

        <div class="box-body">
            
          <table class="table table-striped table-bordered table-sm" id="data-table-expertbot-users">
            <thead>
              <tr>
                <th style="width: 5%">ID</th>
                <th style="width: 10%">ID портала</th>
                <th style="width: 15%">Дилерский центр</th>
                <th style="width: 10%">ID чата</th>
                <th style="width: 17%">ФИО</th>
                <th style="width: 3%"></th>
                <th style="width: 10%">Телефон</th>
                <th style="width: 10%">Направление</th>
                <th style="width: 10%">Подразделение</th>
                <th style="width: 5%">Шаг</th>
                <th style="width: 5%"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ( $arRes as $item ) { ?>
              <tr>
                <td><a href="/expertbot/users/edit/<?=$item['id']?>/"><?= $item['id'];?></a></td>
                <td><?= $item['ext_id'];?></td>
                <td><?= $item['dealership'];?></td>
                <td><?= $item['chat_id'];?></td>
                <td><a href="/expertbot/users/edit/<?=$item['id']?>/"><?= $item['name'];?></a></td>
                <td>
                  <?php if ( $item['is_admin'] ) { ?>
                  <i class="fa fa-user-secret" aria-hidden="true"></i>
                  <?php } ?>
                </td>
                <td><?= (($item['phone'])?Helper::formatPhoneOut($item['phone']):'');?></td>
                <td><?= $item['type'];?></td>
                <td><?= $item['departament'];?></td>
                <td><?= $item['step'];?></td>
                <td class="text-right">
                  <a href="/expertbot/users/edit/<?=$item['id']?>/">
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
  