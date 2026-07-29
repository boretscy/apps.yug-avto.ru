<?php if ( $currentRoute->action == 'delete' ) $app->Apps->delMenuPoint( $currentRoute->id ); ?>
<?php $arRes = $app->Apps->getMenuPoints(); ?>
<!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Настройки <small>Пункты меню</small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
      
      <div class="row">
        
        <div class="col-md-12">
          
          <div class="box box-primary">
            
            <div class="box-header with-border">
              
              <h3 class="box-title">Пункты меню</h3>
                
              <!-- /.box-tools -->
            </div>
            
			<div class="box-body">
		      <div class="col-xs-12">
			    <a href="/admin/menu/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
			  </div>
			</div>
            
            <div class="box-body">
              
              <?php if ( $GETRes ) HTML::Error( $GETRes ); ?>
              
              <table id="data-table-menu" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                  <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 20%">Приложение</th>
                    <th style="width: 20%">Наименование</th>
                    <th style="width: 10%">Url ключ</th>
                    <th style="width: 10%">Иконка</th>
                    <th style="width: 10%">Уровень доступа</th>
                    <th style="width: 10%">Сорт</th>
                    <th style="width: 10%">&nbsp;</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach( $arRes as $item ) { ?>
                  <tr>
                    <td><a href="/admin/menu/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                    <td><a href="/admin/menu/edit/<?=$item['id']?>/"><?=$app->Apps->getAppById($item['app_id'])['ru_name']?></a></td>
                    <td><a href="/admin/menu/edit/<?=$item['id']?>/"><?=$item['name']?></a></td>
                    <td><?=$item['url_key']?></td>
                    <td><i class="fa fa-<?=$item['icon']?>"></i></td>
                    <td><?=(($app->User->getRole($item['role_id'])->ru_name)?:'---')?></td>
                    <td><?=$item['sort']?></td>
                    <td class="text-right">
                      <a href="/admin/menu/edit/<?=$item['id']?>/">
                        <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                      </a>
                      <a href="/admin/menu/delete/<?=$item['id']?>/" role="delete">
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